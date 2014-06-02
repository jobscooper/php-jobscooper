<?php
/**
 * Copyright 2014 Bryan Selner
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
require_once dirname(__FILE__) . '/Options.php';
require_once dirname(__FILE__) . '/ClassJobsSitePluginCommon.php';



abstract class ClassJobsSitePlugin extends ClassJobsSitePluginCommon
{
    protected $siteName = 'NAME-NOT-SET';
    protected $arrLatestJobs = null;
    protected $arrSearchesToReturn = null;
    protected $nJobListingsPerPage = 20;
    protected $flagAutoMarkListings = true; // All the called classes do it for us already

    function __construct($bitFlags = null, $strOutputDirectory = null)
    {
        $this->_bitFlags = $bitFlags;
        if($strOutputDirectory == null)
        {
            $strOutputDirectory = $GLOBALS['output_file_details']['directory'];
        }
        $this->setOutputFolder($strOutputDirectory);
    }

    function __destruct()
    {
        __debug__printLine("Closing ".$this->siteName." class instance.", C__DISPLAY_ITEM_START__);

        //
        // Write out the interim data to file if we're debugging
        //
        if($GLOBALS['OPTS']['DEBUG'] == true)
        {

            if($this->arrLatestJobs != null)
            {
                $strOutPathWithName = $this->getOutputFileFullPath($this->siteName . "_");
                __debug__printLine("Writing ". $this->siteName." " .count($this->arrLatestJobs) ." job records to " . $strOutPathWithName . " for debugging (if needed).", C__DISPLAY_ITEM_START__);
                $this->writeMyJobsListToFile($strOutPathWithName, false);
            }
        }
    }


    abstract function parseJobsListForPage($objSimpHTML); // returns an array of jobs
    abstract function parseTotalResultsCount($objSimpHTML); // returns a settings array


    /**  NOT YET TESTED AND INTEGRATED
    //
    // Parses a relative date string such as "5 hrs ago" or "22 days ago"
    // and returns a date string representing the actual date (i.e. "2014-05-15")
    // the relative string represents.
    function getDateFromRelativeDateString($strDaysPast, $fReturnNullForFailure = false)
    {
        $nRetNumber = null;
        $strRetUnit = null;
        $nRetDays = null;

        //
        // First, let's break the string into it's words
        //
        $arrDateStringWords = explode(" ", $strDaysPast);

        if(count($arrDateStringWords) <= 1) return null; // we don't know enough to parse the value

        // Let's see if the first item is numeric
        if(is_string($arrDateStringWords[0]) && is_numeric($arrDateStringWords[0]))
        {
            $nRetNumber = floatval($arrDateStringWords[0]);

            switch ($arrDateStringWords[1])
            {
                case "hrs":
                case "hr":
                case "hours":
                case "hour":
                $strRetUnit = "hours";
                    $nRetDays = intceil($nRetNumber / 24);  // divide to get number of days and then round up
                    break;

                case "d":
                case "days":
                case "day":
                $strRetUnit = "hours";
                    $nRetDays = intceil($nRetNumber);  // divide to get number of days
                    break;

                default:
                    return null;  // we don't know what this is so return null
                    break;
            }
        }

        if($strRetUnit != null && $strRetUnit != "")
        {
            $now = new DateTime();
            $retDate = $now->sub(new DateInterval('P'.$nRetDays.'D')); // P1D means a period of 1 day
            return $retDate->format('Y-m-d');
        }

        //
        // If we were told to return null on failure, return null.
        //
        if($fReturnNullForFailure == true)
        {
            return null;
        }

        //
        // Return the input string if we weren't told to return null on failure
        //
        return $strDaysPast;

    }
**/

    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */
    function getMyJobsList() { return $this->arrLatestJobs; }


    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */
    function loadMyJobsListFromCSVs($arrFilesToLoad)
    {
        $arrAllJobsLoadedFromSrc = $this->loadJobsListFromCSVs($arrFilesToLoad);


        // These will be used at the beginning and end of
        // job processing to filter out jobs we'd previous seen
        // and to make sure our notes get updated on active jobs
        // that we'd seen previously
        //
        //
        // Set a global var with an array of all input cSV jobs marked new or not marked as excluded (aka "Yes" or "Maybe")
        //
        $GLOBALS['active_jobs_from_input_source_files'] = array_filter($arrAllJobsLoadedFromSrc, "isMarked_InterestedOrBlank");

        //
        // Set a global var with an array of all input CSV jobs that are not in the first set (aka marked Not Interested & Not Blank)
        //
        $GLOBALS['inactive_jobs_from_input_source_files'] = array_filter($arrAllJobsLoadedFromSrc, "isMarked_NotInterestedAndNotBlank");

        //
        // Initialize the run's jobs list with all the jobs we'd previously set as inactive.
        //
        $this->arrLatestJobs =  $GLOBALS['inactive_jobs_from_input_source_files'];
    }


    /**
     * Main worker function for all jobs sites.
     *
     *
     * @param  integer $nDays Number of days of job listings to pull
     * @param  Array $arrInputFilesToMergeWithResults Optional list of jobs list CSV files to include in the results
     * @param  integer $fIncludeFilteredJobsInResults If true, filters out jobs flagged with "not interested" values from the results.
     * @return string If successful, the final output CSV file with the full jobs list
     */
    function downloadAllUpdatedJobs($nDays = -1)
    {
        $retFilePath = '';

        // Now go download and output the latest jobs from this site
        __debug__printLine("Downloading new ". $this->siteName ." jobs...", C__DISPLAY_ITEM_START__);

        //
        // Call the child classes getJobs function to update the object's array of job listings
        // and output the results to a single CSV
        //
        $this->getJobsForAllSearches($nDays);

        if($this->flagAutoMarkListings == true)
        {
            $this->markMyJobsList_withAutoItems();
        }
    }



    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */

    function getActualPostURL($strSrcURL)
    {
        $retURL = null;

        $classAPI = new APICallWrapperClass();
        __debug__printLine("Getting source URL for ". $strSrcURL , C__DISPLAY_ITEM_START__);

        try
        {
            $curlObj = $classAPI->cURL($strSrcURL);
            if($curlObj && !$curl_object['error_number'] && $curl_object['error_number'] == 0 )
            {
                $retURL  =  $curlObj['actual_site_url'];
            }
        }
        catch(ErrorException $err)
        {
            // do nothing
        }
        return $retURL;
    }

    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */
    function is_IncludeBrief()
    {
        $val = $this->_bitFlags & C_EXCLUDE_BRIEF;
        $notVal = !($this->_bitFlags & C_EXCLUDE_BRIEF);
        // __debug__printLine('ExcludeBrief/not = ' . $val .', '. $notVal, C__DISPLAY_ITEM_START__);
        return false;
    }

    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */
    function is_IncludeActualURL()
    {
        $val = $this->_bitFlags & C_EXCLUDE_GETTING_ACTUAL_URL;
        $notVal = !($this->_bitFlags & C_EXCLUDE_GETTING_ACTUAL_URL);
        // __debug__printLine('ExcludeActualURL/not = ' . $val .', '. $notVal, C__DISPLAY_ITEM_START__);

        return !$notVal;
    }

/*    function getOutputFileFullPath($strFilePrefix = "", $strBase = "jobs", $strExtension = "csv")
    {
        return parent::getOutputFileFullPath($this->siteName . "_" . $strFilePrefix, $strBase, $strExtension);
    }
*/
    function getMyOutputFileFullPath($strFilePrefix = "")
    {
        return parent::getOutputFileFullPath($this->siteName . "_" . $strFilePrefix, "jobs", "csv");
    }

    function markMyJobsList_withAutoItems()
    {
        $this->markJobsList_withAutoItems($this->arrLatestJobs, $this->siteName);
    }


    /**
     * Write this class instance's list of jobs to an output CSV file.  Always rights
     * the full unfiltered list.
     *
     *
     * @param  string $strOutFilePath The file to output the jobs list to
     * @param  Array $arrMyRecordsToInclude An array of optional job records to combine into the file output CSV
     * @return string $strOutFilePath The file the jobs was written to or null if failed.
     */
    function writeMyJobsListToFile($strOutFilePath = null)
    {
        return $this->writeJobsListToFile($strOutFilePath, $this->arrLatestJobs, true, false, $this->siteName);
    }


    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */
    function _addJobsToMyJobsList_($arrAdd)
    {
        addJobsToJobsList($this->arrLatestJobs, $arrAdd);

    }

    /**
     * TODO:  DOC
     *
     *
     * @param  string TODO DOC
     * @param  string TODO DOC
     * @return string TODO DOC
     */
    function getJobsForAllSearches($nDays = -1)
    {
        foreach($this->arrSearchesToReturn as $search)
        {
            $strIncludeKey = 'include_'.strtolower($search['site_name']);

            if($GLOBALS['OPTS'][$strIncludeKey] == null || $GLOBALS['OPTS'][$strIncludeKey] == 0)
            {
                __debug__printLine($search['site_name'] . " excluded, so skipping its '" . $search['search_name'] . "' search.", C__DISPLAY_ITEM_START__);

                continue;
            }

            $class = null;
            $nLastCount = count($this->arrLatestJobs);
            __debug__printLine("Running ". $search['site_name'] . " search '" . $search['search_name'] ."'...", C__DISPLAY_SECTION_START__);

            $strSite = strtolower($search['site_name']);
            if(strcasecmp($strSite, $this->siteName) == 0)
            {
                $this->getMyJobsForSearch($search, $nDays);
            }
        }
    }
    function getMyJobsForSearch($search, $nDays = -1)
    {
        $nItemCount = 1;
        $nPageCount = 1;

        try
        {
            $strURL = $this->_getURLfromBase_($search, $nDays, $nPageCount, $nItemCount);
            __debug__printLine("Getting count of " . $this->siteName ." jobs for search '".$search['search_name']. "': ".$strURL, C__DISPLAY_ITEM_DETAIL__);
            var_dump( $strURL);
            $objSimpleHTML = $this->getSimpleObjFromPathOrURL(null, $strURL );
            if(!$objSimpleHTML) throw new ErrorException("Error:  unable to get SimpleHTML object for ".$strURL);
        }
        catch (ErrorException $ex)
        {
            throw new ErrorException("Error:  unable to getMyJobsForSearch from ".$strURL. " Reason:".$ex->getMessage());
            return;
        }
        $strTotalResults = $this->parseTotalResultsCount($objSimpleHTML);
        if($strTotalResults == C__JOB_PAGECOUNT_NOTAPPLICABLE__)
        {
            $totalPagesCount = 1;
            $nTotalListings = C__JOB_ITEMCOUNT_UNKNOWN__ ; // placeholder because we don't know how many are on the page
        }
        else
        {
            $strTotalResults  = intval(str_replace(",", "", $strTotalResults));
            $nTotalListings = intval($strTotalResults);
            $totalPagesCount = intceil($nTotalListings  / $this->nJobListingsPerPage); // round up always
            if($totalPagesCount < 1)  $totalPagesCount = 1;
        }

        if($nTotalListings <= 0)
        {
            __debug__printLine("No new job listings were found on " . $this->siteName . " for search '" . $search['search_name']."'.", C__DISPLAY_ITEM_START__);
            return;
        }

        __debug__printLine("Querying " . $this->siteName ." for " . $totalPagesCount . " pages with ". ($nTotalListings == C__JOB_ITEMCOUNT_UNKNOWN__  ? "a not yet know number of" : $nTotalListings) . " jobs:  ".$strURL, C__DISPLAY_ITEM_START__);

        while ($nPageCount <= $totalPagesCount )
        {
            $arrPageJobsList = null;

            $objSimpleHTML = null;
            $strURL = $this->_getURLfromBase_($search, $nDays, $nPageCount, $nItemCount);

            if(!$objSimpleHTML) $objSimpleHTML = $this->getSimpleObjFromPathOrURL(null, $strURL);
            if(!$objSimpleHTML) throw new ErrorException("Error:  unable to get SimpleHTML object for ".$strURL);

            $arrPageJobsList = $this->parseJobsListForPage($objSimpleHTML);


            if(!is_array($arrPageJobsList))
            {
                // we likely hit a page where jobs started to be hidden.
                // Go ahead and bail on the loop here
                __debug__printLine("Not getting results back from ". $this->siteName . " starting on page " . $nPageCount.".  They likely have hidden the remaining " . $maxItem - $nPageCount. " pages worth. ", C__DISPLAY_ITEM_START__);
                $nPageCount = $totalPagesCount ;
            }
            else
            {
                $this->_addJobsToMyJobsList_($arrPageJobsList);
                $nItemCount += $this->nJobListingsPerPage;
            }

            // clean up memory
            $objSimpleHTML->clear();
            unset($objSimpleHTML);
            $nPageCount++;

        }

        __debug__printLine(PHP_EOL.$this->siteName . "[".$search['search_name']."]" .": " . $nItemCount . " jobs found." .PHP_EOL, C__DISPLAY_ITEM_RESULT__);

    }

    protected function getMyJobsFromHTMLFiles($strCompanyName)
    {

        $nItemCount = 1;
        $dataFolder = $this->strOutputFolder ;

        $strFileName = $dataFolder . $strCompanyName. "-jobs-page-".$nItemCount.".html";
        if(!is_file($strFileName)) // try the current folder instead
        {
            $dataFolder = "./";
            $strFileName = $dataFolder . $strCompanyName. "-jobs-page-".$nItemCount.".html";
        }
        if(!is_file($strFileName)) // last try the debugging data folder
        {
            $dataFolder = C_STR_DATAFOLDER;
            $strFileName = $dataFolder . $strCompanyName. "-jobs-page-".$nItemCount.".html";
        }

        while (file_exists($strFileName) && is_file($strFileName))
        {
            $objSimpleHTML = $this->getSimpleHTMLObjForFileContents($strFileName);
            if(!$objSimpleHTML)
            {
                throw new ErrorException('Error:  unable to get SimpleHTML object from file('.$filePath.') or '.$strURL);
            }

            $arrNewJobs = $this->parseJobsListForPage($objSimpleHTML);

            $objSimpleHTML->clear();
            unset($objSimpleHTML);

            $this->_addJobsToMyJobsList_($arrNewJobs);

            $nItemCount++;

            $strFileName = $dataFolder . $strCompanyName . "-jobs-page-".$nItemCount.".html";

        }
    }

    function getDaysURLValue($days) { return ($days == null || $days == "") ? 1 : $days; } // default is to return the raw number
    function getItemURLValue($nItem) { return ($nItem == null || $nItem == "") ? 0 : $nItem; } // default is to return the raw number
    function getPageURLValue($nPage) { return ($nPage == null || $nPage == "") ? 0 : $nPage; } // default is to return the raw number

    function addSearchURL($site, $name, $fmtURL)
    {
        $this->addSearches(array('site_name' => $site, 'search_name' => $name, 'base_url_format' =>$fmtURL));

    }



    function addSearches($arrSearches)
    {
        foreach($arrSearches as $search)
        {
            $this->arrSearchesToReturn[] = $search;
        }
    }


    protected function _getURLfromBase_($search, $nDays, $nPage, $nItem = null)
    {
        $strURL = $search['base_url_format'];
        $strURL = str_ireplace("***NUMBER_DAYS***", $this->getDaysURLValue($nDays), $strURL );
        $strURL = str_ireplace("***PAGE_NUMBER***", $nPage, $strURL );
        $strURL = str_ireplace("***ITEM_NUMBER***", $this->getItemURLValue($nItem), $strURL );
        return $strURL;
    }






}

?>
