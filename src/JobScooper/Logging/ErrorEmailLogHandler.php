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
namespace JobScooper\Logging;


use JobScooper\Utils\JobsMailSender;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\MailHandler;

class ErrorEmailLogHandler extends MailHandler
{
    /**
     * {@inheritdoc}
     */
    protected function send($content, array $records)
    {
        $searchParams = $this->_getUserSearchSiteRunContent();

        $subject = "JobScooper Error: [" . gethostname() . "] for " . getRunDateRange();

        $data = array(
            'daterange' => getRunDateRange(),
            'monolog_error_content' => $content,
            'search_parameters_content' => $searchParams,
            'app_version' => __APP_VERSION__,
            'app_run_id' => getConfigurationSetting('app_run_id'),
            "server" => gethostname()
        );
        $renderer = loadTemplate(__ROOT__ . '/src/assets/templates/html_email_error_alerts.tmpl');

	    $htmlBody = call_user_func($renderer, $data);

        $mailer = new JobsMailSender(true);
        return $mailer->sendEmail("", $htmlBody, null, $subject, "errors");

    }

    /**
     * {@inheritDoc}
     */
    protected function getDefaultFormatter()
    {
        return new HtmlFormatter();
    }

	/**
	 * @return mixed
	 * @throws \Exception
	 */
	private function _getUserSearchSiteRunContent()
    {
        $renderer = loadTemplate(__ROOT__ . '/src/assets/templates/partials/html_email_body_search_config_details.tmpl');

        $data = getAllConfigurationSettings();
        $arrData = objectToArray($data);
        $flatData = array();
        foreach($arrData as $key => $value)
        	if(is_array($value))
	        	$flatData[$key] = flattenWithKeys($value);
            else
	            $flatData[$key] = $value;

        return call_user_func($renderer, $flatData);

    }
}
