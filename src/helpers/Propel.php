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


/******************************************************************************
 *
 *
 *
 *
 *  New, Propel-Related Utils
 *
 *
 *
 *
 *
 ******************************************************************************/

use \JobScooper\DataAccess\Map\UserJobMatchTableMap;
use JobScooper\DataAccess\Map\UserSearchSiteRunTableMap;
use \Propel\Runtime\ActiveQuery\Criteria;

/******************************************************************************
 *
 *  Database Helper Functions
 *
 ******************************************************************************/
function loadSqlite3MathExtensions()
{
	try {
	    $con = \Propel\Runtime\Propel::getConnection();
        $expath = '/opt/sqlite/extensions/libsqlitefunctions';
        if(PHP_OS == "Darwin")
            $expath .= ".dylib";
        else
            $expath .= ".so";

        $sql2 = "SELECT load_extension('{$expath}');";
        $stmt = $con->prepare($sql2);
        $stmt->execute();
        return true;

	} catch (Exception $ex) {
		handleException($ex,"FAILED to load math functions extension for SQLite with call: " . $sql2 . "   ERROR DETAILS: %s", true);
	}

	return false;
}


/******************************************************************************
 *
 *  JobPosting Helper Functions
 *
 ******************************************************************************/

function updateOrCreateJobPosting($arrJobItem)
{
    if(is_null($arrJobItem) || !is_array($arrJobItem))
        return null;

    try {
        if(array_key_exists('JobPostingId', $arrJobItem) && !is_null($arrJobItem['JobPostingId'])) {
            $jobRecord =  \JobScooper\DataAccess\JobPostingQuery::create()
                ->filterByPrimaryKey($arrJobItem['JobPostingId'])
                ->findOneOrCreate();
        }
        else {
            if(is_null($arrJobItem['JobSiteKey']) || strlen($arrJobItem['JobSiteKey']) == 0)
                throw new InvalidArgumentException("Attempted to create a new job posting record without a valid JobSiteKey value set.");

            $jobRecord = \JobScooper\DataAccess\JobPostingQuery::create()
                ->filterByJobSiteKey($arrJobItem['JobSiteKey'])
                ->filterByJobSitePostId($arrJobItem['JobSitePostId'])
                ->findOneOrCreate();
        }

        $jobRecord->fromArray($arrJobItem);
        $jobRecord->save();
        return $jobRecord;

    }
    catch (Exception $ex)
    {
        handleException($ex);
    }

    return null;

}


//
// User Job Match List Functions
//


/**
 * @param null $jobsiteKey
 * @param null $usrTitleKeywordSets
 *
 * @return \JobScooper\DataAccess\UserJobMatch[]
 *
 * @throws \Exception
 * @throws \Propel\Runtime\Exception\PropelException
 */
function getAllMatchesForUserNotification($userNotificationState, $arrGeoLocIds=null, $nNumDaysBack=null, $user=null)
{
	if(empty($user))
	    throw new Exception("No user was specified to query for user job matches.");

	$userStateCriteria = [UserJobMatchTableMap::COL_USER_NOTIFICATION_STATE_NOT_YET_MARKED, \Propel\Runtime\ActiveQuery\Criteria::EQUAL];
	if(empty(!$userNotificationState) && is_array($userNotificationState))
	{
		$userStateCriteria = $userNotificationState;
	}

    $query = \JobScooper\DataAccess\UserJobMatchQuery::create()
        ->filterByUserNotificationState($userStateCriteria[0], $userStateCriteria[1])
        ->filterByUserFromUJM($user)
        ->joinWithJobPostingFromUJM();

	if(!empty($nNumDaysBack) && is_integer($nNumDaysBack))
	{
		$startDate = new \DateTime();
		$strMod = "-{$nNumDaysBack} days";
		$dateDaysAgo = $startDate->modify($strMod);
		$strDateDaysAgo = $dateDaysAgo->format("Y-m-d");

		$sql = UserJobMatchTableMap::COL_SET_BY_USER_SEARCH_SITE_RUN_KEY . " IN (SELECT user_search_site_run_key FROM user_search_site_run WHERE date_ended >= '{$strDateDaysAgo}') ";
		$query->add(UserJobMatchTableMap::COL_SET_BY_USER_SEARCH_SITE_RUN_KEY, $sql, Criteria::CUSTOM);

	}

    if(!empty($arrGeoLocIds) && is_array($arrGeoLocIds))
    {
    	$locIdColumnName = $query->getAliasedColName(\JobScooper\DataAccess\Map\JobPostingTableMap::COL_GEOLOCATION_ID);
        $query->useJobPostingFromUJMQuery()
	       ->addCond('locIdsCond1', $locIdColumnName, $arrGeoLocIds, Criteria::IN)
	       ->addCond('locIdsCond2', $locIdColumnName, null, Criteria::ISNULL)
	       ->combine(array('locIdsCond1', 'locIdsCond2'), Criteria::LOGICAL_OR)
	       ->orderByKeyCompanyAndTitle()
	       ->endUse();
    }
    else
	    $query->useJobPostingFromUJMQuery()
		    ->orderByKeyCompanyAndTitle()
		    ->endUse();

	$results =  $query->find()->toKeyIndex("UserJobMatchId");

    return $results;
}


/**
 * @param $arrUserJobMatchIds
 * @param $strNewStatus
 *
 * @throws \Exception
 * @throws \Propel\Runtime\Exception\PropelException
 */
function updateUserJobMatchesStatus($arrUserJobMatchIds, $strNewStatus)
{
	LogMessage("Marking " . count($arrUserJobMatchIds) . " user job matches as {$strNewStatus}...");
	$con = \Propel\Runtime\Propel::getWriteConnection(UserJobMatchTableMap::DATABASE_NAME);
	$valueSet = UserJobMatchTableMap::getValueSet(UserJobMatchTableMap::COL_USER_NOTIFICATION_STATE);
	$statusInt = array_search($strNewStatus, $valueSet);
	$nChunkCounter = 1;
	foreach (array_chunk($arrUserJobMatchIds, 50) as $chunk) {
		$nMax = ($nChunkCounter+50);
		LogMessage("Marking user job matches " . $nChunkCounter . " - " . ($nMax >= count($arrUserJobMatchIds) ? count($arrUserJobMatchIds) - 1 : $nMax) . " as {$strNewStatus}...");
		\JobScooper\DataAccess\UserJobMatchQuery::create()
			->filterByUserJobMatchId($chunk)
			->update(array("UserNotificationState" => $statusInt), $con);
		$nChunkCounter += 50;
	}

}


//
// Jobs List Filter Functions
//

function isUserJobMatchAndNotExcluded($var)
{
	return ((empty($var['IsExcluded']) || $var['IsExcluded'] !== true) && (!empty($var['IsJobMatch']) && $var['IsJobMatch'] === true));
}

