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
require_once dirname(__FILE__) . '/../include/ClassJobsSitePlugin.php';



class PluginDisney extends ClassJobsSitePlugin
{
    protected $siteName = 'Disney';
    protected $siteBaseURL = 'http://disneycareers.com/';



    function getDaysURLValue($nDays)
    {
        if($nDays > 1)
        {
            __debug__printLine($this->siteName ." jobs can only be pulled for, at most, 1 day.  Ignoring number of days value and just pulling current listings.", C__DISPLAY_WARNING__);

        }
        return 1;

    }



    function parseTotalResultsCount($objSimpHTML)
    {
        $resultsSection= $objSimpHTML->find("div[id='searchResultMessage'] h1");  // "Your Search returned 30  results"
        $totalItemsText = $resultsSection[0]->plaintext;
        $arrItemItems = explode(" ", trim($totalItemsText));
        $strTotalItemsCount = trim($arrItemItems[3]);
        $strTotalItemsCount = str_replace(",", "", $strTotalItemsCount);

        __debug__printLine($this->siteName ." only pulling the last 10 jobs posted out of " . $strTotalItemsCount, C__DISPLAY_WARNING__);

        return $strTotalItemsCount;
    }

    function parseJobsListForPage($objSimpHTML)
    {
        $ret = null;


        $nodesJobs= $objSimpHTML->find('table[id="searchResultsBlock"] tr');


        foreach($nodesJobs as $node)
        {
/*            if(strcasecmp($node->attr['class'], "gradeA even") != 0 &&
                strcasecmp($node->attr['class'], "gradeA odd") != 0)
            {
                continue;
            }
*/
            $item = parent::getEmptyItemsArray();
            $item['job_site'] = $this->siteName;
            $item['company'] = $this->siteName;

            $titleLink = $node->find("td[class='column1'] div a")[0];


            $item['job_title'] = $titleLink->plaintext;
            $item['job_post_url']  = $titleLink->href;

          if($item['job_title'] == '') continue;

            $item['job_id'] = explode("jobid=", $item['job_post_url'])[1];


            $item['job_site_category'] = strScrub($node->find("td[class='column2'] div")[0]->plaintext);
            $item['location'] = strScrub($node->find("td[class='column3'] div span[class='bold-text']")[0]->plaintext);

            $item['date_pulled'] = $this->getTodayAsString();

            $item['job_site_date'] = $node->find("td[class='column4']")[0]->plaintext;

            $ret[] = $this->normalizeItem($item);
        }

//        var_dump($node->getAllAttributes());

        return $ret;
    }

}