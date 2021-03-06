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

namespace JobScooper\DataAccess;

use JobScooper\DataAccess\Map\JobPostingTableMap;
use JobScooper\Manager\LocationManager;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Map\TableMap;
use Exception;
use Propel\Runtime\Exception\PropelException;
Use Propel\Runtime\Connection\ConnectionInterface;


class JobPosting extends \JobScooper\DataAccess\Base\JobPosting implements \ArrayAccess
{

	/**
	 * @param \Propel\Runtime\Connection\ConnectionInterface|null $con
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function save(ConnectionInterface $con = null)
	{
		try {
			return parent::save($con); // TODO: Change the autogenerated stub
		} catch (PropelException $ex) {
			handleException($ex, "Failed to save JobPosting: %s", true);
		}
	}

	/**
	 * @param bool $includeGeolocation
	 *
	 * @return array
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function toFlatArrayForCSV($includeGeolocation = false)
	{
		$location = array();
		$arrJobPosting = $this->toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false);
		updateColumnsForCSVFlatArray($arrJobPosting, new JobPostingTableMap());
		if ($includeGeolocation === true) {
			$jobloc = $this->getGeoLocationFromJP();
			if (!is_null($jobloc))
				$location = $jobloc->toFlatArrayForCSV();

			$arrItem = array_merge_recursive_distinct($arrJobPosting, $location);

		} else
			$arrItem = $arrJobPosting;

		return $arrItem;
	}

	/**
	 * @return int
	 */
	public function checkAndMarkDuplicatePosting()
	{
		if (is_null($this->getDuplicatesJobPostingId())) {
			$this->updateAutoColumns();
			$sinceWhen = date_add(new \DateTime(), date_interval_create_from_date_string('7 days ago'));

			$masterPost = JobPostingQuery::create()
				->filterByDuplicatesJobPostingId(null)
				->filterByKeyCompanyAndTitle($this->getKeyCompanyAndTitle())
				->filterByPostedAt(array('max' => $sinceWhen))
				->filterByJobPostingId($this->getJobPostingId(), Criteria::NOT_EQUAL)
				->orderByPostedAt('asc')
				->findOne();

			if (!is_null($masterPost) && $masterPost !== false) {
				$this->setDuplicatesJobPostingId($masterPost->getJobPostingId());

				return $masterPost->getJobPostingId();
			}
		}
	}

	/**
	 *
	 */
	protected function updateAutoColumns()
	{
		$this->setKeyCompanyAndTitle(cleanupSlugPart($this->getCompany() . $this->getTitle()));
	}

	/**
	 * @param $method
	 * @param $v
	 *
	 * @return mixed
	 */
	public function setAutoColumnRelatedProperty($method, $v)
	{
		if (is_null($v) || strlen($v) <= 0)
			$v = "_VALUENOTSET_";
		$ret = parent::$method($v);
		$this->updateAutoColumns();

		return $ret;
	}

	/**
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _setDenormalizedLocationDisplayValue_()
	{
		$val = "";
		$location = $this->getGeoLocationFromJP();
		if (!is_null($location)) {
			$val = $location->getDisplayName();
		} else {
			$val = $this->getLocation();
		}

		$val = $this->_cleanupTextValue($val);
		if (is_null($val) || strlen($val) == 0)
			$val = $this->getLocation();

		$this->setLocationDisplayValue($val);
	}

	/**
	 *
	 */
	public function normalizeJobRecord()
	{
		$this->updateAutoColumns();
		$this->setJobSiteKey(cleanupSlugPart($this->getJobSiteKey()));
	}

	/**
	 * @param \Propel\Runtime\Connection\ConnectionInterface|null $con
	 *
	 * @return bool
	 */
	public function preSave(\Propel\Runtime\Connection\ConnectionInterface $con = null)
	{
		$this->normalizeJobRecord();

		if (is_callable('parent::preSave')) {
			return parent::preSave($con);
		}

		return true;
	}

	/**
	 * @param     $v
	 * @param int $maxLength
	 *
	 * @return bool|null|string|string[]
	 */
	private function _cleanupTextValue($v, $maxLength=255)
	{
		$ret = cleanupTextValue($v);
		if(!empty($ret) && is_string($ret))
			$ret = substr($ret, 0, min($maxLength-1, strlen($ret)));

		return $ret;
	}

	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 * @throws \Exception
	 */
	public function setTitle($v)
	{
		// Removes " NEW!", etc from the job title.  ZipRecruiter tends to occasionally
		// have that appended which then fails de-duplication. (Fixes issue #45) Glassdoor has "- easy apply" as well.
		$v = str_ireplace(" NEW!", "", $v);
		$v = str_ireplace("- new", "", $v);
		$v = str_ireplace("- easy apply", "", $v);
		$v = $this->_cleanupTextValue($v);

		if (strlen($v) == 0)
			throw new \Exception($this->getJobSiteKey() . " posting's title string is empty.");

		parent::setTitle($v);
	}


	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 */
	public function setLocation($v)
	{
		$oldVal = $this->getLocation();

		//
		// Restructure locations like "US-VA-Richmond" to be "Richmond, VA"
		//
		$arrMatches = preg_split("/[\-]/", $v);
		if (strlen($v) == 3) {
			$v = sprintf("%s %s %s", $arrMatches[2], $arrMatches[1], $arrMatches[0]);
		}
//        $arrMatches = array();
//        $matched = preg_match('/.*(\w{2})\s*[\-,]\s*.*(\w{2})\s*[\-,]s*([\w]+)/', $v, $arrMatches);
//        if ($matched !== false && count($arrMatches) == 4) {
//            $v = $arrMatches[3] . ", " . $arrMatches[2];
//        }

		$v = $this->_cleanupTextValue($v);

		parent::setLocation(trim($v));

		// clear any previous job location ID when we set a new location string
//        if(is_null($oldVal)) strcmp($oldVal, $this->getLocation() != 0))
//        {
		$this->_updateAutoLocationColumns();
//        }
	}

	/**
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _updateAutoLocationColumns()
	{
		$loc_str = $this->getLocation();
		if (is_null($loc_str) || strlen($loc_str) == 0) {
			// clear any previous job location ID when we set a new location string
			$this->setGeoLocationFromJP(null);
			$this->setGeoLocationId(null);
			$this->setLocationDisplayValue(null);

			return;
		}

		try {
			$locmgr = LocationManager::getLocationManager();

			$location = $locmgr->getAddress($loc_str);
			if (!is_null($location)) {
				$this->setGeoLocationFromJP($location);
				$this->_setDenormalizedLocationDisplayValue_();
			}
		} catch (Exception $ex) {
			LogWarning("Failed to lookup and set a geolocation for job posting " . $this->getKeySiteAndPostId() . ".  Error:  " . $ex->getMessage());
		}
	}

	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 */
	public function setCompany($v)
	{
		$v = $this->_cleanupTextValue($v, $maxLength=100);

		if (is_null($v) || strlen($v) == 0) {
			$v = $this->getJobSiteKey();
		} else {
			$v = strip_punctuation($v);

			// Remove common company name extensions like "Corporation" or "Inc." so we have
			// a higher match likelihood
			$v = preg_replace(array('/\s[Cc]orporat[e|ion]/', '/\s[Cc]orp\W{0,1}/', '/\.com/', '/\W{0,}\s[iI]nc/', '/\W{0,}\s[lL][lL][cC]/', '/\W{0,}\s[lL][tT][dD]/'), "", $v);

			switch (strScrub($v)) {
				case "amazon":
				case "amazon com":
				case "a2z":
				case "lab 126":
				case "amazon Web Services":
				case "amazon fulfillment services":
				case "amazonwebservices":
				case "amazon (seattle)":
					$v = "Amazon";
					break;

				case "market leader":
				case "market leader inc":
				case "market leader llc":
					$v = "Market Leader";
					break;


				case "walt disney parks &amp resorts online":
				case "walt disney parks resorts online":
				case "the walt disney studios":
				case "walt disney studios":
				case "the walt disney company corporate":
				case "the walt disney company":
				case "disney parks &amp resorts":
				case "disney parks resorts":
				case "walt disney parks resorts":
				case "walt disney parks &amp resorts":
				case "walt disney parks resorts careers":
				case "walt disney parks &amp resorts careers":
				case "disney":
					$v = "Disney";
					break;

			}
		}
		parent::setCompany($v);

	}

	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 */
	public function setDepartment($v)
	{
		$v = $this->_cleanupTextValue($v);
		parent::setDepartment($v);
	}

	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 */
	public function setPayRange($v)
	{
		$v = $this->_cleanupTextValue($v, $maxLength=100);
		parent::setPayRange($v);
	}

	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 */
	public function setEmploymentType($v)
	{
		$v = $this->_cleanupTextValue($v, $maxLength=100);
		parent::setEmploymentType($v);
	}

	/**
	 * @param string $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|void
	 */
	public function setCategory($v)
	{
		$v = $this->_cleanupTextValue($v, $maxLength=100);
		parent::setCategory($v);
	}

	/**
	 * @param mixed $v
	 *
	 * @return $this|\JobScooper\DataAccess\JobPosting|null
	 */
	public function setPostedAt($v)
	{
		if (empty($v))
			return null;

		$newV = null;

		if (strcasecmp($v, "Just posted") == 0)
			$newV = getTodayAsString();

		$v = strtolower(str_ireplace(array("Posted Date", "posted", "posted at"), "", $v));
		$v = $this->_cleanupTextValue($v);

		if (empty($newV)) {
			$dateVal = strtotime($v, $now = time());
			if (!($dateVal === false)) {
				$newV = $dateVal;
			}
		}

		if (empty($newV) && preg_match('/^\d+$/', $v)) {
			$vstr = strval($v);
			if (strlen($vstr) == strlen("20170101")) {
				try {
					$datestr = substr($vstr, 4, 2) . "/" . substr($vstr, 6, 2) . "/" . substr($vstr, 0, 4);
					$dateVal = strtotime($datestr, $now = time());
					if (!($dateVal === false)) {
						$newV = $dateVal;
					}
				} catch (Exception $ex) {
					try {
						$datestr = substr($vstr, 2, 2) . "/" . substr($vstr, 0, 2) . "/" . substr($vstr, 4, 4);
						$dateVal = strtotime($datestr, $now = time());
						if (!($dateVal === false)) {
							$newV = $dateVal;
						}
					} catch (Exception $ex) {

					}

				}
			}
		}

		if (empty($newV) && !empty($v)) {
			$info = date_parse($v);
			$date = "";
			foreach (array("month", "day", "year") as $dateval) {
				if ($info[$dateval] !== false) {
					$date .= strval($info[$dateval]);
				} else {
					$date .= strval(getdate()[$dateval]);
				}
			}
			$newV = $date;
		}

		if (empty($newV)) {
			$newV = $v;
		}

		parent::setPostedAt($newV);
	}

	/**
	 * @return null|string
	 */
	public function getKeySiteAndPostId()
	{
		if(!empty($this->getJobSiteKey()) && !empty($this->getJobSitePostId()))
			return cleanupSlugPart(sprintf("%s_%s", $this->getJobSiteKey(), $this->getJobSitePostId()));

		return null;
	}


	/**
	 * JobPosting constructor.
	 *
	 * @param null $arrJobFacts
	 *
	 * @throws \Exception
	 */
	function __construct($arrJobFacts = null)
	{
		parent::__construct();
		if (!is_null($arrJobFacts) && count($arrJobFacts) > 1) {
			foreach (array_keys($arrJobFacts) as $key)
				$this->set($key, $arrJobFacts[$key]);
			$this->save();
		}
	}

	/**
	 * @param string $keyType
	 * @param bool   $includeLazyLoadColumns
	 * @param array  $alreadyDumpedObjects
	 * @param bool   $includeForeignObjects
	 *
	 * @return array
	 */
	function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
	{
		$ret = parent::toArray($keyType, $includeLazyLoadColumns, $alreadyDumpedObjects, $includeForeignObjects);
		if(!empty($ret) && is_array($ret)) {
			$ret['KeySiteAndPostId'] = $this->getKeySiteAndPostId();

		}

		return $ret;
	}

	/**
	 * @param mixed $offset
	 *
	 * @return mixed
	 */
	public function offsetGet($offset)
    {
        return $this->get($offset);
    }

	/**
	 * @param mixed $offset
	 * @param mixed $value
	 */
	public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Whether or not an offset exists
     *
     * @param string An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset)
    {
        return null !== $this->get($offset);
    }

    /**
     * Unsets an offset
     *
     * @param string The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->set($offset, null);
        }
    }
}
