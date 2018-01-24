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



/**
 * Class PluginZipRecruiter
 * @package JobScooper\Plugins
 */
class PluginZipRecruiter extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'ziprecruiter';
    protected $JobPostingBaseUrl = 'www.ziprecruiter.com';
    protected $JobListingsPerPage = C__TOTAL_ITEMS_UNKNOWN__; // we use this to make sure we only have 1 single results page
	protected $CountryCodes = array("US");

    protected $SearchUrlFormat = "https://www.ziprecruiter.com/candidate/search?search=***KEYWORDS***&include_near_duplicates=1&location=***LOCATION***&radius=25&days=***NUMBER_DAYS***";
    protected $PaginationType = C__PAGINATION_INFSCROLLPAGE_VIALOADMORE;
    protected $LocationType = 'location-city-comma-statecode';

    protected $arrListingTagSetup = array(
        'NoPostsFound'    => array('selector' => 'section.no-results h2', 'return_attribute' => 'text', 'return_value_callback' => "checkNoJobResults"),
        'TotalPostCount'        => array('selector' => 'h1.headline', 'return_attribute' => 'text', 'return_value_regex' =>  '/\b([\d,]+)\+?\b/i'),
        'JobPostItem'      => array('selector' => '#job_list div article'),
        'Title'                 => array('selector' => 'span.just_job_title', 'return_attribute' => 'text'),
        'Url'                  => array('tag' => 'a', 'attribute'=>'class', 'attribute_value' => 'job_link', 'return_attribute' => 'href'),
        'Company'               => array('tag' => 'a', 'attribute'=>'class', 'attribute_value' => 't_org_link name', 'return_attribute' => 'text'),
        'Location'              => array('tag' => '*', 'attribute'=>'class', 'attribute_value' => 'Location', 'return_attribute' => 'text'),
        'JobSitePostId'                => array('tag' => 'span', 'attribute'=>'class', 'attribute_value' => 'just_job_title', 'return_attribute' => 'data-job-id'),
    );

	/**
	 * @param $var
	 *
	 * @return int|null
	 * @throws \Exception
	 */
	function checkNoJobResults($var)
    {
        return noJobStringMatch($var, "No jobs");
    }

	/**
	 * @param $objSimpHTML
	 *
	 * @return null|string
	 * @throws \Exception
	 */
	function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        sleep($this->additionalLoadDelaySeconds + 1);

        $dismissPopup = "
            var popup = document.querySelector('.div#createAlertPop'); 
            if (popup != null) 
            {
                var popupstyle = popup.getAttribute('style'); 
                if (popupstyle!= null && popupstyle.indexOf('display: none') < 0) {
                    var close = document.querySelector('.modal-close'); 
                    if (close != null) 
                    {
                        console.log('Clicking close on modal popup dialog...');
                        close.click();
                    }
                }
            }
        ";

        $this->runJavaScriptSnippet($dismissPopup, true);

        return parent::parseTotalResultsCount($objSimpHTML);
    }

	/**
	 * @param null $nTotalItems
	 *
	 * @throws \Exception
	 */
	protected function goToEndOfResultsSetViaLoadMore($nTotalItems = null)
    {
        $this->selectorMoreListings = ".load_more_jobs";
        parent::goToEndOfResultsSetViaLoadMore($nTotalItems);

        parent::goToEndOfResultsSetViaPageDown($nTotalItems);

    }

}


class PluginZipRecruiterUK extends PluginZipRecruiter
{
	protected $JobSiteName = 'ZipRecruiterUK';
	protected $JobPostingBaseUrl = 'http://www.ziprecruiter.co.uk';
	protected $SearchUrlFormat = "https://www.ziprecruiter.co.uk/candidate/search?search=***KEYWORDS***&include_near_duplicates=1&location=***LOCATION***&radius=25&days=***NUMBER_DAYS***";
	protected $CountryCodes = array("UK");
}