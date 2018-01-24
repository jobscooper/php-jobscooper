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
require_once dirname(dirname(__FILE__))."/bootstrap.php";

const C__STR_TAG_AUTOMARKEDJOB__ = "[auto-marked]";
const C__STR_TAG_DUPLICATE_POST__ = "No (Duplicate Job Post?)";
const C__STR_TAG_BAD_TITLE_POST__ = "No (Bad Title & Role)";
const C__STR_TAG_NOT_A_KEYWORD_TITLE_MATCH__ = "No (Not a Keyword Title Match)";
const C__STR_TAG_NOT_EXACT_TITLE_MATCH__ = "No (Not an Exact Title Match)";

//
//function obj2array ( &$Instance ) {
//    $clone = (array) $Instance;
//    $rtn = array ();
//    $rtn['___SOURCE_KEYS_'] = $clone;
//
//    while ( list ($key, $value) = each ($clone) ) {
//        $aux = explode ("\0", $key);
//        $newkey = $aux[count($aux)-1];
//        $rtn[$newkey] = &$rtn['___SOURCE_KEYS_'][$key];
//    }
//
//    return $rtn;
//}
//

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


function handleException($ex, $fmtLogMsg = null, $raise = true)
{
    $toThrow = $ex;
    if (is_null($toThrow))
        $toThrow = new Exception($fmtLogMsg);

    if (!array_key_exists('ERROR_REPORT_FILES', $GLOBALS['USERDATA']))
        $GLOBALS['USERDATA']['ERROR_REPORT_FILES'] = array();

    $msg = $fmtLogMsg;
    if (!is_null($toThrow) && !is_null($fmtLogMsg) && !is_null($ex) && strlen($fmtLogMsg) > 0 &&
        stristr($fmtLogMsg, "%s") !== false)
    {
        $msg = sprintf($fmtLogMsg, $toThrow->getMessage());
        $toThrow = new Exception($msg, $toThrow->getCode(), $previous=$ex);
    }
    elseif(!is_null($ex))
    {
        $msg = $toThrow->getMessage();
    }

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

    if (isset($GLOBALS['logger'])) {
        $GLOBALS['logger']->logLine(PHP_EOL . PHP_EOL . PHP_EOL);
        $GLOBALS['logger']->logLine($msg, \Scooper\C__DISPLAY_ERROR__);
        $GLOBALS['logger']->logLine(PHP_EOL . PHP_EOL . PHP_EOL);
    }

    $now = new DateTime('NOW');

    $debugData = array(
        "error_time" => $now->format('Y-m-d\TH:i:s'),
        "exception_code" => $toThrow->getCode(),
        "exception_message" => $msg,
        "exception_file" => $toThrow->getFile(),
        "exception_line" => $toThrow->getLine(),
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

function getRunDateRange()
{
    $strDateRange = null;
    $startDate = new DateTime();
    $strMod = "-".$GLOBALS['USERDATA']['configuration_settings']['number_days']." days";
    $startDate = $startDate->modify($strMod);
    $today = new DateTime();
    if($startDate->format('Y-m-d') != $today->format('Y-m-d'))
    {
        $strDateRange = $startDate->format('D, M d') . " - " . $today->format('D, M d');
    }
    else
    {
        $strDateRange = $today->format('D, M d');
    }
    return $strDateRange;
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

function saveDomToFile($htmlNode, $filepath)
{

    $strHTML = strval($htmlNode);

    $htmlTmp = \voku\helper\HtmlDomParser::str_get_html($strHTML);
    $htmlTmp->save($filepath);

    return $strHTML;

}

function array_find_closest_key_match($search, $arr) {
    $closest = null;
    $closestScore = null;
    $percent = 0;
    foreach (array_keys($arr) as $item) {
        similar_text($search, $item, $percent);
        if($percent > $closestScore) {
            $closestScore = $percent;
            $closest = $item;
        }
//        if ($closest === null || abs($search - $closest) > abs($item - $search)) {
//            $closest = $item;
//        }
    }
    return $closest;
}

function clean_utf8($string, $control = true)
{
    $string = iconv('UTF-8', 'UTF-8//IGNORE', $string);

    if ($control === true) {
        return preg_replace('~\p{C}+~u', '', $string);
    }

    return preg_replace(array('~\r\n?~', '~[^\P{C}\t\n]+~u'), array("\n", ''), $string);
}

function getDateForDaysAgo($strDaysAgo)
{
    $retDate = null;

    if (!isset($strDaysAgo) || strlen($strDaysAgo) <= 0) return $retDate;

    if (is_numeric($strDaysAgo)) {
        $daysToSubtract = $strDaysAgo;
    } else {
        $strDaysAgo = \Scooper\strScrub($strDaysAgo, SIMPLE_TEXT_CLEANUP | LOWERCASE);
        if (strcasecmp($strDaysAgo, "yesterday") == 0) {
            $daysToSubtract = 1;
        } elseif (strcasecmp($strDaysAgo, "today") == 0) {
            $daysToSubtract = 0;
        } else {
            $daysToSubtract = null;
        }

    }

    if (isset($daysToSubtract)) {
        $date = new DateTime();
        $date->modify("-" . $daysToSubtract . " days");
        $retDate = $date->format('Y-m-d');
    }

    return $retDate;
}

function combineTextAllChildren($node, $fRecursed = false)
{

    $retStr = "";
    if ($node->hasChildNodes()) {
        $retStr = \Scooper\strScrub($node->plaintext . " " . $retStr, HTML_DECODE | REMOVE_EXTRA_WHITESPACE);
        foreach ($node->childNodes() as $child) {
            $retStr = $retStr . " " . combineTextAllChildren($child, true);
        }
        unset($child);

    } elseif (isset($node->plaintext) && $fRecursed == false) {
        $retStr = \Scooper\strScrub($node->plaintext . " " . $retStr, HTML_DECODE | REMOVE_EXTRA_WHITESPACE);
    }

    return $retStr;

}

function combineTextAllNodes($nodes)
{

    $retStr = "";
    if ($nodes) {
        foreach ($nodes as $node) {
            if($retStr != "")
                $retStr = $retStr . ", ";

            $retStr = $retStr . \Scooper\strScrub($node->plaintext . " " . $retStr, HTML_DECODE | REMOVE_EXTRA_WHITESPACE);
            if(!is_null($node->childNodes())) {
                foreach ($node->childNodes() as $child) {
                    $retStr = $retStr . " " . combineTextAllChildren($child, true);
                }
            }
        }
    }
    return $retStr;

}

function sortByCountDesc($a, $b)
{
    $al = $a["total_listings"];
    $bl = $b["total_listings"];
    if ($al == $bl) {
        return 0;
    }
    return ($al < $bl) ? +1 : -1;
}

function sortByErrorThenCount($a, $b)
{
    $rank = array($a['name'] => 0, $b['name'] => 0);

    if ($a['had_error'] > $b['had_error']) {
        return 1;
    } elseif ($a['had_error'] < $b['had_error']) {
        return -1;
    }

    if ($a["total_listings"] == $b["total_listings"]) {
        return 0;
    }

    return ($a["total_listings"] < $b["total_listings"]) ? +1 : -1;
}

function getArrayKeyValueForJob($job)
{

    return $job['key_jobsite_siteid'];

    /*
        $strKey = \Scooper\strScrub($job['job_site'], DEFAULT_SCRUB | REMOVE_PUNCT | REPLACE_SPACES_WITH_HYPHENS);

        // For craigslist, they change IDs on every post, so deduping that way doesn't help
        // much.  Instead, let's dedupe for Craigslist by using the role title and the jobsite
        // (Company doesn't usually get filled out either with them.)
        if(strcasecmp($strKey, "craigslist") == 0)
        {
            $strKey = $strKey . "-" . \Scooper\strScrub($job['job_title'], DEFAULT_SCRUB | REMOVE_PUNCT | REPLACE_SPACES_WITH_HYPHENS );
        }
        if($job['job_id'] != null && $job['job_id'] != "")
        {
            $strKey = $strKey . "-" . \Scooper\strScrub($job['job_id'], REPLACE_SPACES_WITH_HYPHENS | REMOVE_PUNCT | HTML_DECODE | LOWERCASE);
        }
        else
        {
            $strKey = $strKey . "-" . \Scooper\strScrub($job['company'], DEFAULT_SCRUB | REMOVE_PUNCT | REPLACE_SPACES_WITH_HYPHENS );
            $strKey = $strKey . "-" . \Scooper\strScrub($job['job_title'], DEFAULT_SCRUB | REMOVE_PUNCT | REPLACE_SPACES_WITH_HYPHENS );
        }
        return $strKey;
    */


}

function doExec($cmd)
{
    $cmdOutput = array();
    $cmdRet = "";

    exec($cmd, $cmdOutput, $cmdRet);
    foreach ($cmdOutput as $resultLine)
        if (!is_null($GLOBALS['logger'])) $GLOBALS['logger']->logLine($resultLine, \Scooper\C__DISPLAY_ITEM_DETAIL__);
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

function countAssociativeArrayValues($arrToCount)
{
    if ($arrToCount == null || !is_array($arrToCount)) {
        return 0;
    }

    $count = 0;
    foreach ($arrToCount as $item) {
        $count = $count + 1;
    }
    unset($item);


    $arrValues = array_values($arrToCount);
    $nValues = count($arrValues);
    return max($nValues, $count);
//    $arrKeys = array_keys($arrToCount);
//    $nKeys = count($arrKeys);

//    return max($nKeys, $nValues);
}

function countJobRecords($arrJobs)
{
    return countAssociativeArrayValues($arrJobs);
}


function addJobsToJobsList(&$arrJobsListToUpdate, $arrAddJobs)
{
    if ($arrAddJobs == null) return;

    if (!is_array($arrAddJobs) || count($arrAddJobs) == 0) {
        // skip. no jobs to add
        return;
    }
    if ($arrJobsListToUpdate == null) $arrJobsListToUpdate = array();

    foreach ($arrAddJobs as $jobRecord) {
        addJobToJobsList($arrJobsListToUpdate, $jobRecord);
    }
    unset($jobRecord);


}


function addJobToJobsList(&$arrJobsListToUpdate, $job)
{
    if ($arrJobsListToUpdate == null) $arrJobsListToUpdate = array();

    $jobToAdd = \Scooper\array_copy($job);


    if (isset($arrJobsListToUpdate[$job['key_jobsite_siteid']])) {
        $jobToAdd = getMergedJobRecord($arrJobsListToUpdate[$job['key_jobsite_siteid']], $job);
    }


    $arrJobsListToUpdate[$job['key_jobsite_siteid']] = $jobToAdd;
}


function updateJobColumn(&$job, $newJob, $strColumn, $fAllowEmptyValueOverwrite = false)
{
    $prevJob = \Scooper\array_copy($job);

    if (strlen($job[$strColumn]) == 0) {
        $job[$strColumn] = $newJob[$strColumn];
    } elseif (strlen($newJob[$strColumn]) == 0) {
        if ($fAllowEmptyValueOverwrite == true) {
            $job[$strColumn] = $newJob[$strColumn];
            $job['match_notes'] .= $strColumn . " value '" . $prevJob[$strColumn] . "' removed.'" . PHP_EOL;
        }
    } else {
        if (strcasecmp(\Scooper\strScrub($job[$strColumn]), \Scooper\strScrub($newJob[$strColumn])) != 0) {
            $job[$strColumn] = $newJob[$strColumn];
            $job['match_notes'] .= PHP_EOL . $strColumn . ": old[" . $prevJob[$strColumn] . "], new[" . $job[$strColumn] . "]" . PHP_EOL;
        }
    }

}

function appendJobColumnData(&$job, $strColumn, $delim, $newData)
{
    if (is_string($job[$strColumn]) && strlen($job[$strColumn]) > 0) {
        $job[$strColumn] .= $delim . " ";
    }
    $job[$strColumn] .= $newData;

}

function getArrayItemDetailsAsString($arrItem, $key, $fIsFirstItem = true, $strDelimiter = "", $strIntro = "", $fIncludeKey = true)
{
    $strReturn = "";

    if (isset($arrItem[$key])) {
        $val = $arrItem[$key];
        if (is_string($val) && strlen($val) > 0) {
            $strVal = $val;
        } elseif (is_array($val) && !(\Scooper\is_array_multidimensional($val))) {
            $strVal = join(" | ", $val);
        } else {
            $strVal = print_r($val, true);
        }

        if ($fIsFirstItem == true) {
            $strReturn = $strIntro;
        } else {
            $strReturn .= $strDelimiter;
        }
        if ($fIncludeKey == true) {
            $strReturn .= $key . '=[' . $strVal . ']';
        } else {
            $strReturn .= $strVal;
        }

    }


    return $strReturn;
}

function cloneArray($source, $arrDontCopyTheseKeys = array())
{
    $retDetails = \Scooper\array_copy($source);

    foreach ($arrDontCopyTheseKeys as $key) {
        unset($retDetails[$key]);
    }

    return $retDetails;
}


function array_mapk($callback, $array)
{
    $newArray = array();
    foreach ($array as $k => $v) {
        $newArray[$k] = call_user_func($callback, $k, $v);
    }
    unset($k);

    return $newArray;
}

function array_unique_multidimensional($input)
{
    $serialized = array_map('serialize', $input);
    $unique = array_unique($serialized);
    return array_intersect_key($input, $unique);
}

function getArrayValuesAsString($arrDetails, $strDelimiter = ", ", $strIntro = "", $fIncludeKey = true)
{
    $strReturn = "";

    if (isset($arrDetails) && is_array($arrDetails)) {
        foreach (array_keys($arrDetails) as $key) {
            $strReturn .= getArrayItemDetailsAsString($arrDetails, $key, (strlen($strReturn) <= 0), $strDelimiter, $strIntro, $fIncludeKey);
        }
        unset($key);
    }

    return $strReturn;
}

function updateJobRecord($prevJobRecord, $jobRecordChanges)
{

    return getMergedJobRecord($prevJobRecord, $jobRecordChanges);
}

function getMergedJobRecord($prevJobRecord, $newerJobRecord)
{
    if ($prevJobRecord['key_jobsite_siteid'] == $newerJobRecord['key_jobsite_siteid']) {
        return $prevJobRecord; // don't update yourself.
    }

    // Since we already had a job record for this particular listing,
    // we'll merge the new info into the old one.  For most fields,
    // the latter (aka the passed in $job) values will win.  For some
    // fields such as Notes, the values will be combined.
    //
    $mergedJob = \Scooper\array_copy($prevJobRecord);

    updateJobColumn($mergedJob, $newerJobRecord, 'company', false);
    updateJobColumn($mergedJob, $newerJobRecord, 'job_title', false);
//    updateJobColumn($mergedJob, $newerJobRecord, 'location', false);
//    updateJobColumn($mergedJob, $newerJobRecord, 'job_site_category', false);
//    updateJobColumn($mergedJob, $newerJobRecord, 'date_pulled', false);
//    updateJobColumn($mergedJob, $newerJobRecord, 'job_post_url', false);
//    updateJobColumn($mergedJob, $newerJobRecord, 'job_site_date', false);

    if (!isMarked_InterestedOrBlank($prevJobRecord)) {
        updateJobColumn($mergedJob, $newerJobRecord, 'interested', false);
//        updateJobColumn($mergedJob, $newerJobRecord, 'status', false);
    }


    $mergedJob['match_notes'] = $newerJobRecord['match_notes'] . ' ' . $mergedJob['match_notes'];
    $mergedJob['date_last_updated'] = getTodayAsString();

    return $mergedJob;

}

function sortJobsListByCompanyRole(&$arrJobList)
{

    if (countJobRecords($arrJobList) > 0) {
        $arrFinalJobIDs_SortedByCompanyRole = array();
        $finalJobIDs_CompanyRole = array_column($arrJobList, 'key_company_role', 'key_jobsite_siteid');
        foreach (array_keys($finalJobIDs_CompanyRole) as $key) {
            // Need to add uniq key of job site id to the end or it will collapse duplicate job titles that
            // are actually multiple open posts
            $arrFinalJobIDs_SortedByCompanyRole[$finalJobIDs_CompanyRole[$key] . "-" . $key] = $key;
        }

        ksort($arrFinalJobIDs_SortedByCompanyRole);
        $arrFinalJobs_SortedByCompanyRole = array();
        foreach ($arrFinalJobIDs_SortedByCompanyRole as $jobid) {
            $arrFinalJobs_SortedByCompanyRole[$jobid] = $arrJobList[$jobid];
        }
        $arrJobList = $arrFinalJobs_SortedByCompanyRole;
    }

}

function loadCSV($filename, $indexKeyName = null)
{
    if (!is_file($filename)) {
        throw new Exception("Specified input file '" . $filename . "' was not found.  Aborting.");
    }

    $file = fopen($filename, "r");
    if (is_bool($file)) {
        throw new Exception("Specified input file '" . $filename . "' could not be opened.  Aborting.");
    }

    $headers = fgetcsv($file);
    $arrLoadedList = array();
    while (!feof($file)) {
        $rowData = fgetcsv($file);
        if ($rowData === false) {
            break;
        } else {
            $arrRec = array_combine($headers, $rowData);
            if ($indexKeyName != null)
                $arrLoadedList[$arrRec[$indexKeyName]] = $arrRec;
            else
                $arrLoadedList[] = $arrRec;
        }
    }

    fclose($file);

    return $arrLoadedList;

}

/**
 * Allows multiple expressions to be tested on one string.
 * This will return a boolean, however you may want to alter this.
 *
 * @author William Jaspers, IV <wjaspers4@gmail.com>
 * @created 2009-02-27 17:00:00 +6:00:00 GMT
 * @access public
 * @ref http://www.php.net/manual/en/function.preg-match.php#89252
 *
 * @param array $patterns An array of expressions to be tested.
 * @param String $subject The data to test.
 * @param array $findings Optional argument to store our results.
 * @param mixed $flags Pass-thru argument to allow normal flags to apply to all tested expressions.
 * @param array $errors A storage bin for errors
 *
 * @returns bool True if successful; false if errors occurred.
 */
function preg_match_multiple(array $patterns = array(), $subject = null, &$findings = array(), $flags = false, &$errors = array())
{
    foreach ($patterns as $name => $pattern) {
        $found = false;
        if (1 <= preg_match_all($pattern, $subject, $found, $flags)) {
            $findings[$name] = $found;
        } else {
            if (PREG_NO_ERROR !== ($code = preg_last_error())) {
                $errors[$name] = $code;
            } else {
                // No match was found, so don't return it in the findings
                // $findings[$name] = array();
            }
        }
    }
    unset($pattern);
    unset($name);

    return (0 === sizeof($errors));
}

/**
 * Allows multiple expressions to be tested on one string.
 * This will return a boolean, however you may want to alter this.
 *
 * @author William Jaspers, IV <wjaspers4@gmail.com>
 * @created 2009-02-27 17:00:00 +6:00:00 GMT
 * @access public
 * @ref http://www.php.net/manual/en/function.preg-match.php#89252
 *
 * @param array $patterns An array of expressions to be tested.
 * @param String $subject The data to test.
 * @param array $findings Optional argument to store our results.
 * @param mixed $flags Pass-thru argument to allow normal flags to apply to all tested expressions.
 * @param array $errors A storage bin for errors
 *
 * @returns bool True if successful; false if errors occurred.
 */
function substr_count_multi($subject = "", array $patterns = array(), &$findings = array(), $boolMustMatchAllKeywords = false)
{
    foreach ($patterns as $name => $pattern) {
        $found = false;
        $count = \Scooper\substr_count_array($subject, $pattern);
        if (0 < $count) {
            $findings[$name] = $pattern;
        } else {

            if ($boolMustMatchAllKeywords == true)
                return (sizeof($findings) === sizeof($patterns));

//            if (PREG_NO_ERROR !== ($code = preg_last_error() )) {
//                $errors[$name] = $code;
//            }
//            else
//            {
            // No match was found, so don't return it in the findings
            // $findings[$name] = array();
//            }
        }
    }
    return !(0 === sizeof($findings));
}

function getTodayAsString($delim = "-")
{
    $fmt = "Y" . $delim . "m" . $delim . "d";
    return date($fmt);
}

function getArrayItem($key, $arr)
{
    if(array_key_exists($key, $arr))
        return $arr[$key];

    return null;
}

function getDefaultJobsOutputFileName($strFilePrefix = '', $strBase = '', $strExt = '', $delim = "")
{
    $strFilename = '';
    if (strlen($strFilePrefix) > 0) $strFilename .= $strFilePrefix . "-";
    $date = date_create(null);
    $fmt = "Y" . $delim . "m" . $delim . "d" . "Hi";

    $strFilename .= date_format($date, $fmt);

    if (strlen($strBase) > 0) $strFilename .= "-" . $strBase;
    if (strlen($strExt) > 0) $strFilename .= "." . $strExt;

    return $strFilename;
}

function isValueURLEncoded($str)
{
    if (strlen($str) <= 0) return 0;
    return (\Scooper\substr_count_array($str, array("%22", "&", "=", "+", "-", "%7C", "%3C")) > 0);
}


function addDelimIfNeeded($currString, $delim, $strToAdd)
{
    $result = $currString;
    if (strlen($currString) > 0)
        $result = $result . $delim;

    $result = $result . $strToAdd;
    return $result;
}

function writeJobsListDataToLocalJSONFile($fileKey, $dataJobs, $listType, $dirKey = null, $searchDetails = null)
{
    if (is_null($dataJobs))
        $dataJobs = array();

    $fileKey = str_replace(" ", "", $fileKey);

    $resultsFile = join(DIRECTORY_SEPARATOR, array($GLOBALS['USERDATA']['directories'][$dirKey], strtolower($fileKey)));

    if (stripos($fileKey, ".json") === false)
        $resultsFile = $resultsFile . "-" . strtolower(getTodayAsString("")) . ".json";

    return writeJobsListDataToFile($resultsFile, $fileKey, $dataJobs, $listType, $searchDetails);
}

function writeJobsListDataToFile($filepath, $fileKey = null, $dataJobs, $listType, $searchDetails = null)
{
    if (is_null($dataJobs))
        $dataJobs = array();

    if (is_null($fileKey))
    {
        $fileKey = basename($filepath);
    }

    $data = array('key' => $fileKey, 'listtype' => $listType, 'jobs_count' => countJobRecords($dataJobs), 'jobslist' => $dataJobs, 'search' => $searchDetails);
    return writeJSON($data, $filepath);
}

function writeJSON($data, $filepath)
{
    $jsonData = json_encode($data, JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES);
    if ($jsonData === false) {
        $err = json_last_error_msg();
        $errMsg = "Error:  Unable to convert jobs list data to json due to error   " . $err;
        $GLOBALS['logger']->logLine($errMsg, \Scooper\C__DISPLAY_ERROR__);
        throw new Exception($errMsg);

    }

    $GLOBALS['logger']->logLine("Writing final job data pull results to json file " . $filepath);
    if (file_put_contents($filepath, $jsonData, FILE_TEXT) === false) {
        $err = error_get_last();
        $errMsg = "Error:  Unable to save JSON results to file " . $filepath . " due to error   " . $err;
        $GLOBALS['logger']->logLine($errMsg, \Scooper\C__DISPLAY_ERROR__);
        throw new Exception($errMsg);

    }

    return $filepath;
}

function loadJSON($file, $options=JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP | JSON_UNESCAPED_SLASHES)
{
    if(is_file($file)) {
        if(!is_null($GLOBALS['logger'])) $GLOBALS['logger']->logLine("Reading json data from file " . $file, \Scooper\C__DISPLAY_ITEM_DETAIL__);

        $jsonText = file_get_contents($file, FILE_TEXT);
//        $jsonText = str_replace('\\', '\\\\', $jsonText);
        $data = json_decode($jsonText, $assoc = true, $depth=512, $options);
        return $data;
    }
    else
    {
        if(!is_null($GLOBALS['logger'])) $GLOBALS['logger']->logLine("Unable to load json data from file " . $file, \Scooper\C__DISPLAY_ERROR__);
        return null;
    }

}



function readJobsListDataFromLocalJsonFile($fileKey, $returnFailedSearches = true, $dirKey = null)
{

    assert(!is_null($dirKey));

    $fileKey = str_replace(" ", "", $fileKey);
    $resultsFile = join(DIRECTORY_SEPARATOR, array($GLOBALS['USERDATA']['directories'][$dirKey], strtolower($fileKey)));
    if (stripos($fileKey, ".json") === false)
        $resultsFile = $resultsFile . "-" . strtolower(getTodayAsString("")) . ".json";

    return readJobsListDataFromLocalFile($resultsFile, $returnFailedSearches);
}

function readJobsListDataFromLocalFile($filepath, $returnFailedSearches = true)
{

    if (stripos($filepath, ".json") === false)
        $filepath = $filepath . "-" . strtolower(getTodayAsString("")) . ".json";

        if (is_file($filepath)) {
            if(!is_null($GLOBALS['logger'])) $GLOBALS['logger']->logLine("Reading json data from file " . $filepath, \Scooper\C__DISPLAY_ITEM_DETAIL__);
            $data = loadJSON($filepath);
//            $jsonText = file_get_contents($filepath, FILE_TEXT);
//
//            $data = json_decode($jsonText, $assoc = true, JSON_HEX_QUOT | JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP);

            if ($returnFailedSearches === false) {
                if ($data['search']['search_run_result']['success'] !== true) {
                    if(!is_null($GLOBALS['logger'])) $GLOBALS['logger']->logLine("Ignoring incomplete search results found in file with key " . $filepath);
                    $data = null;
                }
        }


        return $data;
    }

    return array();
}

function readJobsListFromLocalJsonFile($fileKey, $returnFailedSearches = true, $dirKey = null)
{
    $retJobs = null;
    $data = readJobsListDataFromLocalJsonFile($fileKey, $returnFailedSearches, $dirKey);

    if (!is_null($data) && is_array($data)) {
        if (array_key_exists("jobslist", $data)) {
            $retJobs = array_filter($data['jobslist'], "isNotExcludedJobSite");
        }

        return $retJobs;
    }

    return null;
}

use LightnCandy\LightnCandy;

function loadTemplate($path)
{
    $template  = file_get_contents($path);


    $partialDir = dirname($path) . "/partials";

    $phpStr = LightnCandy::compile($template, Array(
        'flags' => LightnCandy::FLAG_RENDER_DEBUG | LightnCandy::FLAG_ERROR_LOG| LightnCandy::FLAG_ERROR_EXCEPTION| LightnCandy::FLAG_HANDLEBARSJS_FULL,
        'partialresolver' => function ($cx, $name) use($partialDir) {
            $partialpath = "$partialDir/$name.tmpl";
            if (file_exists($partialpath)) {
                return file_get_contents($partialpath);
            }
            return "[partial (file:$partialpath) not found]";
        }

    ));  // set compiled PHP code into $phpStr

    // Save the compiled PHP code into a php file
    $renderFile = $GLOBALS['USERDATA']['directories']['debug'] . "/" .basename($path) .'-render.php';

    file_put_contents($renderFile, '<?php ' . $phpStr . '?>');

    // Get the render function from the php file
//    $renderer = include($renderFile);
// Get the render function
    $renderer = LightnCandy::prepare($phpStr);
    if($renderer == false)
    {
        throw new Exception("Error: unable to compile template '$path'");
    }


    return $renderer;
}

function getPhpMemoryUsage()
{
    $size = memory_get_usage(true);

    $unit = array(' bytes', 'KB', 'MB', 'GB', 'TB', 'PN');

    return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
}

function getFailedSearchesByPlugin()
{
    if (!array_key_exists('search_results', $GLOBALS['USERDATA']) || is_null($GLOBALS['USERDATA']['search_results']))
        return null;

    $arrFailedSearches = array_filter($GLOBALS['USERDATA']['search_results'], function ($k) {
        return ($k['search_run_result']['success'] !== true);
    });

    if (is_null($arrFailedSearches) || !is_array($arrFailedSearches))
        return null;

    $arrFailedPluginsReport = array();
    foreach ($arrFailedSearches as $search) {
        if (!is_null($search['search_run_result']['success'])) {
            if (!array_key_exists($search['site_name'], $arrFailedPluginsReport))
                $arrFailedPluginsReport[$search['site_name']] = array();

            $arrFailedPluginsReport[$search['site_name']][$search['key']] = cloneArray($search, array(
                'keywords_string_for_url',
                'base_url_format',
                'keywords_array_tokenized',
                'user_setting_flags',
                'search_start_url',
                'location_set_key',
                'location_user_specified_override',
                'location_search_value',
                'keyword_search_override',
                'keywords_array'));
        }
    }
    unset($search);

    return $arrFailedPluginsReport;
}
function getFailedSearches()
{
    if (!array_key_exists('search_results', $GLOBALS['USERDATA']) || is_null($GLOBALS['USERDATA']['search_results']))
        return null;

    $arrFailedSearches = array_filter($GLOBALS['USERDATA']['search_results'], function ($k) {
        return ($k['search_run_result']['success'] !== true);
    });

    if (is_null($arrFailedSearches) || !is_array($arrFailedSearches))
        return null;

    $arrFailedPluginsReport = array();
    foreach ($arrFailedSearches as $search) {
        if (!is_null($search['search_run_result']['success'])) {

            $arrFailedPluginsReport[$search['key']] = $search;
        }
    }

    return $arrFailedPluginsReport;
}

function noJobStringMatch($var, $matchString)
{
    if(is_null($matchString) || strlen($matchString) == 0)
        throw new Exception("Invalid match string passed to helper noJobStringMatch.");
        
    if(stristr(strtoupper($var), strtoupper($matchString)) !== false)
        return 0;

    return null;
}

