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



class PluginCraigslist extends \JobScooper\Plugins\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'Craigslist';
    protected $JobListingsPerPage = 120;
    protected $JobPostingBaseUrl = 'http://seattle.craigslist.org';
    protected $SearchUrlFormat = "http://***LOCATION***.craigslist.org/search/jjj?sort=date&query=***KEYWORDS***&srchType=T&searchNearby=1&s=***ITEM_NUMBER***";
//    protected $SearchUrlFormat = "http://***LOCATION***.craigslist.org/search/jjj?s=***ITEM_NUMBER***&catAbb=jjj&query=***KEYWORDS***&srchType=T&bundleDuplicates=1";
    // BUGBUG: craigslist treats sub-rows differently for counting results in different cases.  When a single page of results is returned, they are included in the overall count
    //         But when a multi-page result set is returned, they are not! Setting C__JOB_IGNORE_MISMATCHED_JOB_COUNTS to work around this.
    protected $additionalBitFlags = [C__JOB_LOCATION_REQUIRES_LOWERCASE, C__JOB_KEYWORD_SUPPORTS_QUOTED_KEYWORDS, C__JOB_RESULTS_SHOWN_IN_DATE_DESCENDING_ORDER];
    protected $LocationType = 'location-city';
    protected $strKeywordDelimiter = "|";
    protected $PaginationType = C__PAGINATION_PAGE_VIA_URL;

    protected $arrListingTagSetup = array(
        'NoPostsFound' => array('selector' => 'div.noresults', 'return_attribute' => 'text', 'return_value_callback' => "checkNoJobResults"),
        'TotalPostCount' => array('selector' => 'span.totalcount', 'index'=> 0, 'return_attribute' => 'text'),
        'JobPostItem' => array('selector' => 'ul.rows li.result-row'),
        'Url' => array('selector' => 'a.result-title', 'index'=> 0, 'return_attribute' => 'href'),
        'Title' => array('selector' => 'a.result-title', 'index'=> 0, 'return_attribute' => 'text'),
        'JobSitePostId' => array('selector' => 'a.result-title', 'index'=> 0, 'return_attribute' => 'data-id'),
        'Department' => array('selector' => 'td.listing-department', 'index'=> 0),
        'Location' => array('selector' => 'span.result-hood', 'index'=> 0),
        'PostedAt' => array('selector' => 'time.result-date', 'index'=> 0, 'return_attribute' => 'datetime')
    );

    static function checkNoJobResults($var)
    {
        return noJobStringMatch($var, "Nothing found");
    }

    function getItemURLValue($nItem)
    {
        if($nItem == null || $nItem == 0) { return 0; }

        return $nItem - 1;
    }

}

