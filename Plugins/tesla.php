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



class PluginTesla extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'Tesla';
    protected $JobPostingBaseUrl = 'http://www.tesla.com/careers';
    // BUGBUG:  Hard coded to region = North America (#4)
    protected $SearchUrlFormat = 'https://www.tesla.com/careers/search#/filter/?region=4';
    protected $CountryCodes = array("US", "UK");

    protected $PaginationType = C__PAGINATION_NONE;
    protected $nMaxJobsToReturn = C_JOB_MAX_RESULTS_PER_SEARCH;
    protected $JobListingsPerPage = C_JOB_MAX_RESULTS_PER_SEARCH;

    protected $arrListingTagSetup = array(
        'JobPostItem' => array('selector' => "table tr.table-row" ),
        'Title' => array('selector' => 'th.listing-title a', 'return_attribute' => 'text'),
        'Url' => array('selector' => 'th.listing-title a', 'return_attribute' => 'href'),
        'JobSitePostId' => array('selector' => 'th.listing-title a', 'return_attribute' => 'href', 'return_value_regex' =>  '/.*?-(\d+)/i'),
        'Department' => array('selector' => 'td.listing-department'),
        'Location' => array('selector' => 'td.listing-location')
    );

}


