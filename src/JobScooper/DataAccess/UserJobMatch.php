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


use JobScooper\DataAccess\Base\UserJobMatch as BaseUserJobMatch;
use JobScooper\DataAccess\Map\UserJobMatchTableMap;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Map\TableMap;


/**
 * Skeleton subclass for representing a row from the 'user_job_match' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class UserJobMatch extends BaseUserJobMatch
{
    private $delim = ' | ';

	/**
	 *
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function updateUserMatchStatus()
    {
        if(count($this->getMatchedUserKeywords()) > 0 )
            $this->setIsJobMatch(true);
        else
            $this->setIsJobMatch(false);

	    $this->setIsExcluded(false);
        if(count($this->getMatchedNegativeTitleKeywords()) > 0 )
            $this->setIsExcluded(true);

        if(count($this->getMatchedNegativeCompanyKeywords()) > 0 )
            $this->setIsExcluded(true);

	    if($this->isOutOfUserArea() === true)
		    $this->setIsExcluded(true);

	    $jp = $this->getJobPostingFromUJM();
	    if(!empty($jp) && !empty($jp->getDuplicatesJobPostingId()))
		    $this->setIsExcluded(true);
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function preSave(ConnectionInterface $con = null)
    {
        $this->updateUserMatchStatus();

        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

	/**
	 * @param null $limitToKeys
	 *
	 * @return array
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function toFlatArrayForCSV($limitToKeys=null)
    {
        $jobPost = $this->getJobPostingFromUJM();
        if(empty($jobPost) && $this->isNew())
            $jobPost = new JobPosting();
        $arrJobPost = $jobPost->toFlatArrayForCSV();

        $arrUserJobMatch = $this->toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false);
        foreach($arrUserJobMatch as $k => $v)
        {
	        if(is_array($v))
        		$arrUserJobMatch[$k] = join("|", $v);
        }
        updateColumnsForCSVFlatArray($arrUserJobMatch, new UserJobMatchTableMap());

        $arrItem = array_merge_recursive_distinct($arrJobPost, $arrUserJobMatch);

        if(!empty($limitToKeys) && is_array($limitToKeys))
        {
        	return array_subset_keys($arrItem, $limitToKeys);
        }

        return $arrItem;
    }

	/**
	 * @param array $v
	 *
	 * @return $this|\JobScooper\DataAccess\UserJobMatch
	 */
	public function setMatchedNegativeTitleKeywords($v)
    {
    	if(!empty($v) && is_array($v))
	    {
	    	foreach($v as &$item)
		    {
		    	if(is_array($item))
		    		$item = implode(" ", $item);
		    }
	    }
	    elseif (is_string($v))
		    $v = array($v);
	    elseif (empty($v))
		    $v = array();

	    return parent::setMatchedNegativeTitleKeywords($v);
    }

	/**
	 * @param array $v
	 *
	 * @return $this|\JobScooper\DataAccess\UserJobMatch
	 */
	public function setMatchedUserKeywords($v)
	{
		if(!empty($v) && is_array($v))
		{
			foreach($v as &$item)
			{
				if(is_array($item))
					$item = implode(" ", $item);
			}
		}
		elseif (is_string($v))
			$v = array($v);
		elseif (empty($v))
			$v = array();

		return parent::setMatchedUserKeywords($v);
	}

	/**
	 *
	 */
	public function clearUserMatchState()
	{
		$this->setIsJobMatch(null);
		$this->setOutOfUserArea(null);

		$kwds = $this->getMatchedNegativeCompanyKeywords();
		if(!empty($kwds))
			foreach($kwds as $k)
				$this->removeMatchedNegativeCompanyKeyword($k);

		$kwds = $this->getMatchedNegativeTitleKeywords();
		if(!empty($kwds))
			foreach($kwds as $k)
				$this->removeMatchedNegativeTitleKeyword($k);

		$kwds = $this->getMatchedUserKeywords();
		if(!empty($kwds))
			foreach($kwds as $k)
				$this->removeMatchedUserKeyword($k);

		$this->applyDefaultValues();
	}

}
