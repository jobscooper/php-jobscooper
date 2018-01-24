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
namespace JobScooper\StageProcessor;

use JobScooper\Builders\JobSitePluginBuilder;
use JobScooper\DataAccess\GeoLocationQuery;
use JobScooper\DataAccess\JobPostingQuery;
use JobScooper\DataAccess\Map\GeoLocationTableMap;
use Exception;
use JobScooper\DataAccess\Map\UserJobMatchTableMap;
use JobScooper\DataAccess\User;
use JobScooper\DataAccess\UserJobMatch;
use JobScooper\DataAccess\UserJobMatchQuery;
use JobScooper\Manager\LocationManager;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Propel;

/**
 * Class JobsAutoMarker
 * @package JobScooper\StageProcessor
 */
class JobsAutoMarker
{

	/**
	 * @var LocationManager
	 */
    protected $_locmgr = null;
	protected $title_negative_keyword_tokens = null;
	protected $companies_regex_to_filter = null;

	/**
	 * @var User
	 */
	private $_userBeingMarked = null;


	/**
	 * JobsAutoMarker constructor.
	 *
	 * @param \JobScooper\DataAccess\User $user
	 */
	function __construct(User $user)
    {
    	$this->_userBeingMarked = $user;
        $this->_locmgr = LocationManager::getLocationManager();
    }

	/**
	 * @param null $arrLocIds
	 *
	 * @return \JobScooper\DataAccess\UserJobMatch[]
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _getMatches($arrLocIds=null)
    {
	    return getAllMatchesForUserNotification(
		    [UserJobMatchTableMap::COL_USER_NOTIFICATION_STATE_NOT_YET_MARKED, Criteria::EQUAL],
		    $arrLocIds,
		    null,
		    $this->_userBeingMarked
	    );
    }

	/**
	 *
	 * @throws \Exception
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	public function markJobs()
    {

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Filter the full jobs list looking for duplicates, etc.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        LogMessage(PHP_EOL . "**************  Updating jobs list for known filters ***************" . PHP_EOL);

        try {
	        // Get all the postings that are in the table but not marked as ready-to-send
	        //
	        $arrJobs_AutoUpdatable = $this->_getMatches();

	        if (empty($arrJobs_AutoUpdatable)) {
		        LogMessage("No jobs were found for auto-marking.");

		        return;
	        }

	        foreach ($arrJobs_AutoUpdatable as $jobmatch)
		        $jobmatch->clearUserMatchState();

	        $this->_markJobsList_KeywordMatches_($arrJobs_AutoUpdatable);

	        $this->_findAndMarkRecentDuplicatePostings();

	        $this->_markJobsList_SetOutOfArea_($arrJobs_AutoUpdatable);

	        $this->_markJobsList_SetAutoExcludedCompaniesFromRegex_($arrJobs_AutoUpdatable);

	        //
	        // Since we did not require each update in the previous calls to call save()
	        // for each UserJobMatch and take the perf hit that would generate, it is
	        // probable that some of the rows are inconsistent between their facts for
	        // match exclusion and the IsExcluded fact.  So let's grab them all fresh
	        // from the DB with the updated data and re-save each of them, which will
	        // cause IsExcluded to get updated and back in sync for each record.
	        //
	        // We'll use this same Save call to store that the record has been automarked
	        // and is ready for sending.
	        //
	        $arrJobs_AutoUpdatable = $this->_getMatches();
	        foreach ($arrJobs_AutoUpdatable as $jobMatch) {
		        $jobMatch->setUserNotificationState(UserJobMatchTableMap::COL_USER_NOTIFICATION_STATE_MARKED_READY_TO_SEND);
		        $jobMatch->updateUserMatchStatus();
		        $jobMatch->save();
	        }
        }
        catch (Exception $ex)
        {
			LogError($ex->getMessage(), null, $ex);
			throw $ex;
        }
    }

	/**
	 * @throws \Exception
	 */
	private function _findAndMarkRecentDuplicatePostings()
	{
		startLogSection("Finding new duplicate company / job title pairs in the past 7 days to mark as dupe...");
		try {

			$sinceWhen = date_add(new \DateTime(), date_interval_create_from_date_string('7 days ago'));
			$included_sites = array_keys(JobSitePluginBuilder::getIncludedJobSites());

			$duplicatePostings = JobPostingQuery::create()
				->filterByDuplicatesJobPostingId(null)
				->filterByJobSiteKey($included_sites, Criteria::IN)
				->filterByFirstSeenAt(array('min' => $sinceWhen))
				->withColumn('COUNT(jobposting_id)', "DupeCount")
				->withColumn('MIN(jobposting_id)', "PrimaryJobPostingId")
				->select(array("PrimaryJobPostingId", "Company", "Title", "KeyCompanyAndTitle", "DupeCount"))
				->groupBy(array("KeyCompanyAndTitle", "Company", "Title"))
				->having('DupeCount >= ?', 2, \PDO::PARAM_INT)
				->find()
				->getData();

			$totalMarked = 0;
			if (!empty($duplicatePostings) && is_array($duplicatePostings)) {
				LogMessage("Found " . countAssociativeArrayValues($duplicatePostings) . " company/title pairs that are new duplicates within the past 7 days.  Marking the duplicates now...");
				$chunks = array_chunk($duplicatePostings, 25);
				$nCounter = 1;
				foreach ($chunks as $dupesChunk) {
					LogMessage("... marking duplicate company title pairs " . strval($nCounter) . "-" . strval($nCounter + 25));
					foreach ($dupesChunk as $dupeSet) {
						$nNewlyMarked= JobPostingQuery::create()
							->filterByJobPostingId($dupeSet['PrimaryJobPostingId'], Criteria::NOT_EQUAL)
							->filterByKeyCompanyAndTitle($dupeSet['KeyCompanyAndTitle'], Criteria::EQUAL)
							->update(array("DuplicatesJobPostingId" => $dupeSet['PrimaryJobPostingId']));
						$totalMarked = $totalMarked + $nNewlyMarked;
					}
					$nCounter += 25;
				}
			}
			LogMessage("Marked {$totalMarked} job listings as duplicate of an earlier job posting with the same company and job title.");
		} catch (\Exception $ex)
		{
			handleException($ex, null, false);
		}
		finally
		{
			endLogSection("Finished processing job posting duplicates.");
		}

    }

	/**
	 * @return bool
	 */
	private function _isGeoSpatialWorking()
    {
	    $sqlType = \Propel\Runtime\Propel::getServiceContainer()->getAdapterClass();
	    switch($sqlType)
	    {
		    case "mysql":
			    return true;
				break;

		    default:
		    return false;
			    break;

		    case "sqlite":
			    try {
				    $ret = loadSqlite3MathExtensions();
				    if($ret)
					    LogMessage("Successfully loaded the necessary math functions for SQLite to do geospatial filtering.");
				    return $ret;

			    } catch (\Exception $ex) {
				    LogWarning("Failed to load the necessary math functions for SQLite to do geospatial filtering.  Falling back to county-level instead.");
			    }
			    break;
	    }

        return false;
    }

	/**
	 * @param \JobScooper\DataAccess\UserJobMatch[] $arrJobsList
	 * @throws \Exception
	 */
	private function _markJobsList_SetOutOfArea_(&$arrJobsList)
    {
        if (count($arrJobsList) == 0) return;

        LogMessage("Marking Out of Area Jobs");

        if ($this->_isGeoSpatialWorking()) {
            $this->_markJobsList_OutOfArea_Geospatial($arrJobsList);
        }
        else {
            $this->_markJobsList_OutOfArea_CountyFiltered($arrJobsList);
        }
    }

	/**
	 * @param \JobScooper\DataAccess\UserJobMatch[] $arrJobsList
	 * @throws \Exception
	 */
    private function _markJobsList_OutOfArea_CountyFiltered(&$arrJobsList)
    {
	    try
	    {
		    startLogSection("Automarker: marking jobs as out of area using counties...");

		    $searchLocations = $this->_userBeingMarked->getSearchGeoLocations();


	        $arrIncludeCounties = array();

	        /* Find all locations that are within 50 miles of any of our search locations */

	        LogMessage("Auto-marking postings not in same counties as the search locations...");
	        foreach($searchLocations as $searchloc)
	        {
	            if(!empty($searchloc))
	            {
	                $arrIncludeCounties[] = $searchloc->getCounty() . "~" .$searchloc->getRegion();
	            }
	        }

	        LogMessage("Finding job postings not in the following counties & states: " . getArrayValuesAsString($arrIncludeCounties) . " ...");
	        $arrJobsOutOfArea = array_filter($arrJobsList, function(UserJobMatch $v) use ($arrIncludeCounties) {
	            $posting = $v->getJobPostingFromUJM();
	            $locId = $posting->getGeoLocationId();
	            if(is_null($locId))
	                return false;  // if we don't have a location, assume nearby

	            $location = $posting->getGeoLocationFromJP();
	            $county = $location->getCounty();
	            $state = $location->getRegion();
	            if(!is_null($county) && !is_null($state)) {
	                $match = $county . "~" . $state;
	                if (!in_array($match, $arrIncludeCounties))
	                    return true;
	            }
	            return false;
	        });

	        LogMessage("Marking user job matches as out of area for " . count($arrJobsOutOfArea) . " matches ...");

	        foreach ($arrJobsOutOfArea as &$jobOutofArea) {
	            $jobOutofArea->setOutOfUserArea(true);
	            $jobOutofArea->save();
	        }
        $nJobsMarkedAutoExcluded = count($arrJobsOutOfArea);
	        $nJobsNotMarked = count($arrJobsList) - $nJobsMarkedAutoExcluded;


	        LogMessage("Jobs excluded as out of area: marked ". $nJobsMarkedAutoExcluded . "/" . countAssociativeArrayValues($arrJobsList) .";  not marked " . $nJobsNotMarked . " / " . countAssociativeArrayValues($arrJobsList) );
	    }
	    catch (Exception $ex)
	    {
		    handleException($ex, "Error in _markJobsList_OutOfArea_CountyFiltered: %s", true);
	    }
	    finally
	    {
		    endLogSection("Out of area job marking by county finished.");
	    }
    }

	/**
	 * @param \JobScooper\DataAccess\UserJobMatch[] $arrJobsList
	 * @throws \Exception
	 */
	private function _markJobsList_OutOfArea_Geospatial(&$arrJobsList)
    {
    	try
	    {
	    	startLogSection("Automarker: marking jobs as out of area using geospatial data...");
		    $searchLocations = $this->_userBeingMarked->getSearchGeoLocations();

		    $arrNearbyIds = array();

	        /* Find all locations that are within 50 miles of any of our search locations */

	        LogMessage("Getting locationIDs within 50 miles of search locations...");
	        foreach($searchLocations as $searchloc)
	        {
	            if(!empty($searchloc))
	            {
	                $nearbyLocations = GeoLocationQuery::create()
	                    ->filterByDistanceFrom($searchloc->getLatitude(), $searchloc->getLongitude(), 50, GeoLocationTableMap::MILES_UNIT, Criteria::LESS_THAN)
	                    ->find();

	                if(!empty($nearbyLocations))
	                {
	                    foreach($nearbyLocations as $near)
	                        $arrNearbyIds[] = $near->getGeoLocationId();
	                }
	            }
	        }

	        LogMessage("Marking job postings in the " . count($arrNearbyIds) . " matching areas ...");
		    $arrJobsInArea = $this->_getMatches($arrNearbyIds);
		    $arrJobListIds = array_unique(array_from_orm_object_list_by_array_keys($arrJobsList, array("UserJobMatchId")));
		    $arrInAreaIds = array_unique(array_from_orm_object_list_by_array_keys($arrJobsInArea, array("UserJobMatchId")));
		    foreach(array_chunk($arrInAreaIds, 50) as $chunk) {
			    $con = Propel::getWriteConnection(UserJobMatchTableMap::DATABASE_NAME);
			    UserJobMatchQuery::create()
				    ->filterByUserJobMatchId($chunk)
				    ->update(array("OutOfUserArea" => false), $con);
		    }

		    LogMessage("Marking job postings outside " . count($arrNearbyIds) . " matching areas ...");
		    $arrOutOfAreaIds = array_diff($arrJobListIds, $arrInAreaIds);
		    if(!empty($arrOutOfAreaIds))
			    foreach(array_chunk($arrOutOfAreaIds, 50) as $chunk) {
				    $con = Propel::getWriteConnection(UserJobMatchTableMap::DATABASE_NAME);
				    UserJobMatchQuery::create()
					    ->filterByUserJobMatchId($chunk)
					    ->update(array("OutOfUserArea" => true), $con);
			    }

	        $nJobsMarkedAutoExcluded = count($arrOutOfAreaIds);
	        $nJobsNotMarked = count($arrJobsInArea);

	       LogMessage("Jobs excluded as out of area:  marked out of area ". $nJobsMarkedAutoExcluded . "/" . countAssociativeArrayValues($arrJobsList) ."; marked in area = " . $nJobsNotMarked . "/" . countAssociativeArrayValues($arrJobsList) );
	    }
		catch (Exception $ex)
		{
			handleException($ex, "Error in _markJobsList_OutOfArea_Geospatial: %s", true);
		}
		finally
		{
			endLogSection("Out of area job marking geospatially finished.");
		}
    }

	/**
	 * @param \JobScooper\DataAccess\UserJobMatch[] $arrJobsList
	 * @throws \Exception
	 */
	private function _markJobsList_SetAutoExcludedCompaniesFromRegex_(&$arrJobsList)
    {
	    try {
		    startLogSection("Automarker: marking company names as excluded based on user input files...");

		    //
	        // Load the exclusion filter and other user data from files
	        //
	        $this->_loadCompanyRegexesToFilter();

	        $nJobsSkipped = 0;
	        $nJobsMarkedAutoExcluded = 0;
	        $nJobsNotMarked = 0;

            if(count($arrJobsList) == 0 || is_null($this->companies_regex_to_filter) || count($this->companies_regex_to_filter) == 0) return;

            LogMessage("Excluding Jobs by Companies Regex Matches");
            LogMessage("Checking ".count($arrJobsList) ." roles against ". count($this->companies_regex_to_filter) ." excluded companies.");

            foreach ($arrJobsList as &$jobMatch) {
                $matched_exclusion = false;
                foreach($this->companies_regex_to_filter as $rxInput )
                {
                    if(preg_match($rxInput, strScrub($jobMatch->getJobPostingFromUJM()->getCompany(), DEFAULT_SCRUB)))
                    {
                        $jobMatch->setMatchedNegativeCompanyKeywords(array($rxInput));
                        $jobMatch->save();
                        $nJobsMarkedAutoExcluded++;
                        $matched_exclusion = true;
                        break;
                    }
                }

                if($matched_exclusion !== true)
                    $nJobsNotMarked += 1;
            }

            LogMessage("Jobs marked with excluded companies: ".$nJobsMarkedAutoExcluded . "/" . countAssociativeArrayValues($arrJobsList) ." marked as excluded; not marked ". $nJobsNotMarked . "/" . countAssociativeArrayValues($arrJobsList) );
        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in SetAutoExcludedCompaniesFromRegex: %s", true);
        }
        finally
        {
        	endLogSection("Company exclusion by name finished.");
        }
    }

	/**
	 * @param string $basefile
	 * @param \JobScooper\DataAccess\UserJobMatch [] $arrJobList
	 *
	 * @return null|string
	 * @throws \Exception
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _exportJobMatchesToJson($basefile="automarker", $arrJobList)
	{
		$arrJobItems = array();
		if($arrJobList)
		{
			$item = array_shift($arrJobList);
			$jobMatchKeys = array_keys($item->toArray());
			$jobMatchKeys[] = "Title";
			array_unshift($arrJobList, $item);

		}
		foreach($arrJobList as $job)
			$arrJobItems[$job->getUserJobMatchId()] = $job->toFlatArrayForCSV($jobMatchKeys);

		$searchKeywords = array();
		$keywords = $this->_userBeingMarked->getSearchKeywords();
		if(empty($keywords))
			return null;

		$neg_kwds = $this->_loadUserNegativeTitleKeywords();

		$jsonObj = array(
			"user" => $this->_userBeingMarked->toArray(),
			"job_matches" => $arrJobItems,
			"search_keywords" => $searchKeywords,
			"negative_title_keywords" => $neg_kwds
		);

		$outfile = generateOutputFileName($basefile, "json", true, 'debug');
		writeJson($jsonObj, $outfile );

		return $outfile;
	}


	/**
	 * Reads JSON encoded file with an array of UserJobMatch/JobPosting combo records named "jobs"
	 * and updates the database with the values for each record
	 *
	 * @param String $inputFile The input json file to load
	 *
	 * @returns array Returns array of UserJobMatchIds if successful; empty array if not.
	 * @throws \Exception
	 */
	private function _updateUserJobMatchesFromJson($datafile)
	{

		if (!is_file($datafile))
			throw new Exception("Unable to locate JSON file {$datafile} to load.");

		try {

			LogMessage("Loading and updating UserJobMatch records from json file {$datafile}.");
			$data = loadJSON($datafile);
			$retUJMIds = array();

			if(empty($data) || !array_key_exists('job_matches', $data))
			{
				throw new Exception("Unable to load data from {$datafile}.  No records found.");
			}

				$arrMatchRecs = $data['job_matches'];
				if (!empty($arrMatchRecs) && is_array($arrMatchRecs)) {
					$arrUserJobMatchIds = array_keys($arrMatchRecs);
					$dbRecsById = array();
					$chunks = array_chunk($arrUserJobMatchIds, 50);
					foreach ($chunks as $idchunk) {
						$dbRecsById = UserJobMatchQuery::create()
							->filterByUserJobMatchId($idchunk, Criteria::IN)
							->find()
							->toKeyIndex("UserJobMatchId");
						foreach ($idchunk as $id) {
							$arrItem = $arrMatchRecs[$id];
							if (array_key_exists($id, $dbRecsById)) {
								$dbMatch = $dbRecsById[$id];
							} else {
								$dbMatch = UserJobMatchQuery::create()
									->filterByUserJobMatchId($id)
									->findOneOrCreate();
								$dbMatch->setUserJobMatchId($id);
							}

							$arrJobMatchFacts = array_subset_keys($arrItem, array(
								"UserJobMatchId",
								"IsJobMatch",
								"MatchedNegativeTitleKeywords",
								"MatchedUserKeywords"
							));
							if (!empty($arrJobMatchFacts['MatchedUserKeywords'])) {
								$split = preg_split("/\|/", $arrJobMatchFacts['MatchedUserKeywords'], -1, PREG_SPLIT_NO_EMPTY);
								if(!empty($split))
									$arrJobMatchFacts['MatchedUserKeywords'] = $split;
							}
							if (!empty($arrJobMatchFacts['MatchedNegativeTitleKeywords'])) {
								$split = preg_split("/\|/", $arrJobMatchFacts['MatchedNegativeTitleKeywords'], -1, PREG_SPLIT_NO_EMPTY);
								if(!empty($split))
									$arrJobMatchFacts['MatchedNegativeTitleKeywords'] = $split;
							}
							$dbMatch->fromArray($arrJobMatchFacts);
							$dbMatch->save();
							$retUJMIds[] = $id;
						}
					}
				}
			return $retUJMIds;

		} catch (Exception $ex) {
			throw $ex;
		}
	}



	/**
	 * @param \JobScooper\DataAccess\UserJobMatch[] $arrJobsList
	 * @throws \Exception
	 */
	private function _markJobsList_KeywordMatches_(&$arrJobsList)
	{
		try {
			startLogSection("Automarker: Starting matching of " . count($arrJobsList) . " job role titles against user search keywords ...");

			try {

				$basefile = "mark_titlematches";
				$sourcefile = $this->_exportJobMatchesToJson("{$basefile}_src", $arrJobsList);
				$resultsfile = generateOutputFileName("{$basefile}_results", "json", true, 'debug');

				try {
					startLogSection("Calling python to do work of job title matching.");
					$PYTHONPATH = realpath(__ROOT__ . "/python/pyJobNormalizer/matchTitlesToKeywords.py");
					$cmd = "python " . $PYTHONPATH . " -i " . escapeshellarg($sourcefile) . " -o " . escapeshellarg($resultsfile);

#					$cmd = "source " . realpath(__ROOT__) . "/python/pyJobNormalizer/venv/bin/activate; " . $cmd;

					LogMessage(PHP_EOL . "    ~~~~~~ Running command: " . $cmd . "  ~~~~~~~" . PHP_EOL);
					doExec($cmd);
				} catch (Exception $ex)
				{
					throw $ex;
				}
				finally
				{
					endLogSection("Python command call finished.");
				}

				LogMessage("Updating database with new match results...");
				$this->_updateUserJobMatchesFromJson($resultsfile);

			} catch (Exception $ex) {
				handleException($ex, 'ERROR:  Failed to verify titles against keywords due to error: %s', isDebug());
			}
			LogMessage("Completed matching user keyword phrases against job titles.");
		}
		catch (Exception $ex)
		{
			handleException($ex, null, true);
		}
		finally
		{
			endLogSection("Job role title matching finished.");
		}


	}

	/**
	 * @throws \Exception
	 */
	private function _loadUserNegativeTitleKeywords()
	{
		assert(!empty($this->_userBeingMarked));

		$inputfiles = $this->_userBeingMarked->getInputFiles("negative_title_keywords");

		if (!is_array($inputfiles)) {
			// No files were found, so bail
			LogDebug("No input files were found with title token strings to exclude.");

			return array();
		}

		$arrNegKwds = array();

		foreach ($inputfiles as $fileItem) {

			$arrRecs = loadCSV($fileItem);
			foreach ($arrRecs as $arrRec) {
				if (array_key_exists('negative_keywords', $arrRec)) {
					$kwd = strtolower($arrRec['negative_keywords']);
					$arrNegKwds[$kwd] = $kwd;
				}
			}
		}
		$negKwdForTokens = array_unique($arrNegKwds, SORT_REGULAR);

		return $negKwdForTokens;
	}

	/**
	 * @param $pattern
	 *
	 * @return string
	 * @throws \Exception
	 */
	private function _scrubRegexSearchString($pattern)
    {
        $delim = '~';
        if(strpos($pattern, $delim) != false)
        {
            $delim = '|';
        }

        $rx = $delim.preg_quote(trim($pattern), $delim).$delim.'i';
        try
        {
            $testMatch = preg_match($rx, "empty");
        }
        catch (\Exception $ex)
        {
            LogError($ex->getMessage());
            if(isDebug() == true) { throw $ex; }
        }
        return $rx;
    }



    /**
     * Initializes the global list of titles we will automatically mark
     * as "not interested" in the final results set.
     * @throws \Exception
     */
    function _loadCompanyRegexesToFilter()
    {
        if(isset($this->companies_regex_to_filter) && count($this->companies_regex_to_filter) > 0)
        {
            // We've already loaded the companies; go ahead and return right away
            LogDebug("Using previously loaded " . count($this->companies_regex_to_filter) . " regexed company strings to exclude." );
            return;
        }
	    $inputfiles = $this->_userBeingMarked->getInputFiles("regex_filter_companies");
        if(!isset($inputfiles) ||  !is_array($inputfiles)) { return; }

	    $regexList = array();
        foreach($inputfiles as $fileItem) {
	        LogDebug("Loading job Company regexes to filter from " . $inputfiles . ".");
	        $loadedCompaniesRegex = loadCSV($fileItem, "match_regex");
	        if (!empty($loadedCompaniesRegex)) {
		        //	        $classCSVFile = new SimpleCSV($fileItem, 'r');
		        //	        $loadedCompaniesRegex= $classCSVFile->readAllRecords(true, array('match_regex'));
		        $regexList = array_merge($regexList, array_column($loadedCompaniesRegex, "match_regex"));
		        LogDebug(count($loadedCompaniesRegex) . " companies found in the file {$fileItem} that will be automatically filtered from job listings.");
	        }
        }
	    $regexList = array_unique($regexList);

        //
        // Add each Company we found in the file to our list in this class, setting the key for
        // each record to be equal to the job Company so we can do a fast lookup later
        //
        if(!empty($regexList) && is_array($regexList))
        {
            foreach($regexList as $rxItem)
            {
                try
                {
                    $rx = $this->_scrubRegexSearchString($rxItem);
                    $this->companies_regex_to_filter[] = $rx;

                }
                catch (\Exception $ex)
                {
                    $strError = "Regex test failed on company regex pattern " . $rxItem .".  Skipping.  Error: '".$ex->getMessage();
                    LogError($strError);
                    if(isDebug() == true) { throw new \ErrorException( $strError); }
                }
            }
        }

        if(count($inputfiles) == 0)
            LogDebug("No file specified for companies regexes to exclude from '" . getArrayValuesAsString($inputfiles) . "'.  Final list will not be filtered." );
        elseif(empty($this->companies_regex_to_filter))
            LogDebug("Could not load regex list for companies to exclude from '" . getArrayValuesAsString($inputfiles) . "'.  Final list will not be filtered." );

        LogMessage("Loaded " . count($this->companies_regex_to_filter). " regexes to use for filtering companies from " . getArrayValuesAsString($inputfiles)  );
    }
}
