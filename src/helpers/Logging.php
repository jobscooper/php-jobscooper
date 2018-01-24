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


/****************************************************************************************************************/
/****                                                                                                        ****/
/****         Logging                                                                                        ****/
/****                                                                                                        ****/
/****************************************************************************************************************/

/**
 * @param $headerText
 * @param $nSectionLevel
 * @param $nType
 */
function startLogSection($headerText)
{
	if(is_null($GLOBALS['logger']) || !isset($GLOBALS['logger']))
	{
		print($headerText. "\r\n");
	}
	else
	{
		$GLOBALS['logger']->startLogSection($headerText);
	}
}

/**
 * @param $headerText
 * @param $nSectionLevel
 * @param $nType
 */
function endLogSection($headerText)
{
	if(is_null($GLOBALS['logger']) || !isset($GLOBALS['logger']))
	{
		print($headerText. "\r\n");
	}
	else
	{
		$GLOBALS['logger']->endLogSection($headerText);
	}
}


/**
 * @param $msg
 * @param \Psr\Log\LogLevel $level
 * @param array $context
 */
function LogMessage($msg, $logLevel=\Monolog\Logger::INFO, $extras=array(), $ex=null)
{
	if(empty($GLOBALS['logger']) || !isset($GLOBALS['logger']))
	{
		print($msg . "\r\n");
	}
	else
	{
		$GLOBALS['logger']->logRecord($logLevel, $msg, $extras, $ex);
	}
}


/**
 * @param $msg
 */
function LogError($msg, $extras=array(), $ex=null)
{
	LogMessage($msg,\Monolog\Logger::ERROR, $extras, $ex);
}


/**
 * @param $msg
 */
function LogWarning($msg, $extras=array())
{
	LogMessage($msg,\Monolog\Logger::WARNING, $extras);
}

/**
 * @param     $msg
 * @param int $scooper_level
 */
function LogDebug($msg, $extras=array())
{
	if(isDebug())
	{
		LogMessage($msg,\Monolog\Logger::DEBUG, $extras);
	}
}

/**
 * @param       $msg
 * @param array $context
 */
function LogPlainText($msg, $context = array())
{
	$textParts = preg_split("/[\\r\\n|" . PHP_EOL . "]/", $msg);
	if(($textParts === false) || is_null($textParts))
		LogMessage($msg);
	else {
		foreach ($textParts as $part) {
			LogMessage($part);
		}
	}
}
/**
 * @param      $ex
 * @param null $fmtLogMsg
 * @param bool $raise
 *
 * @throws \Exception
 */

function handleException(Exception $ex, $fmtLogMsg= null, $raise=true, $extraData=null)
{
	$toThrow = $ex;
	if (empty($toThrow))
		$toThrow = new Exception($fmtLogMsg);


	$msg = $fmtLogMsg;
	if (!is_null($toThrow) && !is_null($fmtLogMsg) && !is_null($ex) && strlen($fmtLogMsg) > 0)
	{
		if(stristr($fmtLogMsg, "%s") !== false)
		{
			$msg = sprintf($fmtLogMsg, $toThrow->getMessage());
			$toThrow = new Exception($msg, null, $ex);
		}
		else
		{
			$msg = $fmtLogMsg . PHP_EOL . " ~ " . $toThrow->getMessage();
		}
	}
	elseif(!is_null($ex))
	{
		$msg = $toThrow->getMessage();
	}

	LogError($msg, $extraData, $toThrow);

	if ($raise == true) {
		throw $toThrow;
	}
}
