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
if (!strlen(__ROOT__) > 0) { define('__ROOT__', dirname(dirname(dirname(__FILE__)))); }
require_once(__ROOT__ . '/include/ClassJobsSiteCommon.php');




class PluginBoeing extends BasePluginTalentBrew
{
    protected $siteName = 'Boeing';
    protected $siteBaseURL = 'https://jobs.boeing.com';
    protected $nJobListingsPerPage = 20;

}

class PluginDisney extends BasePluginTalentBrew
{
    protected $siteName = 'Disney';
    protected $siteBaseURL = 'https://jobs.disneycareers.com';
//    protected $additionalFlags = [ C__JOB_CLIENTSIDE_INFSCROLLPAGE ];
    protected $nJobListingsPerPage = 15;

    function __construct($strBaseDir = null)
    {

        parent::__construct($strBaseDir);
        $this->strBaseURLFormat = $this->strBaseURLFormat  . "?orgIds=391-5733-5732&kt=1";
    }

}

class BasePluginTalentBrew extends ClassClientHTMLJobSitePlugin
{
    protected $strBaseURLFormat = "/search-jobs/***KEYWORDS***/***LOCATION***";
    //
    // BUGBUG:  Disney & Boeing are both hit or miss around returning the full set of listings correctly.
    //          Setting to ignore_mismatched to avoid the error results that will happen when they do.
    //
    protected $additionalFlags = [ C__JOB_KEYWORD_PARAMETER_SPACES_RAW_ENCODE, C__JOB_IGNORE_MISMATCHED_JOB_COUNTS ];
    protected $typeLocationSearchNeeded = 'location-city-comma-statecode';
    protected $additionalLoadDelaySeconds = 5;

    protected $nJobListingsPerPage = 50;

    protected $arrListingTagSetup = array(
        'tag_listings_count' => array(array('tag' => 'section', 'attribute' => 'id', 'attribute_value' => 'search-results'), 'return_attribute' => 'data-total-results'),
        'tag_listings_section' => array(array('tag' => 'section', 'attribute' => 'id', 'attribute_value' => 'search-results-list'), array('tag' => 'ul'),array('tag' => 'li')),
        'tag_title' =>  array(array('tag' => 'a'), array('tag' => 'h2')),
        'tag_link' =>  array(array('tag' => 'a'), 'return_attribute' => 'href'),
        'tag_job_id' =>  array(array('tag' => 'a'), 'return_attribute' => 'data-job-id'),
        'tag_company' =>  array('return_value_callback' => 'ClassBaseHTMLJobSitePlugin::setCompanyToSiteName'),
        'tag_location' =>  array('tag' => 'span', 'attribute' => 'class', 'attribute_value' => 'job-location'),
        'tag_job_posting_date' =>  array('tag' => 'span', 'attribute' => 'class', 'attribute_value' => 'job-date-posted'),
        'tag_next_button' => array('selector' => '#pagination-bottom > div.pagination-paging > a.next')
    );
    function __construct($strBaseDir = null)
    {

        parent::__construct($strBaseDir);
        $this->strBaseURLFormat = $this->siteBaseURL . $this->strBaseURLFormat;
    }

}