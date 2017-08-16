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
require_once dirname(dirname(__FILE__))."/bootstrap.php";
use \Khartnett\Normalization as Normalize;



const C__STR_TAG_AUTOMARKEDJOB__ = "[auto-marked]";
const C__STR_TAG_DUPLICATE_POST__ = "No (Duplicate Job Post?)";
const C__STR_TAG_BAD_TITLE_POST__ = "No (Bad Title & Role)";
const C__STR_TAG_NOT_A_KEYWORD_TITLE_MATCH__ = "No (Not a Keyword Title Match)";
const C__STR_TAG_NOT_EXACT_TITLE_MATCH__ = "No (Not an Exact Title Match)";


class JobsAutoMarker extends ClassJobsSiteCommon
{
    protected $siteName = "JobsAutoMarker";
    protected $arrLatestJobs_UnfilteredByUserInput = array();
    protected $arrMasterJobList = array();
    protected $cbsaList  = array();
    protected $normalizer  = null;
    protected $userMatchedCBSAPlaces  = array();
    protected $cbsaLocSetMapping = array();
    protected $validCityValues = array();

    function __construct($arrJobObjsToMark = array(), $strOutputDirectory = null)
    {
        parent::__construct($strOutputDirectory);

        if (!is_null($arrJobObjsToMark) && count($arrJobObjsToMark) > 0)
            $this->arrMasterJobList = $arrJobObjsToMark;
        else
            $this->arrMasterJobList = getUserJobMatchesForAppRun();

        $this->normalizer = new Normalize();

        $this->cbsaList = loadJSON(__ROOT__.'/include/static/cbsa_list.json');
        $cbsaCityMapping = loadCSV(__ROOT__.'/include/static/us_place_to_csba_mapping.csv', 'PlaceKey');

        foreach($GLOBALS['USERDATA']['configuration_settings']['location_sets'] as $locset)
        {
            if(array_key_exists('location-city', $locset) === true && (array_key_exists('location-statecode', $locset)))
            {
                $cityName = $this->_normalizeLocation_($locset['location-city'])."_".strtoupper($locset['location-statecode']);
                $placekey = strtoupper($cityName);
                $cbsa = $cbsaCityMapping[$placekey];
                $this->cbsaLocSetMapping[$locset['key']] = $cbsa['CBSA'];
            }
        }

        $this->validCities = array();

        $cbsaInUsa = array_unique($this->cbsaLocSetMapping);
        foreach($cbsaCityMapping as $place)
        {
            $placeName = $this->_normalizeLocation_($place['Place'] . ", " . $place['StateCode'] );
            $placeKey = $this->getLocationLookupKey($placeName);
            $this->validCityValues[$placeKey] = $placeName;
            if(array_search($place['CBSA'], $cbsaInUsa) !== false)
            {
                $this->userMatchedCBSAPlaces[$place['PlaceKey']] = $place;
            }
        }

        unset($locset);
        unset($cbsaCityMapping);
        
    }

    function __destruct()
    {
        LogLine("Closing ".$this->siteName." instance of class " . get_class($this), \Scooper\C__DISPLAY_ITEM_DETAIL__);

    }

    public function markJobs()
    {

        if(is_null($this->arrMasterJobList) || count($this->arrMasterJobList) <= 0)
            throw new Exception("No jobs found to auto-mark.");

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Filter the full jobs list looking for duplicates, etc.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        LogLine(PHP_EOL . "**************  Updating jobs list for known filters ***************" . PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);

        $masterCopy = \Scooper\array_copy($this->arrMasterJobList);

        $arrJobs_AutoUpdatable = array_filter($masterCopy, "isJobAutoUpdatable");
        $this->_markJobsList_SetLikelyDuplicatePosts_($arrJobs_AutoUpdatable);
        $this->_markJobsList_SetOutOfArea_($arrJobs_AutoUpdatable);
        $this->_markJobsList_withAutoItems_($arrJobs_AutoUpdatable);
        $newMaster = array_replace_recursive($masterCopy, $arrJobs_AutoUpdatable);

        $this->arrMasterJobList = \Scooper\array_copy($newMaster);
    }

    public function getMarkedJobs()
    {
        return $this->arrMasterJobList;
    }


    private function _markJobsList_withAutoItems_(&$arrJobsList)
    {
        $this->_markJobsList_SearchKeywordsNotFound_($arrJobsList);
        $this->_markJobsList_SetAutoExcludedTitles_($arrJobsList);
        $this->_markJobsList_SetAutoExcludedCompaniesFromRegex_($arrJobsList);
    }





    function _markJobsList_SetLikelyDuplicatePosts_(&$arrJobsList)
    {
        try
        {
            if(count($arrJobsList) == 0) return;

            $origJobMatchForEachCompanyRole = array();
            $dupeJobMatchesForEachCompanyRole = array();

            $nJobsMatched = 0;
            $arrAllJobSiteIds_byCompanyAndRole = array();

            LogLine("Finding Duplicate Job Roles" , \Scooper\C__DISPLAY_ITEM_START__);
            foreach($arrJobsList as $job)
            {
                $indexKey = $job->getJobPosting()->getKeySiteAndPostID();
                $compKey = $job->getJobPosting()->getKeyCompanyAndTitle();
                if(!array_key_exists($compKey, $origJobMatchForEachCompanyRole))
                    $origJobMatchForEachCompanyRole[$compKey] = $job;
                else
                    $dupeJobMatchesForEachCompanyRole[$indexKey] = $job;
            }

            LogLine("Marking jobs as duplicate..." , \Scooper\C__DISPLAY_ITEM_DETAIL__);

            foreach($dupeJobMatchesForEachCompanyRole as $dupeJobMatch)
            {
                $compKey = $dupeJobMatch->getJobPosting()->getKeyCompanyAndTitle();

                //
                // Add a note to the previous listing that it had a new duplicate
                //
                $origJobMatch = $origJobMatchForEachCompanyRole[$compKey];
                $origJobMatch->updateMatchNotes($this->getNotesWithDupeIDAdded($origJobMatch->getMatchNotes(), $dupeJobMatch->getJobPosting()->getKeySiteAndPostID()));
                $origJobMatch->save();

                //
                // Add a note to the duplicate listing that tells user which is the original post
                //
                $dupeJobMatch->setUserMatchStatus("exclude-match");
                $dupeJobMatch->setUserMatchReason(C__STR_TAG_DUPLICATE_POST__ . " " . C__STR_TAG_AUTOMARKEDJOB__);
                $dupeJobMatch->updateMatchNotes($this->getNotesWithDupeIDAdded($dupeJobMatch->getMatchNotes(), $origJobMatch->getJobPosting()->getKeySiteAndPostID() ));
                $dupeJobMatch->save();
            }
            
            LogLine(count($dupeJobMatchesForEachCompanyRole). "/" . countAssociativeArrayValues($arrJobsList) . " jobs have immediately been marked as duplicate based on company/role pairing. " , \Scooper\C__DISPLAY_ITEM_RESULT__);

        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in SetLikelyDuplicatePosts: %s", true);
        }
    }

    private function _normalizeLocation_($locString)
    {
        $stringToNormalize = "111 Bogus St, " . $locString;
        $location = $this->normalizer->parse($stringToNormalize);
        if ($location !== false)
            $locString = $location['city'] . ", " . $location['state'];
        return $locString;
    }

    private function getLocationLookupKey($locString)
    {
        $citystate = $this->_normalizeLocation_($locString);
        return strtoupper(str_replace(" ", "", str_replace("Greater", "", str_replace(", ", "_", $citystate))));

    }

    private function _doesLocationMatchUserSearch($locationKey)
    {
        if (in_array($locationKey, array_keys($this->userMatchedCBSAPlaces)))
        {
            return true;
        }

        $placeKey = array_find_closest_key_match($locationKey, array_keys($this->userMatchedCBSAPlaces));
        if (strncmp($placeKey, $locationKey, 5) == 0 || !array_key_exists($locationKey, array_keys($this->validCityValues))) {
            return true;
        }

        return false;

    }
    private function _markJobsList_SetOutOfArea_(&$arrJobsList)
    {
        try
        {
            if (count($arrJobsList) == 0) return;

            LogLine("Marking Out of Area Jobs", \Scooper\C__DISPLAY_ITEM_START__);

            $arrJobsByLocationKey = array();
            $nJobsSkipped = 0;
            $nJobsMarkedAutoExcluded = 0;
            $nJobsNotMarked = 0;

            LogLine("Building jobs by locations list and excluding failed matches...", \Scooper\C__DISPLAY_ITEM_DETAIL__);
            foreach ($arrJobsList as $jobMatch) {
                $locValue = $jobMatch->getJobPosting()->getLocation();
                $locKey = $this->getLocationLookupKey($locValue);

                if(in_array($jobMatch->getUserMatchStatus(), array("exclude-match")) == 1) {
                    $nJobsSkipped += 1;
                }
                elseif($this->_doesLocationMatchUserSearch($locKey)) {
                    $nJobsNotMarked++;
                }
                else
                {
                    $jobMatch->setUserMatchStatus("exclude-match");
                    $jobMatch->setUserMatchReason("Out of Search Area" . C__STR_TAG_AUTOMARKEDJOB__);
                    $jobMatch->updateMatchNotes("location '" . $locKey . "' did not match CBSA for user's search.");
                    $jobMatch->save();
                    $nJobsMarkedAutoExcluded++;
                }
            }

            assert(count($arrJobsList) == $nJobsMarkedAutoExcluded + $nJobsSkipped + $nJobsNotMarked);

            LogLine("Jobs marked as out of area: marked ".$nJobsMarkedAutoExcluded . "/" . countAssociativeArrayValues($arrJobsList) .", skipped " . $nJobsSkipped . "/" . countAssociativeArrayValues($arrJobsList) .", not marked ". $nJobsNotMarked . "/" . countAssociativeArrayValues($arrJobsList).")" , \Scooper\C__DISPLAY_ITEM_RESULT__);
        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in SetOutOfArea: %s", true);
        }
    }

    private function _markJobsList_SetAutoExcludedCompaniesFromRegex_(&$arrJobsList)
    {
        try
        {
            if(count($arrJobsList) == 0) return;

            $nJobsNotMarked = 0;
            $nJobsMarkedAutoExcluded = 0;

            LogLine("Excluding Jobs by Companies Regex Matches", \Scooper\C__DISPLAY_NORMAL__);
            LogLine("Checking ".count($arrJobsList) ." roles against ". count($GLOBALS['USERDATA']['companies_regex_to_filter']) ." excluded companies.",
                \Scooper\C__DISPLAY_NORMAL__);
            $arrJobs_AutoUpdatable= array_filter($arrJobsList, "isJobAutoUpdatable");
            $nJobsSkipped = count($arrJobsList) - count($arrJobs_AutoUpdatable);

            if(count($arrJobs_AutoUpdatable) > 0 && count($GLOBALS['USERDATA']['companies_regex_to_filter']) > 0)
            {
                foreach($arrJobs_AutoUpdatable as $job)
                {
                    $fMatched = false;
                    // get all the job records that do not yet have an interested value

                    foreach($GLOBALS['USERDATA']['companies_regex_to_filter'] as $rxInput )
                    {
                        if(preg_match($rxInput, \Scooper\strScrub($job['company'], DEFAULT_SCRUB)))
                        {
                            $strJobIndex = $job['key_jobsite_siteid'];
                            $arrJobsList[$strJobIndex]['interested'] = 'No (Wrong Company)' . C__STR_TAG_AUTOMARKEDJOB__;
                            $job->appendJobColumnData($arrJobsList[$strJobIndex], 'match_notes', "|", "Matched regex[". $rxInput ."]");
                            $arrJobsList[$strJobIndex]['date_last_updated'] = getTodayAsString();
                            $nJobsMarkedAutoExcluded++;
                            $fMatched = true;
                            break;
                        }
                        if($fMatched == true) break;
                    }
                    if($fMatched == false)
                    {
                        $nJobsNotMarked++;
                    }

    //                if($fMatched == false)
    //                  LogLine("Company '".$job['company'] ."' was not found in the companies exclusion regex list.  Keeping for review." , \Scooper\C__DISPLAY_ITEM_DETAIL__);

                }
                unset($job);
            }

            LogLine("Jobs marked not interested via companies regex: marked ".$nJobsMarkedAutoExcluded . "/" . countAssociativeArrayValues($arrJobs_AutoUpdatable) .", skipped " . $nJobsSkipped . "/" . countAssociativeArrayValues($arrJobs_AutoUpdatable) .", not marked ". $nJobsNotMarked . "/" . countAssociativeArrayValues($arrJobs_AutoUpdatable).")" , \Scooper\C__DISPLAY_ITEM_RESULT__);
        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in SetAutoExcludedCompaniesFromRegex: %s", true);
        }
    }
    private function getNotesWithDupeIDAdded($strNote, $strNewDupe)
    {
        $strDupeNotes = null;

        $strDupeMarker_Start = "<dupe>";
        $strDupeMarker_End = "</dupe>";
        $strUserNotePart = "";

        if(substr_count($strNote, $strDupeMarker_Start)>0)
        {
            $arrNote = explode($strDupeMarker_Start, $strNote);
            $strUserNotePart = $arrNote[0];
            $strDupeNotes = $arrNote[1];
            $arrDupesListed = explode(";", $strDupeNotes);
            if(count($arrDupesListed) > 3)
            {
                $strDupeNotes = $arrDupesListed[0] . "; " . $arrDupesListed[1] . "; " . $arrDupesListed[2] . "; " . $arrDupesListed[3] . "; and more";
            }

            $strDupeNotes = str_replace($strDupeMarker_End, "", $strDupeNotes);
            $strDupeNotes .= $strDupeNotes ."; ";
        }
        elseif(strlen($strNote) > 0)
        {
            $strUserNotePart = $strNote;
        }

        return (strlen($strUserNotePart) > 0 ? $strUserNotePart . " " . PHP_EOL : "") . $strDupeMarker_Start . $strDupeNotes . $strNewDupe . $strDupeMarker_End;

    }


    private function _getJobsList_MatchingJobTitleKeywords_($arrJobs, $keywordsToMatch, $logTagString = "UNKNOWN")
    {
        try {
            $ret = array("skipped" => array(), "matched" => array(), "notmatched" => array());
            if (count($arrJobs) == 0) return $ret;

            LogLine("Checking " . count($arrJobs) . " roles against " . count($keywordsToMatch) . " keywords in titles. [_getJobsList_MatchingJobTitleKeywords_]", \Scooper\C__DISPLAY_ITEM_DETAIL__);
            $arrMatchedTitles = array();
            $arrNotMatchedTitles = array();
            $arrTitlesWithBlanks = array_filter($arrJobs, "isMarkedBlank");
            $ret["skipped"] = array_filter($arrJobs, "isMarkedNotBlank");

            try {

                foreach ($arrTitlesWithBlanks as $job) {
                    $arrKeywordsMatched = array();
                    $strJobIndex = $job['key_jobsite_siteid'];

                    foreach ($keywordsToMatch as $kywdtoken) {
                        $kwdTokenMatches = array();

                        $matched = substr_count_multi($job['job_title_tokenized'], $kywdtoken, $kwdTokenMatches, true);
                        if (count($kwdTokenMatches) > 0) {
                            $strTitleTokenMatches = getArrayValuesAsString(array_values($kwdTokenMatches), " ", "", false);

                            if (count($kwdTokenMatches) === count($kywdtoken)) {
                                $arrKeywordsMatched[$strTitleTokenMatches] = $kwdTokenMatches;
                            } else {
                                // do nothing
                            }
                        }
                    }

                    if (countAssociativeArrayValues($arrKeywordsMatched) > 0) {
                        $job['keywords_matched'] = $arrKeywordsMatched;
                        $ret['matched'][$strJobIndex] = $job;
                    } else {
                        $job['keywords_matched'] = $arrKeywordsMatched;
                        $ret['notmatched'][$strJobIndex] = $job;
                    }
                }
            } catch (Exception $ex) {
                LogLine('ERROR:  Failed to verify titles against keywords [' . $logTagString . '] due to error: ' . $ex->getMessage(), \Scooper\C__DISPLAY_ERROR__);
                if (isDebug()) {
                    throw $ex;
                }
            }
            LogLine("Processed " . countAssociativeArrayValues($arrJobs) . " titles for auto-marking [" . $logTagString . "]: skipped " . countAssociativeArrayValues($ret['skipped']) . "/" . countAssociativeArrayValues($arrJobs) . "; matched " . countAssociativeArrayValues($ret['matched']) . "/" . countAssociativeArrayValues($arrJobs) . "; not matched " . countAssociativeArrayValues($ret['notmatched']) . "/" . countAssociativeArrayValues($arrJobs), \Scooper\C__DISPLAY_ITEM_RESULT__);

            return $ret;
        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in MatchingJobTitleKeywords: %s", true);
        }

    }


    private function _markJobsList_SearchKeywordsNotFound_(&$arrJobsList)
    {
        try {
            $arrKwdSet = array();
            $arrJobsStillActive = array_filter($arrJobsList, "isMarkedBlank");
            $nStartingBlankCount = countAssociativeArrayValues($arrJobsStillActive);
            foreach ($GLOBALS['USERDATA']['configuration_settings']['searches'] as $search) {
                if (array_key_exists('keywords_array_tokenized', $search)) {
                    foreach ($search['keywords_array_tokenized'] as $kwdset) {
                        $arrKwdSet[$kwdset] = explode(" ", $kwdset);
                    }
                    $arrKwdSet = \Scooper\my_merge_add_new_keys($arrKwdSet, $arrKwdSet);
                }
            }

            $ret = $this->_getJobsList_MatchingJobTitleKeywords_($arrJobsStillActive, $arrKwdSet, "TitleKeywordSearchMatch");
            foreach ($ret['notmatched'] as $job) {
                $strJobIndex = $job['key_jobsite_siteid'];
                $arrJobsList[$strJobIndex]['interested'] = NO_TITLE_MATCHES;
                $arrJobsList[$strJobIndex]['date_last_updated'] = getTodayAsString();
                $job->appendJobColumnData($arrJobsList[$strJobIndex], 'match_notes', "|", "title keywords not matched to terms [" . getArrayValuesAsString($arrKwdSet, "|", "", false) . "]");
            }

            $nEndingBlankCount = countAssociativeArrayValues(array_filter($arrJobsList, "isMarkedBlank"));
            LogLine("Processed " . $nStartingBlankCount . "/" . countAssociativeArrayValues($arrJobsList) . " jobs marking if did not match title keyword search:  updated " . ($nStartingBlankCount - $nEndingBlankCount) . "/" . $nStartingBlankCount . ", still active " . $nEndingBlankCount . "/" . $nStartingBlankCount, \Scooper\C__DISPLAY_ITEM_RESULT__);
        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in SearchKeywordsNotFound: %s", true);
        }

    }

    private function _markJobsList_SetAutoExcludedTitles_(&$arrJobsList)
    {
        try {
            $arrJobsStillActive = array_filter($arrJobsList, "isMarkedBlank");
            $nStartingBlankCount = countAssociativeArrayValues($arrJobsStillActive);

            $ret = $this->_getJobsList_MatchingJobTitleKeywords_($arrJobsStillActive, $GLOBALS['USERDATA']['title_negative_keyword_tokens'], "TitleNegativeKeywords");
            foreach ($ret['matched'] as $job) {
                $strJobIndex = $job['key_jobsite_siteid'];
                $arrJobsList[$strJobIndex]['interested'] = TITLE_NEG_KWD_MATCH;
                $arrJobsList[$strJobIndex]['date_last_updated'] = getTodayAsString();
                $job->appendJobColumnData($arrJobsList[$strJobIndex], 'match_notes', "|", "matched negative keyword title[" . getArrayValuesAsString($job['keywords_matched'], "|", "", false) . "]");
            }
            $nEndingBlankCount = countAssociativeArrayValues(array_filter($arrJobsList, "isMarkedBlank"));
            LogLine("Processed " . $nStartingBlankCount . "/" . countAssociativeArrayValues($arrJobsList) . " jobs marking negative keyword matches:  updated " . ($nStartingBlankCount - $nEndingBlankCount) . "/" . $nStartingBlankCount . ", still active " . $nEndingBlankCount . "/" . $nStartingBlankCount, \Scooper\C__DISPLAY_ITEM_RESULT__);
        }
        catch (Exception $ex)
        {
            handleException($ex, "Error in SetAutoExcludedTitles: %s", true);
        }
    }

} 