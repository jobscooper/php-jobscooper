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



class PluginPersonForce extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'PersonForce';
    protected $JobPostingBaseUrl = 'http://www.personforce.com';
    protected $SearchUrlFormat = 'https://www.personforce.com/jobs/tags/***KEYWORDS***/in/***LOCATION***/p/***PAGE_NUMBER***';
    protected $LocationType = 'location-city-comma-statecode';
    protected $JobListingsPerPage = 20;
    protected $additionalLoadDelaySeconds = 5;
    protected $PaginationType = C__PAGINATION_PAGE_VIA_URL;


    protected $arrListingTagSetup = array(

    'TotalPostCount' => array(array('tag' => 'div', 'attribute'=>'class', 'attribute_value' => 'module_jobs'), array('tag' => 'div', 'attribute_value' =>'text'), 'return_value_regex' => '/.*?of total (\d+).*?/'),
    'JobPostItem' => array(array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'content-col-content hir left'),array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'row'),array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'row')),
    'Title' => array(array('tag' => 'div', 'attribute' => 'class', 'attribute_value' => 'hir-job-title'), array('tag' => 'a'), 'return_attribute' => 'text'),
    'Url' => array(array('tag' => 'div', 'attribute' => 'class', 'attribute_value' => 'hir-job-title'), array('tag' => 'a'), 'return_attribute' => 'href'),
    'Company' => array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'hir-company-title', 'return_value_regex' => '/(.*?) \- .*/'),
    'Location' => array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'hir-company-title', 'return_value_regex' => '/.*? \- (.*)/'),
    'NextButton' => array('selector' => 'div.pagination ul li.active a'),
    'JobSitePostId' =>  array(array('tag' => 'div', 'attribute' => 'class', 'attribute_value' => 'hir-job-title'), array('tag' => 'a'), 'return_attribute' => 'href'),
    //        'regex_link_job_id' => '/.*?\/(\d+)|.*?;ad=-(.{1,})$/'
    );


    protected function normalizeJobItem($arrItem)
    {

        $arrItem ['PostedAt'] = strScrub($arrItem['PostedAt'], REMOVE_EXTRA_WHITESPACE | LOWERCASE | HTML_DECODE );
        $dateVal = strtotime($arrItem ['PostedAt'], $now = time());
        if(!($dateVal === false))
        {
            $arrItem['PostedAt'] = date('Y-m-d', $dateVal);
        }


        $arrItem['JobSitePostId'] = strScrub($arrItem['Company'], FOR_LOOKUP_VALUE_MATCHING) . strScrub($arrItem['Title'], FOR_LOOKUP_VALUE_MATCHING). strScrub($arrItem['PostedAt'], FOR_LOOKUP_VALUE_MATCHING);

        return parent::normalizeJobItem($arrItem);
    }


}
