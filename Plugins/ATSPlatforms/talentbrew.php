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


/*class PluginHP extends AbstractTalentBrew
{
    protected $JobSiteName = "HP";
    protected $JobPostingBaseUrl = 'https://h30631.www3.hp.com';
    protected $JobListingsPerPage = 15;
    protected $SearchUrlFormat = "/search-jobs";  // HP's keyword search is a little squirrelly so more successful if we don't filter up front and get the mismatches removed later
    protected $additionalBitFlags = [ C__PAGINATION_INFSCROLLPAGE_NOCONTROL];

    function __construct($strBaseDir = null)
    {
        unset($this->arrListingTagSetup['NextButton']);

        parent::__construct();
    }

    function takeNextPageAction($nItem=null, $nPage=null)
    {
        $this->runJavaScriptSnippet("document.getElementsByClassName(\"next\")[0].setAttribute(\"class\", \"pagination-show-all\"); document.getElementsByClassName(\"pagination-show-all\")[0].click();", false, $this->additionalLoadDelaySeconds + 10);
        sleep($this->additionalLoadDelaySeconds+1 );
        $this->nMaxJobsToReturn = C_JOB_MAX_RESULTS_PER_SEARCH * 3;
        $this->JobListingsPerPage = $this->nMaxJobsToReturn;

        $this->moveDownOnePageInBrowser();
        
    }
}*/


class PluginBoeing extends AbstractTalentBrew
{
    protected $JobSiteName = 'Boeing';
    protected $JobPostingBaseUrl = 'https://jobs.boeing.com';
    protected $JobListingsPerPage = 20;
    protected $SearchUrlFormat = "/search-jobs";
    protected $arrListingTagSetup = array(
        'TotalPostCount' => array('selector' => 'h1[role="status"]'),
        'JobPostItem' => array('selector' => 'section#search-results-list ul li'),
//        'Title' =>  array('selector' => 'a h2', 'return_value_regex' =>  '/([^–]*)/i'),
        'Title' =>  array('selector' => 'a h2'),
        'Url' =>  array('selector' => 'a', 'return_attribute' => 'href'),
        'JobSitePostId' =>  array('selector' => 'a', 'return_attribute' => 'data-job-id'),
//        'Location' =>  array('selector' => 'a h2', 'return_value_regex' =>  '/[^–]*\s?–\s(.*)/i'),
        'PostedAt' =>  array('selector' => 'span.job-date-posted'),
        'NextButton' => array('selector' => '#pagination-bottom a.next')
    );

    function __construct()
    {
        $this->arrListingTagSetup['TotalPostCount'] = array('selector' => 'h1[role="status"]');
        parent::__construct();
    }
}

class PluginDisney extends AbstractTalentBrew
{
    protected $JobSiteName = 'Disney';
    protected $JobPostingBaseUrl = 'https://jobs.disneycareers.com';
    protected $additionalBitFlags = [ C__JOB_ITEMCOUNT_NOTAPPLICABLE__ ];
    protected $JobListingsPerPage = 15;

    function __construct()
    {
        parent::__construct();
        unset($this->arrListingTagSetup['TotalPostCount']);
        $this->arrListingTagSetup['JobPostItem'] = array(array('tag' => 'section', 'attribute' => 'id', 'attribute_value' => 'search-results-list'), array('tag' => 'table'),array('tag' => 'tr'));
        $this->arrListingTagSetup['NextButton'] = array(array('tag' => 'a', 'attribute' => 'class', 'attribute_value' => 'next', 'index' => 0));


        $this->SearchUrlFormat = $this->JobPostingBaseUrl . "/search-jobs?k=&alp=6252001-5815135&alt=3";
    }

}

class AbstractTalentBrew extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $SearchUrlFormat = "/search-jobs/***KEYWORDS***/***LOCATION***";
    //
    // BUGBUG:  Disney & Boeing are both hit or miss around returning the full set of listings correctly.
    //          Setting to ignore_mismatched to avoid the error results that will happen when they do.
    //
    protected $LocationType = 'location-city-comma-statecode';
    protected $additionalLoadDelaySeconds = 5;

    protected $JobListingsPerPage = 50;

    protected $arrListingTagSetup = array(
        'TotalPostCount' => array(array('tag' => 'section', 'attribute' => 'id', 'attribute_value' => 'search-results'), array('tag' => 'h1'), 'return_attribute' => 'text', 'return_value_regex' =>  '/(.*?) .*/'),
        'TotalResultPageCount' => array(array('tag' => 'span', 'attribute' => 'class', 'attribute_value' => 'pagination-total-pages'), 'return_attribute' => 'text', 'return_value_regex' =>  '/of (.*)/'),
        'JobPostItem' => array(array('tag' => 'section', 'attribute' => 'id', 'attribute_value' => 'search-results-list'), array('tag' => 'ul'),array('tag' => 'li')),
        'Title' =>  array('selector' => 'a h2'),
        'Url' =>  array('tag' => 'a', 'return_attribute' => 'href'),
        'JobSitePostId' =>  array('tag' => 'a', 'return_attribute' => 'data-job-id'),
        'Location' =>  array('tag' => 'span', 'attribute' => 'class', 'attribute_value' => 'job-location'),
        'PostedAt' =>  array('tag' => 'span', 'attribute' => 'class', 'attribute_value' => 'job-date-posted'),
        'NextButton' => array('selector' => '#pagination-bottom a.next')
    );
    function __construct()
    {

        parent::__construct();
        $this->SearchUrlFormat = $this->JobPostingBaseUrl . $this->SearchUrlFormat;
    }

}