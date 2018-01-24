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


abstract class BaseForceComClass extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $additionalLoadDelaySeconds = 3;
    protected $JobListingsPerPage = 25;
    protected $PaginationType = C__PAGINATION_PAGE_VIA_CALLBACK;

	/**
	 * BaseForceComClass constructor.
	 */
	function __construct()
    {
        parent::__construct();

        if(is_null($this->SearchUrlFormat) || strlen($this->SearchUrlFormat) == 0) {
            $this->JobPostingBaseUrl = "http://" . strtolower($this->JobSiteName) . ".force.com/careers";
            $this->SearchUrlFormat = "http://" . strtolower($this->JobSiteName) . ".force.com/careers";
        }
    }

	/**
	 * @param null $nItem
	 * @param null $nPage
	 */
	function takeNextPageAction($nItem=null, $nPage=null)
	{
		$nextPageJS = "function contains(selector, text) {
                var elements = document.querySelectorAll(selector);
                return Array.prototype.filter.call(elements, function(element){
                return RegExp(text).test(element.textContent);
                });
            }
            var linkNext = contains('a', 'Next');
            if(linkNext.length >= 1)
            {
                console.log(linkNext[0]);
                linkNext[0].click();
            }
        ";

		$this->runJavaScriptSnippet($nextPageJS, false);
	}



	protected $arrListingTagSetup = array(
        'TotalPostCount' => array('selector' => 'div#atsSearchResultsText', 'return_value_regex' => '/(\d+).*?/'),
        'JobPostItem' => array('selector' => "table.atsSearchResultsTable tbody tr"),
        'Title' =>  array('selector' => 'td a', 'index' => 0, 'return_attribute' => 'text'),
        'Url' =>  array('selector' => 'td a', 'index' => 0, 'return_attribute' => 'href'),
        'JobSitePostId' =>  array('selector' => 'td a', 'index' => 0, 'return_attribute' => 'href', 'return_value_regex' => '/.*?jobId=(\w+)&.*?/'),
        'Department' =>  array('selector' => 'td span', 'index' => 0, 'return_attribute' => 'text'),
        'Location' =>  array('selector' => 'td span', 'index' => 1, 'return_attribute' => 'text'),
        'PostedAt' =>  array('selector' => 'td span', 'index' => 2, 'return_attribute' => 'text')
    );

}



abstract class BaseNoDeptForceComClass extends BaseForceComClass
{
	/**
	 * BaseNoDeptForceComClass constructor.
	 */
	function __construct()
    {
        parent::__construct();
        $this->arrListingTagSetup['Department'] = null;
        $this->arrListingTagSetup['Location']['index'] = 0;
        $this->arrListingTagSetup['PostedAt']['index'] = 1;
    }
}

class PluginSlalom extends BaseNoDeptForceComClass
{
    protected $JobSiteName = "Slalom";
}
