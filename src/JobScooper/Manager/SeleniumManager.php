<?php

/**
 * Copyright 2014-17 Bryan Selner
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
use ErrorException;
use Exception;
use GuzzleHttp\Client;



class SeleniumManager extends \PropertyObject
{
    private $remoteWebDriver = null;
    private $additionalLoadDelaySeconds = null;
    private $lastCookies = array();
    private $_seleniumIsRunning = false;

    function __construct($additionalLoadDelaySeconds = 0)
    {
        $this->additionalLoadDelaySeconds = $additionalLoadDelaySeconds;

        if ($GLOBALS['USERDATA']['selenium']['autostart'] == True) {
            SeleniumManager::startSeleniumServer();
        }
    }

    function __destruct()
    {
        $this->doneWithRemoteWebDriver();

        if ($GLOBALS['USERDATA']['selenium']['autostart'] == True) {
            SeleniumManager::shutdownSelenium();
        }
        $this->_seleniumIsRunning = false;
    }

    function getPageHTML($url, $recursed=false)
    {
        try {
            $driver = $this->get_driver();

            foreach ($this->lastCookies as $cookie) {
                $driver->manage()->addCookie(array(
                    'name' => $cookie['name'],
                    'value' => $cookie['value'],
                ));
            }
            $this->loadPage($url);

            $this->lastCookies = $driver->manage()->getCookies();

            $src = $driver->getPageSource();


            // BUGBUG:  Firefox has started to return "This tab has crashed" responses often as of late February 2017.
            //          Adding check for that case and a session kill/reload when it happens
            if (stristr($src, "tab has crashed") != false) {
                $GLOBALS['logger']->logLine("Error in Firefox WebDriver:  tab has crashed retrieving page at " . $url . ".  Killing WebDriver and trying one more time...", \C__DISPLAY_WARNING__);
                // We found "tab has crashed" in the response, so we can't use it.
                if ($recursed != true) {
                    $this->killAllAndRestartSelenium();
                    return $this->getPageHTML($url, $recursed = true);
                } else {
                    handleException(new Exception("Error in Firefox WebDriver:  tab has crashed getting " . $url . " a second time.  Cannot load correct results so aborting..."), "%s", $raise = true);
                }
            }

            return $src;
        } catch (\WebDriverCurlException $ex) {
            handleException($ex, null, true);
        } catch (\WebDriverException $ex) {
            handleException($ex, null, true);
        } catch (\Exception $ex) {
            handleException($ex, null, true);
        }
    }

    function loadPage($url)
    {
        try
        {
            $driver = $this->get_driver();
            if(strncmp($driver->getCurrentURL(), $url, strlen($url)) != 0) {
                $driver->get($url);
                sleep(2+$this->additionalLoadDelaySeconds);
            }
        } catch (\WebDriverCurlException $ex) {
            handleException($ex, "Error retrieving Selenium page at {$url}: %s ", false);
        } catch (\WebDriverException $ex) {
            handleException($ex, "Error retrieving Selenium page at {$url}: %s ", false);
        } catch (Exception $ex) {
            handleException($ex, "Error retrieving Selenium page at {$url}: %s ", false);
        }
    }

    function done()
    {
        $this->doneWithRemoteWebDriver();
        $this->shutdownSelenium();
    }

    function killAllAndRestartSelenium()
    {
        try {
            $this->doneWithRemoteWebDriver();
        }
        catch (Exception $ex)
        {
            $GLOBALS['logger']->logLine("Error stopping active Selenium sessions: " . $ex, \C__DISPLAY_ERROR__);
        }

        $webdriver = $this->getWebDriverKind();
        $pscmd = doExec("pkill -i " . $webdriver);

        SeleniumManager::shutdownSelenium();

        SeleniumManager::startSeleniumServer();

    }

    static function shutdownSelenium()
    {
        if((array_key_exists('selenium_started', $GLOBALS) && $GLOBALS['selenium_started'] === true) &&
                $GLOBALS['USERDATA']['selenium']['autostart'] == True) {

            if(array_key_exists('stop_command', $GLOBALS['USERDATA']['selenium']) && !is_null($GLOBALS['USERDATA']['selenium']['stop_command']))
            {
                $GLOBALS['logger']->logLine("Attempting to stop Selenium server with command \"" . $GLOBALS['USERDATA']['selenium']['stop_command'] . "\"", \C__DISPLAY_NORMAL__);
                $res = doExec($GLOBALS['USERDATA']['selenium']['stop_command']);
                $GLOBALS['logger']->logLine("Stopping Selenium server result: "  . $res, \C__DISPLAY_NORMAL__);

                $GLOBALS['selenium_started'] = false;

            }
            else {

                try {
                    // The only way to shutdown standalone server in 3.0 is by killing the local process.
                    // Details: https://github.com/SeleniumHQ/selenium/issues/2852
                    //
                    $cmd = 'pid=`ps -eo pid,args | grep selenium-server | grep -v grep | cut -c1-6`; if [ "$pid" ]; then kill -9 $pid; echo "Killed Selenium process #"$pid; else echo "Selenium server is not running."; fi';
                    if (isset($GLOBALS['logger'])) {
                        $GLOBALS['logger']->logLine("Killing Selenium server process with command \"" . $cmd . "\"", \C__DISPLAY_NORMAL__);
                    }
                    $res = doExec($cmd);
                    $GLOBALS['logger']->logLine("Killing Selenium server result: "  . $res, \C__DISPLAY_NORMAL__);
                    $GLOBALS['selenium_started'] = false;
                } catch (Exception $ex) {
                    $pscmd = doExec("pkill -i selenium");
                    if (isset($GLOBALS['logger'])) {
                        $GLOBALS['logger']->logLine("Failed to send shutdown to Selenium server.  Attempted to kill process, however you may need to manually shut it down.", \C__DISPLAY_ERROR__);
                    }
                } finally {
                    $GLOBALS['selenium_started'] = false;
                }
            }
        }
        else
        {
            if (isset($GLOBALS['logger'])) {
                $GLOBALS['logger']->logLine("Skipping Selenium server shutdown since we did not start it.", \C__DISPLAY_WARNING__);
            }
        }

    }

     protected function doneWithRemoteWebDriver()
    {
        try {

            $driver = $this->get_driver();
            if(!is_null($driver))
            {
                $driver->quit();
            }
        } catch (\WebDriverCurlException $ex) {
            handleException($ex, "Failed to quit Webdriver: ", false);
        } catch (\WebDriverException $ex) {
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

    function startSelenium()
    {
        $this->_seleniumIsRunning = SeleniumManager::startSeleniumServer();


    }
    static function startSeleniumServer()
    {

        $seleniumStarted = SeleniumManager::isServerUp();
        if($seleniumStarted == false)
        {
            if(array_key_exists('start_command', $GLOBALS['USERDATA']['selenium']) && !is_null($GLOBALS['USERDATA']['selenium']['start_command']))
            {
                $GLOBALS['logger']->logLine("Attempting to start Selenium server with command \"" . $GLOBALS['USERDATA']['selenium']['start_command'] . "\"", \C__DISPLAY_NORMAL__);
                $res = doExec($GLOBALS['USERDATA']['selenium']['start_command']);
                $GLOBALS['logger']->logLine("Starting Selenium server result: "  . $res, \C__DISPLAY_NORMAL__);

                sleep(10);
                $seleniumStarted = true;
                $GLOBALS['selenium_started'] = $seleniumStarted;
            }
            else if(stripos($GLOBALS['USERDATA']['selenium']['host_location'], "localhost") != false || (stripos($GLOBALS['USERDATA']['selenium']['host_location'], "127.0.0.1") != false)) {

                $strCmdToRun = "java ";
                if (array_key_exists('prefix_switches', $GLOBALS['USERDATA']['selenium']))
                    $strCmdToRun .= $GLOBALS['USERDATA']['selenium']['prefix_switches'];

                $strCmdToRun .= " -jar \"" . $GLOBALS['USERDATA']['selenium']['jar'] . "\" -port " . $GLOBALS['USERDATA']['selenium']['port'] . " ";
                if (array_key_exists('prefix_switches', $GLOBALS['USERDATA']['selenium']))
                    $strCmdToRun .= $GLOBALS['USERDATA']['selenium']['postfix_switches'];

                $strCmdToRun .= " >/dev/null &";

                $GLOBALS['logger']->logLine("Starting Selenium with command: '" . $strCmdToRun . "'", \C__DISPLAY_ITEM_RESULT__);
                $res = doExec($strCmdToRun);
                sleep(10);
                $GLOBALS['logger']->logLine("Starting Selenium server result: "  . $res, \C__DISPLAY_NORMAL__);
                $seleniumStarted = true;
                $GLOBALS['selenium_started'] = true;
            }
            else {
                $seleniumStarted = false;
                throw new Exception("Selenium is not running and was not set to autostart. Cannot continue without an instance of Selenium running.");
            }
        }
        else {
            $seleniumStarted = true;
            $GLOBALS['logger']->logLine("Selenium is already running on port " . $GLOBALS['USERDATA']['selenium']['port'] . ".  Skipping startup of server.", \C__DISPLAY_WARNING__);
        }
        return $seleniumStarted;
    }

    static function isServerUp()
    {
        $hostHubPageURL = $GLOBALS['USERDATA']['selenium']['host_location'] . '/wd/hub';
        $msg = "Checking Selenium server up.... ";

        $ret = false;

        try{

            $client = new \GuzzleHttp\Client();

            $res = $client->request('GET', $hostHubPageURL);
            $rescode = $res->getStatusCode();
            if($rescode > 200)
            {
                return false;
            }
            $strHtml = $res->getBody();

            $objSimplHtml = \SimpleHTMLHelper::str_get_html($strHtml);
            if ($objSimplHtml === false)
            {
                $ret = false;
                return $ret;
            }

            $tag = $objSimplHtml->find("title");
            if (is_null($tag) != true && count($tag) >= 1)
            {
                $title = $tag[0]->plaintext;
                $msg = $msg . " Found hub server page '" . $title . "' as expected.  Selenium server is up.'";
                $ret = true;
            }

        } catch (\WebDriverCurlException $ex) {
            $msg = $msg . " Selenium not yet running (failed to access the hub page.)";
        } catch (\WebDriverException $ex) {
            $msg = $msg . " Selenium not yet running (failed to access the hub page.)";
        } catch (Exception $ex) {
            $msg = $msg . " Selenium not yet running (failed to access the hub page.)";
        }
        finally
        {
            LogLine($msg, \C__DISPLAY_NORMAL__);
        }

        return $ret;
    }

    function get_driver()
    {
        try
        {
            if (is_null($this->remoteWebDriver))
                $this->create_remote_webdriver();
            return $this->remoteWebDriver;
        }
        catch (Exception $ex)
        {
            handleException($ex, "Failed to get Selenium remote webdriver: ", true);
        }

        return null;
    }

    function getWebDriverKind()
    {
        $webdriver = (array_key_exists('webdriver', $GLOBALS['USERDATA']['selenium'])) ? $GLOBALS['USERDATA']['selenium']['webdriver'] : null;
        if(is_null($webdriver)) {
            $webdriver = "phantomjs";
            if (PHP_OS == "Darwin")
                $webdriver = "safari";
        }

        return $webdriver;
    }

    private function create_remote_webdriver()
    {
        $host = $GLOBALS['USERDATA']['selenium']['host_location'] . '/wd/hub';
        logLine("Creating Selenium remote web driver to host {$host}...");

        try {
            $webdriver = $this->getWebDriverKind();
            $host = $GLOBALS['USERDATA']['selenium']['host_location'] . '/wd/hub';
            $driver = null;

            $capabilities = \DesiredCapabilities::$webdriver();

            $capabilities->setCapability("nativeEvents", true);
            $capabilities->setCapability("setThrowExceptionOnScriptError", false);
            $capabilities->setCapability("webStorageEnabled", true);
            $capabilities->setCapability("databaseEnabled", true);
            $capabilities->setCapability("applicationCacheEnabled", true);
            $capabilities->setCapability("locationContextEnabled", true);
            $capabilities->setCapability("unexpectedAlertBehaviour", "dismiss");

            $this->remoteWebDriver = \RemoteWebDriver::create(
                $host,
                $desired_capabilities = $capabilities,
                $connection_timeout_in_ms = 60000,
                $request_timeout_in_ms = 60000
            );

            LogLine("Remote web driver instantiated.");

            return $this->remoteWebDriver;
        } catch (\WebDriverCurlException $ex) {
            handleException($ex, "Failed to get webdriver from {$host}: ", true);
        } catch (\WebDriverException $ex) {
            handleException($ex, "Failed to get webdriver from {$host}: ", true);
        } catch (Exception $ex) {
            handleException($ex, "Failed to get webdriver from {$host}: ", true);
        }
        return null;
    }


}
