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
use JobScooper\DataAccess\UserSearchSiteRun;

/**
 * Class AbstractMadgexATS
 */
abstract class AbstractMadgexATS extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{

	/**
	 * AbstractMadgexATS constructor.
	 * @throws \Exception
	 */
	function __construct()
    {
	    $this->JobPostingBaseUrl = "https://{$this->SiteReferenceKey}.com";
	    $this->SearchUrlFormat = "{$this->JobPostingBaseUrl}/searchjobs?Keywords=***KEYWORDS***&radialtown=***LOCATION:{Place}***+***LOCATION:{Region}***&RadialLocation=50&NearFacetsShown=true&countrycode=***LOCATION:{CountryCode}***&sort=Date&Page=***PAGE_NUMBER***";
        $this->prevURL = $this->JobPostingBaseUrl;
        $this->PaginationType = C__PAGINATION_PAGE_VIA_URL;

        $this->additionalBitFlags[] = C__JOB_RESULTS_SHOWN_IN_DATE_DESCENDING_ORDER;
        parent::__construct();
    }

    protected $JobSiteName = 'madgexats';
    protected $locationid = null;
    protected $SiteReferenceKey = "UNKNOWN";

    protected $LocationType = 'location-city-comma-state';

	/**
	 * @param \JobScooper\DataAccess\UserSearchSiteRun $searchDetails
	 * @param null                                     $nPage
	 * @param null                                     $nItem
	 *
	 * @return mixed|null|string|string[]
	 * @throws \Exception
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	protected function getPageURLfromBaseFmt(UserSearchSiteRun $searchDetails, $nPage = null, $nItem = null)
    {
        $strURL = parent::getPageURLfromBaseFmt($searchDetails, $nPage, $nItem);
		$searchDetails->setSearchStartUrl($strURL);
	    if (empty($this->locationid)) {
		    $this->locationid = $this->getLocationIdFromPage($searchDetails);
	    }

	    if (!empty($this->locationid))
            $strURL = $strURL. "&LocationID=" . $this->locationid;

        $page = $nPage == 1 ? "" : "&Page=" . $this->getPageURLValue($nPage);
        $strURL = preg_replace('/&[Pp]age=\d+/', $page, $strURL);
        return $strURL;

    }

    protected $arrListingTagSetup = array(
        'NoPostsFound'          => array('selector' => 'h1#searching', 'return_attribute' => 'text', 'return_value_callback' => 'matchesNoResultsPattern', 'callback_parameter' => "Found 0 jobs"),
        'TotalPostCount'        => array('selector' => 'h1#searching', 'return_attribute' => 'text', 'return_value_regex' =>  '/\b(\d+)\b/i'),
        'JobPostItem'           => array('selector' => 'li.lister__item'),
        'Title'                 => array('selector' => 'h3.lister__header a span', 'return_attribute' => 'text'),
        'Url'                   => array('selector' => 'h3.lister__header a', 'return_attribute' => 'href'),
        'Company'               => array('selector' => 'ul li.lister__meta-item--recruiter', 'return_attribute' => 'text'),
        'PageRange'             => array('selector' => 'ul li.lister__meta-item--salary', 'return_attribute' => 'text'),
        'Location'              => array('selector' => 'ul li.lister__meta-item--location', 'return_attribute' => 'text'),
        'JobSitePostId'         => array('selector' => 'li.lister__item', 'return_attribute' => 'id', 'return_value_regex' =>  '/item\-(\d+)/i'),
        'PostedAt'              => array('selector' => 'li.job-actions__action', 'index' =>0),
        'company_logo'          => array('selector' => 'img.lister__logo', 'return_attribute' => 'src'),
        'NextButton'            => array('selector' => 'li.paginator__item a[rel="next"]')
    );


	/**
	 * @param UserSearchSiteRun &$searchDetails
	 *
	 * @return null|integer
	 *
	 * @throws \Exception
	 */
	protected function getLocationIdFromPage(UserSearchSiteRun &$searchDetails)
    {
	    $locValParam = $this->getGeoLocationURLValue($searchDetails, "{Place} {Region}");
	    $locApi = parse_url($searchDetails->getSearchStartUrl(), PHP_URL_SCHEME) . "://" . parse_url($searchDetails->getSearchStartUrl(), PHP_URL_HOST) . "/location-lookup/?term={$locValParam}";
//	    $locApi = "http://" . parse_url($this->JobPostingBaseUrl, PHP_URL_HOST) . "/location-lookup/?term={$locValParam}";

	    LogMessage("Determining LocationId value for {$locValParam}... from {$locApi} ..." );
	    $objLocChoices = $this->getJsonApiResult($locApi, $searchDetails, $searchDetails->getSearchStartUrl());
	    if(empty($objLocChoices) || !is_array($objLocChoices))
	    {
	    	return null;
	    }

	    $locId = $objLocChoices[0]->value;

	    $newUrl = $searchDetails->getSearchStartUrl() . "&LocationId=" . strval($locId);
	    $searchDetails->setSearchStartUrl($newUrl);

	    return $locId;
    }
}
