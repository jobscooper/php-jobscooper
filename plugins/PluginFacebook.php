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



class PluginFacebook extends ClassJobsSitePlugin
{
    protected $siteName = 'Facebook';
    protected $siteBaseURL = 'https://www.facebook.com/careers/';
    protected $nJobListingsPerPage = 10;



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
        __debug__printLine($this->siteName ." does now show how many jobs are available as a count.  Processing everything we can.", C__DISPLAY_WARNING__);
        return 1;
    }

    function parseJobsListForPage($objSimpHTML)
    {
        $ret = null;


        $nodesJobs= $objSimpHTML->find('ul[class="careersList"]');

        foreach($nodesJobs as $node)
        {

            $item = parent::getEmptyItemsArray();
            $item['job_site'] = $this->siteName;
            $item['company'] = $this->siteName;
            $item['job_title'] = $node->find("li span a")[0]->plaintext;
            $item['job_post_url'] = $node->find("li span a")[0]->href;
            $item['job_id'] = explode("req=", $item['job_post_url'])[1];
            if($item['job_title'] == '') continue;

            $item['job_site_category'] = $node->parent()->find("h3")[0]->plaintext;

            $item['location'] = "Seattle, WA";
            $item['date_pulled'] = $this->getTodayAsString();

//            var_dump($item);
            $ret[] = $this->normalizeItem($item);
        }

        return $ret;
    }

}

?>