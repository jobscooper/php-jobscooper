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

namespace JobScooper\Builders;

use JobScooper\DataAccess\User;
use JobScooper\DataAccess\UserSearchQuery;
use JobScooper\DataAccess\UserSearchSiteRunQuery;
use JobScooper\DataAccess\UserSearchSiteRun;
use Propel\Runtime\ActiveQuery\Criteria;

class SearchBuilder
{
	private $_cacheCutOffTime = null;

	function __construct()
	{
		$this->_cacheCutOffTime = date_sub(new \DateTime(), date_interval_create_from_date_string('18 hours'));
	}

	public function initializeSearches()
    {

	    $this->_setUserSearches();

	    //
	    // Create searches needed to run all the keyword sets
	    //
	    $this->_generateUserSearchSiteRuns();

	    JobSitePluginBuilder::setSitesAsExcluded(getConfigurationSetting("config_excluded_sites"));

	    JobSitePluginBuilder::filterJobSitesByCountryCodes(getConfigurationSetting("country_codes"));

	    $this->_filterJobSitesThatAreExcluded();

	    //
	    // Filter out sites excluded in the user's config file
	    //
	    $this->_filterJobSitesThatShouldNotRunYet();

	    $this->_filterUserSearchesThatShouldNotRunYet();
    }

    private function _setUserSearches()
    {

	    $arrLocations = getConfigurationSetting('search_locations');
	    if (empty($arrLocations)) {
		    LogWarning("No search locations have been set. Unable to setup a user search run.");

		    return null;
	    }

	    $keywordSets = getConfigurationSetting('user_keyword_sets');
	    if (empty($keywordSets)) {
		    LogWarning("No user keyword sets have been configured. Unable to setup a user search run.");

		    return null;
	    }

	    $userSearches = array();
	    foreach ($arrLocations as $lockey => $searchLoc) {
		    LogMessage("Adding user searches in " . $searchLoc->getDisplayName() . " for user's keywords sets");

		    foreach ($keywordSets as $setKey => $keywordSet) {

			    $user_search = UserSearchQuery::create()
				    ->filterByUserKeywordSetFromUS($keywordSet)
				    ->filterByUserFromUS(User::getCurrentUser())
				    ->filterByGeoLocationId($searchLoc->getGeoLocationId())
				    ->findOneOrCreate();

			    $user_search->setUserKeywordSetFromUS($keywordSet);
			    $user_search->setUserFromUS(User::getCurrentUser());
			    $user_search->setGeoLocationFromUS($searchLoc);
			    $user_search->save();

			    $userSearches[$user_search->getUserSearchKey()] = $user_search;
		    }
	    }

	    if (empty($userSearches)) {
		    LogMessage("Could not create user searches for the given user keyword sets and geolocations.  Cannot continue.");

		    return null;
	    }
	    setConfigurationSetting("user_searches", $userSearches);
	    LogMessage("Generated " . count($userSearches) . " user searches.");

    }

	private function _filterJobSitesThatShouldNotRunYet()
	{

		$sitesToSkip = array();

		$completedSitesAllOnly = UserSearchSiteRunQuery::create()
			->useJobSiteFromUSSRQuery()
			->filterByResultsFilterType("all-only")
			->withColumn("JobSiteFromUSSR.ResultsFilterType", "ResultsFilterType")
			->endUse()
			->addAsColumn('LastCompleted', 'MAX(user_search_site_run.date_ended)')
			->select(array('JobSiteKey', 'ResultsFilterType', 'LastCompleted'))
			->filterByRunResultCode("successful")
			->groupBy(array("JobSiteKey", "ResultsFilterType"))
			->find()
			->getData();

		if (!empty($completedSitesAllOnly)) {
			$sitesToSkip = array_merge($sitesToSkip, $completedSitesAllOnly);
		}

		$searchLocations = getConfigurationSetting('search_locations');
		foreach ($searchLocations as $location) {
			$sitesAllLocationOnly = UserSearchSiteRunQuery::create()
				->useJobSiteFromUSSRQuery()
				->filterByResultsFilterType("all-by-location")
				->withColumn("JobSiteFromUSSR.ResultsFilterType", "ResultsFilterType")
				->endUse()
				->addAsColumn('LastCompleted', 'MAX(user_search_site_run.date_ended)')
				->select(array('JobSiteKey', 'ResultsFilterType', 'LastCompleted'))
				->filterByRunResultCode("successful")
				->filterByGeoLocationFromUSSR($location)
				->groupBy(array("JobSiteKey", "ResultsFilterType"))
				->find()
				->getData();

			if (!empty($sitesAllLocationOnly)) {
				$sitesToSkip = array_merge($sitesToSkip, $sitesAllLocationOnly);
			}
		}
		$sitesToSkip = array_column($sitesToSkip, "LastCompleted", "JobSiteKey");

		// Filter sites that can be skipped by date.
		//
		// Remove any that ran before the cache cut off time, not since that time.
		// We are left with only those we should skip, aka the ones that
		// ran after our cutoff time
		//
		foreach ($sitesToSkip as $key => $result) {
			if (new \DateTime($result) <= $this->_cacheCutOffTime)
				unset($sitesToSkip[$key]);
		}

		$searchesByJobsite = getConfigurationSetting("user_search_site_runs");
		if (empty($searchesByJobsite))
			return;
		$keepThese = array_diff_key($searchesByJobsite, $sitesToSkip);

		$skipTheseSearches = array_intersect_key($searchesByJobsite, $sitesToSkip);
		foreach($skipTheseSearches as $siteKey => $siteSearches)
		{
			if(!empty($siteSearches))
				foreach($siteSearches as $search)
				{
					$search->setRunResultCode("skipped");
					$search->save();
				}
		}
		unset($GLOBALS[JOBSCOOPER_CONFIGSETTING_ROOT]["user_search_site_runs"]);
		setConfigurationSetting("user_search_site_runs", $keepThese);

	 LogMessage("Skipping the following sites & searches because they have run since " . $this->_cacheCutOffTime->format("Y-m-d H:i") . ": " . getArrayDebugOutput($skipTheseSearches));
	}

	private function _filterJobSitesThatAreExcluded()
	{
		$allSites = JobSitePluginBuilder::getJobSites();
		$includedSites = JobSitePluginBuilder::getIncludedJobSites();

		$keysExcludedSites = array_diff_key($allSites, $includedSites);
		$searchesByJobsite = getConfigurationSetting("user_search_site_runs");
		if (empty($searchesByJobsite))
			return;

		foreach($searchesByJobsite as $k => $siteSearches)
		{
			if(!empty($siteSearches))
				foreach($siteSearches as $search)
				{
					if(in_array($search->getJobSiteKey(), $keysExcludedSites))
					{
						$search->setRunResultCode("excluded");
						$search->save();
						unset($searchesByJobsite[$k]);
					}
				}
		}
		unset($GLOBALS[JOBSCOOPER_CONFIGSETTING_ROOT]["user_search_site_runs"]);
		setConfigurationSetting("user_search_site_runs", $searchesByJobsite);
	}

	private function _filterUserSearchesThatShouldNotRunYet()
    {

	    $completedSearchesUserRecent = UserSearchSiteRunQuery::create()
		    ->addAsColumn('LastCompleted', 'MAX(user_search_site_run.date_ended)')
		    ->addAsColumn('PartialUSSRKey', 'CONCAT(jobsite_key, user_search_key)')
		    ->select(array('PartialUSSRKey', 'LastCompleted'))
		    ->filterByRunResultCode("successful")
		    ->groupBy(array("PartialUSSRKey", 'UserSearchKey'))
		    ->filterByUserFromUSSR(User::getCurrentUser())
		    ->orderBy("LastCompleted", Criteria::ASC)
		    ->find()
		    ->getData();
	    $completedSearchesUserRecent = array_column($completedSearchesUserRecent, "LastCompleted", "PartialUSSRKey");

	    $searchesByJobsite = getConfigurationSetting("user_search_site_runs");
	    if (empty($searchesByJobsite))
		    return;


	    // Filter sites that can be skipped by date and reorder by least-recent-first
	    //
	    // Remove any that ran before the cache cut off time, not since that time.
	    // We are left with only those we should skip, aka the ones that
	    // ran after our cutoff time
	    //
		$searchesToRunBySiteNewOrder = array();

	    foreach($searchesByJobsite as $jobSiteKey => $siteSearches)
	    {
	    	foreach($siteSearches as $ussrKey => $searchRun)
		    {
		    	$fKeepSearch = true;
		    	$partialUSSRKey = $jobSiteKey . $searchRun->getUserSearchKey();
		    	if(array_key_exists($partialUSSRKey, $completedSearchesUserRecent) && !empty($completedSearchesUserRecent[$partialUSSRKey]))
			    {
				    if (new \DateTime($completedSearchesUserRecent[$partialUSSRKey]) >= $this->_cacheCutOffTime)
				    {
					    $fKeepSearch = false;
					    LogMessage("Skipping search {$ussrKey} because it has run since " .  $this->_cacheCutOffTime->format("Y-m-d H:i"));
				    }
			    }

			    if($fKeepSearch == true) {
				    if (!is_array($searchesToRunBySiteNewOrder[$jobSiteKey]))
					    $searchesToRunBySiteNewOrder[$jobSiteKey] = array();
				    $searchesToRunBySiteNewOrder[$jobSiteKey][$ussrKey] = $searchRun;
			    }
			    else
			    {
				    $searchRun->setRunResultCode("skipped");
				    $searchRun->save();
			    }
		    }
	    }

	    unset($GLOBALS[JOBSCOOPER_CONFIGSETTING_ROOT]["user_search_site_runs"]);
	    setConfigurationSetting("user_search_site_runs", $searchesToRunBySiteNewOrder);
    }


    private function _generateUserSearchSiteRuns()
    {
        //
        // let's start with the searches specified with the details in the the config.ini
        //
        $userSearches = getConfigurationSetting('user_searches');
        $includedSites = JobSitePluginBuilder::getIncludedJobSites($fOptimizeBySiteRunOrder=true);
	    if (empty($userSearches) || empty($includedSites))
		    return;

        LogMessage(" Creating search runs for " . strval(count($userSearches)) . " user searches across " . count($includedSites) . " jobsites.");

        $user = User::getCurrentUser();
        $searchRuns = array();

        foreach($includedSites as $jobsiteKey => $site)
        {
        	$searchRuns[$jobsiteKey] = array();

            foreach($userSearches as $search)
            {
//            	$searchRun = UserSearchSiteRunQuery::create()
//		            ->filterByUser($user)
//		            ->filterByUserSearch($search)
//		            ->filterByJobSiteKey()
//		            ->filterByAppRunId($apprun)
//

                $searchrun = new UserSearchSiteRun();
	            $searchrun->setUserFromUSSR(User::getCurrentUser());
	            $searchrun->setUserSearchFromUSSR($search);
                $searchrun->setJobSiteKey($site);
                $searchrun->setAppRunId(getConfigurationSetting('app_run_id'));
                $searchrun->setStartedAt(time());
                $searchrun->save();

                $searchRuns[$jobsiteKey][$searchrun->getUserSearchSiteRunKey()] = $searchrun;

            }
        }

	    setConfigurationSetting("user_search_site_runs", $searchRuns);
    }


}
