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


class AbstractIcimsATS extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
	protected $additionalBitFlags = [C__JOB_ITEMCOUNT_NOTAPPLICABLE__, C__JOB_DAYS_VALUE_NOTAPPLICABLE__, C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED];
	protected $JobListingsPerPage = 20;
	protected $strInitialReferer = null;
	protected $TagIndexes = null;
	protected $SiteReferenceKey = null;


	/**
	 * AbstractIcimsATS constructor.
	 *
	 * @param null $strBaseDir
	 *
	 * @throws \Exception
	 */
	function __construct($strBaseDir = null)
	{
		$this->JobPostingBaseUrl = "http://{$this->SiteReferenceKey}.icims.com";
		$this->strInitialReferer = $this->JobPostingBaseUrl . "/jobs/search?pr=0";
		$this->SearchUrlFormat = $this->JobPostingBaseUrl . "/jobs/search?mobile=true&width=1170&height=500&bga=true&needsRedirect=false&jan1offset=-480&jun1offset=-420&needsRedirect=false&in_iframe=1&pr=***PAGE_NUMBER***";
		$this->PaginationType = C__PAGINATION_PAGE_VIA_URL;

		foreach (array_keys($this->TagIndexes) as $tagKey) {
			if (array_key_exists($tagKey, $this->TagIndexes) === true && is_null($this->TagIndexes[$tagKey]) !== true &&
				array_key_exists($tagKey, $this->arrBaseListingTagSetup)) {
				if (array_key_exists(0, $this->arrBaseListingTagSetup[$tagKey]) && is_array($this->arrBaseListingTagSetup[$tagKey]) === true)
					$this->arrBaseListingTagSetup[$tagKey][0]['index'] = $this->TagIndexes[$tagKey];
				else
					$this->arrBaseListingTagSetup[$tagKey]['index'] = $this->TagIndexes[$tagKey];
			}
		}

		parent::__construct();
	}

	protected $arrBaseListingTagSetup = array(

		'JobPostItem'          => array('selector' => 'div.iCIMS_JobsTable div.row'),
		'Title'                => array('selector' => 'div.title a span'),
		'Url'                  => array('selector' => 'div.title a', 'return_attribute' => 'href'),
		'JobSitePostId'        => array('selector' => 'div.title a', 'return_attribute' => 'title', 'return_value_regex' => '/(\d+)/'),
		'Location'             => array('selector' => 'div span', 'index' => 0),
		'PostedAt'             => array('selector' => 'span.sr-only'),
		// TotalResultPageCount tag is a placeholder needed to trigger the right code paths, but is overridden by the
		// custom parseTotalResultsCount() method below
		'TotalResultPageCount' => array('selector' => 'a', 'return_value_regex' => '/pr=(\d+)/', 'type' => 'XPATH')
	);

	/**
	 * @param $nPage
	 *
	 * @return int|string
	 * @throws \Exception
	 */
	protected function getPageURLValue($nPage)
	{
		return ($nPage - 1);
	}

	/**
	 * @param $objSimpHTML
	 *
	 * @return mixed|null|string
	 * @throws \Exception
	 */
	function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		/*
			ICIMS has two main layouts:
		        - one with a dropdown listing all the page #s
				- one with a "Last" link to the last page

			We try each to find the last page of results for this search
		    so we can estimate the total # of results & pages to load

			if we fail both of those, we still try to get the value
			from any tag override the site class does
		*/

		$xpathLastLink = "//a[contains(./span/text(), 'Last')]";
		$lastLink = $objSimpHTML->findByXpath($xpathLastLink);
		if (!empty($lastLink) && is_array($lastLink)) {
			$lastLink = $lastLink[0];
			$href = $lastLink->getAttribute("href");
			$matches = array();
			$matched = preg_match("/pr=(\d+)/", $href, $matches);
			if ($matched) {
				$lastPage = $matches[1];
				$nLastPage = intval($lastPage);
				$nTotalItemsMax = $nLastPage * $this->JobListingsPerPage;

				return $nTotalItemsMax;
			}
		}

		$nodeDropdownItem = $objSimpHTML->find("div.iCIMS_Paginator_Bottom select[paginator='true'] option:last-child");
		if (!empty($nodeDropdownItem) && is_array($nodeDropdownItem)) {
			$lastPage = $nodeDropdownItem[0]->text();
			$nLastPage = intval($lastPage);
			$nTotalItemsMax = $nLastPage * $this->JobListingsPerPage;

			return $nTotalItemsMax;
		}

		return parent::parseTotalResultsCount($objSimpHTML);
	}

	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		$jobImpressions = $this->getActiveWebdriver()->executeScript('return window.jobImpressions;');
		if(!empty($jobImpressions) && is_array($jobImpressions))
		{
			$ret = array();
			foreach($jobImpressions as $jobImp)
			{
				$item = array(
					"Title" => $jobImp["title"],
					"JobSitePostId" => $jobImp["id"],
					"Category" => $jobImp["category"],
					"Company" => $jobImp["company"],
					"EmploymentType" => $jobImp["positionType"],
					"Location" => "{$jobImp["location"]["city"]} {$jobImp["location"]["state"]} {$jobImp["location"]["country"]}",
					"Url" => $this->JobPostingBaseUrl
				);
				$ret[$jobImp["position"]] = $item;
			}
			return $ret;
		}
		else
			return parent::parseJobsListForPage($objSimpHTML);
	}

	/**
	 * @param $arrItem
	 *
	 * @return array
	 */
	function cleanupJobItemFields($arrItem)
	{
		if (!empty($arrItem['PostedAt'])) {
			// remove the parens around the date
			$arrItem['PostedAt'] = remove_prefix($arrItem['PostedAt'], "(");
			$arrItem['PostedAt'] = remove_postfix($arrItem['PostedAt'], ")");
		}

		return parent::cleanupJobItemFields($arrItem);
	}
}
