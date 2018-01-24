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




CONST C__FILEPATH_NO_FLAGS = 0x0;
CONST C__FILEPATH_FILE_MUST_EXIST = 0x1;
CONST C__FILEPATH_DIRECTORY_MUST_EXIST = 0x2;
CONST C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED= 0x4;

/**
 * @param     $strFilePath
 * @param int $flags
 *
 * @return SplFileInfo
 * @throws \ErrorException
 */
function parsePathDetailsFromString($strFilePath, $flags = C__FILEPATH_NO_FLAGS)
{

	// if the path doesn't start with a '/', it's a relative path
	//
	$fPathIsRelative = !(substr($strFilePath, 0, 1) == '/');

	//************************************************************************
	//
	// Now let's figure out what each part really maps to and setup the array with names for returning
	// to the caller.
	//
	// If AllParts only has one item, then there were no "/" characters in the path string.
	// So assume the path was either a filename only OR a relative directory path with no trailing '/'
	//

	if(!empty($strFilePath)) {
		$f = new SplFileInfo($strFilePath);

		//
		// At this point, we've set the values for the return array completely
		//


		if(isBitFlagSet($flags, C__FILEPATH_DIRECTORY_MUST_EXIST) && !$f->isDir())
		{
			throw new \ErrorException("Directory '" . $f->getPathname() . "' does not exist.");
		}

		if(isBitFlagSet($flags, C__FILEPATH_FILE_MUST_EXIST) && !$f->isFile())
		{
			throw new \ErrorException("File '" . $f->getPathname() . "' does not exist.");
		}

		if(isBitFlagSet($flags, C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED) && !$f->isDir())
		{
			mkdir($f->getPathname(), 0777, true);
		}


		return $f;
	}


}


function getOutputDirectory($key)
{
	$ret = getConfigurationSetting("output_directories.".$key);
	if(empty($ret))
		$ret =  sys_get_temp_dir();

	return $ret;
}

function generateOutputFileName($baseFileName="NONAME", $ext="UNK", $isUserSpecific=true, $dirKey="debug")
{
	$outDir = getOutputDirectory($dirKey);
	$today = "_" . getNowAsString("");
	$user = "";
	if($isUserSpecific === true) {
		$objUser = \JobScooper\DataAccess\User::getCurrentUser();
		if(!is_null($objUser))
		{
			$user = "_" . $objUser->getUserSlug();
		}
	}

	$ret = "{$outDir}/{$baseFileName}{$user}{$today}.{$ext}";
	return $ret;
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


function getDefaultJobsOutputFileName($strFilePrefix = '', $strBase = '', $strExt = '', $delim = "", $directoryKey = null)
{
    $strFilename = '';
    if (strlen($strFilePrefix) > 0) $strFilename .= $strFilePrefix . "-";
    $date = date_create(null);
    $fmt = "Y" . $delim . "m" . $delim . "d" . "Hi";

    $strFilename .= date_format($date, $fmt);

    if (strlen($strBase) > 0) $strFilename .= "-" . $strBase;
    if (strlen($strExt) > 0) $strFilename .= "." . $strExt;

	$directory = getOutputDirectory($directoryKey);
    if(!empty($directory))
        $strFilename = $directory . DIRECTORY_SEPARATOR . $strFilename;
    return $strFilename;
}

/**
 * json encode that can handle invalid UTF-8 chars
 *
 * Source:  http://php.net/manual/en/function.json-last-error.php#121233
 * @param $value
 * @param int $options
 * @param int $depth
 * @return string
 */
function safe_json_encode($value, $options = 0, $depth = 512) {
    $encoded = json_encode($value, $options, $depth);
    if ($encoded === false && $value && json_last_error() == JSON_ERROR_UTF8) {
        $encoded = json_encode(utf8ize($value), $options, $depth);
    }
    return $encoded;
}

/**
 * use this code with mb_convert_encoding, you can json_encode some corrupt UTF-8 chars
 *
 * Source:  http://php.net/manual/en/function.json-last-error.php#121233
 * @param $mixed
 * @return array|mixed|string
 */
function utf8ize($mixed) {
    if (is_array($mixed)) {
        foreach ($mixed as $key => $value) {
            $mixed[$key] = utf8ize($value);
        }
    } elseif (is_string($mixed)) {
        return mb_convert_encoding($mixed, "UTF-8", "UTF-8");
    }
    return $mixed;
}

function encodeJSON($data)
{
    $jsonData = safe_json_encode($data, JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP |  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
    if ($jsonData === false) {
        $err = json_last_error_msg();
        $errMsg = "Error:  Unable to encode data to JSON.  Error: " . $err;
        LogError($errMsg);
        throw new Exception($errMsg);
    }
    return $jsonData;
}

function writeJSON($data, $filepath)
{
    $jsonData = encodeJSON($data);

    LogMessage("Writing data to json file " . $filepath);
    if (file_put_contents($filepath, $jsonData, FILE_TEXT) === false) {
        $err = error_get_last();
        $errMsg = "Error:  Unable to save JSON results to file " . $filepath . " due to error   " . $err;
        LogError($errMsg);
        throw new Exception($errMsg);

    }

    return $filepath;
}

function decodeJSON($strJsonText, $options=null, $boolEscapeBackSlashes=false)
{
    if(is_null($options))
        $options = JSON_PRETTY_PRINT | JSON_HEX_QUOT | JSON_HEX_APOS | JSON_HEX_AMP |  JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK;


    if($boolEscapeBackSlashes === true)
        $strJsonText = str_replace('\\', '\\\\', $strJsonText);
    $data = json_decode($strJsonText, $assoc = true, $depth=512, $options);
    return $data;

}

function loadJSON($file, $options=null, $boolEscapeBackSlashes=false)
{
    if(is_file($file)) {
        LogDebug("Reading json data from file " . $file);
        $jsonText = file_get_contents($file, FILE_TEXT);
        return decodeJSON($jsonText, $options, $boolEscapeBackSlashes);
    }
    else
    {
        LogError("Unable to load json data from file " . $file);
        return null;
    }

}


function file_prepend($prependText, $filepath)
{
    $context = stream_context_create();
    $orig_file = fopen($filepath, 'r', 1, $context);
    if($orig_file === false)
        throw new ErrorException("Unable to open file stream {$filepath} for reading.");

    $temp_filename = tempnam(sys_get_temp_dir(), 'php_prepend_');
    file_put_contents($temp_filename, $prependText);
    file_put_contents($temp_filename, $orig_file, FILE_APPEND);

    fclose($orig_file);
    unlink($filepath);
    rename($temp_filename, $filepath);

}

