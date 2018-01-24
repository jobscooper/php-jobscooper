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

namespace JobScooper\BasePlugin\Classes;

use DiDom\Query;
use \Exception;
use JobScooper\Utils\SimpleHTMLHelper;

/**
 * Class SimplePlugin
 * @package JobScooper\BasePlugin\Classes
 */
class SimplePlugin extends BaseJobsSite
{
    protected $JobSiteName = '';
    protected $JobPostingBaseUrl = '';
    protected $JobListingsPerPage = 20;
    protected $childSiteURLBase = '';
    protected $childSiteListingPage = '';
    protected $additionalLoadDelaySeconds = 2;
    protected $nextPageScript = null;
    protected $arrListingTagSetup = array();
    protected $arrBaseListingTagSetup = array();

	/**
	 * SimplePlugin constructor.
	 * @throws \Exception
	 */
	function __construct()
    {
        if (empty($this->arrListingTagSetup))
            $this->arrListingTagSetup = array();

        if(!empty($this->arrBaseListingTagSetup))
            $this->arrListingTagSetup = array_replace($this->arrBaseListingTagSetup, $this->arrListingTagSetup);

        if (strlen($this->JobPostingBaseUrl) == 0)
            $this->JobPostingBaseUrl = $this->childSiteURLBase;
        if (strlen($this->SearchUrlFormat) == 0)
            $this->SearchUrlFormat = $this->childSiteURLBase;


        if (array_key_exists('NextButton', $this->arrListingTagSetup) && is_array($this->arrListingTagSetup['NextButton']) && count($this->arrListingTagSetup['NextButton'])) {
            $this->selectorMoreListings = $this->getTagSelector($this->arrListingTagSetup['NextButton']);
            $this->PaginationType = C__PAGINATION_PAGE_VIA_NEXTBUTTON;
        } elseif (array_key_exists('LoadMoreControl', $this->arrListingTagSetup) && is_array($this->arrListingTagSetup['LoadMoreControl']) && count($this->arrListingTagSetup['LoadMoreControl'])) {
            $this->PaginationType = C__PAGINATION_INFSCROLLPAGE_VIALOADMORE;
            $this->selectorMoreListings = $this->getTagSelector($this->arrListingTagSetup['LoadMoreControl']);
        }

        if (!array_key_exists('TotalPostCount', $this->arrListingTagSetup) &&  !in_array(C__JOB_ITEMCOUNT_NOTAPPLICABLE__, $this->additionalBitFlags))
        {
            $this->additionalBitFlags[]  = C__JOB_ITEMCOUNT_NOTAPPLICABLE__;
        }

        if (!array_key_exists('TotalResultPageCount', $this->arrListingTagSetup) &&  !in_array(C__JOB_PAGECOUNT_NOTAPPLICABLE__, $this->additionalBitFlags))
        {
            $this->additionalBitFlags[]  = C__JOB_PAGECOUNT_NOTAPPLICABLE__;
        }

        parent::__construct();

        foreach(array_keys($this->arrListingTagSetup) as $k)
        {
            if(is_array($this->arrListingTagSetup[$k])) {
                if (array_key_exists('type', $this->arrListingTagSetup[$k])) {
                    switch ($this->arrListingTagSetup[$k]['type']) {
                        case "CSS":
                            if (array_key_exists('return_attribute', $this->arrListingTagSetup[$k]) &&
                                in_array(strtoupper($this->arrListingTagSetup[$k]['return_attribute']), array("NODE", "COLLECTION")) === true)
                                $rank = 10;
                            else
                                $rank = 50;
                            break;

                        case "STATIC":
                            $rank = 100;
                            break;


                        case "REGEX":
                            $rank = 1000;
                            break;

                        default:
                            $rank = 999999;
                            break;
                    }
                } else
                    $rank = 999999;
                $this->arrListingTagSetup[$k]['rank'] = $rank;
            }
        }

        uasort($this->arrListingTagSetup, function($a, $b) {
            if(!is_array($a) || !is_array($b))
                return 0;

            if ($a['rank'] == $b['rank']) {
                return 0;
            }
            return ($a['rank'] < $b['rank']) ? -1 : 1;

        });


    }

	/**
	 * @param $var
	 *
	 * @return int|null
	 * @throws \Exception
	 */
	function matchesNoResultsPattern($var)
    {
        $val = $var[0];
        $match_value = $var[1];

        if(is_null($match_value))
            throw new \Exception("Plugin " . $this->JobSiteName  . " definition missing pattern match value for matchesNoResultsPattern callback.");
        return noJobStringMatch($val, $match_value);
    }

    /**
     * parseTotalResultsCount
     *
     * If the site does not show the total number of results
     * then set the plugin flag to C__JOB_PAGECOUNT_NOTAPPLICABLE__
     * in the Constants.php file and just comment out this function.
     *
     * parseTotalResultsCount returns the total number of listings that
     * the search returned by parsing the value from the returned HTML
     * *
     * @param $objSimpHTML
     * @return string|null
     * @throws \Exception
     */
    function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        if (array_key_exists('NoPostsFound', $this->arrListingTagSetup) && !is_null($this->arrListingTagSetup['NoPostsFound']) && count($this->arrListingTagSetup['NoPostsFound']) > 0) {
            try
            {
                $noResultsVal = $this->_getTagValueFromPage_($objSimpHTML, 'NoPostsFound');
                if (!is_null($noResultsVal)) {
                    LogMessage("Search returned " . $noResultsVal . " and matched expected 'No results' tag for " . $this->JobSiteName);
                    return $noResultsVal;
                }
            } catch (\Exception $ex) {
                LogWarning("Warning: Did not find matched expected 'No results' tag for " . $this->JobSiteName . ".  Error:" . $ex->getMessage());
            }
        }

        $retJobCount = C__TOTAL_ITEMS_UNKNOWN__;
        if (array_key_exists('TotalPostCount', $this->arrListingTagSetup) && is_array($this->arrListingTagSetup['TotalPostCount']) && count($this->arrListingTagSetup['TotalPostCount']) > 0) {
            $retJobCount = $this->_getTagValueFromPage_($objSimpHTML, 'TotalPostCount');
            if (is_null($retJobCount) || (is_string($retJobCount) && strlen($retJobCount) == 0))
                throw new \Exception("Unable to determine number of listings for the defined tag:  " . getArrayValuesAsString($this->arrListingTagSetup['TotalPostCount']));
        } else if (array_key_exists('TotalResultPageCount', $this->arrListingTagSetup) && is_array($this->arrListingTagSetup['TotalResultPageCount']) && count($this->arrListingTagSetup['TotalResultPageCount']) > 0) {
            $retPageCount = $this->_getTagValueFromPage_($objSimpHTML, 'TotalResultPageCount');
            if (is_null($retJobCount) || (is_string($retJobCount) && strlen($retJobCount) == 0))
                throw new \Exception("Unable to determine number of listings for the defined tag:  " . getArrayValuesAsString($this->arrListingTagSetup['TotalResultPageCount']));

            $retJobCount = $retPageCount * $this->JobListingsPerPage;
        } elseif ($this->isBitFlagSet(C__JOB_ITEMCOUNT_NOTAPPLICABLE__))
            $retJobCount = C__TOTAL_ITEMS_UNKNOWN__;
        else
            throw new \Exception("Error: plugin is missing either C__JOB_ITEMCOUNT_NOTAPPLICABLE__ flag or an implementation of parseTotalResultsCount for that job site. Cannot complete search.");

        return $retJobCount;

    }

	/**
	 * @param $arrTag
	 *
	 * @return null|string
	 * @throws \Exception
	 */
	protected function getTagSelector($arrTag)
    {
        if ($arrTag == null) return null;

        $arrKeys = array_keys($arrTag);
        if (!(in_array("selector", $arrKeys) || in_array("tag", $arrKeys))) {
            throw (new \Exception("Invalid tag configuration " . getArrayValuesAsString($arrTag)));
        }
        $strMatch = "";

        if (array_key_exists("selector", $arrTag)) {
            $strMatch = $strMatch . $arrTag['selector'];
        } elseif(array_key_exists("tag", $arrTag)) {
            if (strlen($strMatch) > 0) $strMatch = $strMatch . ' ';
            {
                $strMatch = $strMatch . $arrTag['tag'];
                if (array_key_exists('attribute', $arrTag) && strlen($arrTag['attribute']) > 0) {
                    $strMatch = $strMatch . '[' . $arrTag['attribute'];
                    if (array_key_exists('attribute_value', $arrTag) && strlen($arrTag['attribute_value']) > 0) {
                        $strMatch = $strMatch . '="' . $arrTag['attribute_value'] . '"';
                    }
                    $strMatch = $strMatch . ']';
                }
            }
        }

        return $strMatch;
    }

	/**
	 * @param      $node
	 * @param      $tagKey
	 * @param null $item
	 *
	 * @return mixed|null|string
	 * @throws \Exception
	 */
	protected function _getTagValueFromPage_($node, $tagKey, $item = null)
    {
        if (!(array_key_exists($tagKey, $this->arrListingTagSetup) && count($this->arrListingTagSetup[$tagKey]) >= 1))
            return null;

        $arrTag = $this->arrListingTagSetup[$tagKey];

        if(!is_array($arrTag) || count($arrTag) == 0 )
            return null;

        if (!array_key_exists("type", $arrTag) || empty($arrTag['type'])) {
            $arrTag['type'] = "CSS";
        }

        switch(strtoupper($arrTag['type']))
        {
            case 'CSS':
                return $this->_getTagMatchValueViaFind_($node, $arrTag, Query::TYPE_CSS);
                break;

	        case 'XPATH':
		        return $this->_getTagMatchValueViaFind_($node, $arrTag, Query::TYPE_XPATH);
		        break;

	        case 'STATIC':
		        return $this->_getTagMatchValueStatic_($arrTag);
		        break;

	        case 'SOURCEFIELD':
                return $this->_getTagMatchValueSourceField_($arrTag, $item);
                break;

            case 'MICRODATA':
                // Do nothing; we've already parsed the microdata
                break;

            default:
                throw new \Exception("Unknown field definition type of " . $arrTag['type']);
        }

    }

	/**
	 * @param $arrTag
	 *
	 * @return null
	 */
	protected function _getTagMatchValueStatic_($arrTag)
    {
        $ret = null;
        if (array_key_exists("value", $arrTag) && !is_null($arrTag['value'])) {
            $value  = $arrTag['value'];

            if(is_null($value) || strlen($value) == 0)
                $ret = null;
            else
                $ret = $value;
        }

        return $ret;
    }

	/**
	 * @param $arrTag
	 * @param $item
	 *
	 * @return null
	 */
	protected function _getTagMatchValueSourceField_($arrTag, $item)
    {
        $ret = null;
        if (array_key_exists("return_value_regex", $arrTag) && !empty($arrTag['return_value_regex']))
            $arrTag['pattern'] = $arrTag['return_value_regex'];

        if (array_key_exists("pattern", $arrTag) && !empty($arrTag['pattern'])) {
            $pattern = $arrTag['pattern'];
            $value = "";

            if (array_key_exists("field", $arrTag) && !empty($arrTag['field'])) {
                if (array_key_exists($arrTag['field'], $item)) {
                    $value = $item[$arrTag['field']];
                }
            }

            if(empty($value))
                $ret = null;
            else
            {
                $newPattern = str_replace("\\\\", "\\", $pattern);

                if (preg_match($newPattern, $value, $matches) > 0) {
                	array_shift($matches);
	                $ret = $this->_getReturnValueByIndex($matches, $arrTag['index']);
                }
            }
        }

        return $ret;
    }

	/**
	 * @param $arr
	 * @param $indexValue
	 *
	 * @return null
	 */
	private function _getReturnValueByIndex($arr, $indexValue)
	{
		$index = $this->translateTagIndexValue($arr, $indexValue);
		if(is_null($index))
			return null;

		return $arr[$index];
	}

	/**
	 * @param $arr
	 * @param $indexValue
	 *
	 * @return null
	 */
	function translateTagIndexValue($arr, $indexValue)
	{
		switch($indexValue)
		{
			case null:
				$ret = 0;
				break;

			case "LAST":
				$ret = count($arr) - 1;
				break;

			case $indexValue < 0:
				$ret = count($arr) - 1 - abs($indexValue);
				break;

			case $indexValue > count($arr):
				$strError = sprintf("%s plugin failed to find index #%d in the %d matching nodes. ", $this->JobSiteName, $indexValue, count($arr));
				LogWarning($strError);
				$ret = null;
				break;

			default:
				$ret = $indexValue;
				break;
		}

		return $ret;

	}


	/**
	 * @param        $node
	 * @param        $arrTag
	 * @param string $searchType
	 *
	 * @return mixed|null|string
	 * @throws \Exception
	 */
	protected function _getTagMatchValueViaFind_($node, $arrTag, $searchType=Query::TYPE_CSS)
    {
        $ret = null;
        $propertyRegEx = null;

        if (!empty($arrTag['return_attribute']))
            $returnAttribute = $arrTag['return_attribute'];
        else
            $returnAttribute = 'text';

	    if (array_key_exists("return_value_regex", $arrTag)) {
		    $propertyRegEx = $arrTag['return_value_regex'];
	    }
	    elseif (array_key_exists("pattern", $arrTag)) {
		    $propertyRegEx = $arrTag['pattern'];
	    }

	    $strMatch = $this->getTagSelector($arrTag);
        if (is_null($strMatch)) {
            return $ret;
        }
        elseif(strlen($strMatch) > 0)
        {
            $nodeMatches = $node->find($strMatch, $searchType);

            if ($returnAttribute === "collection") {
                $ret = $nodeMatches;
                // do nothing.  We already have the node set correctly
            } elseif (!empty($nodeMatches) && array_key_exists('index', $arrTag) && is_array($nodeMatches))
            {
	            $index = intval($arrTag['index']);
                if ( $index > count($nodeMatches) - 1) {
		            LogWarning("Tag specified index {$index} but only " . count($nodeMatches) . " were matched.  Defaulting to first node.");
		            $index = 0;
	            } elseif(empty($index) && $index !== 0)
                {
	                LogWarning("Tag specified index value was invalid {$arrTag['index']}.  Defaulting to first node.");
	                $index = 0;
                }
                $ret = $this->_getReturnValueByIndex($nodeMatches, $index);
            } elseif (!empty($nodeMatches) && is_array($nodeMatches)) {
                if (count($nodeMatches) > 1) {
                    $strError = sprintf("Warning:  %s plugin matched %d nodes to selector '%s' but did not specify an index.  Assuming first node.  Tag = %s", $this->JobSiteName, count($nodeMatches), $strMatch, getArrayDebugOutput($arrTag));
                    LogWarning($strError);
                }
                $ret = $nodeMatches[0];
            }

            if (!empty($ret) && !in_array($returnAttribute, ['collection', 'node'])) {
                $ret = $ret->$returnAttribute;

                if (!is_null($propertyRegEx) && is_string($ret) && strlen($ret) > 0) {
                    $match = array();
	                $propertyRegEx = str_replace("\\\\", "\\", $propertyRegEx);
	                $retTemp = str_replace("\n", " ", $ret);
                    if (preg_match($propertyRegEx, $retTemp, $match) !== false && count($match) >= 1)
                    {
                        $ret = $match[1];
                    }
                    else
                    	LogDebug(sprintf("%s plugin failed to find match for regex '%s' for tag '%s' with value '%s' as expected.", $this->JobSiteName, $propertyRegEx, getArrayDebugOutput($arrTag), $ret));
                }
            }
        }
        else
        {
            $ret = $strMatch;
        }

        if (array_key_exists("return_value_callback", $arrTag) && (strlen($arrTag['return_value_callback']) > 0)) {
            $callback = get_class($this) . "::" . $arrTag['return_value_callback'];
            if (!method_exists($this, $arrTag['return_value_callback'])) {
                $strError = sprintf("%s plugin failed could not call the tag callback method '%s' for attribute name '%s'.", $this->JobSiteName, $callback, $returnAttribute);
                LogError($strError);
                throw new \Exception($strError);
            }

            if (array_key_exists("callback_parameter", $arrTag) && (strlen($arrTag['callback_parameter']) > 0))
                $ret = call_user_func($callback, array($ret, $arrTag['callback_parameter']));
            else
                $ret = call_user_func($callback, $ret);
        }

        return $ret;
    }

	/**
	 * /**
	 * parseJobsListForPage
	 *
	 * This does the heavy lifting of parsing each job record from the
	 * page's HTML it was passed.
	 * *
	 */
	function getJobFactsFromMicrodata($objSimpHTML, $item=array())
	{

		if(empty($objSimpHTML) || !method_exists($objSimpHTML, "find"))
			return $item;

		$itempropNodes = $objSimpHTML->find("*[itemprop]");
		if(!empty($itempropNodes) && is_array($itempropNodes)) {
			foreach ($itempropNodes as $node) {
				$attribs = $node->attributes();

				if (!empty($attribs)) {
					$itemPropKind = strtolower($attribs['itemprop']);
					$eachProp = preg_split("/\s+/", $itemPropKind);
					foreach ($eachProp as $propKind)
					{
						switch ($propKind) {
							case "itemlistelement":
								if (array_key_exists("id", $attribs))
									$item['JobSitePostId'] = $attribs['id'];
								if (array_key_exists("data-index", $attribs))
									$item['JobSitePostId'] = empty($item['JobSitePostId']) ? $attribs['data-index'] : $item['JobSitePostId'] . "-" . $attribs['data-index'];
								break;

							case "name":
							case "title":
								$item['Title'] = combineTextAllChildren($node);
								break;

							case "identifier":
								$item['Title'] = combineTextAllChildren($node);
								break;

							case "url":
								$item['Url'] = $attribs['href'];
								break;

							case "joblocation":
							case "address":
							case "postaladdress":
								$item['Location'] = combineTextAllChildren($node);
								break;

							case "employmenttype":
								$item['EmploymentType'] = combineTextAllChildren($node);
								break;

							case "dateposted":
								$item['PostedAt'] = combineTextAllChildren($node);
								break;

							case "industry":
							case "occupationalcategory":
								$item['Category'] = combineTextAllChildren($node);
								break;

							case "hiringorganization":
								$item['Company'] = combineTextAllChildren($node);
								break;


						}
					}
				}
			}
		}
		return $item;
	}


	/**
     * /**
     * parseJobsListForPage
     *
     * This does the heavy lifting of parsing each job record from the
     * page's HTML it was passed.
     *
     * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
     *
     * @return array|null
     * @throws \Exception
     */
    function parseJobsListForPage(SimpleHTMLHelper $objSimpHTML)
    {
        $ret = null;
        $item = null;

        assert(array_key_exists('JobPostItem', $this->arrListingTagSetup));

        if(array_key_exists('return_attribute', $this->arrListingTagSetup['JobPostItem']) === false)
        {
            $this->arrListingTagSetup['JobPostItem']['return_attribute'] = 'collection';
        }


        // first looked for the detail view layout and parse that
        $strNodeMatch = $this->getTagSelector($this->arrListingTagSetup['JobPostItem']);

        LogMessage($this->JobSiteName . " finding nodes matching: " . $strNodeMatch);
        $nodesJobRows = $this->_getTagValueFromPage_($objSimpHTML, 'JobPostItem', 'collection');

        if ($nodesJobRows !== false && !is_null($nodesJobRows) && is_array($nodesJobRows) && count($nodesJobRows) > 0) {
            foreach ($nodesJobRows as $node) {
                //
                // get a new record with all columns set to null
                //
                $item = getEmptyJobListingRecord();

                $item = $this->getJobFactsFromMicrodata($node, $item);

                foreach(array_keys($this->arrListingTagSetup) as $itemKey)
                {
                    if(in_array($itemKey, ["JobPostItem", "NextButton", "TotalResultPageCount", "TotalPostCount", "NoPostsFound"]))
                        continue;

                    $newVal = $this->_getTagValueFromPage_($node, $itemKey, $item);
                    if(!empty($newVal))
                        $item[$itemKey] = $newVal;
                }

                if (empty($item['Title']) || strcasecmp($item['Title'], "title") == 0)
                    continue;

                if(empty($item['JobSiteKey']))
                    $item['JobSiteKey'] = $this->JobSiteName;

                if (array_key_exists('regex_link_job_id', $this->arrListingTagSetup) && count($this->arrListingTagSetup['regex_link_job_id']) >= 1)
                    $this->regex_link_job_id = $this->arrListingTagSetup['regex_link_job_id'];

                $ret[] = $item;

            }
        }
        else
        {
            $objSimpHTML->debug_dump_to_file();

            throw new \Exception("Could not find matching job elements in HTML for " . $strNodeMatch . " in plugin " . $this->JobSiteName);
        }

        LogMessage($this->JobSiteName . " returned " . countAssociativeArrayValues($ret) . " jobs from page.");

        return $ret;
    }

}
