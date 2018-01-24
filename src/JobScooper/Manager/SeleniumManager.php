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
namespace JobScooper\Manager;
use Exception;
use Facebook\WebDriver\Exception\WebDriverCurlException;
use Facebook\WebDriver\Exception\WebDriverException;
use Facebook\WebDriver\Firefox\FirefoxDriver;
use Facebook\WebDriver\Firefox\FirefoxProfile;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\WebDriverCapabilityType;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverCapabilities;
use Facebook\WebDriver\WebDriverDimension;
use Facebook\WebDriver\WebDriverKeys;
use JobScooper\Utils\PropertyObject;
use JobScooper\Utils\SimpleHTMLHelper;

use Facebook\WebDriver\Remote\DesiredCapabilities;

/**
 * Class SeleniumManager
 * @package JobScooper\Manager
 */
class SeleniumManager extends PropertyObject
{
	/**
	 * @var RemoteWebDriver|null
	 */
	private $remoteWebDriver = null;
    private $additionalLoadDelaySeconds = null;
    private $_seleniumIsRunning = false;
    private $_settings = null;

	/**
	 * SeleniumManager constructor.
	 *
	 * @param int $additionalLoadDelaySeconds
	 *
	 * @throws \Exception
	 */
	function __construct($additionalLoadDelaySeconds = 0)
    {
        $this->additionalLoadDelaySeconds = $additionalLoadDelaySeconds;
        $this->_settings = getConfigurationSetting('selenium');
		if(empty($this->_settings))
			$this->_settings = array();
		
        if ($this->_settings['autostart'] == true) {
            $this->startSeleniumServer();
        }
    }

	/**
	 *
	 * @throws \Exception
	 */
	function __destruct()
    {
        $this->doneWithRemoteWebDriver();

        if ($this->_settings['autostart'] == true) {
            $this->shutdownSelenium();
        }
        $this->_seleniumIsRunning = false;
    }

	/**
	 * @param      $url
	 * @param bool $recursed
	 *
	 * @return string
	 * @throws \Exception
	 */
	function getPageHTML($url, $recursed = false)
    {
        try {
            $driver = $this->get_driver();

            $this->loadPage($url);

            $src = $driver->getPageSource();


            // BUGBUG:  Firefox has started to return "This tab has crashed" responses often as of late February 2017.
            //          Adding check for that case and a session kill/reload when it happens
            if (stristr($src, "tab has crashed") != false) {
                LogWarning("Error in Firefox WebDriver:  tab has crashed retrieving page at " . $url . ".  Killing WebDriver and trying one more time...");
                // We found "tab has crashed" in the response, so we can't use it.
                if ($recursed != true) {
                    $this->killAllAndRestartSelenium();
                    return $this->getPageHTML($url, $recursed = true);
                } else {
                    handleException(new Exception("Error in Firefox WebDriver:  tab has crashed getting " . $url . " a second time.  Cannot load correct results so aborting..."), "%s", $raise = true);
                }
            }

            return $src;
        } catch (WebDriverCurlException $ex) {
            handleException($ex, null, true);
        } catch (WebDriverException $ex) {
            handleException($ex, null, true);
        } catch (\Exception $ex) {
            handleException($ex, null, true);
        }
    }

	/**
	 * @param $url
	 *
	 * @throws \Exception
	 */
	function loadPage($url)
    {
        try {
            $driver = $this->get_driver();
            if (strncmp($driver->getCurrentURL(), $url, strlen($url)) != 0) {
                $driver->get($url);
                sleep(2 + $this->additionalLoadDelaySeconds);
            }
        } catch (WebDriverCurlException $ex) {
            handleException($ex, "Error retrieving Selenium page at {$url}", false);
        } catch (WebDriverException $ex) {
            handleException($ex, "Error retrieving Selenium page at {$url} ", false);
        } catch (Exception $ex) {
            handleException($ex, "Error retrieving Selenium page at {$url}", false);
        }
    }

	/**
	 *
	 * @throws \Exception
	 */
	function done()
    {
        $this->doneWithRemoteWebDriver();
        $this->shutdownSelenium();
    }

	/**
	 * @throws \Exception
	 */
	function waitForAjax($framework='jquery')
	{
		// javascript framework
		switch($framework){
			case 'jquery':
				$code = "return jQuery.active;"; break;
			case 'prototype':
				$code = "return Ajax.activeRequestCount;"; break;
			case 'dojo':
				$code = "return dojo.io.XMLHTTPTransport.inFlight.length;"; break;
			default:
				throw new Exception('Not supported framework');
		}

		// wait for at most 30s, retry every 2000ms (2s)
		$driver = $this->get_driver();
		$driver->wait(30, 2000)->until(
			function ($driver, $code) {
				return !($driver->executeScript($code));
			}
		);
	}


	/**
	 * @throws \Exception
	 */
	function killAllAndRestartSelenium()
    {
        try {
            $this->doneWithRemoteWebDriver();
        } catch (Exception $ex) {
            LogError("Error stopping active Selenium sessions: " . $ex);
        }

        $webdriver = $this->getWebDriverKind();
        $pscmd = doExec("pkill -i " . $webdriver);

        $this->shutdownSelenium();

        $this->startSeleniumServer();

    }

	/**
	 *
	 */
	function shutdownSelenium()
    {
        $canStop = false;
        $settings = $this->_settings;
        if(array_key_exists('autostart', $settings))
            $canStop = ($settings['autostart'] === True);

        if($canStop)
        {
            if(array_key_exists('stop_command', $settings) && !is_null($settings['stop_command']) && !empty($settings['stop_command']))
            {
                LogMessage("Attempting to stop Selenium server with command \"" . $settings['stop_command'] . "\"");
                $res = doExec($settings['stop_command']);
                LogMessage("Stopping Selenium server result: "  . $res);
            }
            else {

                try {
                    // The only way to shutdown standalone server in 3.0 is by killing the local process.
                    // Details: https://github.com/SeleniumHQ/selenium/issues/2852
                    //
                    $cmd = 'pid=`ps -eo pid,args | grep selenium-server | grep -v grep | cut -c1-6`; if [ "$pid" ]; then kill -9 $pid; echo "Killed Selenium process #"$pid; else echo "Selenium server is not running."; fi';
                    LogMessage("Killing Selenium server process with command \"" . $cmd . "\"");

                    $res = doExec($cmd);
                    LogMessage("Killing Selenium server result: " . $res);
                } catch (Exception $ex) {
                    $pscmd = doExec("pkill -i selenium");
                    LogMessage("Killing Selenium server result: " . $res);
                    LogError("Failed to send shutdown to Selenium server.  Attempted to kill process, however you may need to manually shut it down.");
                }
            }
        }
        else
        {
            LogDebug("Did not attempt Selenium server shutdown since we didn't and can't autostart it.");
        }

    }

	/**
	 * @throws \Exception
	 */
	protected function doneWithRemoteWebDriver()
    {
//	    $logs_browse = $this->remoteWebDriver->manage()->getLog("browser");
//	    $logs_client = $this->remoteWebDriver->manage()->getLog("client");
//
//	    LogMessage("Selenium browser log:  " . getArrayDebugOutput($logs_browse));
//	    LogMessage("Selenium client log:  " . getArrayDebugOutput($logs_client));

	    try {

            if(!is_null($this->remoteWebDriver))
            {
                $this->remoteWebDriver->quit();
            }
        } catch (WebDriverCurlException $ex) {
            handleException($ex, "Failed to quit Webdriver: ", false);
        } catch (WebDriverException $ex) {
            handleException($ex, "Failed to quit Webdriver: ", false);
        } catch (Exception $ex) {
            handleException($ex, "Failed to quit Webdriver: ", false);
        }
        finally
        {
            $driver = null;
            $this->remoteWebDriver = null;
        }
    }

	/**
	 * @return bool
	 * @throws \Exception
	 */
	function startSeleniumServer()
    {

        $settings = $this->_settings;

        $autostart = false;
        if(array_key_exists('autostart', $settings))
        {
            $autostart = ($settings['autostart'] === True);
        }
        $canStop = $autostart;

        if($autostart) {
            $seleniumStarted = $this->isServerUp();
            if ($seleniumStarted == false) {
                if ($canStop === True && array_key_exists('start_command', $settings) && !is_null($settings['start_command'])) {
                    LogMessage("Attempting to start Selenium server with command \"" . $settings['start_command'] . "\"");
                    $res = doExec($settings['start_command']);
                    LogMessage("Starting Selenium server result: " . $res);

                    sleep(10);
                    $seleniumStarted = true;
                } else if ($canStop && stripos($settings['host_location'], "localhost") != false || (stripos($settings['host_location'], "127.0.0.1") != false)) {

                    $strCmdToRun = "java ";
                    if (array_key_exists('prefix_switches', $settings))
                        $strCmdToRun .= $settings['prefix_switches'];

                    $strCmdToRun .= " -jar \"" . $settings['jar'] . "\" -port " . $settings['port'] . " ";
                    if (array_key_exists('prefix_switches', $settings))
                        $strCmdToRun .= $settings['postfix_switches'];

                    $strCmdToRun .= " >/dev/null &";

                    LogMessage("Starting Selenium with command: '" . $strCmdToRun . "'");
                    $res = doExec($strCmdToRun);
                    sleep(10);
                    LogMessage("Starting Selenium server result: " . $res);
                    $seleniumStarted = true;
                } else {
                    $seleniumStarted = false;
                    throw new Exception("Selenium is not running and was not set to autostart. Cannot continue without an instance of Selenium running.");
                }
            } else {
                $seleniumStarted = true;
                LogWarning("Selenium is already running; skipping startup of server.");
            }
            return $seleniumStarted;
        }

        return $this->isServerUp();
    }

	/**
	 * @return bool
	 */
	function isServerUp()
    {

        $hostHubPageURL = $this->_settings['host_location'] . '/wd/hub';
        $msg = "Checking Selenium server up.... ";
        LogMessage("Checking if Selenium is running at " . $hostHubPageURL);

        $ret = false;

        try{
            $objSimplHtml = new SimpleHtmlHelper($$hostHubPageURL);
            if ($objSimplHtml === false)
            {
                $ret = false;
                return $ret;
            }

            $tag = $objSimplHtml->find("title");
            if (is_null($tag) != true && count($tag) >= 1)
            {
                $title = $tag[0]->text();
                $msg = $msg . " Found hub server page '" . $title . "' as expected.  Selenium server is up.'";
                $ret = true;
            }

        } catch (WebDriverCurlException $ex) {
            $msg = "Selenium not yet running (failed to access the hub page.)";
        } catch (WebDriverException $ex) {
            $msg = " Selenium not yet running (failed to access the hub page.)";
        } catch (Exception $ex) {
            $msg = " Selenium not yet running (failed to access the hub page.)";
        }
        finally
        {
            LogMessage($msg);
        }

        return $ret;
    }

    /**
     * Get the current webdriver or create a new one if needed.
     *
     * @throws \Exception
     * @return \Facebook\WebDriver\Remote\RemoteWebDriver|null
     *
     */
    function get_driver()
    {
        try {

                try {
                    if (is_null($this->remoteWebDriver))
                        $this->create_remote_webdriver();
                    return $this->remoteWebDriver;

                } catch (WebDriverCurlException $ex) {
                    $this->killAllAndRestartSelenium();
                    $this->create_remote_webdriver();
                    return $this->remoteWebDriver;

                } catch (WebDriverException $ex) {
                    $this->killAllAndRestartSelenium();
                    $this->create_remote_webdriver();
                    return $this->remoteWebDriver;

                } catch (Exception $ex) {
                    $this->killAllAndRestartSelenium();
                    $this->create_remote_webdriver();
                    return $this->remoteWebDriver;

                }
        } catch (WebDriverCurlException $ex) {
            handleException($ex, "Failed to get Selenium remote webdriver: ", true);
        }

        return null;
    }

	/**
	 * @return mixed|null|string
	 */
	function getWebDriverKind()
    {
        $webdriver = (array_key_exists('webdriver', $this->_settings)) ? $this->_settings['webdriver'] : null;
        if(is_null($webdriver)) {
            $webdriver = "phantomjs";
            if (PHP_OS == "Darwin")
                $webdriver = "safari";
        }

        return $webdriver;
    }

	/**
	 * @return null|\Facebook\WebDriver\Remote\RemoteWebDriver
	 * @throws \Exception
	 */
	private function create_remote_webdriver()
    {
        $hubUrl = $this->_settings['host_location'] . '/wd/hub';
        LogMessage("Creating Selenium remote web driver to host {$hubUrl}...");

        try {

            $webdriver = $this->getWebDriverKind();
            $hubUrl = $this->_settings['host_location'] . '/wd/hub';
            $driver = null;

	        /** @var DesiredCapabilities $capabilities */
	        $capabilities = call_user_func(array("Facebook\WebDriver\Remote\DesiredCapabilities", $webdriver));

//
//	        $capabilities->setCapability(
//		        'moz:firefoxOptions',
//		        ['args' => ['-headless']]
//	        );
//	        $capabilities->setCapability(
//		        'moz:webdriverClick',
//		        false
//	        );

//	        $prof = $capabilities->getCapability(FirefoxDriver::PROFILE);
//
//	        $fflog = getOutputDirectory('debug') . "/firefox_webdriver_log.csv";
//	        $prof->setPreference("setEnableNativeEvents", true);
//	        $prof->setPreference("webdriver.log.file", $fflog);
//	        $prof->setPreference("webdriver.log.driver", "INFO");
//	        $capabilities->setCapability(FirefoxDriver::PROFILE, $prof);


	        $capabilities->setCapability('acceptInsecureCerts', true);
	        $capabilities->setCapability("setThrowExceptionOnScriptError", false);
            $capabilities->setCapability("unexpectedAlertBehaviour", "dismiss");
            $capabilities->setCapability(WebDriverCapabilityType::ACCEPT_SSL_CERTS, true);
            $capabilities->setCapability(WebDriverCapabilityType::APPLICATION_CACHE_ENABLED, true);
            $capabilities->setCapability(WebDriverCapabilityType::CSS_SELECTORS_ENABLED, true);
            $capabilities->setCapability(WebDriverCapabilityType::WEB_STORAGE_ENABLED, true);
            $capabilities->setCapability(WebDriverCapabilityType::NATIVE_EVENTS, true);
            $capabilities->setCapability(WebDriverCapabilityType::HANDLES_ALERTS, true);
            $capabilities->setCapability(WebDriverCapabilityType::DATABASE_ENABLED, true);
            $capabilities->setCapability(WebDriverCapabilityType::LOCATION_CONTEXT_ENABLED, true);

            $this->remoteWebDriver = RemoteWebDriver::create(
                $hubUrl,
                $desired_capabilities = $capabilities,
                $connection_timeout_in_ms = 60000,
                $request_timeout_in_ms = 60000
            );

//
//	        $window = new WebDriverDimension(1024, 768);
//	        $this->remoteWebDriver->manage()->window()->setSize($window);

	        LogMessage("Remote web driver instantiated.");

            return $this->remoteWebDriver;
        } catch (WebDriverCurlException $ex) {
            handleException($ex, "Failed to get webdriver from {$hubUrl}: ", true);
        } catch (WebDriverException $ex) {
            handleException($ex, "Failed to get webdriver from {$hubUrl}: ", true);
        } catch (Exception $ex) {
            handleException($ex, "Failed to get webdriver from {$hubUrl}: ", true);
        }
        return null;
    }


}
