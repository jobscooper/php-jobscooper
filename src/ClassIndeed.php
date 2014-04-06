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
require_once dirname(__FILE__) . '/../include/ClassJobsSite.php';


class ClassIndeed extends ClassJobsSite
{
    protected $siteName = 'Indeed';


    function getMyJobs($nDays = -1, $fIncludeFilteredJobsInResults = true)
    {
        switch($nDays)
        {
            case 7:
                $strSearch = "http://www.indeed.com/jobs?q=title%3A%28%22vice+president%22+or+VP+or+director+or+CTO+or+CPO+or+director+or+%22chief+product+officer%22+or+%22Chief+Technology+Officer%22%29&l=Seattle%2C+WA&sort=date&limit=50&fromage=7&start=";
                __debug__printLine("Getting " . $nDays . " days worth of postings from " . $this->siteName ." jobs: ".$strURL, C__DISPLAY_ITEM_START__);
                break;

            case 3:
                $strSearch = "http://www.indeed.com/jobs?q=title%3A%28%22vice+president%22+or+VP+or+director+or+CTO+or+CPO+or+director+or+%22chief+product+officer%22+or+%22Chief+Technology+Officer%22%29&l=Seattle%2C+WA&sort=date&limit=50&fromage=3&start=";
                __debug__printLine("Getting " . $nDays . " days worth of postings from " . $this->siteName ." jobs: ".$strURL, C__DISPLAY_ITEM_START__);
                break;

            default:  // Yesterday was giving me headaches, so switched "24 hours" to really mean last 3 days for Indeed
                $strDays = $nDays < 1 ? "24 hours" : $nDays;
                $strSearch = "http://www.indeed.com/jobs?q=title%3A%28%22vice+president%22+or+VP+or+director+or+CTO+or+CPO+or+director+or+%22chief+product+officer%22+or+%22Chief+Technology+Officer%22%29&l=Seattle%2C+WA&sort=date&limit=50&fromage=3&start=";
                __debug__printLine("Getting " . $strDays . " days worth of postings from " . $this->siteName ." jobs: ".$strURL, C__DISPLAY_ITEM_START__);
                break;
        }

        $this->__getMyJobsFromSearch__($strSearch, 'Exec Keywords in Seattle, WA', $strAlternateLocalHTMLFile);


    }


    private function __getMyJobsFromSearch__($strBaseURL, $category,  $strAlternateLocalHTMLFile = null)
    {
        $arrAllJobs = array();
        $nItemCount = 0;

        $objSimpleHTML = $this->getSimpleObjFromPathOrURL($strAlternateLocalHTMLFile, $strBaseURL);

        $nItemChunkSize = 50;


        // # of items to parse
        $pageDiv= $objSimpleHTML->find('div[id="searchCount"]');
        $pageDiv = $pageDiv[0];
        $pageText = $pageDiv->plaintext;
        $arrItemItems = explode(" ", trim($pageText));
        $totalItems = $arrItemItems[5];
        $totalItems  = intval(str_replace(",", "", $totalItems));


        $maxItem = intval($totalItems / $nItemChunkSize);

        $nURLItemCount = 1;

        if($maxItem < 1)  $maxItem = 1;

        __debug__printLine("Downloading " . $maxItem . " pages of ".$totalItems  . " jobs from " . $this->siteName , C__DISPLAY_ITEM_START__);

        while ($nURLItemCount <= $totalItems)
        {
            $objSimpleHTML = null;
            $strURL = $strBaseURL.$nURLItemCount;
            __debug__printLine("Querying for jobs  " . $nItemCount . " - " . ($nItemCount + $nItemChunkSize - 1) . " from " . $this->siteName ." jobs: ".$strURL, C__DISPLAY_ITEM_START__);

            if(!$objSimpleHTML) $objSimpleHTML = parent::getSimpleObjFromPathOrURL($strAlternateLocalHTMLFile, $strURL);
            if(!$objSimpleHTML) throw new ErrorException('Error:  unable to get SimpleHTML object from file('.$strAlternateLocalHTMLFile.') or '.$strURL);

            $arrNewJobs = $this->_scrapeItemsFromHTML_($objSimpleHTML, $category);
            if(!is_array($arrNewJobs))
            {
                // we likely hit a page where jobs started to be hidden.
                // Go ahead and bail on the loop here
                __debug__printLine("Not getting results back from Indeed starting on page " . intval($nItemCount/$nItemChunkSize).".  They likely have hidden the remaining " . $maxItem - $nPageCount. " pages worth. ", C__DISPLAY_ITEM_START__);
                $nItemCount = $maxItem;
            }
            else
            {
                $arrAllJobs = array_merge($arrAllJobs, $arrNewJobs);

                $nItemCount = count($arrAllJobs);
            }
            $nURLItemCount = $nURLItemCount + $nItemChunkSize + ($nURLItemCount == 1 ? -1 : 0);
            $nItemCount += $nItemChunkSize;

            // clean up memory
            $objSimpleHTML->clear();
            unset($objSimpleHTML);

        }
        $this->arrLatestJobs = array_copy($arrAllJobs);

        return $arrAllJobs;
    }

    private function _scrapeItemsFromHTML_($objSimpleHTML, $category)
    {
        $ret = null;


        $nodesJobs = $objSimpleHTML->find('div[class="row"]');


        foreach($nodesJobs as $node)
        {
            $item = parent::getEmptyItemsArray();
            $item['job_id'] = $node->attr['id'];
            $item['job_site'] = $this->siteName;



            $jobInfoNode = $node->firstChild()->firstChild();
            $item['job_title'] = $jobInfoNode->attr['title'];
            if($item['job_title'] == '') continue;

            $item['job_post_url'] = 'http://www.indeed.com' . $jobInfoNode->href;
            $item['company'] = trim($node->find("span[class='company'] span")[0]->plaintext);
            $item['location'] =trim( $node->find("span[class='location'] span")[0]->plaintext);
            $item['date_pulled'] = $this->_getCurrentDateAsString_();
            $item['job_site_date'] = $node->find("span[class='date']")[0]->plaintext;

            if($this->is_IncludeBrief() == true)
            {
                $item['brief_description'] = $node->find("span[class='summary']")[0]->plaintext;
            }


/*            $item[ 'script_search_key'] = $category;

            // calculate the original source
            $item['job_site'] = $this->siteName;
            $origSiteNode = $node->find("span[class='sdn']");
            if($origSiteNode && $origSiteNode[0])
            {
                $item['original_source'] = trim($origSiteNode[0]->plaintext);
            }
            if($this->is_IncludeActualURL())
            {
                $item['job_source_url'] = parent::getActualPostURL($item['job_post_url']);
            }
*/
            $ret[] = $item;

        }

        return $ret;
    }

}