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

namespace JobScooper\StageProcessor;

use JobScooper\DataAccess\UserSearchSiteRunQuery;
use JobScooper\Utils\JobsMailSender;
use Exception;
use JobScooper\Utils\SimpleHTMLHelper;
use Propel\Runtime\ActiveQuery\Criteria;

/**
 * Class NotifierDevAlerts
 * @package JobScooper\StageProcessor
 */
class NotifierDevAlerts extends JobsMailSender
{
	/**
	 * NotifierDevAlerts constructor.
	 */
	function __construct()
	{
		parent::__construct(false);
	}

	/**
	 * @throws \Exception
	 */
	function processPluginErrors()
	{

		startLogSection("Processing plugin error alerts...");

		$startDate = new \DateTime();
		$strMod = "-5 days";
		$dateDaysAgo = $startDate->modify($strMod);

		$errFields = array("JobSiteKey", "RunResultCode", "RunErrorDetails", "RunErrorPageHtml");

		$returnedJobSites = UserSearchSiteRunQuery::create()
			->select($errFields)
			->addAsColumn('LastStartedAt', 'max(date_started)')
			->addAsColumn('LastCompletedAt', 'max(date_ended)')
			->addAsColumn('CountFailedRuns', 'count(*)')
			->groupBy($errFields)
			->filterByRunResultCode("failed", Criteria::EQUAL)
			->filterByEndedAt($dateDaysAgo, Criteria::GREATER_EQUAL)
			->orderBy("CountFailedRuns", Criteria::DESC)
			->find()
			->getData();

		$lastJobsiteSuccess = UserSearchSiteRunQuery::create()
			->select($errFields)
			->addAsColumn('LastCompletedAt', 'max(date_ended)')
			->groupBy($errFields)
			->filterByRunResultCode("successful", Criteria::EQUAL)
			->filterByEndedAt($dateDaysAgo, Criteria::GREATER_EQUAL)
			->find()
			->toArray("JobSiteKey");

		$failedJobSites = array();
		foreach($returnedJobSites as $jobsite) {
			$jskey = $jobsite['JobSiteKey'];
			if (!(array_key_exists($jskey, $lastJobsiteSuccess) && $jobsite['LastCompletedAt'] < $lastJobsiteSuccess[$jskey]['LastCompletedAt']))
			{
				$jobsite['RunErrorDetails'] = nl2br(htmlentities($jobsite['RunErrorDetails']));
				if ($jobsite['CountFailedRuns'] >= 3) {
					$failedJobSites[$jskey] = $jobsite;
				}
			}
		}

		if(!empty($failedJobSites)) {
			$renderer = loadTemplate(join(DIRECTORY_SEPARATOR, array(__ROOT__, "src", "assets", "templates", "html_email_plugin_error_alert.tmpl")));
			$subject = "Plugin Failures for " . getRunDateRange(5);
			$data = array(
				"Email"        => array(
					"Subject"       => $subject,
					"BannerText"    => "JobScooper Plugin Errors",
					"Headline"      => $subject,
					"IntroText"     => "",
					"PreHeaderText" => ""
				),
				"PluginErrors" => $failedJobSites
			);

			$html = call_user_func($renderer, $data);

//			$objPageHtml = new SimpleHTMLHelper($html);
//			$filepath = $objPageHtml->debug_dump_to_file();
//			LogMessage("Debug template for html error email saved to: {$filepath}");

			try {
				$mailer = new JobsMailSender(true);
				$mailer->sendEmail("", $html, null, $subject, "errors");
			} catch (\Exception $ex)
			{
				handleException($ex);
			}
		}
		endLogSection(" Dev Plugin Failure Notification.");
	}
}