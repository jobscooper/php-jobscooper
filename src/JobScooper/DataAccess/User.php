<?php

namespace JobScooper\DataAccess;

use JobScooper\Builders\SearchBuilder;
use JobScooper\DataAccess\Base\User as BaseUser;
use JobScooper\Manager\LocationManager;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use JobScooper\DataAccess\Map\UserSearchPairTableMap;
use Propel\Runtime\Propel;

/**
 * Skeleton subclass for representing a row from the 'user' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class User extends BaseUser
{
	private $_userSearchSiteRunsByJobSite = array();

	/**
	 * @return \JobScooper\DataAccess\User
	 */
	static function getCurrentUser()
    {
        return getConfigurationSetting('current_user');
    }

	/**
	 * @param \JobScooper\DataAccess\User $user
	 */
	static function setCurrentUser(User $user)
    {
        setConfigurationSetting('current_user', $user);
	    setConfigurationSetting("alerts.results.to", $user);
    }

	/**
	 * @param \Propel\Runtime\Connection\ConnectionInterface|null $con
	 *
	 * @return bool|void
	 * @throws \Exception
	 */
	public function postSave(ConnectionInterface $con = null)
    {
	    parent::postSave($con);

	    if (!empty($this->getSearchLocations()) && !empty($this->getSearchKeywords())) {
		    try {
			    $this->_updateUserSearchPairs();
		    } catch (PropelException $ex) {
			    handleException($ex);
		    }
	    }
    }

	/**
	 * @return \JobScooper\DataAccess\GeoLocation[]
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function getSearchGeoLocations()
    {
    	$loc = array();
	    $searchpairs = $this->getUserSearchPairs();
	    foreach ($searchpairs as $pair)
	    {
	    	$loc[] = $pair->getGeoLocationFromUS();
	    }
	    return array_unique($loc);
    }

	/**
	 * @param array  $arr
	 * @param string $keyType
	 */
	public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
	    if (array_key_exists("email", $arr)) {
		    $this->setEmailAddress($arr["email"]);
		    unset($arr['email']);
	    }

	    if (array_key_exists("display_name", $arr)) {
		    $this->setName($arr["display_name"]);
		    unset($arr['display_name']);
	    }

	    foreach ($arr as $k => $v)
	    {
			switch(strtolower($k))
			{
				case "email":
					$this->setEmailAddress($arr["email"]);
					unset($arr['email']);
					break;

				case "display_name":
					$this->setName($arr["display_name"]);
					unset($arr['display_name']);
					break;

				case "search_keywords":
				case "keywords":
					$this->setSearchKeywords($arr[strtolower($k)]);
					unset($arr[strtolower($k)]);
					break;

				case "search_locations":
					$this->setSearchLocations($arr["search_locations"]);
					unset($arr['search_locations']);
					break;

				case "inputfiles":
					$this->_parseConfigUserInputFiles($arr);
					unset($arr['inputfiles']);
					break;

				default:
					$arr[ucwords($k)] = $v;
					unset($arr[$k]);
			}

	    }

	    parent::fromArray($arr, $keyType);
    }

	/**
	 * @throws \Exception
	 * @return null
	 */
    private function _parseConfigUserInputFiles($arrUserFacts)
    {
	    //
	    // Validate each of the inputfiles that the user passed
	    // and configure all searches
	    //
	    $verifiedInputFiles = array();
	    if (array_key_exists('inputfiles', $arrUserFacts) && !empty($arrUserFacts['inputfiles']) && is_array($arrUserFacts['inputfiles'])) {
		    $inputfiles = $arrUserFacts['inputfiles'];
		    foreach ($inputfiles as $cfgvalue)
		    {
			    $split= preg_split("/;/", $cfgvalue);
			    $type = $split[0];
			    $path = $split[1];

			    $tempFileDetails = null;
			    $fileinfo = new \SplFileInfo($path);
			    if($fileinfo->getRealPath() !== false){
				    $tempFileDetails = parsePathDetailsFromString($fileinfo->getRealPath(), C__FILEPATH_FILE_MUST_EXIST);
			    }

			    if(empty($tempFileDetails) || $tempFileDetails->isFile() !== true) {
				    throw new \Exception("Specified input file '" . $path . "' was not found.  Aborting.");
			    }

			    $key = $fileinfo->getBasename(".csv");
			    $verifiedInputFiles[$type] = array();
			    $verifiedInputFiles[$type][$key] = $tempFileDetails->getPathname();
		    }

		    $this->setInputFiles($verifiedInputFiles);
	    }

    }

	/**
	 * @param array[]|null $v
	 * @throws \Exception
	 */
	public function setInputFiles($v)
	{
		if(!empty($v))
			$this->setInputFilesJson(encodeJSON($v));
	}

	/**
	 * @param string $type Return the subset of input files of a specific type only.
	 *
	 * @return array[]|null
	 * @throws \Exception
	 */
	public function getInputFiles($type=null)
	{
		$files = null;
		$v = $this->getInputFilesJson();
		if(!empty($v) && is_string($v))
			$files = decodeJSON($v);

		if(!empty($type) && !empty($files))
		{
			if(array_key_exists($type, $files))
				$files = $files[$type];
			else
				$files = array();
		}

		return $files;
	}

	/**
	 * @return null
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _updateUserSearchPairs()
	{
		$userSearchPairs = array();
		$slug = $this->getUserSlug();

		$searchLocations = $this->getSearchLocations();
		if (empty($searchLocations)) {
			LogWarning("No search locations have been set for {$slug}. Unable to create any search pairings for user.");
			return ;
		}

		$searchKeywords = $this->getSearchKeywords();
		if (empty($searchKeywords)) {
			LogWarning("No user search keywords have been configuredfor {$slug}. Unable to create any search pairings for user");
			return ;
		}

		$locmgr = LocationManager::getLocationManager();
		if(empty($locmgr)) {
			LocationManager::create();
			$locmgr = LocationManager::getLocationManager();
		}
		$searchGeoLocIds = array();

		foreach ($searchLocations as $lockey => $searchLoc)
		{
			$location = $locmgr->getAddress($searchLoc);
			if (!empty($location)) {
				LogMessage("Updating/adding user search keyword/location pairings for location " . $location->getDisplayName() . " and user {$slug}'s keywords");
				$locId = $location->getGeoLocationId();
				$searchGeoLocIds[$locId] = $locId;

				foreach ($searchKeywords as $kwd) {
					$user_search = UserSearchPairQuery::create()
						->filterByUserId($this->getUserId())
						->filterByUserKeyword($kwd)
						->filterByGeoLocationId($locId)
						->findOneOrCreate();

					$user_search->setUserId($this->getUserId());
					$user_search->setUserKeyword($kwd);
					$user_search->setIsActive(true);
					$user_search->setGeoLocationId($locId);
					$user_search->save();

					$userSearchPairs[$user_search->getUserSearchPairId()] = $user_search;
				}
			}
			else
				LogError("Could not create user searches for the '{$searchLoc}'' search location.");
		}

		try {
			$query = UserSearchPairQuery::create();

			$locIdColumnName = $query->getAliasedColName(UserSearchPairTableMap::COL_GEOLOCATION_ID);
			$kwdColumnName = $query->getAliasedColName(UserSearchPairTableMap::COL_USER_KEYWORD);
			$con = Propel::getWriteConnection(UserSearchPairTableMap::DATABASE_NAME);

			$oldPairUpdate  = $query->filterByUserSearchPairId(array_keys($userSearchPairs), Criteria::NOT_IN)
				->filterByUserId($this->getUserId())
				->filterByIsActive(true, Criteria::EQUAL)
				->addCond('condUserKwds', $kwdColumnName, array_values($searchKeywords), Criteria::NOT_IN)
				->addCond('condUserLocs', $locIdColumnName, array_values($searchGeoLocIds), Criteria::NOT_IN)
				->combine(array('condUserKwds', 'condUserLocs'), Criteria::LOGICAL_OR)
				->update(array("IsActive" => false), $con);

			LogMessage("Marked {$oldPairUpdate} previous user search pairs as inactive.");

		} catch (PropelException $ex) {
			handleException($ex, null, false);
		} catch (\Exception $ex) {
			handleException($ex, null, false);
		}

		if (empty($userSearchPairs)) {
			LogMessage("Could not create user searches for the given user keyword sets and geolocations.  Cannot continue.");
			return ;
		}
		LogMessage("Updated or created " . count($userSearchPairs) . " user search pairs for {$slug}.");

	}

	/**
	 * @return array|\JobScooper\DataAccess\UserSearchPair[]|\Propel\Runtime\Collection\ObjectCollection
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function getActiveUserSearchPairs()
	{
		$searchPairs = $this->getUserSearchPairs();
		if(!empty($searchPairs))
		{
			$ret = array_filter($searchPairs->getData(), function (UserSearchPair $v) {
				return $v->isActive();
			});
		}

		return $ret;
	}

	/**
	 * @return array
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function createUserSearchSiteRuns()
	{
		startLogSection("Initializing search runs for user " . $this->getUserSlug());
		$srchmgr = new SearchBuilder();
		$this->_userSearchSiteRunsByJobSite = $srchmgr->createSearchesForUser($this);
		endLogSection(" User search site runs initialization.");

		return $this->_userSearchSiteRunsByJobSite;
	}

	/**
	 * @return array
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function getUserSearchSiteRuns()
	{
		if(empty($this->_userSearchSiteRunsByJobSite))
			$this->createUserSearchSiteRuns();
		return $this->_userSearchSiteRunsByJobSite;
	}
}
