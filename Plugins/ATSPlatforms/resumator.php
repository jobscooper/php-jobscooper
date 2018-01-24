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


//abstract class AbstractResumator extends \JobScooper\BasePlugin\Classes\ServerHtmlSimplePlugin
//{
//    protected $arrListingTagSetup = array(
//        'JobPostItem' => array('selector' => 'div#resumator-content-left-wrapper div.resumator-job'),
//        'Title' => array('tag' => 'a', 'attribute' => 'class', 'attribute_value' =>'resumator-job-title-link'),
//        'Url' => array('tag' => 'a', 'attribute' => 'class', 'attribute_value' =>'resumator-job-title-link', 'return_attribute' => 'href'),
//        'Department' => array('tag' => 'span', 'attribute' => 'class', 'attribute_value' =>'resumator-job-info'),
//        'Location' => null,
//        'regex_link_job_id' => '/.com\/apply\/(\S*)\//i',
//    );
//}
//

abstract class AbstractResumatorFall2017  extends \JobScooper\BasePlugin\Classes\ServerHtmlSimplePlugin
{
	protected $arrListingTagSetup = array(
		'JobPostItem' => array('selector' => 'ul.list-group li.list-group-item'),
		'Title' => array('selector' => 'h4.list-group-item-heading a'),
		'Url' => array('selector' => 'h4.list-group-item-heading a', 'return_attribute' => 'href'),
		'Location' => array('selector' => 'ul li', 'index' => 0),
		'Department' => array('selector' => 'ul li', 'index' => 1),
		'regex_link_job_id' => '/.com\/apply\/(\S*)\//i',
	);
}

class PluginAtlanticMedia extends AbstractResumatorFall2017
{
    protected $JobSiteName = 'AtlanticMedia';
    protected $childSiteURLBase = 'http://atlanticmedia.theresumator.com/';
    protected $childSiteListingPage = 'http://atlanticmedia.theresumator.com/';
}




class PluginMashableCorporate extends AbstractResumatorFall2017
{
    protected $JobSiteName = 'MashableCorporate';
    protected $childSiteURLBase = 'http://mashable.theresumator.com/';
    protected $childSiteListingPage = 'http://mashable.theresumator.com/';

}







