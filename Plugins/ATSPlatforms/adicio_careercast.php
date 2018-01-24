<?php
/**
 * Copyright 2014-18 Bryan Selner
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may
 * not use this file except in compliance with the License. You may obtain
 * a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations
 * under the License.
 */

use http\Url;


/**
 * Class AbstractAdicio
 */
abstract class AbstractAdicio extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
	protected $PaginationType = C__PAGINATION_PAGE_VIA_URL;
	protected $JobListingsPerPage = 50;
	protected $LastKnownSiteLayout = null;
	protected $CountryCodes = array("US");

	// BUGBUG: setting "search job title only" seems to not find jobs with just one word in the title.  "Pharmacy Intern" does not come back for "intern" like it should.  Therefore not setting the kwsJobTitleOnly=true flag.
	//
	protected $strBaseURLPathSection = "/jobs/results/keyword/***KEYWORDS***?view=List_Detail&SearchNetworks=US&networkView=national&location=***LOCATION***&radius=50&sort=PostDate+desc%2C+Priority+desc%2C+score+desc&rows=50&page=***PAGE_NUMBER***";
	protected $additionalLoadDelaySeconds = 2;
	protected $strBaseURLPathSuffix = "";
	protected $SearchUrlFormat = null;
	protected $LocationType = 'location-city-comma-statecode';
	protected $nTotalJobs = null;
	protected $lastResponseData = null;
	/**
	 * @var \JobScooper\DataAccess\UserSearchSiteRun|null
	 */
	protected $currentJsonSearchDetails = null;

	protected $arrBaseListingTagSetupNationalSearch = array(
		'TotalPostCount' => array('selector' => 'span#retCount span'),  # BUGBUG:  need this empty array so that the parent class doesn't auto-set to C__JOB_ITEMCOUNT_NOTAPPLICABLE__
		'NoPostsFound' => array('selector' => 'div#aiSearchResultsSuccess h2', 'return_attribute' => 'text', 'return_value_callback' => "matchesNoResultsPattern", 'callback_parameter' => 'Oops'),
		'JobPostItem' => array('selector' => 'div.aiResultsWrapper'),
		'Title' => array('selector' => 'div.aiResultTitle h3 a'),
		'Url' => array('selector' => 'div.aiResultTitle h3 a', 'return_attribute' => 'href'),
		'JobSitePostId' => array('selector' => 'div.aiResultsMainDiv', 'return_attribute' => 'id', 'return_value_regex' =>  '/aiResultsMainDiv(.*)/'),
		'Company' => array('selector' => 'li.aiResultsCompanyName'),
		'Location' => array('selector' => 'span.aiResultsLocationSpan'),
		'PostedAt' => array('selector' => 'div.aiDescriptionPod ul li', 'index' => 2),
		'Category' => array('selector' => 'div.aiDescriptionPod ul li', 'index' => 3)
	);

	protected $arrBaseListingTagSetupJobsResponsive = array(
		'TotalPostCount' => array('selector' => 'h1#search-title-holder', 'return_value_regex' => '/(.*) [Jj]obs/'),
		'JobPostItem' => array('selector' => 'div.arJobPodWrap'),
		'Title' => array('selector' => 'div.arJobTitle h3 a'),
		'Url' => array('selector' => 'div.arJobTitle h3 a', 'return_attribute' => 'href'),
		'JobSitePostId' => array('selector' => 'div.arSaveJob a', 'return_attribute' => 'data-jobid'),
		'Company' => array('selector' => 'div.arJobCoLink'),
		'Location' => array('selector' => 'div.arJobCoLoc'),
		'PostedAt' => array('selector' => 'div.arJobPostDate span')
	);

	protected $_layout = null;

	/**
	 * AbstractAdicio constructor.
	 * @throws \Exception
	 */
	function __construct()
	{
		$fDoNotRemoveSetup = false;
		if(!empty($this->arrListingTagSetup))
			$fDoNotRemoveSetup = true;
		else
			$this->arrListingTagSetup = $this->arrBaseListingTagSetupNationalSearch;

		$this->JobPostingBaseUrl = $this->childSiteURLBase;
		$this->SearchUrlFormat = $this->childSiteURLBase . $this->strBaseURLPathSection . $this->strBaseURLPathSuffix;

		parent::__construct();

		if($fDoNotRemoveSetup !== true)
			$this->arrListingTagSetup = array();
	}

	/**
	 * @param $apiUri
	 * @param $hostPageUri
	 *
	 * @throws \Exception
	 * @return \stdClass
	 */
	private function getJsonResultsPage($apiUri, $hostPageUri=null)
	{
		LogMessage("Downloading JSON listing data from {$apiUri} for " . $this->getJobSiteKey() . "...");
		if(empty($hostPageUri))
			$hostPageUri = $this->getActiveWebdriver()->getCurrentURL();
		if(empty($hostPageUri) && !empty($this->currentJsonSearchDetails))
			$hostPageUri = $this->currentJsonSearchDetails->nextResultsPageUrl;

		$ret = array();
		$respdata = $this->getJsonApiResult($apiUri, $this->currentJsonSearchDetails, $hostPageUri);
		if(!empty($respdata))
		{
			$this->lastResponseData = $respdata;
			try
			{
				$ret['count'] = $respdata->Total;
				$ret['jobs'] = $respdata->Jobs;

			}
			catch(Exception $ex)
			{
				throw new Exception($respdata->error);
			}
		}

		return $respdata;
	}

	/**
	 * @param $jobs
	 *
	 * @return array
	 */
	private function _parseJsonJobs($jobs)
	{
		$jobsite = $this->getJobSiteKey();
		$ret = array();
		foreach($jobs as $job)
		{
			$ret[$job->Id] = array(
				'JobSiteKey' => $jobsite,
				'JobSitePostId' => "{$job->AdId}-{$job->Id}",
				'Company' => $job->Company,
				'Title' =>  $job->JobTitle,
				'Url' => $job->Url,
				'Location' => $job->FormattedCityStateCountry,
				'Category' => is_array($job->CategoryDisplay) ? join(" | ", $job->CategoryDisplay) : null,
				'PostedAt' => $job->PostDate
			);
		}

		LogMessage("Loaded " . count($ret) . " jobs from JSON with " . count($jobs));
		return $ret;
	}

	/**
	 * @param \JobScooper\DataAccess\UserSearchSiteRun $searchDetails
	 * @param null                                     $nOffset
	 *
	 * @return string
	 */
	private function _getJsonSearchUrl(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails, $nOffset=null)
	{
		if(!empty($searchDetails->nextResultsPageUrl))
			$jsonUrl = $searchDetails->nextResultsPageUrl. "&format=json";
		else
			$jsonUrl = $searchDetails->getSearchStartUrl() . "&format=json";
		return $jsonUrl;
	}
	/**
	 * @param $searchDetails
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	function doFirstPageLoad(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails)
	{
		$this->nTotalJobs = 0;
		$this->lastResponseData = 0;

		$this->currentJsonSearchDetails = $searchDetails;
		$hostPage = $searchDetails->getSearchStartUrl();
		LogMessage("Loading first page for {$this->getJobSiteKey()} from {$hostPage}");
		$jsonUrl = $this->_getJsonSearchUrl($searchDetails);
		$retData = null;
		try {
			$retData = $this->getJsonResultsPage($jsonUrl, $hostPage);
			$this->nTotalJobs = $retData->Total;
			$this->lastResponseData = $retData;
		}
		catch (Exception $ex) {
			//
		}

		if(is_null($this->nTotalJobs))
		{
			$this->setLayoutIfNeeded($searchDetails);
		}
	}

	/**
	 * @throws \Exception
	 */
	protected function setLayoutIfNeeded()
	{
		if (!empty($this->LastKnownSiteLayout)) {
			$this->setAdicioPageLayout($this->LastKnownSiteLayout);
		} else {
			$template = $this->_determinePageLayout();

			$this->setAdicioPageLayout($template);
			LogDebug("Adicio Template for " . get_class($this) . " with url '{$this->SearchUrlFormat}: " . PHP_EOL . "$template = {$template}," . PHP_EOL . "layout = {$this->_layout},  " . PHP_EOL . "template = {$template},  " . PHP_EOL . "id = {$id}, " . PHP_EOL . "matches = " . getArrayDebugOutput($matches));
		}
	}

	/**
	 * parseTotalResultsCount
	 *
	 * If the site does not show the total number of results
	 * then set the plugin flag to C__JOB_PAGECOUNT_NOTAPPLICABLE__
	 * in the Constants.php file and just comment out this function.
	 *
	 * parseTotalResultsCount returns the total number of listings that
	 * the search returned by parsing the value from the returned HTML
	 * *
	 * @param $objSimpHTML
	 * @return string|null
	 * @throws \Exception
	 */
	function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		if(!is_null($this->nTotalJobs))
			return $this->nTotalJobs;

		if(empty($this->arrListingTagSetup))
			$this->setLayoutIfNeeded();

		return parent::parseTotalResultsCount($objSimpHTML);
	}

	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function getLayoutAndParseJobs(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		if(empty($this->arrListingTagSetup))
			$this->setLayoutIfNeeded();

		return $this->parseJobsListForPage($objSimpHTML);

	}
	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{

		if (!empty($this->lastResponseData) || !empty($this->currentJsonSearchDetails))
		{
			try {
				$ret = array();
				$nOffset = 0;
				if (!empty($this->lastResponseData) && !empty($this->lastResponseData->Jobs) && count($this->lastResponseData->Jobs) > 0) {
					$jobs = $this->lastResponseData->Jobs;
					$this->lastResponseData = null;
				}
				else {
					$jsonUrl = $this->_getJsonSearchUrl($this->currentJsonSearchDetails, $nOffset);
					LogMessage("Loading job results JSON data for {$this->getJobSiteKey()} from {$jsonUrl}");
					$respData = $this->getJsonResultsPage($jsonUrl);
					$jobs = $respData->Jobs;
					$this->nTotalJobs = $respData->Total;
				}
//				return $this->_parseJsonJobs($jobs);
//
				while (!empty($jobs) && $nOffset < $this->nTotalJobs) {
					$curPageJobs = $this->_parseJsonJobs($jobs);
					$jobs = null;
					$ret = array_merge($ret, $curPageJobs);
					$nOffset = $nOffset + count($curPageJobs);
					if ($nOffset < $this->nTotalJobs) {
						$jsonUrl = $this->_getJsonSearchUrl($this->currentJsonSearchDetails, $nOffset);
						LogMessage("Loading next page of JSON data for {$this->getJobSiteKey()} from {$hostPage}");
						$respData = $this->getJsonResultsPage($jsonUrl);
						$jobs = $respData->Jobs;
					}
				}
				return $ret;
			} catch (Exception $ex) {
				LogWarning("Failed to download " . $this->getJobSiteKey() . " listings via JSON.  Reverting to HTML.  " . $ex->getMessage());

				return $this->getLayoutAndParseJobs($objSimpHTML);
			}
		}

		return $this->getLayoutAndParseJobs($objSimpHTML);
	}

	/**
	 * @throws \Exception
	 */
	private function _determinePageLayout(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails)
	{
		$urlParts = parse_url($this->SearchUrlFormat);
		$urlParts['query'] = "";
		$urlParts['fragment'] = "";
		$urlParts['path'] = "/jobs/search/results";
		$baseUrl = new http\Url($urlParts, $urlParts, Url::REPLACE);
		$url = $baseUrl->toString();
		if (is_null($this->selenium)) {
			try {
				$this->selenium = new \JobScooper\Manager\SeleniumManager();
			} catch (Exception $ex) {
				handleException($ex, "Unable to start Selenium to get jobs for plugin '" . $this->JobSiteName . "'", true);
			}
		}

		$baseHTML = $this->getSimpleHtmlDomFromSeleniumPage($searchDetail, $url);
		$this->_layout = "careersdefault";

		if (!empty($baseHTML)) {
			try {
				$head = $baseHTML->find("head");
				if (!empty($head) && count($head) >= 1) {
					foreach ($head[0]->children() as $child) {
						if ($child->isCommentNode()) {
							$template = "unknown";
							$id = 0;
							$matches = array();
							$matched = preg_match("/Template Type Requested:\s*([^\(]+)\s*([^,]+)*,?\s?(\d+)?/", $child->text(), $matches);
							if ($matched !== false) {
								if (count($matches) > 2) {
									$template = $matches[2];
									$id = $matches[3];
								} elseif (count($matches) == 2) {
									$template = $matches[1];
								}
								break;
							}
						}
					}
				}


			} catch (Exception $ex) {
			} finally {
				$this->selenium->done();
				$this->selenium = null;
			}
		}
	}

	/**
	 * @param $layout
	 */
	protected function setAdicioPageLayout($layout)
	{
		$tags = array();
		$switchVal = cleanupSlugPart($layout, "");
		$this->_layout = $switchVal;
		switch($switchVal) {
			case 'jobsresponsivedefault':
				$tags = $this->arrBaseListingTagSetupJobsResponsive;
				break;

			case "careersdefault":
				$tags = $this->arrBaseListingTagSetupNationalSearch;
				break;

			case "jobsearchresults":
				$tags = $this->arrBaseListingTagSetupNationalSearch;
				$this->arrListingTagSetup['TotalPostCount']['selector'] = "span#retCountNumber";
				break;

			default:
				LogWarning("UNKNOWN ADICIO LAYOUT");
				$this->_layout = "default";
				$tags = $this->arrBaseListingTagSetupNationalSearch;
				break;
		}
		$this->arrListingTagSetup = array_merge_recursive_distinct($tags, $this->arrListingTagSetup);

	}
}


/**
 * Class PluginMashable
 */
class PluginMashable extends AbstractAdicio
{
	protected $JobSiteName = 'Mashable';
	protected $childSiteURLBase = 'http://jobs.mashable.com';
	// Note:  Mashable has a short list of jobs (< 500-1000 total) so we exclude keyword search here as an optimization.  We may download more jobs overall, but through fewer round trips to the servers
	protected $strBaseURLPathSection = "/jobs/search/results?location=***LOCATION***&radius=50&view=List_Detail&sort=PostType+asc%2C+PostDate+desc%2C+IsFeatured+desc&rows=50&";
	protected $LocationType = 'location-city-comma-state';
	protected $LastKnownSiteLayout = "jobsresponsivedefault";

}


/**
 * Class PluginJacksonville
 */
class PluginJacksonville extends AbstractAdicio
{
	protected $JobSiteName = 'Jacksonville';
	protected $childSiteURLBase = 'http://jobs.jacksonville.com';
	protected $LastKnownSiteLayout = "jobsresponsivedefault";
}


/**
 * Class PluginPolitico
 */
class PluginPolitico extends AbstractAdicio
{
	protected $JobSiteName = 'Politico';
	protected $childSiteURLBase = 'http://jobs.powerjobs.com';
	protected $LastKnownSiteLayout = "jobsearchresults";
}

/**
 * Class PluginIEEE
 */
class PluginIEEE extends AbstractAdicio
{
	protected $JobSiteName = 'IEEE';
	protected $childSiteURLBase = 'http://jobs.ieee.org';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginVariety
 */
class PluginVariety extends AbstractAdicio
{
	protected $JobSiteName = 'Variety';
	protected $childSiteURLBase = 'http://jobs.variety.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCellCom
 */
class PluginCellCom extends AbstractAdicio
{
	protected $JobSiteName = 'CellCom';
	protected $childSiteURLBase = 'http://jobs.cell.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

// NO LONGER ADICIO-BASED
///**
// * Class PluginCareerJet
// */
//class PluginCareerJet extends AbstractAdicio
//{
//	protected $JobSiteName = 'CareerJet';
//	protected $childSiteURLBase = 'http://www.careerjet.co.uk';
//}

/**
 * Class PluginHamptonRoads
 */
class PluginHamptonRoads extends AbstractAdicio
{
	protected $JobSiteName = 'HamptonRoads';
	protected $childSiteURLBase = 'http://careers.hamptonroads.com';
	protected $LastKnownSiteLayout = "jobsresponsivedefault";
}

/**
 * Class PluginAnalyticTalent
 */
class PluginAnalyticTalent extends AbstractAdicio
{
	protected $JobSiteName = 'AnalyticTalent';
	protected $childSiteURLBase = 'http://www.analytictalent.datasciencecentral.com';
	protected $LastKnownSiteLayout = "jobsresponsivedefault";
}

/**
 * Class PluginKenoshaNews
 */
class PluginKenoshaNews extends AbstractAdicio
{
	protected $JobSiteName = 'KenoshaNews';
	protected $childSiteURLBase = 'http://kenosha.careers.adicio.com';
	protected $LastKnownSiteLayout = "jobsresponsivedefault";
}

/**
 * Class PluginTopekaCapitalJournal
 */
class PluginTopekaCapitalJournal extends AbstractAdicio
{
	protected $JobSiteName = 'TopekaCapitalJournal';
	protected $childSiteURLBase = 'http://jobs.cjonline.com';
	protected $LastKnownSiteLayout = "jobsearchresults";
}

/**
 * Class PluginRetailCareersNow
 */
class PluginRetailCareersNow extends AbstractAdicio
{
	protected $JobSiteName = 'RetailCareersNow';
	protected $childSiteURLBase = 'http://retail.careers.adicio.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginHealthJobs
 */
class PluginHealthJobs extends AbstractAdicio
{
	protected $JobSiteName = 'HealthJobs';
	protected $childSiteURLBase = 'http://healthjobs.careers.adicio.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginPharmacyJobCenter
 */
class PluginPharmacyJobCenter extends AbstractAdicio
{
	protected $JobSiteName = 'PharmacyJobCenter';
	protected $childSiteURLBase = 'http://pharmacy.careers.adicio.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginKCBD
 */
class PluginKCBD extends AbstractAdicio
{
	protected $JobSiteName = 'KCBD';
	protected $childSiteURLBase = 'http://kcbd.careers.adicio.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginAfro
 */
class PluginAfro extends AbstractAdicio
{
	protected $JobSiteName = 'Afro';
	protected $childSiteURLBase = 'http://afro.careers.adicio.com';
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginJamaCareerCenter
 */
class PluginJamaCareerCenter extends AbstractAdicio
{
	protected $JobSiteName = 'JamaCareerCenter';
	protected $childSiteURLBase = 'http://jama.careers.adicio.com';

	protected $SearchUrlFormat = "http://jama.careers.adicio.com/jobs/search/results?view=List_Detail&sort=PostDate+desc&radius=25&rows=50";

}

/**
 * Class PluginSeacoastOnline
 */
class PluginSeacoastOnline extends AbstractAdicio
{
	protected $JobSiteName = 'SeacoastOnline';
	protected $childSiteURLBase = 'http://seacoast.careers.adicio.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginAlbuquerqueJournal
 */
class PluginAlbuquerqueJournal extends AbstractAdicio
{
	protected $JobSiteName = 'AlbuquerqueJournal';
	protected $childSiteURLBase = 'http://abqcareers.careers.adicio.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginWestHawaiiToday
 */
class PluginWestHawaiiToday extends AbstractAdicio
{
	protected $JobSiteName = 'WestHawaiiToday';
	protected $childSiteURLBase = 'http://careers.westhawaiitoday.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginDeadline
 */
class PluginDeadline extends AbstractAdicio
{
	protected $JobSiteName = 'Deadline';
	protected $childSiteURLBase = 'http://jobsearch.deadline.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginLogCabinDemocrat
 */
class PluginLogCabinDemocrat extends AbstractAdicio
{
	protected $JobSiteName = 'LogCabinDemocrat';
	protected $childSiteURLBase = 'http://jobs.thecabin.net';
}

/**
 * Class PluginPennEnergy
 */
class PluginPennEnergy extends AbstractAdicio
{
	protected $JobSiteName = 'PennEnergy';
	protected $childSiteURLBase = 'http://careers.pennenergyjobs.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginBig4Firms
 */
class PluginBig4Firms extends AbstractAdicio
{
	protected $JobSiteName = 'Big4Firms';
	protected $childSiteURLBase = 'http://careers.big4.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginVindy
 */
class PluginVindy extends AbstractAdicio
{
	protected $JobSiteName = 'Vindy';
	protected $childSiteURLBase = 'http://careers.vindy.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCast
 */
class PluginCareerCast extends AbstractAdicio
{
	protected $JobSiteName = 'CareerCast';
	protected $childSiteURLBase = 'http://www.careercast.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginTheLancet
 */
class PluginTheLancet extends AbstractAdicio
{
	protected $JobSiteName = 'TheLancet';
	protected $childSiteURLBase = 'http://careers.thelancet.com';
protected $LastKnownSiteLayout = "careersdefault";
}

class PluginVictoriaTXAdvocate extends AbstractAdicio
{
	protected $JobSiteName = 'VictoriaTXAdvocate';
	protected $childSiteURLBase = 'http://jobs.crossroadsfinder.com';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginTVB
 */
class PluginTVB extends AbstractAdicio
{
	protected $JobSiteName = 'TVB';
	protected $childSiteURLBase = 'http://postjobs.tvb.org';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginSHRM
 */
class PluginSHRM extends AbstractAdicio
{
	protected $JobSiteName = 'SHRM';
	protected $childSiteURLBase = 'http://hrjobs.shrm.org';
protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastIT
 */
class PluginCareerCastIT extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastIT";
	protected $childSiteURLBase = "http://it.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastHealthcare
 */
class PluginCareerCastHealthcare extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastHealthcare";
	protected $childSiteURLBase = "http://healthcare.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastNursing
 */
class PluginCareerCastNursing extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastNursing";
	protected $childSiteURLBase = "http://nursing.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastTempJobs
 */
class PluginCareerCastTempJobs extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastTempJobs";
	protected $childSiteURLBase = "http://tempjobs.careercast.com";
	protected $LastKnownSiteLayout = "jobsresponsivedefault";

	protected $arrListingTagSetup = array(
		'NoPostsFound' =>  array('selector' => 'h5', 'index' => 0, 'return_attribute' => 'node', 'return_value_callback' => "matchesNoResultsPattern", 'callback_parameter' => 'Oops! Nothing')
	);
}

/**
 * Class PluginCareerCastMarketing
 */
class PluginCareerCastMarketing extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastMarketing";
	protected $childSiteURLBase = "http://marketing.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastRetail
 */
class PluginCareerCastRetail extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastRetail";
	protected $childSiteURLBase = "http://retail.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastGreenNetwork
 */
class PluginCareerCastGreenNetwork extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastGreenNetwork";
	protected $childSiteURLBase = "http://green.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastDiversity
 */
class PluginCareerCastDiversity extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastDiversity";
	protected $childSiteURLBase = "http://diversity.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastConstruction
 */
class PluginCareerCastConstruction extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastConstruction";
	protected $childSiteURLBase = "http://construction.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastEnergy
 */
class PluginCareerCastEnergy extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastEnergy";
	protected $childSiteURLBase = "http://energy.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastTrucking
 */
class PluginCareerCastTrucking extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastTrucking";
	protected $childSiteURLBase = "http://trucking.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastDisability
 */
class PluginCareerCastDisability extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastDisability";
	protected $childSiteURLBase = "http://disability.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastHR
 */
class PluginCareerCastHR extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastHR";
	protected $childSiteURLBase = "http://hr.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastVeteran
 */
class PluginCareerCastVeteran extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastVeteran";
	protected $childSiteURLBase = "http://veteran.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastHospitality
 */
class PluginCareerCastHospitality extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastHospitality";
	protected $childSiteURLBase = "http://hospitality.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

/**
 * Class PluginCareerCastFinance
 */
class PluginCareerCastFinance extends AbstractAdicio
{
	protected $JobSiteName = "CareerCastFinance";
	protected $childSiteURLBase = "http://finance.careercast.com";
	protected $LastKnownSiteLayout = "careersdefault";
}

