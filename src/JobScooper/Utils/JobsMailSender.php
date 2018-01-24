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
namespace JobScooper\Utils;

//Import PHPMailer classes into the global namespace
use JobScooper\DataAccess\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use InvalidArgumentException;

/**
 * Class JobsMailSender
 * @package JobScooper\Utils
 */
class JobsMailSender extends PHPMailer
{
    private $alwaysNotify = false;

	/**
	 * JobsMailSender constructor.
	 *
	 * @param bool $alwaysNotify
	 */
	function __construct($alwaysNotify = false)
    {
        $this->alwaysNotify = $alwaysNotify;
        parent::__construct();
    }

	/**
	 *
	 */
	function __destruct()
    {

    }


	/**
	 * @param null                             $strBodyText
	 * @param null                             $strBodyHTML
	 * @param array                            $arrDetailsAttachFiles
	 * @param string                           $subject
	 * @param string                           $emailKind
	 * @param User                             $toUser
	 *
	 * @return bool
	 * @throws \Exception
	 */
	function sendEmail($strBodyText = null, $strBodyHTML = null, $arrDetailsAttachFiles = array(), $subject="No subject", $emailKind='results', User $toUser = null)
    {
    	try
	    {

	        if($this->alwaysNotify === false) {
	            $skipNotify = getGlobalConfigOptionBoolean("command_line_args.disable-notifications");
	            if ($skipNotify === true) {
	                LogMessage(PHP_EOL . "User set -send_notifications = false so skipping email notification.)" . PHP_EOL);
	                LogMessage("Mail contents would have been:" . PHP_EOL . $strBodyText);
	                return null;
	            }
	        }


	        $smtpSettings = getConfigurationSetting('alerts.configuration.smtp');

	        if($smtpSettings != null && is_array($smtpSettings))
	        {
	            $this->isSMTP();
	            $properties = array_keys($smtpSettings);
	            foreach($properties as $property)
	            {
		            $this->$property = $smtpSettings[$property];
	            }

	        }
	        else
	        {
		        $this->isSendmail();
	        }


		    //
		    // Add initial email address header values
		    //
	        if(!empty($toUser))
	        {
		        $this->addAnAddress("to", $toUser->getEmailAddress(), $toUser->getName());
	        }

		    $alerts_users = getConfigurationSetting("alerts." . $emailKind);
	        if(empty($alerts_users))
	        {
	            //
	            // hardcoded in the case where we were unable to load the email addresses for some reason
		        //
		        $this->addAnAddress("to", "dev@bryanselner.com", "JobScooper Deveopers");
		        $this->setFrom("dev@bryanselner.com", "JobScooper Deveopers");
		        $this->addReplyTo("dev@bryanselner.com", "JobScooper Deveopers" );

	        }
	        else {
		        foreach ($alerts_users as $kind => $user) {
		        	$email = null;
		        	$name = null;

			        if(array_key_exists("email", $user) && !empty($user['email']))
				        $email = $user['email'];
			        elseif(array_key_exists("Email", $user) && !empty($user['Email']))
				        $email = $user['email'];
			        elseif(array_key_exists("User", $user) && !empty($user['User']))
				        $email = $user['User']->getEmailAddress();

			        if(array_key_exists("name", $user) && !empty($user['name']))
				        $name = $user['name'];
			        elseif(array_key_exists("Name", $user) && !empty($user['Name']))
				        $name = $user['Name'];
			        elseif(array_key_exists("User", $user) && !empty($user['User']))
				        $name = $user['User']->getName();

			        switch ($kind) {

				        case "from":
					        $this->setFrom($email, $name);
					        break;

				        default:
					        $this->addAnAddress($kind, $email, $name);

			        }
		        }
	        }

	        $this->addReplyTo("dev@bryanselner.com", "JobScooper Deveopers" );
		    $this->SMTPOptions = array(
	            'ssl' => array(
	                'verify_peer' => false,
	                'verify_peer_name' => false,
	                'allow_self_signed' => true
	            )
	        );

		    // Set word wrap to 120 characters
		    $this->WordWrap = 120;

		    // Add attachments
		    if(!empty($arrDetailsAttachFiles) && is_array($arrDetailsAttachFiles))
	        {
	            foreach($arrDetailsAttachFiles as $detailsAttach)
	            {
	                $this->addAttachment($detailsAttach);
	            }
	        }

		    $this->isHTML(true);                                            // Set email format to HTML

	        if(strlen($strBodyText) == 0 || strlen($strBodyHTML) > 0)
	            $strBodyText = strip_tags($strBodyHTML);

		    $this->Body    = $strBodyHTML;
		    $this->AltBody = $strBodyText;
		    $this->Subject = $subject;


	        $ret = $this->send();
	        if($ret !== true)
	        {
	            try
		        {

		            $errorInfo = array(
		                "phpmailer_error" => $this->ErrorInfo,
				        "phpmail_settings" => objectToArray($this),
				        "alerts_users" => $alerts_users
			        );
		            $jsonErrorInfo = encodeJSON($errorInfo);

		            //
		            // If sending the email fails, try again but this time with SMTP debug
		            // enabled so we have any idea what the issue might be in the log.
		            // If we don't do this, we just get "failed" without any useful details.
		            //
		            $msg = "Failed to send notification email with error = {$this->ErrorInfo}.   ". PHP_EOL . PHP_EOL . "Full details = {$jsonErrorInfo} ". PHP_EOL . PHP_EOL . "Retrying email send with debug enabled to log error details...";
		        } catch (\Exception $ex)
		        {
			        $msg = "Failed to send notification email with error = {$this->ErrorInfo}.   Retrying email send with debug enabled to log error details...";
		        }
	            LogError($msg);
		        $this->SMTPDebug = 1;
	            $ret = $this->send();
	            if($ret === true) return $ret;

	            $msg = "Failed second attempt to send notification email.  Debug error details should be logged above.  Error: " . PHP_EOL .$this->ErrorInfo;
	            LogError($msg);
	            throw new Exception($msg);

	        }
	        else
	        {
	            LogMessage("Email sent to " . getArrayValuesAsString($this->getAllRecipientAddresses()) . " from " . $this->From);
	        }
	        return $ret;
	    }
		catch (Exception $ex)
		{
			handleException($ex, "Failed to send notification email with error = %s", true);
		}
    }

	/**
	 * @param $strFilePath
	 * @throws \Exception
	 */
	public function addMailCssToHTMLFile($strFilePath)
    {

        $strHTMLContent = file_get_contents($strFilePath);
        $retWrapped = $this->addMailCssToHTML($strHTMLContent);

        file_put_contents($strFilePath, $retWrapped);

    }

	/**
	 * @param $strHTML
	 *
	 * @return string
	 * @throws \Exception
	 * @throws \TijsVerkoyen\CssToInlineStyles\Exception
	 */
	public function addMailCssToHTML($strHTML)
    {
        $cssData = array(
            'color-primary' => "#49332D",
            'color-secondary' => "#e7dcd7",
            'color-level-1' => '#6d4d41',
            'color-level-2' => '#61443a',
            'color-level-3' => '#553c33',
            'color-level-4' => '#49332c',
            'color-level-5' => '#3d2b24'
        );


        $renderer = loadTemplate(__ROOT__."/src/assets/templates/html_notification_email_css.tmpl");

        $css = renderTemplate($renderer, $cssData);

        $cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
        $cssToInlineStyles->setHTML($strHTML);
        $cssToInlineStyles->setCSS($css);
        return $cssToInlineStyles->convert();
    }


}