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



class PluginOuterwall extends \JobScooper\BasePlugin\Classes\ServerHtmlSimplePlugin
{
    protected $JobSiteName = 'Outerwall';
    protected $JobPostingBaseUrl = 'http://outerwall.jobs';
    protected $SearchUrlFormat = "http://outerwall.jobs/***LOCATION***/usa/jobs/";
    protected $LocationType = 'location-state';

    protected $arrListingTagSetup = array(

        'TotalPostCount' => array('tag' => 'h3', 'attribute' => 'class', 'attribute_value' =>'direct_highlightedText', 'return_attribute' => 'text', 'return_value_regex' => '/.*?(\d+).*?/'),
        'JobPostItem' => array(array('tag' => 'ul', 'attribute'=>'class', 'attribute_value' => 'default_jobListing'), array('tag' => 'li')),
        'Title' => array(array('tag' => 'h4'), array('tag' => 'a'), array('tag' => 'span'), 'return_attribute' => 'text'),
        'Url' =>  array(array('tag' => 'h4'), array('tag' => 'a'), 'return_attribute' => 'href'),
        'Location' => array('tag' => 'div', 'attribute' => 'class', 'attribute_value' =>'direct_joblocation'),
        'regex_link_job_id' => '/\/[j\/]{0,2}(.*)/i'
    );

}

