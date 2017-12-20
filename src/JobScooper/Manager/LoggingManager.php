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

use JobScooper\Logging\CSVLogHandler;
use JobScooper\Logging\ErrorEmailLogHandler;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\DeduplicationHandler;
use Propel\Runtime\Propel;
use Psr\Log\LogLevel as LogLevel;
use \Monolog\Handler\StreamHandler;
use Monolog\Logger;
use DateTime;
use Exception;

/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Helper Class:  Information and Error Logging                                               ****/
/****                                                                                                        ****/
/****************************************************************************************************************/

function getChannelLogger($channel)
{
    if(!is_null($GLOBALS['logger']))
        return $GLOBALS['logger']->getChannelLogger($channel);
}

Class JobsErrorHandler extends ErrorHandler
{
    public function handleException($e)
    {
	    if(empty($GLOBALS['logger']))
		    $GLOBALS['logger'] = getChannelLogger("default");

	    LogError(sprintf("Uncaught Exception: %s", $e->getMessage()));
	    handleException($e, "Uncaught Exception: %s");
//        exit(255);
    }

}

Class LoggingManager extends \Monolog\Logger
{
    protected $arrCumulativeErrors = array();

    private $_handlersByType = array();
    private $_loggerName = "default";
    private $_loggers = array();
    private $_csvHandle = null;
    private $_dedupeHandle = null;
    private $_doLogContext = false;

    public function __construct($name, array $handlers = array(), array $processors = array())
    {
        $GLOBALS['logger'] = null;
        $GLOBALS['logger'] = $this;
        
        $name = C__APPNAME__;
        JobsErrorHandler::register($this, array(), LogLevel::ERROR);

        $this->_handlersByType = array(
//            'stderr' => new StreamHandler('php://stderr', isDebug() ? Logger::DEBUG : Logger::INFO)
        );

        parent::__construct($name, $handlers = $this->_handlersByType);

        $this->_loggers[$this->_loggerName] = $this;
        $this->_loggers['plugins'] = $this->withName('plugins');
        $this->_loggers['database'] = $this->withName('database');

        $logOptions = getConfigurationSetting('logging');
        $this->_doLogContext = filter_var($logOptions['always_log_context'], FILTER_VALIDATE_BOOLEAN);


        $now = new DateTime('NOW');

        $this->_handlersByType['stderr'] = new StreamHandler("php://stderr", Logger::DEBUG );
	    $fmter = $this->_handlersByType['stderr']->getFormatter();
	    $fmter->allowInlineLineBreaks(true);
	    $fmter->includeStacktraces(true);
	    $fmter->ignoreEmptyContextAndExtra(true);
	    $this->_handlersByType['stderr']->setFormatter($fmter);
        $this->pushHandler($this->_handlersByType['stderr']);
        $this->LogRecord(\Psr\Log\LogLevel::INFO,"Logging started from STDIN");

//        $serviceContainer->setLogger('defaultLogger', $defaultLogger);
        $propelContainer = Propel::getServiceContainer();

        $this->logRecord(LogLevel::INFO,"Logging started for " . __APP_VERSION__ ." at " . $now->format('Y-m-d\TH:i:s'));
    }

    public function getChannelLogger($channel)
    {
        if( is_null($channel) || !in_array($channel, array_keys($this->_loggers)))
            $channel = 'default';

        return $this->_loggers[$channel];
    }

    /**
     * @private
     */
    public function handleException($e)
    {
        handleException($e);
        exit(255);
    }

    public function updatePropelLogging()
    {
        Propel::getServiceContainer()->setLogger('defaultLogger', $this);
        if(isDebug()) {
            $con = Propel::getWriteConnection(\JobScooper\DataAccess\Map\JobPostingTableMap::DATABASE_NAME);
            $con->useDebug(true);
            LogMessage("Enabled debug logging for Propel.");
        }
    }

    public function addFileHandlers($logPath)
    {
        $logLevel = (isDebug() ? Logger::DEBUG : Logger::INFO);

        $today = getTodayAsString("-");
        $mainLog = $logPath. DIRECTORY_SEPARATOR . "{$this->_loggerName}-{$today}.log";
        $this->_handlersByType['logfile'] = new StreamHandler($mainLog, $logLevel, $bubble = true);
	    $fmter = $this->_handlersByType['logfile']->getFormatter();
	    $fmter->allowInlineLineBreaks(true);
	    $fmter->includeStacktraces(true);
	    $fmter->ignoreEmptyContextAndExtra(true);
	    $this->_handlersByType['logfile']->getFormatter($fmter);
	    $this->pushHandler($this->_handlersByType['logfile']);
        $this->logRecord(\Psr\Log\LogLevel::INFO,"Logging started to logfile at {$mainLog}");

        $now = getNowAsString("-");
        $csvlog = $logPath. DIRECTORY_SEPARATOR . "{$this->_loggerName}-{$now}-run_errors.csv";
        $fpcsv = fopen($csvlog, "w");
        $this->_handlersByType['csverrors'] = new CSVLogHandler($fpcsv, Logger::WARNING, $bubble = true);
        $this->pushHandler($this->_handlersByType['csverrors'] );
        $this->LogRecord(\Psr\Log\LogLevel::INFO, "Logging started to CSV file at {$csvlog}");

        $now = getNowAsString("-");
        $dedupeLog = $logPath. DIRECTORY_SEPARATOR . "{$this->_loggerName}-{$now}-dedupe_log_errors.csv";
        $this->_dedupeHandle = fopen($dedupeLog, "w");
        $this->_handlersByType['dedupe_email'] = new DeduplicationHandler(new ErrorEmailLogHandler(Logger::ERROR, true),  $deduplicationStore = $dedupeLog, $deduplicationLevel = Logger::ERROR, $time = 60, $bubble = true);
        $this->pushHandler($this->_handlersByType['dedupe_email']);
        $this->LogRecord(\Psr\Log\LogLevel::INFO, "Logging started for deduped email log file at {$dedupeLog}");

        $this->updatePropelLogging();

    }

    function __destruct()
    {
        $this->flushErrorNotifications();

        if(!is_null($this->_csvHandle))
        {
            fclose($this->_csvHandle);
        }
    }

    function flushErrorNotifications()
    {
//        $this->_handlersByType['bufferedmail']->flush();

    }

	public function logRecord($level, $message, $extras=array(), $ex=null)
	{
		$context = array();
		$monologLevel = \Monolog\Logger::toMonologLevel($level);
		if(in_array($level, array(
			\Monolog\Logger::WARNING, \Monolog\Logger::EMERGENCY, \Monolog\Logger::ERROR, \Monolog\Logger::DEBUG, \Monolog\Logger::CRITICAL)))
			$context = $this->getDebugContext($extras, $ex);

		if(parent::log($monologLevel, $message, $context) === false)
			print($message .PHP_EOL . PHP_EOL );
	}

    private $_openSections = 0;


	const C__LOG_SECTION_BEGIN = 1;
	const C__LOG_SECTION_END = 2;

	function startLogSection($headerText)
	{
		return $this->_logSectionHeader($headerText, LoggingManager::C__LOG_SECTION_BEGIN);
	}

	function endLogSection($headerText)
	{
		return $this->_logSectionHeader($headerText, LoggingManager::C__LOG_SECTION_END);
	}


	private function _logSectionHeader($headerText, $nType)
	{

		if ($nType == LoggingManager::C__LOG_SECTION_BEGIN)
		{
			$indentCount = $this->_openSections * 2;
			$lineChar = strval($this->_openSections + 1);
			$intro = "BEGIN: ";
			$this->_openSections += 1;
		}
		else {
			$this->_openSections -= 1;
			$lineChar = strval($this->_openSections + 1);
			$indentCount = $this->_openSections * 2;
			$intro = "END: ";
		}

        $indent = sprintf("%-{$indentCount}s", "");
		$numCharsSecLines = max((strlen($headerText) + 15), 80);
		$sepLineFmt = "[%'{$lineChar}{$numCharsSecLines}s]";

		$sepLine = sprintf($sepLineFmt, "") . PHP_EOL;

		$fmt = PHP_EOL . PHP_EOL .
			"{$indent}{$sepLine}" . PHP_EOL .
			"{$indent}%-5s%s%s " . PHP_EOL . PHP_EOL .
			"{$indent}{$sepLine}" .
			PHP_EOL;

		$lineContent = sprintf($fmt, "", $intro, $headerText );

		$this->log(LogLevel::INFO, $lineContent);

    }


	/**
	 * @param array $context
	 *
	 * @return array
	 */
	function getDebugContext($context=array(), \Exception $thrownExc = null)
	{
		$baseContext = [
			'class_call' => "",
			'exception_message' => "",
			'exception_file' => "",
			'exception_line' => "",
//		'exception_trace' => "",
			'channel' => "",
			'jobsite' => "",
			'user' => \JobScooper\DataAccess\User::getCurrentUser()
		];

		if(is_array($context))
			$context = array_merge($baseContext, $context);
		else
			$context = $baseContext;

		//Debug backtrace called. Find next occurence of class after Logger, or return calling script:
		$dbg = debug_backtrace();
		$i = 0;
		$jobsiteKey = null;
		$usersearch = null;
		$loggedBacktrace = array();

		$class = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
		while ($i < count($dbg) - 1 ) {
			if (!empty($dbg[$i]['class']) && stripos($dbg[$i]['class'], 'LoggingManager') === false &&
				(empty($dbg[$i]['function']) || !in_array($dbg[$i]['function'], array("getDebugContent", "handleException"))))
			{
				$loggedBacktrace = $dbg[$i];

				$class = $dbg[$i]['class'] . "->" . $dbg[$i]['function'] ."()";
				if(!empty($dbg[$i]['object']))
				{
					$objclass = get_class($dbg[$i]['object']);
					if(strcasecmp($objclass, $dbg[$i]['class']) != 0)
					{
						$class = "{$objclass} -> {$class}";
						try{
							if( is_object($dbg[$i]['object']) && method_exists($dbg[$i]['object'], "getName"))
								$jobsiteKey = $dbg[$i]['object']->getName();
						} catch (Exception $ex) {
							$jobsiteKey = "";
						}
						try{
							if(array_key_exists('args', $dbg[$i]) & is_array($dbg[$i]['args']))
								if(is_object($dbg[$i]['args'][0]) && method_exists(get_class($dbg[$i]['args'][0]), "getUserSearchSiteRunKey"))
									$usersearch = $dbg[$i]['args'][0]->getUserSearchSiteRunKey();
								else
									$usersearch = "";
						} catch (Exception $ex) { $usersearch = ""; }
					}
					break;
				}
			}
			$i++;
		}


		$context['class_call'] = $class;
		$context['channel'] = is_null($jobsiteKey) ? "default" : "plugins";
		$context['jobsite'] = $jobsiteKey;
//	$context['user_search_run_key'] = $usersearch,
//	$context['memory_usage'] = memory_get_usage() / 1024 / 1024;


		if(!empty($thrownExc))
		{
			$context['exception_message'] = $thrownExc->getMessage();
			$context['exception_file'] = $thrownExc->getFile();
			$context['exception_line'] = $thrownExc->getLine();
//		$context['exception_trace'] = join("|", preg_split("/$/", encodeJSON($thrownExc->getTrace())));
		}

		return $context;
	}

}
