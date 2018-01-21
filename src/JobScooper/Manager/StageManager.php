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
namespace JobScooper\Manager;

use JobScooper\Builders\JobSitePluginBuilder;
use JobScooper\Builders\SearchBuilder;
use JobScooper\DataAccess\User;
use JobScooper\DataAccess\UserSearchSiteRun;
use JobScooper\StageProcessor\JobsAutoMarker;
use JobScooper\StageProcessor\NotifierDevAlerts;
use JobScooper\StageProcessor\NotifierJobAlerts;
use JobScooper\Builders\ConfigBuilder;


const JOBLIST_TYPE_UNFILTERED = "unfiltered";
const JOBLIST_TYPE_MARKED = "marked";
const JSON_FILENAME = "-alljobsites.json";

/**
 * Class StageManager
 * @package JobScooper\Manager
 */
class StageManager
{
    protected $JobSiteName = "StageManager";
    protected $classConfig = null;

	/**
	 * @throws \Exception
	 */
	public function runAll()
    {
        try {
	        $this->classConfig = new ConfigBuilder();

	        $this->classConfig->initialize();

	        /*
	         * Run specific stages requested via command-line
	         */
	        $user = User::getCurrentUser();

	        $arrRunStages = getConfigurationSetting("command_line_args.stages");
            if (!empty($arrRunStages)) {

	            foreach ($arrRunStages as $stage) {
                    $stageFunc = "doStage" . $stage;
                    try {
                        call_user_func(array($this, $stageFunc), $user);
                    } catch (\Exception $ex) {
                        throw new \Exception("Error:  failed to call method \$this->" . $stageFunc . "() for " . $stage . " from option --StageProcessor " . join(",", $arrRunStages) . ".  Error: " . $ex);
                    }
                }
            } else {
	            /*
				 * If no stage was specifically requested, we default to running stages 1 - 3
				 */
                $this->doStage1($user);
                $this->doStage2($user);
                $this->doStage3($user);
            }
        } catch (\Exception $ex) {
	        handleException($ex, null, true);
		}
        finally
        {
        	try
	        {
	        	$this->doFinalStage();
	        } catch (\Exception $ex) {
			    handleException($ex, null, true);
		    }
        }
    }

	/**
	 * @param User $user
	 * @throws \Exception
	 */
	public function doStage1(User $user)
    {

        startLogSection("Stage 1: Downloading Latest Matching Jobs ");
	    $arrSearchesToRunBySite = $user->getUserSearchSiteRuns();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // OK, now we have our list of searches & sites we are going to actually run
        // Let's go get the jobs for those searches
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($arrSearchesToRunBySite != null)
        {
            LogMessage(PHP_EOL . "**************  Starting Run of Searches for " . count($arrSearchesToRunBySite) . " Job Sites **************  " . PHP_EOL);

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //
            // Run each plugin, set the searches to run and then download the jobs from that jobsite
            //
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            try {
                $jobsites = JobSitePluginBuilder::getIncludedJobSites();
                if(!empty($jobsites))
	                foreach($arrSearchesToRunBySite as $jobsiteKey => $searches)
	                {
	                    $plugin = null;
	                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	                    //
	                    // Add the user's searches to the plugin
	                    //
	                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	                    startLogSection("Setting up " . count($searches) . " search(es) for ". $jobsiteKey . "...");
	                    try
	                    {
	                        $site = $jobsites[$jobsiteKey];
	                        $site->addSearches($searches, $user);
	                    }
	                    catch (\Exception $classError)
	                    {
	                        handleException($classError, "Unable to add searches to {$jobsiteKey} plugin: %s", $raise = false);
	                    }
	                    finally
	                    {
	                        endLogSection(" added ". $jobsiteKey . " searches.");
	                    }

	                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	                    //
	                    // Download all the job listings for all the users searches for this plugin
	                    //
	                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	                    try
	                    {
	                        startLogSection("Downloading updated jobs on " . count($searches) . " search(es) for ". $jobsiteKey . "...");
	                        $site->downloadLatestJobsForAllSearches();
	                    }
	                    catch (\Exception $classError)
	                    {
	                        handleException($classError, $jobsiteKey . " failed to download job postings: %s", $raise = false);
	                    }
	                    finally
	                    {
	                        endLogSection("Job downloads have ended for ". $jobsiteKey . ".");
	                    }
	                }
            } catch (\Exception $ex) {
                handleException($ex, null, false);
            }

        } else {
            LogWarning("No searches have been set to be run.");
        }

        endLogSection("Stage 1");
    }

	/**
	 * @param User $user
	 * @throws \Exception
	 */
	public function doStage2(User $user)
    {
        
        try {
	        startLogSection("Stage 2:  Auto-marking all user job matches...");
            $marker = new JobsAutoMarker($user);
            $marker->markJobs();
        } catch (\Exception $ex) {
            handleException($ex, null, true);
        }
        finally
        {
            endLogSection("End of stage 2 (auto-marking)");
        }
    }

	/**
	 * @param \JobScooper\DataAccess\User $user
	 *
	 * @throws \Exception
	 */
	public function doStage3(User $user)
	{
		try {
			startLogSection("Stage 3: Notifying User");
			$notifier = new NotifierJobAlerts();
			$notifier->processRunResultsNotifications($user);
		} catch (\Exception $ex) {
			handleException($ex, null, true);
		} catch (\PhpOffice\PhpSpreadsheet\Style\Exception $ex) {
			handleException(null, $ex->getMessage(), true);
		}
		finally
		{
			endLogSection("End of stage 3 (notifying user)");
		}
	}


	/**
	 * @param \JobScooper\DataAccess\User $user
	 *
	 * @throws \Exception
	 */
	public function doStage4(User $user)
	{
		try {
			startLogSection("Stage 4: Send a Weekly Recap to User");
			$notify = new NotifierJobAlerts();
			$notify->processWeekRecapNotifications($user);
		} catch (\Exception $ex) {
			handleException($ex, null, true);
		} catch (\PhpOffice\PhpSpreadsheet\Style\Exception $ex) {
			handleException(null, $ex->getMessage(), true);
		}
		finally
		{
			endLogSection("End of stage 4 (send weekly recap)");
		}
	}


	/**
	 * @throws \Exception
	 */
	public function doFinalStage()
    {
	    try {
		    startLogSection("Final Stage: Developer Alerts + Cleanup");
		    $devNotifier = new NotifierDevAlerts();
		    $devNotifier->processPluginErrors();
	    } catch (\Exception $ex) {
		    handleException($ex, null, true);
	    }
	    finally
	    {
		    endLogSection("End of final stage (dev alerts + cleanup)");
	    }

    }
}