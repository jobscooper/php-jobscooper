<?php

namespace JobScooper\DataAccess;

use JobScooper\DataAccess\Base\UserSearchSiteRun as BaseUserSearchSiteRun;
use JobScooper\DataAccess\Map\UserSearchSiteRunTableMap;
use JobScooper\Utils\SimpleHTMLHelper;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Map\TableMap;

/**
 * Skeleton subclass for representing a row from the 'user_search_site_run' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class UserSearchSiteRun extends BaseUserSearchSiteRun
{
	public $nextResultsPageUrl = null;

	function failRunWithErrorMessage($err, SimpleHTMLHelper $objPageHtml=null)
	{
		$arrV = "";
		if(is_a($err, "\Exception") || is_subclass_of($err, "\Exception"))
		{
			$arrV = array(strval($err));
		}
		elseif(is_object($err))
		{
			$arrV = get_object_vars($err);
			$arrV["toString"] = strval($err);
		}
		elseif(is_string($err))
			$arrV = array($err);

		$this->setRunResultCode("failed");
		if(!empty($objPageHtml))
		{
			try
			{
				$filepath = $objPageHtml->debug_dump_to_file();
				$this->setRunErrorPageHtml($filepath);
			} catch (\Exception $ex)
			{
				LogWarning("Failed to save HTML for page that generated the error.");
			}
		}
		$this->setRunErrorDetails($arrV);
	}

	function setRunSucceeded()
	{
		return $this->setRunResultCode('successful');
	}

	function setRunResultCode($val)
	{
		switch ($val) {
			case "failed":
				break;

			case 'successful':
				$this->setRunErrorDetails(null);
				break;

			case "skipped":
				break;

			case "not-run":
			case "excluded":
			default:
				break;
		}

		$ret = parent::setRunResultCode($val);

		parent::setEndedAt(time());

		return $ret;

	}


	/**
	 * Derived method to catches calls to undefined methods.
	 *
	 *
	 * @param string $name
	 * @param mixed  $params
	 *
	 * @return array|string
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function __call($method, $params)
	{
		$user = null;
		$user_search = $this->getUserSearchPairFromUSSR();
		if(!empty($user_search))
			$user = $user_search->getUserFromUS();

		if(method_exists($this, $method)) {
			return call_user_func(
				array($this, $method),
				$params
			);
		}
		else {
			foreach(array($user_search, $user) as $relObject)
			{
				if(method_exists($relObject, $method)) {
					return call_user_func(
						array($relObject, $method),
						$params
					);
				}
			}
		}

		return false;
	}


	/**
	 * @param bool $includeGeolocation
	 *
	 * @return array
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function toFlatArray($includeGeolocation = false)
	{
		$location = array();
		$arrJobPosting = $this->toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false);
		updateColumnsForCSVFlatArray($arrJobPosting, new UserSearchSiteRunTableMap());
		if ($includeGeolocation === true) {
			$searchPair = $this->getUserSearchPairFromUSSR();
			if(!empty($searchPair) && !empty($searchPair->getGeoLocationId())) {
				$jobloc = $searchPair->getGeoLocationFromUS();
				if (!is_null($jobloc))
					$location = $jobloc->toFlatArrayForCSV();

				$arrItem = array_merge_recursive_distinct($arrJobPosting, $location);
			}
		} else
			$arrItem = $arrJobPosting;

		return $arrItem;
	}

	/**
	 * @return array
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function toLoggedContext()
	{
		return array_subset($this->toFlatArray(), array("UserSearchSiteRunKey", "GeoLocationId", "SearchStartUrl"));
	}


}
