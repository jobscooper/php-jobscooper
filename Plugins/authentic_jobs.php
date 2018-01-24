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


class PluginAuthenticJobs extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'AuthenticJobs';
    protected $JobPostingBaseUrl = "https://authenticjobs.com";
    protected $SearchUrlFormat = 'https://authenticjobs.com/#location=***LOCATION***';
#    protected $SearchUrlFormat = 'https://authenticjobs.com/#location=***LOCATION***&query=***KEYWORDS***';
    protected $LocationType = 'location-city';
    protected $JobListingsPerPage = 50;
	private $_currentSearchDetails = null;

    protected $arrListingTagSetup = array(
        'NoPostsFound'    => array('selector' => 'ul#listings li#no-results', 'return_attribute' => 'text', 'return_value_callback' => "checkNoJobResults"),
        'JobPostItem'      => array('selector' => 'ul#listings li'),
        'Title'                 =>  array('selector' => 'a div h3', 'return_attribute' => 'text'),
        'Url'                  =>  array('selector' => 'a', 'return_attribute' => 'href'),
        'Company'               =>  array('selector' => 'a div h4', 'return_attribute' => 'text'),
        'Location'              =>  array('selector' => 'a ul li.location', 'return_attribute' => 'text'),
        'EmploymentType'       =>  array('selector' => 'a ul li', 'index' => 0, 'return_attribute' => 'text'),
        'JobSitePostId'                =>  array('selector' => 'a', 'return_attribute' => 'href', 'return_value_regex' =>  '/\/jobs\/([^?]+)/i'),
        'LoadMoreControl'             =>  array('selector' => 'a.ladda-button')
    );

    function checkNoJobResults($var)
    {
        return noJobStringMatch($var, "No jobs");
    }

	function doFirstPageLoad(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails)
	{
		$this->_currentSearchDetails = $searchDetails;
	}

    protected function goToEndOfResultsSetViaLoadMore($nTotalItems)
    {
        $objSimplHtml = $this->getSimpleHtmlDomFromSeleniumPage($this->_currentSearchDetails);

        $node = $objSimplHtml->find("p.more");
        if($node == null || count($node) == 0)
        {
            return false;
        }
        else
        {
            if(stristr($node[0]->attr["style"], "display: none") !== false) {
                return false;
            }
        }

        return parent::goToEndOfResultsSetViaLoadMore($nTotalItems);
    }

}

