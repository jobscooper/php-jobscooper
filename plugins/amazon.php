<?php
/**
 * Copyright 2014-17 Bryan Selner
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

class PluginAmazon extends \JobScooper\Plugins\Classes\AjaxHtmlSimplePlugin
{

    protected $JobSiteName = 'Amazon';
    protected $JobListingsPerPage = 10;
    protected $JobPostingBaseUrl = 'http://www.amazon.jobs';
    protected $SearchUrlFormat = "https://www.amazon.jobs/en/search?base_query=***KEYWORDS***&loc_query=***LOCATION***&sort=recent&cache";
//    protected $SearchUrlFormat = "https://www.amazon.jobs/en/search?offset=0&result_limit=10&sort=recent&cities[]=London&distanceType=Mi&radius=24km&latitude=&longitude=&loc_group_id=&loc_query=***LOCATION***&base_query=director&city=&country=&region=&county=&query_options=&"
    protected $PaginationType = C__PAGINATION_PAGE_VIA_NEXTBUTTON;
    protected $LocationType = 'location-city-comma-statecode-comma-country';
    protected $nMaxJobsToReturn = 2000; // Amazon maxes out at 2000 jobs in the list
    protected $additionalLoadDelaySeconds = 1;
    protected $CountryCodes = array("US", "UK");
    protected $additionalBitFlags = [C__JOB_RESULTS_SHOWN_IN_DATE_DESCENDING_ORDER];

    protected $selectorMoreListings = "button[data-label='right']";

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

    function doFirstPageLoad($searchDetails)
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
        ";

	    $this->getSimpleHtmlDomFromSelenium($searchDetails->getSearchParameter('search_start_url'));

	    $this->runJavaScriptSnippet($js, false);
	    sleep($this->additionalLoadDelaySeconds + 2);

	    return $this->getSimpleHtmlDomFromSelenium();
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


    static function cleanupLocationValue($var)
    {
        $ret = "";
        $parts = preg_split("/,\s?/", $var);
        $revparts = array_reverse($parts);
        $ret = join(", ", $revparts);
        return $ret;
    }



}
