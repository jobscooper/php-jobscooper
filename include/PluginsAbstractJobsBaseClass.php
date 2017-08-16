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
require_once dirname(dirname(__FILE__))."/bootstrap.php";

const VALUE_NOT_SUPPORTED = -1;
const BASE_URL_TAG_LOCATION = "***LOCATION***";
const BASE_URL_TAG_KEYWORDS = "***KEYWORDS***";
use \Khartnett\Normalization as Normalize;

abstract class AbstractClassBaseJobsPlugin extends ClassJobsSiteCommon
{
    protected $arrSearchesToReturn = null;
    protected $strBaseURLFormat = null;
    protected $siteBaseURL = null;
    protected $typeLocationSearchNeeded = null;
    protected $siteName = 'NAME-NOT-SET';
    private $userObject = null;

    function __construct($strOutputDirectory = null, $attributes = null)
    {
        parent::__construct($strOutputDirectory);

        if (array_key_exists("JOBSITE_PLUGINS", $GLOBALS) && (array_key_exists(strtolower($this->siteName), $GLOBALS['JOBSITE_PLUGINS']))) {
            $plugin = $GLOBALS['JOBSITE_PLUGINS'][strtolower($this->siteName)];
            if (array_key_exists("other_settings", $plugin) && is_array($plugin['other_settings'])) {
                $keys = array_keys($plugin['other_settings']);
                foreach ($keys as $attrib_name) {
                    $this->$attrib_name = $plugin['other_settings'][$attrib_name];
                }
            }
        }
        $this->userObject = $GLOBALS['USERDATA']['configuration_settings']['user_details'];

        $this->normalizer = new Normalize();

        if (stristr($this->strBaseURLFormat, "***KEYWORDS***") == false)
            $this->additionalFlags[] = C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED;

        if (stristr($this->strBaseURLFormat, "***LOCATION***") == false)
            $this->additionalFlags[] = C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED;

        if (stristr($this->strBaseURLFormat, "***NUMBER_DAYS***") == false)
            $this->additionalFlags[] = C__JOB_DAYS_VALUE_NOTAPPLICABLE__;

        if (is_array($this->additionalFlags)) {
            foreach ($this->additionalFlags as $flag) {
                // If the flag is already set, don't try to set it again or it will
                // actually unset that flag incorrectly
                if (!$this->isBitFlagSet($flag)) {
                    $this->_flags_ = $this->_flags_ | $flag;
                }
            }
        }

        if ($this->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED)) {
            $this->nMaxJobsToReturn = $this->nMaxJobsToReturn * 3;
        }

        if ($this->paginationType == C__PAGINATION_INFSCROLLPAGE_NOCONTROL) {
            $this->nJobListingsPerPage = $this->nMaxJobsToReturn;
        }

        if (!is_null($this->selectorMoreListings) && strlen($this->selectorMoreListings) > 0)
            $this->selectorMoreListings = preg_replace('/(\'|"|\\")/', '\"', $this->selectorMoreListings);

    }


    //************************************************************************
    //
    //
    //
    //  Adding search parameters & downloading new job functions
    //
    //
    //
    //************************************************************************

    public function isBitFlagSet($flagToCheck)
    {
        $ret = \Scooper\isBitFlagSet($this->_flags_, $flagToCheck);
        if ($ret == $flagToCheck) {
            return true;
        }
        return false;
    }

    public function addSearches(&$arrSearches)
    {
        if ($this->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED)) {
            $firstKey = array_keys($arrSearches)[0];
            $arrSearches = array($firstKey => $arrSearches[$firstKey]);
        }

        foreach ($arrSearches as $searchDetails) {
            $this->_addSearch_($searchDetails);
        }
    }

    public function getMyJobsList()
    {
        return \JobScooper\UserJobMatchQuery::create()
            ->filterByUserMatchStatus(null)
            ->filterByUserSlug($this->userObject->getUserSlug())
            ->filterBy("AppRunId", $GLOBALS['USERDATA']['configuration_settings']['app_run_id'])
            ->useJobPostingQuery()
                ->filterByJobSite($this->siteName)
            ->find();

    }

    public function getUpdatedJobsForAllSearches()
    {
        $strIncludeKey = 'include_' . strtolower($this->siteName);

        if (isset($GLOBALS['OPTS'][$strIncludeKey]) && $GLOBALS['OPTS'][$strIncludeKey] == 0) {
            LogLine($this->siteName . ": excluded for run. Skipping '" . count($this->arrSearchesToReturn) . "' site search(es).", \Scooper\C__DISPLAY_ITEM_DETAIL__);
            return array();
        }

        if (count($this->arrSearchesToReturn) == 0) {
            LogLine($this->siteName . ": no searches set. Skipping...", \Scooper\C__DISPLAY_ITEM_DETAIL__);
            return array();
        }

        foreach ($this->arrSearchesToReturn as $search) {
            $this->currentSearchBeingRun = $search;

            try {
                // assert this search is actually for the job site supported by this plugin
                assert(strcasecmp(strtolower($search->getJobSite()), strtolower($this->siteName)) == 0);

                if ($this->isBitFlagSet(C__JOB_USE_SELENIUM)) {
                    try {
                        if ($GLOBALS['USERDATA']['selenium']['autostart'] == True) {
                            SeleniumSession::startSeleniumServer();
                        }
                        $this->selenium = new SeleniumSession();
                    } catch (Exception $ex) {
                        handleException($ex, "Unable to start Selenium to get jobs for plugin '" . $this->siteName . "'", true);
                    }
                }

                $this->_updateJobsDataForSearch_($search);
            } catch (Exception $ex) {
                throw $ex;
            } finally {
                $this->currentSearchBeingRun = null;
            }
        }


        return $this->getMyJobsList();
    }

    function getName()
    {
        $name = strtolower($this->siteName);
        if (is_null($name) || strlen($name) == 0) {
            $name = str_replace("plugin", "", get_class($this));
        }
        return $name;
    }

    //************************************************************************
    //
    //
    //
    //  Protected and Private Class Members
    //
    //
    //
    //************************************************************************

    protected $nJobListingsPerPage = 20;
    protected $additionalFlags = array();
    protected $paginationType = null;
    protected $secsPageTimeout = null;
    protected $selenium = null;
    protected $nextPageScript = null;
    protected $selectorMoreListings = null;
    protected $nMaxJobsToReturn = C_JOB_MAX_RESULTS_PER_SEARCH;
    protected $currentSearchBeingRun = null;
    protected $arrSearchReturnedJobs = array();

    protected $strKeywordDelimiter = null;
    protected $additionalLoadDelaySeconds = 0;
    protected $_flags_ = null;
    protected $pluginResultsType = C__JOB_SEARCH_RESULTS_TYPE_SERVERSIDE_WEBPAGE__;
    protected $normalizer = null;

    function getLocationSettingType()
    {
        return $this->typeLocationSearchNeeded;
    }

    protected function getActiveWebdriver()
    {
        if (!is_null($this->selenium)) {
            return $this->selenium->get_driver();
        } else
            throw new Exception("Error:  active webdriver for Selenium not found as expected.");
    }

    protected function getCombinedKeywordString($arrKeywordSet)
    {
        $arrKeywords = array();

        if (!is_array($arrKeywordSet)) {
            $arrKeywords[] = $arrKeywordSet[0];
        } else {
            $arrKeywords = $arrKeywordSet;
        }

        if ($this->isBitFlagSet(C__JOB_KEYWORD_SUPPORTS_QUOTED_KEYWORDS)) {
            $arrKeywords = array_mapk(function ($k, $v) {
                return "\"{$v}\"";
            }, $arrKeywords);
        }

        $strRetCombinedKeywords = array_shift($arrKeywords);

        return $strRetCombinedKeywords;
    }

    protected function parseJobsListForPage($objSimpHTML)
    {
        throw new \BadMethodCallException(sprintf("Not implemented method  " . __METHOD__ . " called on class \"%s \".", __CLASS__));
    }

    protected function getLocationURLValue($searchDetails, $locSettingSets = null)
    {
        $strReturnLocation = VALUE_NOT_SUPPORTED;

        if ($this->isBitFlagSet(C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED)) {
            throw new ErrorException($this->siteName . " does not support the ***LOCATION*** replacement value in a base URL.  Please review and change your base URL format to remove the location value.  Aborting all searches for " . $this->siteName, \Scooper\C__DISPLAY_ERROR__);
        }

        // Did the user specify an override at the search level in the INI?
        if ($searchDetails != null && isset($searchDetails['location_user_specified_override']) && strlen($searchDetails['location_user_specified_override']) > 0) {
            $strReturnLocation = $searchDetails['location_user_specified_override'];
        } else {
            // No override, so let's see if the search settings have defined one for us
            $locTypeNeeded = $this->getLocationSettingType();
            if ($locTypeNeeded == null || $locTypeNeeded == "") {
                LogLine("Plugin for '" . $searchDetails['site_name'] . "' did not have the required location type of " . $locTypeNeeded . " set.   Skipping search '" . $searchDetails['key'] . "' with settings '" . $locSettingSets['key'] . "'.", \Scooper\C__DISPLAY_ITEM_DETAIL__);
                return $strReturnLocation;
            }

            if (isset($locSettingSets) && count($locSettingSets) > 0 && isset($locSettingSets[$locTypeNeeded])) {
                $strReturnLocation = $locSettingSets[$locTypeNeeded];
            }

            if ($strReturnLocation == null || $strReturnLocation == VALUE_NOT_SUPPORTED) {
                LogLine("Plugin for '" . $searchDetails['site_name'] . "' did not have the required location type of " . $locTypeNeeded . " set.   Skipping search '" . $searchDetails['key'] . "' with settings '" . $locSettingSets['key'] . "'.", \Scooper\C__DISPLAY_ITEM_DETAIL__);
                return $strReturnLocation;
            }
        }

        if (!isValueURLEncoded($strReturnLocation)) {
            $strReturnLocation = urlencode($strReturnLocation);
        }

        return $strReturnLocation;
    }


    protected function getPageURLfromBaseFmt(&$searchDetails, $nPage = null, $nItem = null)
    {
        $strURL = $this->_getBaseURLFormat_($searchDetails, $nPage, $nItem);


        $strURL = str_ireplace("***NUMBER_DAYS***", $this->getDaysURLValue($GLOBALS['USERDATA']['configuration_settings']['number_days']), $strURL);
        $strURL = str_ireplace("***PAGE_NUMBER***", $this->getPageURLValue($nPage), $strURL);
        $strURL = str_ireplace("***ITEM_NUMBER***", $this->getItemURLValue($nItem), $strURL);
        $strURL = str_ireplace(BASE_URL_TAG_KEYWORDS, $this->getKeywordURLValue($searchDetails), $strURL);


        $nSubtermMatches = substr_count($strURL, BASE_URL_TAG_LOCATION);

        if (!$this->isBitFlagSet(C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED) && $nSubtermMatches > 0) {
            $strLocationValue = $searchDetails['location_search_value'];
            if ($strLocationValue == VALUE_NOT_SUPPORTED) {
                LogLine("Failed to run search:  search is missing the required location type of " . $this->getLocationSettingType() . " set.  Skipping search '" . $searchDetails['key'] . ".", \Scooper\C__DISPLAY_ITEM_DETAIL__);
                $strURL = VALUE_NOT_SUPPORTED;
            } else {
                $strURL = str_ireplace(BASE_URL_TAG_LOCATION, $strLocationValue, $strURL);
            }
        }

        if ($strURL == null) {
            throw new ErrorException("Location value is required for " . $this->siteName . ", but was not set for the search '" . $searchDetails['key'] . "'." . " Aborting all searches for " . $this->siteName, \Scooper\C__DISPLAY_ERROR__);
        }

        return $strURL;
    }

    //************************************************************************
    //
    //
    //
    //  Utility Functions
    //
    //
    //
    //************************************************************************
    function getIDFromLink($regex_link_job_id, $url)
    {
        if (isset($regex_link_job_id)) {
            $fMatchedID = preg_match($regex_link_job_id, $url, $idMatches);
            if ($fMatchedID && count($idMatches) >= 1) {
                return $idMatches[count($idMatches) - 1];
            }
        }
        return "";
    }


    protected function _getBaseURLFormat_($searchDetails = null, $nPage = null, $nItem = null)
    {
        $strBaseURL = VALUE_NOT_SUPPORTED;

        if ($searchDetails != null && isset($searchDetails['base_url_format'])) {
            $strBaseURL = $searchDetails['base_url_format'];
        } elseif (!is_null($this->strBaseURLFormat) && strlen($this->strBaseURLFormat) > 0) {
            $strBaseURL = $searchDetails['base_url_format'] = $this->strBaseURLFormat;
        } elseif (!is_null($this->siteBaseURL) && strlen($this->siteBaseURL) > 0) {
            $strBaseURL = $searchDetails['base_url_format'] = $this->siteBaseURL;
        } else {
            throw new ErrorException("Could not find base URL format for " . $this->siteName . ".  Aborting all searches for " . $this->siteName, \Scooper\C__DISPLAY_ERROR__);
        }
        return $strBaseURL;
    }

    protected function getDaysURLValue($nDays = null)
    {
        return ($nDays == null || $nDays == "") ? 1 : $nDays;
    }

    protected function getPageURLValue($nPage)
    {
        return ($nPage == null || $nPage == "") ? "" : $nPage;
    }

    protected function getItemURLValue($nItem)
    {
        return ($nItem == null || $nItem == "") ? 0 : $nItem;
    }

    protected function parseTotalResultsCount($objSimpHTML)
    {
        throw new \BadMethodCallException(sprintf("Not implemented method " . __METHOD__ . " called on class \"%s \".", __CLASS__));
    }


    protected function moveDownOnePageInBrowser()
    {

        // Neat trick written up by http://softwaretestutorials.blogspot.in/2016/09/how-to-perform-page-scrolling-with.html.
        $driver = $this->getActiveWebdriver();

        $driver->executeScript("window.scrollTo(0,document.body.scrollHeight);");

        sleep($this->additionalLoadDelaySeconds + 1);

    }


    protected function getKeywordURLValue($searchDetails)
    {
        if (!$this->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED)) {
            assert($searchDetails['keywords_string_for_url'] != VALUE_NOT_SUPPORTED);
            return $searchDetails['keywords_string_for_url'];
        }
        return "";
    }

    protected function goToEndOfResultsSetViaPageDown($nTotalItems = null)
    {
        $this->moveDownOnePageInBrowser();
        $secs = $this->additionalLoadDelaySeconds * 1000;
        if ($secs <= 0)
            $secs = 1000;

        $js = "
            localStorage.setItem('startTime', Date.now());
            localStorage.setItem('prevHeight', 0);
            scroll = setTimeout(gotoPageBottom, 250);
            function getRunTime()
            {
                var startTime = localStorage.getItem('startTime');
                var endTime = Date.now();
                runtime = Math.floor((endTime-startTime)/(1000));
                return runtime;
            }

            function gotoPageBottom() 
            {
                runtime = getRunTime();
                prevHeight = localStorage.getItem('prevHeight');
                
                window.scrollTo(0,document.body.scrollHeight);
                if(prevHeight == null || (prevHeight < document.body.scrollHeight && runtime <= 60))
                {
                    localStorage.setItem('prevHeight', document.body.scrollHeight);
                    setTimeout(gotoPageBottom, " . $secs . ");
                }
                else
                {
                    console.log('Load more button no longer active; done paginating the results.');
                    console.log('Script needed a minimum of ' + runtime + ' seconds to load all the results.');
                    localStorage.removeItem('startTime');
                    localStorage.removeItem('prevHeight');

                }
            }  
        ";


        if (is_null($nTotalItems)) {
            $nTotalItems = $this->nMaxJobsToReturn;
        }

        if ($nTotalItems == C__TOTAL_ITEMS_UNKNOWN__) {
            $nSleepTimeToLoad = 30 + $this->additionalLoadDelaySeconds;
        } else {
            $nSleepTimeToLoad = ($nTotalItems / $this->nJobListingsPerPage) * $this->additionalLoadDelaySeconds;
        }

        LogLine("Sleeping for " . $nSleepTimeToLoad . " seconds to allow browser to page down through all the results", \Scooper\C__DISPLAY_ITEM_DETAIL__);

        $this->runJavaScriptSnippet($js, false);

        sleep($nSleepTimeToLoad > 0 ? $nSleepTimeToLoad : 2);

        $this->moveDownOnePageInBrowser();

    }

    protected function goToEndOfResultsSetViaLoadMore($nTotalItems)
    {
        $this->moveDownOnePageInBrowser();
        $secs = $this->additionalLoadDelaySeconds * 1000;
        if ($secs <= 0)
            $secs = 1000;

        $js = "
            scroll = setTimeout(doLoadMore, 250);
            function getRunTime()
            {
                var startTime = localStorage.getItem(\"startTime\");
                var endTime = Date.now();
                runtime = Math.floor((endTime-startTime)/(1000));
                return (runtime + ' seconds');
            }

            function doLoadMore() 
            {
                var startTime = localStorage.getItem(\"startTime\");
                if(startTime == null) 
                {
                    localStorage.setItem(\"startTime\", Date.now());
                    localStorage.setItem(\"pageNum\", 1);
                }

                window.scrollTo(0,document.body.scrollHeight);
                console.log('paged-down-before-click');

                var loadmore = document.querySelector(\"" . $this->selectorMoreListings . "\");
                if(loadmore != null && !typeof(loadmore.click) !== \"function\" && loadmore.length >= 1) {
                    loadmore = loadmore[0];
                } 
    
                runtime = getRunTime();
                if(loadmore != null && loadmore.style.display === \"\") 
                { 
                    var pageNum = parseInt(localStorage.getItem(\"pageNum\"));
                    if (pageNum != null)
                    {   
                        console.log('Results for page # ' + pageNum + ' loaded.  Time spent so far:  ' + runtime + ' Going to next page...');
                        localStorage.setItem(\"pageNum\", pageNum + 1);
                    }
                    loadmore.click();  
                    console.log(\"Clicked load more control...\");
                        
                    scroll = setTimeout(doLoadMore, " . $secs . ");
                    window.scrollTo(0,document.body.scrollHeight);
                    console.log('paged-down-after-click');
                }
                else
                {
                    console.log('Load more button no longer active; done paginating the results.');
                    console.log('Script needed a minimum of ' + runtime + ' seconds to load all the results.');
                    localStorage.removeItem(\"startTime\");

                }
            }  
        ";


        if (is_null($nTotalItems)) {
            $nTotalItems = $this->nMaxJobsToReturn;
        }

        if ($nTotalItems == C__TOTAL_ITEMS_UNKNOWN__) {
            $nSleepTimeToLoad = 30 + $this->additionalLoadDelaySeconds;
        } else {
            $nSleepTimeToLoad = ($nTotalItems / $this->nJobListingsPerPage) * $this->additionalLoadDelaySeconds;
        }

        LogLine("Sleeping for " . $nSleepTimeToLoad . " seconds to allow browser to page down through all the results", \Scooper\C__DISPLAY_ITEM_DETAIL__);

        $this->runJavaScriptSnippet($js, false);

        sleep($nSleepTimeToLoad > 0 ? $nSleepTimeToLoad : 2);

        $this->moveDownOnePageInBrowser();

    }


    protected function goToNextPageOfResultsViaNextButton()
    {
        $secs = $this->additionalLoadDelaySeconds * 1000;
        if ($secs <= 0)
            $secs = 1000;

        $objSimplHtml = $this->getSimpleHtmlDomFromSeleniumPage();

        $node = $objSimplHtml->find($this->selectorMoreListings);
        if ($node == null || count($node) == 0) {
            return false;
        } else {
            if (stristr($node[0]->attr["style"], "display: none") !== false) {
                return false;
            }
        }

        $js = "
            scroll = setTimeout(doNextPage, " . $secs . ");
            function doNextPage() 
            {
                var loadnext = document.querySelector(\"" . $this->selectorMoreListings . "\");
                if(loadnext != null && !typeof(loadnext .click) !== \"function\" && loadnext.length >= 1) {
                    loadnext = loadnext[0];
                } 
    
                if(loadnext != null && loadnext.style.display === \"\") 
                { 
                    loadnext.click();  
                    console.log(\"Clicked load next results control " . $this->selectorMoreListings . "...\");
                }
            }  
        ";

        $this->runJavaScriptSnippet($js, false);

        sleep($this->additionalLoadDelaySeconds > 0 ? $this->additionalLoadDelaySeconds : 2);

        return true;
    }


    //************************************************************************
    //
    //
    //
    //  Functions for Adding Searches to Plugin Instance
    //
    //
    //
    //************************************************************************


    private function _addSearch_(&$searchDetails)
    {


        if ($searchDetails->getKey() == "") {
            $searchDetails->setKey(\Scooper\strScrub($searchDetails->getJobSite(), FOR_LOOKUP_VALUE_MATCHING) . "-" . \Scooper\strScrub($searchDetails->getKey(), FOR_LOOKUP_VALUE_MATCHING));
        }

        assert($this->isBitFlagSet(C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED) || ($searchDetails['location_search_value'] !== VALUE_NOT_SUPPORTED && strlen($searchDetails['location_search_value']) > 0));

        if ($this->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED)) {
            // null out any generalized keyword set values we previously had
            $searchDetails['keywords_array'] = null;
            $searchDetails['keywords_array_tokenized'] = null;
            $searchDetails['keywords_string_for_url'] = null;
        } else {
            $this->_setKeywordStringsForSearch_($searchDetails);
        }

        $this->_setStartingUrlForSearch_($searchDetails);

        $searchDetails->save();

        // add a global record for the search so we can report errors
        $GLOBALS['USERDATA']['search_results'][$searchDetails['key']] = $searchDetails;

        //
        // Add the search to the list of ones to run
        //
        $this->arrSearchesToReturn[$searchDetails->getKey()] = $searchDetails;
        LogLine($this->siteName . ": added search (" . $searchDetails->getKey() . ")", \Scooper\C__DISPLAY_ITEM_DETAIL__);

    }

    private function _setKeywordStringsForSearch_(&$searchDetails)
    {
        // Does this search have a set of keywords specific to it that override
        // all the general settings?
        if (isset($searchDetails['keyword_search_override']) && strlen($searchDetails['keyword_search_override']) > 0) {
            // keyword_search_override should only ever be a string value for any given search
            assert(!is_array($searchDetails['keyword_search_override']));

            // null out any generalized keyword set values we previously had
            $searchDetails['keywords_array'] = null;
            $searchDetails['keywords_string_for_url'] = null;

            //
            // Now take the override value and setup the keywords_array
            // and URL value for that particular string
            //
            $searchDetails['keywords_array'] = array($searchDetails['keyword_search_override']);
        }

        if (isset($searchDetails['keywords_array'])) {
            assert(is_array($searchDetails['keywords_array']));

            $searchDetails['keywords_string_for_url'] = $this->_getCombinedKeywordStringForURL_($searchDetails['keywords_array']);
        }

        // Lastly, check if we support keywords in the URL at all for this
        // plugin.  If not, remove any keywords_string_for_url value we'd set
        // and set it to "not supported"
        if ($this->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED)) {
            $searchDetails['keywords_string_for_url'] = VALUE_NOT_SUPPORTED;
        }
    }

    private function _getCombinedKeywordStringForURL_($arrKeywordSet)
    {
        $arrKeywords = array();

        if (!is_array($arrKeywordSet)) {
            $arrKeywords[] = $arrKeywordSet[0];
        } else {
            $arrKeywords = $arrKeywordSet;
        }

        $strRetCombinedKeywords = $this->getCombinedKeywordString($arrKeywords);

        if (!isValueURLEncoded($strRetCombinedKeywords)) {
            if ($this->isBitFlagSet(C__JOB_KEYWORD_PARAMETER_SPACES_RAW_ENCODE))
                $strRetCombinedKeywords = rawurlencode($strRetCombinedKeywords);
            else
                $strRetCombinedKeywords = urlencode($strRetCombinedKeywords);

        }

        if ($this->isBitFlagSet(C__JOB_KEYWORD_PARAMETER_SPACES_AS_DASHES)) {
            $strRetCombinedKeywords = str_replace("%22", "-", $strRetCombinedKeywords);
            $strRetCombinedKeywords = str_replace("+", "-", $strRetCombinedKeywords);
        }

        return $strRetCombinedKeywords;
    }

    private function _setStartingUrlForSearch_(&$searchDetails)
    {

        $searchStartURL = $this->getPageURLfromBaseFmt($searchDetails, 1, 1);
        if (is_null($searchStartURL) || strlen($searchStartURL) == 0)
            $searchStartURL = $this->siteBaseURL;

        $searchDetails['search_start_url'] = $searchStartURL;
        LogLine("Setting start URL for " . $this->siteName . "[" . $searchDetails['key'] . "] to: " . PHP_EOL . $searchDetails['search_start_url'], \Scooper\C__DISPLAY_ITEM_DETAIL__);

    }


    function setCompanyToSiteName($var)
    {
        return $this->siteName;
    }

    function combineTextAllNodes($var)
    {
        return combineTextAllNodes($var);
    }


    //************************************************************************
    //
    //
    //
    //  Job listing download methods
    //
    //
    //
    //************************************************************************

    private function _updateJobsDataForSearch_($searchDetails)
    {
        $this->_logMemoryUsage_();
        $ex = null;

        try {

            // get the url for the first page/items in the results
            if ($this->_checkInvalidURL_($searchDetails, $searchDetails['search_start_url']) == VALUE_NOT_SUPPORTED) return;

            LogLine(("Starting data pull for " . $this->siteName . "[" . $searchDetails['key'] . "]"), \Scooper\C__DISPLAY_ITEM_RESULT__);

            if ($this->pluginResultsType == C__JOB_SEARCH_RESULTS_TYPE_JOBSAPI__) {
                $this->_getMyJobsForSearchFromJobsAPI_($searchDetails);
            } elseif ($this->pluginResultsType == C__JOB_SEARCH_RESULTS_TYPE_SERVERSIDE_WEBPAGE__) {
                $this->_getMyJobsForSearchFromWebpage_($searchDetails);
            } elseif ($this->pluginResultsType == C__JOB_SEARCH_RESULTS_TYPE_CLIENTSIDE_WEBPAGE__) {
                $this->_getMyJobsForSearchFromWebpage_($searchDetails);
            } else {
                throw new ErrorException("Class " . get_class($this) . " does not have a valid setting for parser.  Cannot continue.");
            }

            // Let's do another check to make sure we got any listings at all for those that weren't
            // filtered by keyword.  If we returned zero jobs for any given city and no keyword filter
            // then we are likely broken somehow unexpectedly.   Make sure to error so that we note
            // it in the results & error notifications so that a developer can take a look.
            if ($this->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED) && !$this->isBitFlagSet(C__JOB_SETTINGS_URL_VALUE_REQUIRED) && countJobRecords($this->arrSearchReturnedJobs[$searchDetails->getKey()]) == 0) {
                $strError = "The search " . $searchDetails['key'] . " on " . $this->siteName . " downloaded 0 jobs yet we did not have any keyword filter is use.  Logging as a potential error since we should have had something returned. [URL=" . $searchDetails['search_start_url'] . "].  ";
                handleException(new Exception($strError), null, true);
            }

        } catch (Exception $ex) {

            //
            // BUGBUG:  This is a workaround to prevent errors from showing up
            // when no results are returned for a particular search for EmploymentGuide plugin only
            // See https://github.com/selner/jobs_scooper/issues/23 for more details on
            // this particular underlying problem
            //
            if ((isset($GLOBALS['JOBSITE_PLUGINS']['employmentguide']) && (strcasecmp($this->siteName, $GLOBALS['JOBSITE_PLUGINS']['employmentguide']['name']) == 0) ||
                    (isset($GLOBALS['JOBSITE_PLUGINS']['careerbuilder']) && strcasecmp($this->siteName, $GLOBALS['JOBSITE_PLUGINS']['careerbuilder']['name']) == 0) ||
                    (isset($GLOBALS['JOBSITE_PLUGINS']['ziprecruiter']) && strcasecmp($this->siteName, $GLOBALS['JOBSITE_PLUGINS']['ziprecruiter']['name']) == 0)) &&
                (substr_count($ex->getMessage(), "HTTP error #404") > 0)
            ) {
                $strError = $this->siteName . " plugin returned a 404 page for the search.  This is not an error; it means zero results found.";
                LogLine($strError, \Scooper\C__DISPLAY_ITEM_DETAIL__);

                $this->_setSearchSuccessResult_($searchDetails, $success = true, $details = 'Search found no matching, unfiltered jobs.', array());

            } else {
                //
                // Not the known issue case, so log the error and re-throw the exception
                // if we should have thrown one
                //
                $strError = "Failed to download jobs from " . $this->siteName . " jobs for search '" . $searchDetails['key'] . "[URL=" . $searchDetails['search_start_url'] . "].  " . $ex->getMessage() . PHP_EOL . "Exception Details: " . $ex;
                $this->_setSearchResultError_($searchDetails, $strError, $ex, $this->arrSearchReturnedJobs, null);
                handleException($ex, $strError, false);
            }
        } finally {
            $GLOBALS['logger']->logSectionHeader(("Finished data pull for " . $this->siteName . "[" . $searchDetails['key'] . "]"), \Scooper\C__NAPPTOPLEVEL__, \Scooper\C__SECTION_END__);
        }

        if (!is_null($ex)) {
            throw $ex;
        }

    }

    private function _checkInvalidURL_($details, $strURL)
    {
        if ($strURL == null) throw new ErrorException("Skipping " . $this->siteName . " search '" . $details['key'] . "' because a valid URL could not be set.");
        return $strURL;
        // if($strURL == VALUE_NOT_SUPPORTED) LogLine("Skipping " . $this->siteName ." search '".$details['key']. "' because a valid URL could not be set.");
    }


    private function _setSearchSuccessResult_(&$searchDetails, $success = null, $details = "UNKNOWN RESULT.", $arrSearchedJobs = null, $objSimpleHTMLResults = null)
    {
        if (isDebug() === true)
            $this->_writeDebugFiles_($searchDetails, "SUCCESS", $arrSearchedJobs, $objSimpleHTMLResults);
        $this->_setSearchResult_($searchDetails, $success, $details, null, $files = array());
    }


    private function _setSearchResult_(&$searchDetails, $success = null, $details = "UNKNOWN RESULT.", $exception = null, $files = array())
    {
        LogLine("Setting result value for search '" . $searchDetails['key'] . "' equal to " . ($success == 1 ? "true" : "false") . " with details '" . $details . "'.", \Scooper\C__DISPLAY_ITEM_DETAIL__);

        if (!array_key_exists($searchDetails['key'], $GLOBALS['USERDATA']['search_results']))
            throw new Exception("Error - Cannot Set Search Result for key " . $searchDetails['key'] . ".  Key does not exist in search results array.");

        $srr = $searchDetails->getSearchRunResult();

        $errFiles = $srr['error_files'];
        if (is_null($errFiles)) $errFiles = array();
        if (count($files) > 0) {
            foreach ($files as $f)
                $errFiles[$f] = $f;
        }

        $line = null;
        $code = null;
        $msg = null;
        $file = null;

        if (!is_null($exception)) {
            $line = $exception->getLine();
            $code = $exception->getCode();
            $msg = $exception->getMessage();
            $file = $exception->getFile();
        }
        $srr = array(
            'success' => $success,
            'error_datetime' => new DateTime(),
            'error_details' => $details,
            'exception_code' => $code,
            'exception_message' => $msg,
            'exception_line' => $line,
            'exception_file' => $file,
            'error_files' => $errFiles
        );
        $searchDetails->setSearchRunResult($srr);

        $GLOBALS['USERDATA']['search_results'][$searchDetails['key']] = $searchDetails;
    }


    private function _setSearchResultError_(&$searchDetails, $err = "UNKNOWN Error.", $exception = null, $arrSearchedJobs = array(), $objSimpleHTMLResults = null)
    {
        $arrErrorFiles = array();
        LogLine("Setting error for search '" . $searchDetails['key'] . "' with error '" . $err . "'.", \Scooper\C__DISPLAY_ERROR__);

        if (!array_key_exists($searchDetails['key'], $GLOBALS['USERDATA']['search_results'])) {
            throw new Exception("Error - Cannot Set Search Result for key " . $searchDetails['key'] . ".  Key does not exist in search results array.");
        }

        $this->_writeDebugFiles_($searchDetails, "ERROR", $arrSearchedJobs, $objSimpleHTMLResults);

        $this->_setSearchResult_($searchDetails, $success = false, $details = $err, $exception, $files = $arrErrorFiles);
    }

    function saveDomToFile($htmlNode, $filepath)
    {

        $strHTML = strval($htmlNode);

        $htmlTmp = \voku\helper\HtmlDomParser::str_get_html($strHTML);
        $htmlTmp->save($filepath);

        return $strHTML;

    }


    protected function _writeDebugFiles_(&$searchDetails, $keyName = "UNKNOWN", $arrSearchedJobs = null, $objSimpleHTMLResults = null)
    {
        if (isDebug() === true) {
            LogLine("Writing debug files for plugin " . $this->siteName . "'s search" . $searchDetails['key'], \Scooper\C__DISPLAY_ITEM_DETAIL__);

            $debugHTMLVarFile = $GLOBALS['USERDATA']['directories']['debug'] . "/" . getDefaultJobsOutputFileName($strFilePrefix = "_debug_htmlvar_" . "-" . $keyName, $strBase = $searchDetails['key'], $strExt = "html", $delim = "-");
            $debugHTMLSelenFile = $GLOBALS['USERDATA']['directories']['debug'] . "/" . getDefaultJobsOutputFileName($strFilePrefix = "_debug_htmlselen_" . "-" . $keyName, $strBase = $searchDetails['key'], $strExt = "html", $delim = "-");
            $debugSSFile = $GLOBALS['USERDATA']['directories']['debug'] . "/" . getDefaultJobsOutputFileName($strFilePrefix = "_debug_htmlselen_" . "-" . $keyName, $strBase = $searchDetails['key'], $strExt = "png", $delim = "-");
            $debugCSVFile = substr($debugHTMLVarFile, 0, strlen($debugHTMLVarFile) - 4) . ".csv";

            if (!is_null($objSimpleHTMLResults)) {
                $this->saveDomToFile($objSimpleHTMLResults, $debugHTMLVarFile);
                $arrErrorFiles[$debugHTMLVarFile] = $debugHTMLVarFile;
                LogLine("Wrote page HTML variable out to " . $debugHTMLVarFile, \Scooper\C__DISPLAY_ITEM_DETAIL__);
            }

            if ($this->selenium != null) {
                $driver = $this->selenium->get_driver();
                $html = $driver->getPageSource();
                file_put_contents($debugHTMLSelenFile, $html);
                $arrErrorFiles[$debugHTMLSelenFile] = $debugHTMLSelenFile;
                LogLine("Wrote page HTML from Selenium out to " . $debugHTMLSelenFile, \Scooper\C__DISPLAY_ITEM_DETAIL__);

                $driver->takeScreenshot($debugSSFile);
                $arrErrorFiles[$debugSSFile] = $debugSSFile;
                LogLine("Saved screenshot from Selenium out to " . $debugSSFile, \Scooper\C__DISPLAY_ITEM_DETAIL__);
            }

            if (!is_null($arrSearchedJobs) && is_array($arrSearchedJobs) && countJobRecords($arrSearchedJobs) > 0) {
                $this->writeJobsListToFile($debugCSVFile, $arrSearchedJobs);
                $arrErrorFiles[$debugCSVFile] = $debugCSVFile;
                LogLine("Wrote results CSV data to " . $debugCSVFile, \Scooper\C__DISPLAY_ITEM_DETAIL__);
            }
        }
    }

    protected function _getMyJobsForSearchFromJobsAPI_(&$searchDetails)
    {
        $nItemCount = 0;

        LogLine("Downloading count of " . $this->siteName . " jobs for search '" . $searchDetails['key'] . "'", \Scooper\C__DISPLAY_ITEM_DETAIL__);

        $pageNumber = 1;
        $noMoreJobs = false;
        while ($noMoreJobs != true) {
            $arrPageJobsList = [];
            $apiJobs = $this->getSearchJobsFromAPI($searchDetails);
            if (is_null($apiJobs)) {
                LogLine("Warning: " . $this->siteName . "[" . $searchDetails['key'] . "] returned zero jobs from the API." . PHP_EOL, \Scooper\C__DISPLAY_WARNING__);
                return null;
            }

            foreach ($apiJobs as $job) {
                $item = $this->getEmptyJobListingRecord();
                $item['job_title'] = $job->name;
                $item['job_id'] = $job->sourceId;
                if ($item['job_id'] == null)
                    $item['job_id'] = $job->url;

                if (strlen(trim($item['job_title'])) == 0 || strlen(trim($item['job_id'])) == 0) {
                    continue;
                }
                $item['location'] = $job->location;
                $item['company'] = $job->company;
                if ($job->datePosted != null)
                    $item['job_site_date'] = $job->datePosted->format('Y-m-d');
                $item['job_post_url'] = $job->url;

                $strCurrentJobIndex = $job['key_jobsite_siteid'];
                $arrPageJobsList[$strCurrentJobIndex] = $item;
                $nItemCount += 1;
            }
            if (count($arrPageJobsList) < $this->nJobListingsPerPage) {
                $this->saveUserJobMatches($arrPageJobsList, $searchDetails);
                $noMoreJobs = true;
            } else {
                $this->saveUserJobMatches($arrPageJobsList, $searchDetails);
            }
            $pageNumber++;
        }

        LogLine($this->siteName . "[" . $searchDetails['key'] . "]" . ": " . $nItemCount . " jobs found." . PHP_EOL, \Scooper\C__DISPLAY_ITEM_RESULT__);

    }


    protected function runJavaScriptSnippet($jscript = "", $wrap_in_func = true)
    {
        $driver = $this->getActiveWebdriver();

        if ($wrap_in_func === true) {
            $jscript = "function call_from_php() { " . $jscript . " }; call_from_php();";
        }

        LogLine("Executing JavaScript in browser:  " . $jscript, \Scooper\C__DISPLAY_ITEM_DETAIL__);

        $ret = $driver->executeScript($jscript);

        sleep(5);

        return $ret;
    }
    
    function cleanupJobItemFields($arrItem)
    {
        $arrItem ['job_post_url'] = trim($arrItem['job_post_url']); // DO NOT LOWER, BREAKS URLS

        if (!is_null($arrItem['job_post_url']) || strlen($arrItem['job_post_url']) > 0) {
            $arrMatches = array();
            $matchedHTTP = preg_match(REXPR_MATCH_URL_DOMAIN, $arrItem['job_post_url'], $arrMatches);
            if (!$matchedHTTP) {
                $sep = "";
                if (substr($arrItem['job_post_url'], 0, 1) != "/")
                    $sep = "/";
                $arrItem['job_post_url'] = $this->siteBaseURL . $sep . $arrItem['job_post_url'];
            }
        } else {
            $arrItem['job_post_url'] = "[UNKNOWN]";
        }

        if (is_null($arrItem['job_id']) || strlen($arrItem['job_id']) <= 0)
            $arrItem['job_id'] = $arrItem['job_post_url'];

        $arrItem['job_id'] = preg_replace(REXPR_MATCH_URL_DOMAIN, "", $arrItem['job_id']);
        $arrItem ['job_id'] = \Scooper\strScrub($arrItem['job_id'], FOR_LOOKUP_VALUE_MATCHING);
        if (is_null($arrItem['job_id']) || strlen($arrItem['job_id']) == 0) {
            if (isset($this->regex_link_job_id)) {
                $item['job_id'] = $this->getIDFromLink($this->regex_link_job_id, $arrItem['job_post_url']);
            }
        }

        return $arrItem;

    }

    function saveUserJobMatches($arrJobList, $searchDetails)
    {

        $arrJobsBySitePostId = array_column($arrJobList, null, "job_id");
        if(!array_key_exists($searchDetails->getKey(), $this->arrSearchReturnedJobs))
            $this->arrSearchReturnedJobs[$searchDetails->getKey()] = array();

        foreach (array_keys($arrJobsBySitePostId) as $JobSitePostId) {
            $arrJob = $this->cleanupJobItemFields($arrJobsBySitePostId[$JobSitePostId]);
            $job = updateOrCreateJobPosting($arrJob);

            $newMatch = \JobScooper\UserJobMatchQuery::create()
                ->filterByUserSlug($this->userObject->getUserSlug())
                ->filterByJobPostingId($job->getJobPostingId())
                ->findOneOrCreate();

            $newMatch->setJobPostingId($job->getJobPostingId());
            $newMatch->setUserSlug($this->userObject->getUserSlug());
            $newMatch->setAppRunId($GLOBALS['USERDATA']['configuration_settings']['app_run_id']);
            $newMatch->save();
            $this->arrSearchReturnedJobs[$searchDetails->getKey()][$job->getKeySiteAndPostID()] = $job->getJobPostingId();

        }
    }

//    function saveJobList(&$arrJobList)
//    {
//        if ($arrJobList == null) return null;
//
//        foreach (array_keys($arrJobList) as $k) {
////            $this->normalizeJobItem($arrJobList[$k]);
//        }
//        $savedJobIds = $this->addNewJobsToDB($arrJobList);
//
//        return $savedJobIds;
//
//    }
//

    function saveJobList($arrJobs)
    {
        $addedJobIds = array();
        foreach ($arrJobs as $job) {
            $savedJob = updateOrCreateJobPosting($job);
            $addedJobIds[] = $savedJob->getJobPostingId();
        }

        return $addedJobIds;
    }

    protected function getJobsDbIds($arrJobs)
    {
        $arrIds = array_column($arrJobs, 'job_id', 'job_id');
        $queryData = \JobScooper\JobPostingQuery::create()
            ->select(array("JobPostingId", "JobSitePostID", "JobSite", "KeySiteAndPostID"))
            ->filterByJobSite($this->siteName)
            ->filterByJobSitePostID(array_values($arrIds))
            ->find();
        $jobResults = $queryData->toArray();

        return $jobResults;
    }

//    protected function getJobsFromDB($arrJobs)
//    {
//        $arrIds = array_column($arrJobs, 'job_id', 'job_id');
//        $queryData = \JobScooper\JobPostingQuery::create()
//            ->filterByJobSite($this->siteName)
//            ->filterByJobSitePostID(array_values($arrIds))
//            ->find();
//
//        return $queryData;
//    }
//


    function saveJob($job)
    {
        if (!array_key_exists("job_id", $job) || strlen($job['job_id']) <= 0)
            return null;
        $job['job_site'] = $this->siteName;
        return updateOrCreateJobPosting($job);
    }


    protected function getSimpleHtmlDomFromSeleniumPage()
    {
        $objSimpleHTML = null;
        try {
            $html = $this->getActiveWebdriver()->getPageSource();
            $objSimpleHTML = new SimpleHtmlDom\simple_html_dom($html, null, true, null, null, null, null);
        } catch (Exception $ex) {
            $strError = "Failed to get dynamic HTML via Selenium due to error:  " . $ex->getMessage();
            handleException(new Exception($strError), null, true);
        }
        return $objSimpleHTML;
    }


    private function _getMyJobsForSearchFromWebpage_(&$searchDetails)
    {

        $nItemCount = 1;
        $nPageCount = 1;
        $objSimpleHTML = null;

        LogLine("Getting count of " . $this->siteName . " jobs for search '" . $searchDetails['key'] . "': " . $searchDetails['search_start_url'], \Scooper\C__DISPLAY_ITEM_DETAIL__);

        try {
            if ($this->isBitFlagSet(C__JOB_USE_SELENIUM)) {
                try {
                    if (is_null($this->selenium)) {
                        $this->selenium = new SeleniumSession($this->additionalLoadDelaySeconds);
                    }
                    $html = $this->selenium->getPageHTML($searchDetails['search_start_url']);
                    $objSimpleHTML = new SimpleHtmlDom\simple_html_dom($html, null, true, null, null, null, null);
                } catch (Exception $ex) {
                    $strError = "Failed to get dynamic HTML via Selenium due to error:  " . $ex->getMessage();
                    handleException(new Exception($strError), null, true);
                }
            } else {
                $objSimpleHTML = $this->getSimpleObjFromPathOrURL(null, $searchDetails['search_start_url'], $this->secsPageTimeout, $referrer = $this->prevURL, $cookies = $this->prevCookies);
            }
            if (!$objSimpleHTML) {
                throw new ErrorException("Error:  unable to get SimpleHTML object for " . $searchDetails['search_start_url']);
            }

            $totalPagesCount = C__TOTAL_ITEMS_UNKNOWN__;
            $nTotalListings = C__TOTAL_ITEMS_UNKNOWN__; // placeholder because we don't know how many are on the page
            if ($this->isBitFlagSet(C__JOB_ITEMCOUNT_NOTAPPLICABLE__) && $this->isBitFlagSet(C__JOB_PAGECOUNT_NOTAPPLICABLE__)) {
                switch ($this->paginationType) {

                    case C__PAGINATION_INFSCROLLPAGE_NOCONTROL:
                    case C__PAGINATION_INFSCROLLPAGE_VIALOADMORE:
                    case C__PAGINATION_PAGE_VIA_NEXTBUTTON:
                    case C__PAGINATION_INFSCROLLPAGE_VIA_JS:
                    case C__PAGINATION_PAGE_VIA_CALLBACK:
                        $totalPagesCount = C__TOTAL_ITEMS_UNKNOWN__;
                        $nTotalListings = C__TOTAL_ITEMS_UNKNOWN__;
                        break;

                    default:
                        // if we can't get a number of pages AND we can't get a number of items,
                        // we must assume there is, at most, only one page of results.
                        $totalPagesCount = 1;
                        $nTotalListings = $this->nJobListingsPerPage;
                        break;
                }
            } elseif (!$this->isBitFlagSet(C__JOB_ITEMCOUNT_NOTAPPLICABLE__) || !$this->isBitFlagSet(C__JOB_PAGECOUNT_NOTAPPLICABLE__)) {

                //
                // If we are in debug mode, save the HTML we got back for the listing count page to disk so it is
                // easy for a developer to review it
                //
                if (isDebug() == true && !is_null($objSimpleHTML) && !is_null($objSimpleHTML->root)) {
                    $this->_writeDebugFiles_($searchDetails, "parseTotalResultsCount", null, $objSimpleHTML->root);
                }

                $strTotalResults = $this->parseTotalResultsCount($objSimpleHTML->root);
                $nTotalListings = intval(str_replace(",", "", $strTotalResults));
                if ($nTotalListings == 0) {
                    $totalPagesCount = 0;
                } elseif ($nTotalListings != C__TOTAL_ITEMS_UNKNOWN__) {
                    if ($nTotalListings > $this->nMaxJobsToReturn) {
                        LogLine("Search '" . $searchDetails['key'] . "' returned more results than allowed.  Only retrieving the first " . $this->nMaxJobsToReturn . " of  " . $nTotalListings . " job listings.", \Scooper\C__DISPLAY_WARNING__);
                        $nTotalListings = $this->nMaxJobsToReturn;
                    }
                    $totalPagesCount = \Scooper\intceil($nTotalListings / $this->nJobListingsPerPage); // round up always
                    if ($totalPagesCount < 1) $totalPagesCount = 1;
                }
            }


            //
            // If this is just a test run to verify everything is functioning and all plugins are returning data,
            // then only bring back the first page and/or first 10 or so results to verify.  We don't need to bring
            // back hundreds of results to test things are running successfully.
            //
            if (isTestRun()) {
                $maxListings = $this->nJobListingsPerPage * 2;
                if ($nTotalListings > $maxListings) {
                    $nTotalListings = $maxListings;
                    $totalPagesCount = 2;
                }
            }


            if ($nTotalListings <= 0) {
                LogLine("No new job listings were found on " . $this->siteName . " for search '" . $searchDetails['key'] . "'.", \Scooper\C__DISPLAY_ITEM_START__);
                return array();
            } else {
                $nJobsFound = 0;

                LogLine("Querying " . $this->siteName . " for " . $totalPagesCount . " pages with " . ($nTotalListings == C__TOTAL_ITEMS_UNKNOWN__ ? "an unknown number of" : $nTotalListings) . " jobs:  " . $searchDetails['search_start_url'], \Scooper\C__DISPLAY_ITEM_START__);

                $strURL = $searchDetails['search_start_url'];
                while ($nPageCount <= $totalPagesCount) {

                    $arrPageJobsList = null;

                    if ($this->isBitFlagSet(C__JOB_USE_SELENIUM)) {
                        try {
                            switch (strtoupper($this->paginationType)) {

                                case C__PAGINATION_NONE:
                                    $totalPagesCount = 1;
                                    $this->selenium->loadPage($strURL);
                                    break;

                                case C__PAGINATION_PAGE_VIA_URL:
                                    $strURL = $this->getPageURLfromBaseFmt($searchDetails, $nPageCount, $nItemCount);
                                    if ($this->_checkInvalidURL_($searchDetails, $strURL) == VALUE_NOT_SUPPORTED)
                                        return null;
                                    $this->selenium->loadPage($strURL);
                                    break;

                                case C__PAGINATION_INFSCROLLPAGE_VIALOADMORE:
                                    $this->selenium->loadPage($strURL);
                                    //
                                    // If we dont know how many pages to go down,
                                    // call the method to go down to the very end so we see the whole page
                                    // and whole results set
                                    //
                                    $this->goToEndOfResultsSetViaLoadMore($nTotalListings);
                                    $totalPagesCount = 1;
                                    break;

                                case C__PAGINATION_INFSCROLLPAGE_NOCONTROL:
                                    $this->selenium->loadPage($strURL);
                                    //
                                    // if we know how many pages to do do, call the page down method
                                    // until we get to the right number of pages
                                    //
                                    while ($nPageCount <= $totalPagesCount) {
                                        if (isDebug() == true) {
                                            LogLine("... getting infinite results page #" . $nPageCount . " of " . $totalPagesCount, \Scooper\C__DISPLAY_NORMAL__);
                                        }
                                        $this->moveDownOnePageInBrowser();
                                        $nPageCount = $nPageCount + 1;
                                    }
                                    $totalPagesCount = $nPageCount;
                                    break;

                                case C__PAGINATION_INFSCROLLPAGE_VIA_JS:
                                    if (is_null($this->nextPageScript)) {
                                        handleException(new Exception("Plugin " . $this->siteName . " is missing nextPageScript settings for the defined pagination type."), "", true);

                                    }
                                    $this->selenium->loadPage($strURL);

                                    if ($nPageCount > 1 && $nPageCount <= $totalPagesCount) {
                                        $this->runJavaScriptSnippet($this->nextPageScript, true);
                                        sleep($this->additionalLoadDelaySeconds + 1);
                                    }
                                    break;

                                case C__PAGINATION_PAGE_VIA_NEXTBUTTON:
                                    if (is_null($this->selectorMoreListings)) {
                                        throw(new Exception("Plugin " . $this->siteName . " is missing selectorMoreListings setting for the defined pagination type."));

                                    }
                                    $this->selenium->loadPage($strURL);

                                    if ($nPageCount > 1 && ($totalPagesCount == C__TOTAL_ITEMS_UNKNOWN__ || $nPageCount <= $totalPagesCount)) {
                                        $ret = $this->goToNextPageOfResultsViaNextButton();
                                        if ($ret == false)
                                            $totalPagesCount = $nPageCount;
                                    }
                                    break;

                                case C__PAGINATION_PAGE_VIA_CALLBACK:
                                    if (!method_exists($this, 'takeNextPageAction')) {
                                        handleException(new Exception("Plugin " . $this->siteName . " is missing takeNextPageAction method definiton required for its pagination type."), "", true);
                                    }

                                    if ($nPageCount > 1 && $nPageCount <= $totalPagesCount) {
                                        //
                                        // if we got a driver instance back, then we got a new page
                                        // otherwise we're out of results so end the loop here.
                                        //
                                        try {
                                            $this->takeNextPageAction($this->selenium->driver);
                                        } catch (Exception $ex) {
                                            handleException($ex, ("Failed to take nextPageAction on page " . $nPageCount . ".  Error:  %s"), true);
                                        }
                                    }
                                    break;

                                default:
                                    handleException(null, "No pagination method defined for plugin " . $this->siteName, false);
                                    break;
                            }

                            $strURL = $this->selenium->driver->getCurrentURL();
                            $html = $this->selenium->driver->getPageSource();
                            $objSimpleHTML = new SimpleHtmlDom\simple_html_dom($html, null, true, null, null, null, null);
                            //
                            // If we are in debug mode, save the HTML we got back for the listing count page to disk so it is
                            // easy for a develooper to review it
                            //
                            if (isDebug() && !is_null($objSimpleHTML) && !is_null($objSimpleHTML->root)) {
                                $this->_writeDebugFiles_($searchDetails, "page" . $nPageCount . "-loaded", null, $objSimpleHTML->root);
                            }


                        } catch (Exception $ex) {
                            handleException($ex, "Failed to get dynamic HTML via Selenium due to error:  %s", true);
                        }
                    } else {
                        $strURL = $this->getPageURLfromBaseFmt($searchDetails, $nPageCount, $nItemCount);
                        if ($this->_checkInvalidURL_($searchDetails, $strURL) == VALUE_NOT_SUPPORTED)
                            return null;

                        $objSimpleHTML = $this->getSimpleObjFromPathOrURL(null, $strURL, $this->secsPageTimeout, $referrer = $this->prevURL, $cookies = $this->prevCookies);
                    }
                    if (!$objSimpleHTML) throw new ErrorException("Error:  unable to get SimpleHTML object for " . $strURL);

                    LogLine("Getting jobs page # " . $nPageCount . " of " . $totalPagesCount . " from " . $strURL . ".  Total listings loaded:  " . ($nItemCount == 1 ? 0 : $nItemCount) . "/" . $nTotalListings . ".", \Scooper\C__DISPLAY_ITEM_DETAIL__);
                    try {

                        $arrPageJobsList = $this->parseJobsListForPage($objSimpleHTML);
                        if (!is_array($arrPageJobsList)) {
                            // we likely hit a page where jobs started to be hidden.
                            // Go ahead and bail on the loop here
                            $strWarnHiddenListings = "Could not get all job results back from " . $this->siteName . " for this search starting on page " . $nPageCount . ".";
                            if ($nPageCount < $totalPagesCount)
                                $strWarnHiddenListings .= "  They likely have hidden the remaining " . ($totalPagesCount - $nPageCount) . " pages worth. ";

                            LogLine($strWarnHiddenListings, \Scooper\C__DISPLAY_ITEM_START__);
                            $nPageCount = $totalPagesCount;
                        }

                        if (is_array($arrPageJobsList)) {
                            $this->saveUserJobMatches($arrPageJobsList, $searchDetails);
                            $nJobsFound = count($this->arrSearchReturnedJobs[$searchDetails->getKey()]);

                            if ($nItemCount == 1) {
                                $nItemCount = 0;
                            }
                            $nItemCount += ($nJobsFound < $this->nJobListingsPerPage) ? $nJobsFound : $this->nJobListingsPerPage;

                            // If we don't know the total number of listings we will get, we can guess that we've got them all
                            // if we did not get the max number of job listings from the last page.  Basically, if we couldn't
                            // fill up a page with our search, then they must not be that many listings avaialble.
                            //
                            if ($totalPagesCount > 1 && $nTotalListings == C__TOTAL_ITEMS_UNKNOWN__ && countAssociativeArrayValues($arrPageJobsList) < $this->nJobListingsPerPage) {
                                $totalPagesCount = $nPageCount;
                                $nTotalListings = countAssociativeArrayValues($this->arrSearchReturnedJobs[$searchDetails->getKey()]);
                            }

                            LogLine("Loaded " . countAssociativeArrayValues($this->arrSearchReturnedJobs[$searchDetails->getKey()]) . " of " . $nTotalListings . " job listings from " . $this->siteName, \Scooper\C__DISPLAY_NORMAL__);
                        }
                    } catch (Exception $ex) {
                        handleException($ex, ($this->siteName . " error: %s"), true);
                    }

                    //
                    // Look check for plugin errors that are not caught.  If we have looped through one page of results,
                    // we should either have returned at least 1 listing of the total count OR if we have retrieved fewer
                    // listings than are expected on a page, then we should our page count should be the same as the last page.
                    //
                    // If either is not true, then we're likely in an error condition and about to go a bit wacky, possibly in a major loop.
                    // Throw an error for this search instead and move on.
                    //
                    $err = null;
                    $marginOfErrorAllowed = .05;
                    if ($nTotalListings > 0 && $nItemCount == 0) // We got zero listings but should have found some
                        $err = "Retrieved 0 of the expected " . $nTotalListings . " listings for " . $this->siteName . " (search = " . $searchDetails['key'] . ")";
                    elseif ($nItemCount < $this->nJobListingsPerPage && $nPageCount < $totalPagesCount)
                        $err = "Retrieved only " . $nItemCount . " of the " . $this->nJobListingsPerPage . " job listings on page " . $nPageCount . " for " . $this->siteName . " (search = " . $searchDetails['key'] . ")";
                    elseif ($nJobsFound < $nTotalListings * (1 - $marginOfErrorAllowed) && $nPageCount == $totalPagesCount && !$this->isBitFlagSet(C__JOB_ITEMCOUNT_NOTAPPLICABLE__))
                        $err = "Retrieved only " . $nJobsFound . " of the " . $nTotalListings . " listings that we expected for " . $this->siteName . " (search = " . $searchDetails['key'] . ")";
                    elseif ($nJobsFound > $nTotalListings * (1 + $marginOfErrorAllowed) && $nPageCount == $totalPagesCount && !$this->isBitFlagSet(C__JOB_ITEMCOUNT_NOTAPPLICABLE__)) {
                        $warnMsg = "Warning:  Downloaded " . ($nJobsFound - $nTotalListings) . " jobs more than the " . $nTotalListings . " expected for " . $this->siteName . " (search = " . $searchDetails['key'] . ")";
                        LogLine($warnMsg, \Scooper\C__DISPLAY_WARNING__);
                    }

                    if (!is_null($err)) {
                        if ($this->isBitFlagSet(C__JOB_IGNORE_MISMATCHED_JOB_COUNTS) || $this->isBitFlagSet(C__JOB_ITEMCOUNT_NOTAPPLICABLE__) === true) {
                            LogLine("Warning: " . $err, \Scooper\C__DISPLAY_WARNING__);
                        } else {
                            $err = "Error: " . $err . "  Aborting job site plugin to prevent further errors.";
                            LogLine($err, \Scooper\C__DISPLAY_ERROR__);
                            handleException(new Exception($err), null, true);
                        }
                    }

                    $nPageCount++;
                }

            }

            LogLine($this->siteName . "[" . $searchDetails['key'] . "]" . ": " . $nJobsFound . " jobs found." . PHP_EOL, \Scooper\C__DISPLAY_ITEM_RESULT__);

        } catch (Exception $ex) {
            $this->_setSearchResultError_($searchDetails, "Error: " . $ex->getMessage(), $ex, $this->arrSearchReturnedJobs, $objSimpleHTML);
            handleException($ex, null, true);
        } finally {
            // clean up memory
            if (!is_null($objSimpleHTML)) {
                $objSimpleHTML->clear();
                unset($objSimpleHTML);
            }
        }

        return null;
    }

    protected function getSearchJobsFromAPI($searchDetails)
    {
        throw new \BadMethodCallException(sprintf("Not implemented method " . __METHOD__ . " called on class \"%s \".", __CLASS__));
    }
}