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


/**
 *
 * Pass the array, followed by the column names and sort flags
 * $sorted = array_orderby($data, 'volume', SORT_DESC, 'edition', SORT_ASC);
 *
 * The sorted array is now in the return value of the function instead of being passed by reference.
 *
 * @link http://php.net/manual/en/function.array-multisort.php#100534
 * @author jimpoz at jimpoz dot com ¶
 *
 * @param mixed
 *
 * @return int|mixed
 */
function array_orderby()
{
	$args = func_get_args();
	$data = array_shift($args);
	foreach ($args as $n => $field) {
		if (is_string($field)) {
			$tmp = array();
			foreach ($data as $key => $row)
				$tmp[$key] = $row[$field];
			$args[$n] = $tmp;
		}
	}
	$args[] = &$data;
	call_user_func_array('array_multisort', $args);
	return array_pop($args);
}


/**
 * @param $arrToCount
 *
 * @return int|mixed
 */
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
}

/**
 * @param $arr
 *
 * @return string|void
 */
function getArrayDebugOutput($arr)
{
	try
	{
		$dbg = encodeJSON($arr);
	}
	catch( \Exception $ex)
	{
		$dbg = var_dump($arr, true);
	}
	return $dbg;
}

/**
 * @param        $arrItem
 * @param        $key
 * @param bool   $fIsFirstItem
 * @param string $strDelimiter
 * @param string $strIntro
 * @param bool   $fIncludeKey
 *
 * @return string
 */
function getArrayItemDetailsAsString($arrItem, $key, $fIsFirstItem = true, $strDelimiter = "", $strIntro = "", $fIncludeKey = true)
{
    $strReturn = "";

    if (isset($arrItem[$key])) {
        $val = $arrItem[$key];
        if (is_string($val) && strlen($val) > 0) {
            $strVal = $val;
        } elseif (is_array($val) && !(is_array_multidimensional($val))) {
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

/**
 * @param $callback
 * @param $array
 *
 * @return array
 */
function array_mapk($callback, $array)
{
    $newArray = array();
    foreach ($array as $k => $v) {
        $newArray[$k] = call_user_func($callback, $k, $v);
    }
    unset($k);

    return $newArray;
}

/**
 * @param $input
 *
 * @return array
 */
function array_unique_multidimensional($input)
{
    $serialized = array_map('serialize', $input);
    $unique = array_unique($serialized);
    return array_intersect_key($input, $unique);
}

/**
 * @param        $arrDetails
 * @param string $strDelimiter
 * @param string $strIntro
 * @param bool   $fIncludeKey
 *
 * @return string
 */
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



/**
 * routine to return -1 if there is no match for strpos
 *
 *
 * @author William Jaspers, IV <wjaspers4@gmail.com>
 * @created 2009-02-27 17:00:00 +6:00:00 GMT
 * @access public
 * @ref http://www.php.net/manual/en/function.preg-match.php#89252
 *
 * @param $haystack
 * @param $needle
 * @return bool|int
 */
function inStr($haystack, $needle)
{
    $pos=strpos($haystack, $needle);
    if ($pos !== false)
    {
        return $pos;
    }
    else
    {
        return -1;
    }
}
/**
 *
 * in_string_array that takes an array of values to match against a string.
 * note the stupid argument order (to match strpos).  Returns
 * true if all needles are found in haystack or false if not.
 *
 * @param $haystack
 * @param $needle
 * @return bool|int
 */
function in_string_array($haystack, $needle)
{
    if(!is_array($needle))
    {
        if(!is_string($needle))
            $needle = strval($needle);

        $needle = array($needle);
    }

    foreach($needle as $what) {
        if(($pos = strpos($haystack, $what))===false) return false;
    }
    return true;
}


/**
 * @param array  $array
 * @param string $childPrefix
 * @param string $root
 * @param array  $result
 *
 * @return array
 */
function flattenWithKeys(array $array, $childPrefix = '.', $root = '', $result = array()) {
    //if(!is_array($array)) return $result;

    ### print_r(array(__LINE__, 'arr' => $array, 'prefix' => $childPrefix, 'root' => $root, 'result' => $result));

    foreach($array as $k => $v) {
        if(is_array($v) || is_object($v)) $result = flattenWithKeys( (array) $v, $childPrefix, $root . $k . $childPrefix, $result);
        else $result[ $root . $k ] = $v;
    }
    return $result;
}

/**
 * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
 * keys to arrays rather than overwriting the value in the first array with the duplicate
 * value in the second array, as array_merge does. I.e., with array_merge_recursive,
 * this happens (documented behavior):
 *
 * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('org value', 'new value'));
 *
 * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
 * Matching keys' values in the second array overwrite those in the first array, as is the
 * case with array_merge, i.e.:
 *
 * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
 *     => array('key' => array('new value'));
 *
 * Parameters are passed by reference, though only for performance reasons. They're not
 * altered by this function.
 *
 * @param array $array1
 * @param array $array2
 * @return array
 * @author Daniel <daniel (at) danielsmedegaardbuus (dot) dk>
 * @author Gabriel Sobrinho <gabriel (dot) sobrinho (at) gmail (dot) com>
 */
function array_merge_recursive_distinct ( array &$array1, array &$array2 )
{
    $merged = $array1;

    foreach ( $array2 as $key => &$value )
    {
        if ( is_array ( $value ) && isset ( $merged [$key] ) && is_array ( $merged [$key] ) )
        {
            $merged [$key] = array_merge_recursive_distinct ( $merged [$key], $value );
        }
        else
        {
            $merged [$key] = $value;
        }
    }

    return $merged;
}

/**
 * @param                              $arr
 * @param \Propel\Runtime\Map\TableMap $tablemap
 */
function updateColumnsForCSVFlatArray(&$arr, \Propel\Runtime\Map\TableMap $tablemap)
{
    foreach(array_keys($arr) as $key) {
	    if ($tablemap->hasColumnByPhpName($key)) {
		    $col = $tablemap->getColumnByPhpName($key);
		    if ($col->getType() == "ARRAY" && !empty($arr[$key])) {
			    $arr[$key] = join("|", flattenWithKeys(array($key => $arr[$key])));
		    } elseif ($col->getType() == "TIMESTAMP" && !empty($arr[$key])) {
			    $date = DateTime::createFromFormat("Y-m-d\TH:i:sT", $arr[$key]);
			    if (!empty($date)) {
				    $arr[$key] = $date->format("Y-m-d");
			    }
		    }
	    }
    }
}

/**
 * @param array $haystack
 * @param array $needle
 *
 * @return array
 */
function array_subset(array $haystack, array $needle)
{
	return array_intersect_key($haystack, array_flip($needle));
}

/**
 * @param array $haystack Source array to get data from
 * @param array $keys List of keys to return in the source data
 *
 * @return array
 */
function array_subset_keys(array $haystack, array $keys)
{
	$arrKeys = array_combine($keys, $keys);
	return array_intersect_key($haystack, $arrKeys);
}

/**
 * @param array $list
 * @param array $keysToReturn
 *
 * @return array
 */
function array_from_orm_object_list_by_array_keys(array $list, array $keysToReturn)
{
    $ret = array_map(function ($v) use ($keysToReturn) {
    	return array_subset($v->toArray(), $keysToReturn);}
    , $list);
    if(count($keysToReturn) == 1)
        $ret = array_column($ret, $keysToReturn[0], $keysToReturn[0]);
    return $ret;
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
        $count = substr_count_array($subject, $pattern);
        if (0 < $count) {
            $findings[$name] = $pattern;

            if ($boolMustMatchAllKeywords == true)
                return (sizeof($findings) === sizeof($patterns));

        } else {
            if (PREG_NO_ERROR !== ($code = preg_last_error() )) {
                $errors[$name] = $code;
            }
            else
            {
                // No match was found, so don't return it in the findings
             $findings[$name] = array();
            }
        }
    }
    return !(0 === sizeof($findings));
}

/**
 * @param $key
 * @param $arr
 *
 * @return null
 */
function getArrayItem($key, $arr)
{
    $ret = null;
    if(array_key_exists($key, $arr)) {
        if (is_numeric($arr[$key]))
            $ret = $arr[$key];
        else if (!empty($arr[$key]))
            $ret = $arr[$key];
    }
    return $ret;
}

/**
 * @param $destArray
 * @param $destKey
 * @param $sourceArray
 * @param $sourceKey
 */
function setArrayItem(&$destArray, $destKey, $sourceArray, $sourceKey)
{
    $ret = null;
    $val = getArrayItem($sourceKey, $sourceArray);
    if(is_numeric($val) || !empty($val))
    {
        $destArray[$destKey] = $val;
    }
}


/**
 * @return array
 */
function getEmptyJobListingRecord()
{
    return array(
        'JobSiteKey' => '',
        'JobSitePostId' => '',
        'Company' => '',
        'Title' => '',
        'Url' => '',
        'Location' => '',
        'Category' => '',
        'PostedAt' =>'',
        'EmploymentType' => '',
    );
}


/**
 * @param array $array
 *
 * @return array
 */
function array_copy(array $array ) {
    $result = array();
    foreach( $array as $key => $val ) {
        if( is_array( $val ) ) {
            $result[$key] = array_copy( $val );
        } elseif ( is_object( $val ) ) {
            $result[$key] = clone $val;
        } else {
            $result[$key] = $val;
        }
    }
    return $result;
}

/**
 * @param $haystack
 * @param $needle
 *
 * @return int
 */
function substr_count_array($haystack, $needle ) {
    $count = 0;
    if(!is_array($needle))
    {
        $needle = array($needle);
    }
    foreach ($needle as $substring) {
        $count += substr_count( $haystack, $substring);
    }
    return $count;
}


/**
 * @param $a
 *
 * @return bool
 */
function is_array_multidimensional($a)
{
    if(!is_array($a)) return false;
    foreach($a as $v) if(is_array($v)) return TRUE;
    return FALSE;
}

/**
 * @param $arr1
 * @param $arr2
 *
 * @return array
 * @throws \Exception
 */
function my_merge_add_new_keys($arr1, $arr2 )
{
    // check if inputs are really arrays
    if (!is_array($arr1) || !is_array($arr2)) {
        throw new \Exception("Argument is not an array (in function my_merge_add_new_keys.)");
    }
    $arr1Keys = array_keys($arr1);
    $arr2Keys = array_keys($arr2);
    $arrCombinedKeys = array_merge_recursive($arr1Keys, $arr2Keys);

    $arrNewBlankCombinedRecord = array_fill_keys($arrCombinedKeys, 'unknown');

    $arrMerged =  array_replace( $arrNewBlankCombinedRecord, $arr1 );
    $arrMerged =  array_replace( $arrMerged, $arr2 );

    return $arrMerged;
}


/**
 * Returns an array of all the keys of all values at every level
 * of a multi-dimensional array
 *
 * @param array $array
 *
 * @return array the set of all keys used for values at all levels in the array
 */
function array_keys_multi(array $array)
{
    $keys = array();

    foreach ($array as $key => $value) {
        $keys[] = $key;

        if (is_array($value)) {
            $keys = array_merge($keys, array_keys_multi($value));
        }
    }

    return $keys;
}


/**
 * @param $d
 *
 * @return array
 */
function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}



/**
 * @param $root
 * @param $keyPath
 * @param $value
 */
function setGlobalSetting($root, $keyPath, $value)
{
	doGlobalSettingExists($root);

	$dot = new \Adbar\Dot($GLOBALS[$root]);
	if($dot->has($keyPath))
		$dot->set($keyPath, $value);
	else
		$dot->add($keyPath, $value);
	$GLOBALS[$root] = $dot->all();

//	array_add_element($GLOBALS[$root], $keyPath, $value);
	ksort($GLOBALS[$root]);
}

/**
 * @param $root
 * @param $keyPath
 */
function removeGlobalSetting($root, $keyPath)
{
	doGlobalSettingExists($root);

	$dot = new \Adbar\Dot($GLOBALS[$root]);
	if($dot->has($keyPath))
		$dot->delete($keyPath);
	$GLOBALS[$root] = $dot->all();
}

/**
 * @param      $root
 * @param null $keyPath
 *
 * @return mixed
 */
function getGlobalSetting($root, $keyPath=null, $default=null)
{
	doGlobalSettingExists($root);

	// return the whole array if no keypath was given
	if(empty($keyPath))
		return $GLOBALS[$root];

	$dot = new \Adbar\Dot($GLOBALS[$root]);
	return $dot->get($keyPath, $default);
//	return array_get_element($keyPath, $GLOBALS[$root]);
}

/**
 * @param $root
 */
function doGlobalSettingExists($root)
{
	if(!array_key_exists($root, $GLOBALS))
		$GLOBALS[$root] = array();
}

const JOBSCOOPER_CONFIGSETTING_ROOT = "JSCOOP";
const JOBSCOOPER_CACHES_ROOT = "JSCOOP_CACHES";

/**
 * @param $keyPath
 * @param $value
 */
function setConfigurationSetting($keyPath, $value)
{
	setGlobalSetting($root=JOBSCOOPER_CONFIGSETTING_ROOT, $keyPath, $value);
}

/**
 * @param $keyPath
 *
 * @return mixed
 */
function getConfigurationSetting($keyPath, $default=null)
{
	return getGlobalSetting(JOBSCOOPER_CONFIGSETTING_ROOT, $keyPath, $default);
}

/**
 * @return mixed
 */
function getAllConfigurationSettings()
{
	doGlobalSettingExists(JOBSCOOPER_CONFIGSETTING_ROOT);

	return getGlobalSetting(JOBSCOOPER_CONFIGSETTING_ROOT);
}

/**
 * @param $cacheName
 * @param $keyPath
 * @param $value
 */
function setCacheItem($cacheName, $keyPath, $value)
{
	setGlobalSetting($root=JOBSCOOPER_CACHES_ROOT, $cacheName.".".$keyPath, $value);
}

/**
 * @param $cacheName
 * @param $keyPath
 *
 * @return mixed
 */
function getCacheItem($cacheName, $keyPath)
{
	return getGlobalSetting(JOBSCOOPER_CACHES_ROOT, $cacheName.".".$keyPath);
}

/**
 * @param $cacheName
 *
 * @return mixed
 */
function getCacheAsArray($cacheName)
{
	doGlobalSettingExists(JOBSCOOPER_CACHES_ROOT);

	return getGlobalSetting(JOBSCOOPER_CACHES_ROOT, $cacheName);
}

/**
 * @param $cacheName
 * @param $value
 */
function setAsCacheData($cacheName, $value)
{
	setGlobalSetting($root=JOBSCOOPER_CACHES_ROOT, $cacheName, $value);
}

/**
 * @param $cacheName
 */
function clearCache($cacheName)
{
	removeGlobalSetting($root=JOBSCOOPER_CACHES_ROOT, $cacheName);
}


