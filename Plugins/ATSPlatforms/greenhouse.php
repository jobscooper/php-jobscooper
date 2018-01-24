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
abstract class AbstractGreenhouseATS  extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
	private $gh_api_fmt = "https://api.greenhouse.io/v1/boards/%s/embed/jobs";
	private $searchJsonUrlFmt = null;
	protected $SiteReferenceKey = null;
	protected $JobListingsPerPage = 1000;
	protected $nTotalJobs = null;

	protected $arrListingTagSetup = array(
//		'JobPostItem' => array('selector' => 'ul.list-group li.list-group-item'),
//		'Title' => array('selector' => 'h4.list-group-item-heading a'),
//		'Url' => array('selector' => 'h4.list-group-item-heading a', 'return_attribute' => 'href'),
//		'Location' => array('selector' => 'ul li', 'index' => 0),
//		'Department' => array('selector' => 'ul li', 'index' => 1),
//		'regex_link_job_id' => '/.com\/apply\/(\S*)\//i',
	);

	public function __construct()
	{
		$this->searchJsonUrlFmt = sprintf($this->gh_api_fmt, $this->SiteReferenceKey);
		parent::__construct();
	}

	/**
	 * @param $jobs
	 *
	 * @return array
	 */
	private function _parseJsonJobs($jobs)
	{
		$ret = array();
		foreach($jobs as $job)
		{
			$ret[$job->id] = array(
				'JobSiteKey' => $this->getJobSiteKey(),
				'JobSitePostId' => $job->id,
				'Company' => $this->JobSiteName,
				'Title' =>  $job->title,
				'Url' => $job->absolute_url,
				'Location' => $job->location->name,
				'PostedAt' => $job->updated_at
			);
		}
		return $ret;
	}


	function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		try {
			$retData = $this->getJsonResultsPage(0);
			$this->nTotalJobs = $retData['count'];
			return $this->nTotalJobs;
		}
		catch (Exception $ex)
		{
			handleException($ex);
		}
		return null;
	}

	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		try {
			$ret = array();
			$nOffset = 0;
			$data = $this->getJsonResultsPage("jobs", "meta->total");
			if (!empty($data) && !empty($data['jobs']) && count($data['jobs']) > 0) {
				$jobs = $data['jobs'];;
				$nTotal = $data['count'];
				while (!empty($jobs)) {
					$curPageJobs = $this->_parseJsonJobs($jobs);
					$ret = array_merge($ret, $curPageJobs);
					$nOffset = $nOffset + count($jobs);
					if ($nOffset < $nTotal) {
						$retData = $this->getJsonResultsPage($nOffset);
						$jobs = $retData['jobs'];
					} else
						$jobs = null;
				}
			}

			return $ret;
		} catch (Exception $ex) {
			handleException($ex);
		}
	}

	/**
	 * @param $offset
	 *
	 * @throws \ErrorException
	 * @throws \Exception
	 * @return array
	 */
	private function getJsonResultsPage($jobsKey="jobs", $countKey=null)
	{
		$curl = new \JobScooper\Utils\CurlWrapper();
		if (isDebug()) $curl->setDebug(true);

		$ret = array("count" => null, "jobs" => null);
		$url = $this->searchJsonUrlFmt;
		$lastCookies = $this->getActiveWebdriver()->manage()->getCookies();

		$retObj = $curl->cURL($url, $json = null, $action = 'GET', $content_type = null, $pagenum = null, $onbehalf = null, $fileUpload = null, $secsTimeout = null, $cookies = $lastCookies);
		if (!is_null($retObj) && array_key_exists("output", $retObj) && strlen($retObj['output']) > 0) {
			$respdata = json_decode($retObj['output']);
			if(!empty($respdata))
			{
				$this->lastResponseData = $respdata;
				try
				{
					$ret['jobs'] = $respdata->$jobsKey;
					if(!empty($countKey))
						$ret['count'] = $respdata->$countKey;
					else
						$ret['count'] = count($ret['jobs']);

				}
				catch(Exception $ex)
				{
					throw new Exception($respdata->error);
				}
			}

		}
		return $ret;
	}

}

