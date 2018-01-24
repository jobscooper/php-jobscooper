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



class PluginSmashingMagazine extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'SmashingMagazine';
    protected $childSiteURLBase = 'http://jobs.smashingmagazine.com';
    protected $childSiteListingPage = 'http://jobs.smashingmagazine.com';
    protected $PaginationType = C__PAGINATION_NONE;

    protected $arrListingTagSetup = array(
        'JobPostItem' => array(array('tag' => 'ul', 'attribute' => 'class', 'attribute_value' =>'entry-list compact'), array('tag' => 'li')),
        'Title' => array('tag' => 'h2'),
        'Url' =>  array(array('tag' => 'a'), 'return_attribute' => 'href'),
        'JobSitePostId' =>  array(array('tag' => 'article'), 'return_attribute' => 'id'),
        'Company' => array('tag' => 'span', 'attribute' => 'class', 'attribute_value' =>'entry-company'),
        );

}
