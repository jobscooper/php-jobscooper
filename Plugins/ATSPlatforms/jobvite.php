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
use JobScooper\DataAccess\UserSearchSiteRun;

abstract class AbstractJobviteATS extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    function __construct()
    {
        $this->additionalBitFlags[] = C__JOB_ITEMCOUNT_NOTAPPLICABLE__;
        parent::__construct();
    }
    static function checkNoJobResults($var)
    {
        return noJobStringMatch($var, "Found 0 jobs");
    }

    protected $arrListingTagSetup = array(
//        'JobPostItem'      => array('frame' => 'jobvite_careersite_iframe', 'selector' => 'table.jv-job-list tr'),
        'JobPostItem'      => array('selector' => 'table.jv-job-list tr'),
        'Title'                 => array('selector' => 'td.jv-job-list-name a'),
        'Url'                 => array('selector' => 'td.jv-job-list-name a', 'return_attribute' => 'href'),
        'Location'              => array('selector' => 'td.jv-job-list-location', 'return_attribute' => 'text'),
        'JobSitePostId'                 => array('selector' => 'td.jv-job-list-name a', 'return_attribute' => 'href', 'return_value_regex' =>  '/job\/(.*)/i'),
    );

	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null|void
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {

        $frame = $objSimpHTML->find("*[name='jobvite_careersite_iframe']");
        if(!empty($frame) && array_key_exists('attr', $frame[0]))
        {
            $srcurl = $frame[0]->attr["src"];
            if(!empty($srcurl)) {
                $newUrl = parse_url($srcurl);
                $currentUrl = parse_url($this->getActiveWebdriver()->getCurrentUrl());
                $newUrl['scheme'] = $currentUrl['scheme'];
                $url = http_build_url($newUrl);
                $objSimpHTML = $this->getSimpleHtmlDomFromSeleniumPage($url);
            }
        }
        return parent::parseJobsListForPage($objSimpHTML); // TODO: Change the autogenerated stub
    }
}
