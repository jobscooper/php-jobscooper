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
require_once(__ROOT__ . '/include/ClassJobsSiteCommon.php');

class PluginCyclon extends ClassHTMLJobSitePlugin
{
    protected $siteName = 'Cylcon';
    protected $siteBaseURL = "http://cylcon.com";
//    protected $strBaseURLFormat = "http://cylcon.com/jobs.php?q=***KEYWORDS***&l=***LOCATION***&sort=date&radius=50&start=***ITEM_NUMBER***";
    protected $strBaseURLFormat = "http://www.cylcon.com/jobscylcon.php?q=***KEYWORDS***&l=***LOCATION***&sort=date&radius=50&start=***ITEM_NUMBER***";

    //
    // BUGBUG: We shouldn't have to do C__JOB_IGNORE_MISMATCHED_JOB_COUNTS here, but have not yet figured out what is causing lower counts
    //         to sporadically happen
    //
    protected $nJobListingsPerPage = 15;
    protected $typeLocationSearchNeeded = 'location-city-comma-statecode';

    function __construct($strBaseDir)
    {
        parent::__construct($strBaseDir);
    }
    protected function getItemURLValue($nItem) { return ($nItem == null || $nItem == "" || $nItem <= 1) ? 0 : $nItem; }

    protected $arrListingTagSetup = array(
        'tag_listings_count' => array('selector' => '#searchCount', 'return_attribute' => 'plaintext', 'return_value_regex' => '/.*?of\s+(\d+).*?/'),
        'tag_listings_section' => array('selector' => 'div.joblists'),
        'tag_title' => array('selector' => 'div.col-lg-10 a', 'return_attribute' => 'plaintext'),
        'tag_link' => array('selector' => 'div.col-lg-10 a', 'return_attribute' => 'href'),
        'tag_job_id' => array('selector' => 'div.col-lg-10 a', 'return_attribute' => 'href', 'return_value_regex' => '/.*?[Rr]edirect[Ww][Ee][Bb]\.php\?q=([^&]+)&*.*/'),
        'tag_company' => array('selector' => 'div.col-lg-10 p strong', 'return_value_regex' => '/(.*?)-.*/'),
        'tag_location' => array('selector' => 'div.col-lg-10 p strong span.location', 'return_value_regex' => '/-(.*?)-.*/'),
        'tag_job_category' => array('selector' => 'div.col-lg-10 p strong span.location b'),
        'tag_job_posting_date' => array('selector' => 'span.date'),
        'tag_next_button' => array('selector' => '#page-top > section > div > div.row.text-left > div.col-lg-9 > table > tbody > tr:nth-child(1) > td:nth-child(3) > a')
    );

    protected function normalizeJobItem($arrItem)
    {
        return $this->normalizeJobItemWithoutJobID($arrItem);
    }
}


