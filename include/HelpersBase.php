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



function LogLine($msg, $scooper_level=Scooper\C__DISPLAY_NORMAL__)
{
    if(is_null($GLOBALS['logger']) || !isset($GLOBALS['logger']))
    {
        print($msg);
    }
    else
    {
        $GLOBALS['logger']->logLine($msg, $scooper_level);
    }
}

function LogWarning($msg)
{
    LogLine($msg, Scooper\C__DISPLAY_WARNING__);
}


function object_to_array($obj)
{
    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
    foreach ($arr as $key => $val) {
        $val = (is_array($val) || is_object($val)) ? object_to_array($val) : $val;
        $arr[$key] = $val;
    }
    unset($key);
    unset($val);

    return $arr;
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
    $debugJSONFile = $GLOBALS['USERDATA']['directories']['debug'] . "/" . getDefaultJobsOutputFileName($strFilePrefix = "_debug_" . $strBaseFileName, $strExt = "", $delim = "-") . ".json";
    file_put_contents($debugJSONFile, $jsonSelf);

    return $debugJSONFile;

}

function handleException($ex, $fmtLogMsg = null, $raise = true)
{
    $toThrow = $ex;
    if (is_null($toThrow))
        $toThrow = new Exception($fmtLogMsg);

    if (!array_key_exists('ERROR_REPORT_FILES', $GLOBALS['USERDATA']))
        $GLOBALS['USERDATA']['ERROR_REPORT_FILES'] = array();

    if (!is_null($fmtLogMsg) && !is_null($ex)) {
        $msg = sprintf($fmtLogMsg, $ex->getMessage());
        $toThrow = new Exception($msg, $ex->getCode(), $previous=$ex);
    }
    else if(!is_null($ex))
    {
        $msg = $ex->getMessage();
    }
    else
        $msg = $fmtLogMsg;

//    $msg .= PHP_EOL . "PHP memory usage: " . getPhpMemoryUsage() . PHP_EOL;

    $excKey = md5($msg);

    //
    // Error key = <md5 msg hash><line#>
    //
    if (array_key_exists($excKey, $GLOBALS['USERDATA']['ERROR_REPORT_FILES']) === true) {
        // we already stored this error so need to re-store it.  Just throw it if needed.
        if ($raise === true)
            throw $toThrow;
    }

    LogLine(PHP_EOL . PHP_EOL . PHP_EOL);
    LogLine($msg, \Scooper\C__DISPLAY_ERROR__);
    LogLine(PHP_EOL . PHP_EOL . PHP_EOL);

    $now = new DateTime('NOW');

    $debugData = array(
        "error_time" => $now->format('Y-m-d\TH:i:s'),
        "exception_code" => $ex->getCode(),
        "exception_message" => $msg,
        "exception_file" => $ex->getFile(),
        "exception_line" => $ex->getLine(),
        "exception" => \Scooper\object_to_array($ex)
//        "object_properties" => null,
////        "debug_backtrace" => var_export(debug_backtrace(), true),
//        "exception_stack_trace" => $ex->getTraceAsString()
    );
    $filenm = exportToDebugJSON($debugData, "exception" . $excKey);

    $GLOBALS['USERDATA']['ERROR_REPORT_FILES'][$excKey] = \Scooper\getFilePathDetailsFromString($filenm);


    if ($raise == true) {
        throw $toThrow;
    }
}

/**
 * Strip punctuation from text.
 * http://nadeausoftware.com/articles/2007/9/php_tip_how_strip_punctuation_characters_web_page
 */
function strip_punctuation( $text )
{
    $urlbrackets    = '\[\]\(\)';
    $urlspacebefore = ':;\'_\*%@&?!' . $urlbrackets;
    $urlspaceafter  = '\.,:;\'\-_\*@&\/\\\\\?!#' . $urlbrackets;
    $urlall         = '\.,:;\'\-_\*%@&\/\\\\\?!#' . $urlbrackets;

    $specialquotes  = '\'"\*<>';

    $fullstop       = '\x{002E}\x{FE52}\x{FF0E}';
    $comma          = '\x{002C}\x{FE50}\x{FF0C}';
    $arabsep        = '\x{066B}\x{066C}';
    $numseparators  = $fullstop . $comma . $arabsep;

    $numbersign     = '\x{0023}\x{FE5F}\x{FF03}';
    $percent        = '\x{066A}\x{0025}\x{066A}\x{FE6A}\x{FF05}\x{2030}\x{2031}';
    $prime          = '\x{2032}\x{2033}\x{2034}\x{2057}';
    $nummodifiers   = $numbersign . $percent . $prime;

    return preg_replace(
        array(
            // Remove separator, control, formatting, surrogate,
            // open/close quotes.
            '/[\p{Z}\p{Cc}\p{Cf}\p{Cs}\p{Pi}\p{Pf}]/u',
            // Remove other punctuation except special cases
            '/\p{Po}(?<![' . $specialquotes .
            $numseparators . $urlall . $nummodifiers . '])/u',
            // Remove non-URL open/close brackets, except URL brackets.
            '/[\p{Ps}\p{Pe}](?<![' . $urlbrackets . '])/u',
            // Remove special quotes, dashes, connectors, number
            // separators, and URL characters followed by a space
            '/[' . $specialquotes . $numseparators . $urlspaceafter .
            '\p{Pd}\p{Pc}]+((?= )|$)/u',
            // Remove special quotes, connectors, and URL characters
            // preceded by a space
            '/((?<= )|^)[' . $specialquotes . $urlspacebefore . '\p{Pc}]+/u',
            // Remove dashes preceded by a space, but not followed by a number
            '/((?<= )|^)\p{Pd}+(?![\p{N}\p{Sc}])/u',
            // Remove consecutive spaces
            '/ +/',
        ),
        ' ',
        $text );
}