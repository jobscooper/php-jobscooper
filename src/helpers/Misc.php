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


function object_to_array($obj)
{
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }

    return $arr;
}


function isBitFlagSet($flagSettings = null, $flagToCheck= null)
{
    $ret = ($flagSettings & $flagToCheck);
    if($ret == $flagToCheck) { return true; }
    return false;
}

function getPhpMemoryUsage()
{
    $size = memory_get_usage(true);

    $unit = array(' bytes', 'KB', 'MB', 'GB', 'TB', 'PN');

    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function exportToDebugJSON($obj, $strBaseFileName)
{
    $saveArr = array();
    $arrObj = object_to_array($obj);
    foreach (array_keys($arrObj) as $key) {
        $saveArr[$key] = json_encode($arrObj[$key], JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
    }
    unset($key);

    $jsonSelf = json_encode($saveArr, JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);
    $basefile = getDefaultJobsOutputFileName($strFilePrefix = "_debug_" . $strBaseFileName, $strExt = "", $delim = "-");
    $debugJSONFile = generateOutputFileName($basefile, $ext="json");
    file_put_contents($debugJSONFile, $jsonSelf);

    return $debugJSONFile;

}

function noJobStringMatch($var, $matchString)
{
    if(is_null($matchString) || strlen($matchString) == 0)
        throw new Exception("Invalid match string passed to helper noJobStringMatch.");

    if(stristr(strtoupper($var), strtoupper($matchString)) !== false)
        return 0;

    return null;
}


function getRunDateRange($configNumDays=null)
{
	if(empty($configNumDays))
	    $configNumDays = getConfigurationSetting('number_days');
	
    $num_days = filter_var($configNumDays, FILTER_VALIDATE_INT);
    if($num_days === false)
        $num_days = 1;

    $strDateRange = null;
    $startDate = new DateTime();
    $strMod = "-" . $num_days . " days";
    $startDate = $startDate->modify($strMod);
    $today = new DateTime();
    if ($startDate->format('Y-m-d') != $today->format('Y-m-d')) {
        $strDateRange = $startDate->format('D, M d') . " - " . $today->format('D, M d');
    } else {
        $strDateRange = $today->format('D, M d');
    }
    return $strDateRange;
}


function combineTextAllChildren($node, $fRecursed = false)
{

	if(empty($node))
		return null;

    $retStr = "";
    if (is_array($node) && count($node) > 1) {
        $strError = sprintf("Warning:  " . count($node) . " DOM nodes were sent to combineTextAllChildren instead of a single starting node.  Using first node only.");
        LogWarning($strError);
    }

    if(is_array($node) && count($node) >= 1)
        $node = $node[0];

    if ($node->hasChildren()) {
        foreach ($node->children() as $child) {
            $retStr = $retStr . " " . combineTextAllChildren($child, true);
        }
        unset($child);
    }

    if($fRecursed == false)
    {
        $retStr = strScrub($node->text() . " " . $retStr, HTML_DECODE | REMOVE_EXTRA_WHITESPACE) . $retStr;
    }

    return $retStr;

}

function combineTextAllNodes($nodes)
{
    $retStr = "";
	if(!empty($nodes))
	{
        foreach ($nodes as $node) {
            if($retStr != "")
                $retStr = $retStr . ", ";

            $retStr = $retStr . strScrub($node->text() . " " . $retStr, HTML_DECODE | REMOVE_EXTRA_WHITESPACE);
            if(!is_null($node->children())) {
                foreach ($node->children() as $child) {
                    $retStr = $retStr . " " . combineTextAllChildren($child, true);
                }
            }
        }
    }
    return $retStr;

}


function doExec($cmd)
{
    $cmdOutput = array();
    $cmdRet = "";

    exec($cmd, $cmdOutput, $cmdRet);
    foreach ($cmdOutput as $resultLine)
        if (!is_null($GLOBALS['logger'])) LogMessage($resultLine);
    unset($resultLine);

    if (is_array($cmdOutput))
    {
        if (count($cmdOutput) >= 1)
            return $cmdOutput[0];
        else
            return "";
    }
    return $cmdOutput;
}



if ( ! function_exists('glob_recursive'))
{
	// Does not support flag GLOB_BRACE

	function glob_recursive($pattern, $flags = 0)
	{
		$files = glob($pattern, $flags);

		foreach (glob(dirname($pattern).'/*', GLOB_ONLYDIR|GLOB_NOSORT) as $dir)
		{
			$files = array_merge($files, glob_recursive($dir.'/'.basename($pattern), $flags));
		}

		return $files;
	}
}