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
if (!strlen(__ROOT__) > 0) { define('__ROOT__', dirname(dirname(__FILE__))); }
require_once(__ROOT__.'/include/ClassJobsSiteCommon.php');





class PluginGeekwire extends ClassBaseClientSideHTMLJobSitePlugin
{
    protected $siteName = 'Geekwire';
    protected $siteBaseURL = 'http://www.geekwire.com/jobs/';
    protected $strBaseURLFormat = "http://www.geekwire.com/jobs/";
//    protected $strBaseURLFormat = "http://www.geekwork.com/jobs/?search_keywords=***KEYWORDS***&search_location=***LOCATION***";
    protected $typeLocationSearchNeeded = 'location-statecode';
    protected $additionalLoadDelaySeconds = 20;
    protected $additionalFlags = [C__JOB_ITEMCOUNT_NOTAPPLICABLE__, C__JOB_PREFER_MICRODATA, C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED, C__JOB_DAYS_VALUE_NOTAPPLICABLE__, C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED, C__JOB_PAGECOUNT_NOTAPPLICABLE__];

    function parseJobsListForPage($objSimpHTML)
    {

        $ret = null;

        $nodesJobs = $objSimpHTML->find("ul[class='job_listings'] li[class='type-job_listing']");

        foreach($nodesJobs as $node)
        {
            $item = $this->getEmptyJobListingRecord();
            $item['job_site'] = $this->siteName;

            $item['job_title'] = $node->find("h3")[0]->plaintext;
            $item['job_post_url'] = $node->find("a")[0]->href;

            $item['location'] = $node->find("div[class='location']")[0]->plaintext;

            $item['company'] = $node->find("div[class='company'] span")[0]->plaintext;
            $item['date_pulled'] = getTodayAsString();

            $item['job_site_date'] = $node->find("li[class='date']")[0]->plaintext;
            $dateVal = date_create_from_format("c", $item['job_site_date']);
            if(isset($dateVal))
                $item['job_site_date'] = $dateVal->format('Y-m-d');

            $item['job_site_category'] = $node->find("ul[class='meta'] li")[0]->plaintext;


            $arrLIParts = explode(" ", $node->attr['class']);
            $item['job_id'] = str_ireplace("http://www.geekwire.com/jobs/job/", "", $arrLIParts[0]);

            $ret[] = $this->normalizeJobItem($item);

        }

        return $ret;
    }

}


?>