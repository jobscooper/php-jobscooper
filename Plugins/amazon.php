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


/****************************************************************************************************************/
/***                                                                                                         ****/
/***                     Jobs Scooper Plugin:  Amazon.jobs                                                   ****/
/***                                                                                                         ****/
/****************************************************************************************************************/


/*****
 *
 * To get the right URL for Amazon Jobs search, fill out the parameters on
 * http://www.amazon.jobs/advancedjobsearch and then submit the form.  The URL of the
 * resulting page (e.g. "http://www.amazon.jobs/results?jobCategoryIds[]=83&jobCategoryIds[]=68&locationIds[]=226")
 * is the value you should set in the INI file to get the right filtered results.
 *
 * Note:  backend is powered by https://en-amazon.icims.com/jobs
 *
 */

class PluginAmazon extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{

    protected $JobSiteName = 'Amazon';
    protected $JobListingsPerPage = 10;
    protected $JobPostingBaseUrl = 'http://www.amazon.jobs';
//    protected $SearchUrlFormat = "https://www.amazon.jobs/en/search?base_query=***KEYWORDS***&loc_query=***LOCATION***&sort=recent&cache";
	protected $SearchUrlFormat = "https://www.amazon.jobs/en/search?base_query=&loc_query=***LOCATION***&sort=recent&cache";
//    protected $SearchUrlFormat = "https://www.amazon.jobs/en/search?offset=0&result_limit=10&sort=recent&cities[]=London&distanceType=Mi&radius=24km&latitude=&longitude=&loc_group_id=&loc_query=***LOCATION***&base_query=director&city=&country=&region=&county=&query_options=&"
    protected $PaginationType = C__PAGINATION_PAGE_VIA_NEXTBUTTON;
    protected $LocationType = 'location-city-comma-statecode-comma-country';
    protected $nMaxJobsToReturn = 5000; // Amazon maxes out at 2000 jobs in the list
    protected $additionalLoadDelaySeconds = 1;
    protected $CountryCodes = array("US", "UK");
    protected $additionalBitFlags = [C__JOB_RESULTS_SHOWN_IN_DATE_DESCENDING_ORDER];

    protected $selectorMoreListings = "button[data-label='right']";
	protected $searchStartActualURL = null;
	protected $searchJsonUrlFmt = null;
	protected $lastResponseData = null;
	private $nTotalJobs = null;

	/**
	 * @param \JobScooper\DataAccess\GeoLocation|null $location
	 *
	 * @return null|string
	 */
	function getGeoLocationSettingType(\JobScooper\DataAccess\GeoLocation $location=null)
    {
        if(!is_null($location))
        {
            switch($location->getCountryCode())
            {
                case "US":
                    return 'location-city-comma-statecode-comma-country';
                    break;

                default:
                    return 'location-city-comma-state-comma-country';
                    break;
            }
        }
        return $this->LocationType;
    }

	/**
	 * @param $searchDetails
	 *
	 * @return mixed
	 * @throws \Exception
	 */
	function doFirstPageLoad(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails)
    {
        $js = "
            setTimeout(clickSearchButton, " . strval($this->additionalLoadDelaySeconds) .");

            function clickSearchButton() 
            {
                var btnSearch = document.querySelectorAll(\"button.search-button\");
                if(btnSearch != null && !typeof(btnSearch.click) !== \"function\" && btnSearch.length >= 1) {
                    btnSearch = btnSearch[0];
                } 
                
                if(btnSearch != null && btnSearch.style.display === \"\") 
                { 
                    btnSearch.click();  
                    console.log(\"Clicked search button control...\");
                }
                else
                {
                    console.log('Search button was not active.');
                }
            }  
            
          document.addEventListener(\"DOMContentLoaded\", function(event) {
			 var elem = document.createElement(\"<span id='pageurl'></span>\" );
			 document.body.appendChild(elem);
			 elem.textContent = window.location;
		  });
        ";

        $this->selenium->getPageHTML($searchDetails->getSearchStartUrl());

        $this->runJavaScriptSnippet($js, false);
        sleep($this->additionalLoadDelaySeconds + 2);

        $html = $this->getActiveWebdriver()->getPageSource();

        $this->searchStartActualURL = $this->getActiveWebdriver()->getCurrentURL();
        $this->searchJsonUrlFmt = str_ireplace("/search?", "/search.json?", $this->searchStartActualURL) . "&result_limit=1000";

        return $html;
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
		try {
			$retData = $this->getJsonResultsPage(0);
			$this->JobListingsPerPage = 1000;
			$this->nTotalJobs = $retData['count'];
			return $this->nTotalJobs;
		}
		catch (Exception $ex)
		{
			$this->JobListingsPerPage = 10;
			return parent::parseTotalResultsCount($objSimpHTML);
		}
	}

	/**
	 * @param $jobs
	 *
	 * @return array
	 */
	private function _parseJsonJobs($jobs)
	{
		$ret = array();
		foreach($jobs as $job)
		{
			$ret[$job->id] = array(
				'JobSiteKey' => "amazon",
				'JobSitePostId' => $job->id_icims,
				'Company' => $job->company_name,
				'Title' =>  $job->title,
				'Url' => $job->url_next_step,
				'Location' => "{$job->city} {$job->state} {$job->country_code}",
				'Category' => "{$job->job_category} - {$job->business_category}",
				'PostedAt' => $job->posted_date,
				'Department' => $job->team->label
			);
		}
		return $ret;
	}

	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		try {
			$ret = array();
			$nOffset = 0;
			if (!empty($this->lastResponseData) && !empty($this->lastResponseData->jobs) && count($this->lastResponseData->jobs) > 0) {
				$jobs = $this->lastResponseData->jobs;
				while (!empty($jobs)) {
					$curPageJobs = $this->_parseJsonJobs($jobs);
					$ret = array_merge($ret, $curPageJobs);
					$nOffset = $nOffset + count($jobs);
					if ($nOffset < $this->nTotalJobs) {
						$retData = $this->getJsonResultsPage($nOffset);
						$jobs = $retData['jobs'];
					} else
						$jobs = null;
				}
			}

			return $ret;
		} catch (Exception $ex) {
			LogWarning("Failed to download Amazon listings via JSON.  Reverting to HTML.  " . $ex->getMessage());
			$this->JobListingsPerPage = 10;
			return parent::parseJobsListForPage($objSimpHTML);
		}
	}

	/**
	 * @param $offset
	 *
	 * @throws \ErrorException
	 * @throws \Exception
	 * @return array
	 */
	private function getJsonResultsPage($offset=0)
	{
		$curl = new \JobScooper\Utils\CurlWrapper();
		if (isDebug()) $curl->setDebug(true);

		$ret = array("count" => null, "jobs" => null);
		$url = $this->searchJsonUrlFmt . "&offset={$offset}";
		$lastCookies = $this->getActiveWebdriver()->manage()->getCookies();

		$retObj = $curl->cURL($url, $json = null, $action = 'GET', $content_type = null, $pagenum = null, $onbehalf = null, $fileUpload = null, $secsTimeout = null, $cookies = $lastCookies);
		if (!is_null($retObj) && array_key_exists("output", $retObj) && strlen($retObj['output']) > 0) {
			$respdata = json_decode($retObj['output']);
			if(!empty($respdata))
			{
				$this->lastResponseData = $respdata;
				try
				{
					$ret['count'] = $respdata->hits;
					$ret['jobs'] = $respdata->jobs;

				}
				catch(Exception $ex)
				{
					throw new Exception($respdata->error);
				}
			}

		}
		return $ret;
	}

    protected $arrListingTagSetup = array(
        'TotalPostCount' =>  array('selector' => 'div.job-count-info', 'return_value_regex' => '/.*?of\s(\d+)/'),
        'JobPostItem' => array('selector' => 'div.job-tile'),
        'Title' =>  array('selector' => 'h2.job-title'),
        'Url' =>  array('selector' => 'a.job-link', 'return_attribute' => 'href'),
        'JobSitePostId' =>  array('selector' => 'div.job', 'return_attribute' => 'data-job-id'),
        'Location' =>  array('selector' => 'div.location-and-id', 'return_value_regex' => '/(.*?)\|/', 'return_value_callback' => "cleanupLocationValue"),
        'PostedAt' =>  array('selector' => 'div.posting-date', 'return_value_regex' => '/Posted at (.*)/')
    );


	/**
	 * @param $var
	 *
	 * @return string
	 */
	static function cleanupLocationValue($var)
    {
        $ret = "";
        $parts = preg_split("/,\s?/", $var);
        $revparts = array_reverse($parts);
        $ret = join(", ", $revparts);
        return $ret;
    }



}
