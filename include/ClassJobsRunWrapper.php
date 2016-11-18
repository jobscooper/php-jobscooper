<?php
/**
 * Copyright 2014-15 Bryan Selner
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

if (!strlen(__ROOT__) > 0) { define('__ROOT__', dirname(dirname(__FILE__))); }
require_once(__ROOT__.'/include/SitePlugins.php');
require_once(__ROOT__.'/include/ClassMultiSiteSearch.php');
require_once(__ROOT__.'/include/S3Publisher.php');

const C__RESULTS_INDEX_ALL = '***TOTAL_ALL***';
const C__RESULTS_INDEX_USER = '***TOTAL_USER***';

class ClassJobsRunWrapper extends ClassJobsSiteCommon
{
    protected $siteName = "JobRunWrapper";
    protected $classConfig = null;
    protected $arrUserInputJobs = null;
    protected $arrUserInputJobs_Active = null;
    protected $arrUserInputJobs_Inactive = null;
    protected $arrLatestJobs_UnfilteredByUserInput = array();
    protected $arrLatestJobs = array();

    protected $arrEmailAddresses = null;

    protected $arrAllSearchesFromConfig = null;
    protected $arrEmail_PHPMailer_SMTPSetup = null;

    function __construct()
    {

        $this->siteName = "JobsRunner";
        $this->classConfig = new ClassConfig();
        $this->classConfig->initialize();

    }

    function __destruct()
    {
        if(isset($GLOBALS['logger'])) { $GLOBALS['logger']->logLine("Closing ".$this->siteName." instance of class " . get_class($this), \Scooper\C__DISPLAY_ITEM_START__); }

    }

    private function _combineCSVsToExcel($outfileDetails, $arrCSVFiles)
    {
        $spreadsheet = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($spreadsheet, "Excel2007");
        $GLOBALS['logger']->logLine("Creating output XLS file '" . $outfileDetails['full_file_path'] . "'." . PHP_EOL, \Scooper\C__DISPLAY_ITEM_RESULT__);
        $style_all = array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_TOP,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT,
                'wrap' => true
            ),
            'font' => array(
                'size' => 10.0,
            )
        );

        $style_header = array_replace_recursive($style_all, array(
            'alignment' => array(
                'vertical' => PHPExcel_Style_Alignment::VERTICAL_BOTTOM,
                'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
            ),
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb'=>'E1E0F7'),
            ),
            'font' => array(
                'bold' => true,
            )
        ));
        $spreadsheet->getDefaultStyle()->applyFromArray($style_all);

        foreach($arrCSVFiles as $csvFile)
        {
            if(strcasecmp($csvFile['file_extension'], "csv") == 0)
            {
                $objPHPExcelFromCSV = PHPExcel_IOFactory::createReaderForFile($csvFile['full_file_path']);
                $srcFile = $objPHPExcelFromCSV->load($csvFile['full_file_path']);
                $colCount = count($this->getEmptyJobListingRecord());
                $lastCol = ord("A") + $colCount - 1;
                $lastColLetter = chr($lastCol);
                $headerRange = "A" . 1 . ":" . $lastColLetter . "1";

                $sheet = $srcFile->getSheet(0);
                $sheet->getDefaultColumnDimension()->setWidth(50);
                foreach($sheet->getColumnIterator("a", $lastColLetter) as $col)
                {
                    $sheet->getColumnDimension($col->getColumnIndex())->setWidth(40);
                }

                $nameParts = explode("-", $csvFile['file_name_base']);
                $name = "unknown";
                foreach($nameParts as $part) {
                    $int = intval($part);
//                    print $name . " | " . $part . " | " . $int . " | " . (is_integer($int) && $int != 0) . PHP_EOL;
                    if(!(is_integer($int) && $int != 0))
                    {
                        if($name == "unknown")
                            $name = $part;
                        else
                            $name = $name . "-" . $part;
                    }
                }
                $name = substr($name, max([strlen($name)-31, 0]), 31);

//                $name = $nameParts[count($nameParts)-1];
                $n = 1;
                while($spreadsheet->getSheetByName($name) != null)
                {
                    $n++;
                    $name = $name . $n;
                }
                $sheet->setTitle($name);
                $sheet->getStyle($headerRange)->applyFromArray( $style_header );

                $newSheet = $spreadsheet->addExternalSheet($sheet);
                if($spreadsheet->getSheetCount() > 3)
                {
                    $newSheet->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
                }


                $GLOBALS['logger']->logLine("Added data from CSV '" . $csvFile['full_file_path'] . "' to output XLS file." . PHP_EOL, \Scooper\C__DISPLAY_ITEM_RESULT__);
            }
        }

        $spreadsheet->removeSheetByIndex(0);
        $objWriter->save($outfileDetails['full_file_path']);


        return $outfileDetails;

    }



    function RunAll()
    {
        $this->_setSearchesForRun_();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Process the input CSVs of job listings that the user specified.
        // The inactives get added to the full jobs list as the starting jobs
        // The actives will get added at the end so they overwrite any jobs that
        // were found again
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($this->classConfig->getFileDetails('user_input_files', 'jobs') != null) {
            $GLOBALS['logger']->logLine(PHP_EOL . "**************  Loading user-specified jobs list information from " . count($this->classConfig->getFileDetails('user_input_files', 'jobs')) . " CSV files **************  " . PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
            $this->loadUserInputJobsFromCSV();
        }


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // At this point, we have a full list of all the new jobs that have been posted within the user's search parameters
        // completely unfiltered.  Let's save that off now before we update it with the values that user passed in via CSVs.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if ($this->arrSearchesToReturn != null) {
            $GLOBALS['logger']->logLine(PHP_EOL . "************** Get the latest jobs for all searches ****************" . PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
            $this->getLatestRawJobsFromAllSearches();
        } else {
            throw new ErrorException("No searches have been set to be run.");

        }


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Now we can update the full jobs list with the active jobs we loaded from the CSV at the start
        // $this->arrLatestJobs_UnfilteredByUserInput is the unfiltered list;
        // $this->arrLatestJobs is the processed & filtered jobs list
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        if ($this->arrUserInputJobs_Inactive != null) {
            $this->arrLatestJobs = null;
            addJobsToJobsList($this->arrLatestJobs, $this->arrUserInputJobs_Inactive);
            addJobsToJobsList($this->arrLatestJobs, $this->arrLatestJobs_UnfilteredByUserInput);
        }

        if ($this->arrUserInputJobs_Active != null) {
            addJobsToJobsList($this->arrLatestJobs, $this->arrUserInputJobs_Active);
        }


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Filter the full jobs list looking for duplicates, etc.
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $GLOBALS['logger']->logLine(PHP_EOL . "**************  Updating jobs list for known filters ***************" . PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
        $this->_markJobsList_withAutoItems_();


        //
        // For our final output, we want the jobs to be sorted by company and then role name.
        // Create a copy of the jobs list that is sorted by that value.
        //
        $arrFinalJobs_SortedByCompanyRole = array();
        if (countJobRecords($this->arrLatestJobs) > 0) {
            foreach ($this->arrLatestJobs as $job) {
                // Need to add uniq key of job site id to the end or it will collapse duplicate job titles that
                // are actually multiple open posts
                $arrFinalJobs_SortedByCompanyRole [$job['key_company_role'] . "-" . $job['key_jobsite_siteid']] = $job;
            }
        }
        ksort($arrFinalJobs_SortedByCompanyRole);


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Output the full jobs list into a file and into files for different cuts at the jobs list data
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $GLOBALS['logger']->logSectionHeader("Ouputing Results Files", \Scooper\C__DISPLAY_SECTION_START__, \Scooper\C__NAPPFIRSTLEVEL__);
        $GLOBALS['logger']->logSectionHeader("Files Sent To User", \Scooper\C__DISPLAY_SECTION_START__, \Scooper\C__NAPPSECONDLEVEL__);
        $GLOBALS['logger']->logLine(PHP_EOL . "Writing final list of " . count($arrFinalJobs_SortedByCompanyRole) . " jobs to output files." . PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
        $class = null;


        //
        // Output the final files we'll send to the user
        //

        // Output all records that match the user's interest and are still active
        $arrJobs_UserOutput_InterestedOrBlank = null;
        if($GLOBALS['OPTS']['number_days'] > 1)
        {
            $filterUpdatedOrNewSinceLastRun = "isJobUpdatedTodayOrIsInterestedOrBlank";


            //
            // For our final output, we want the jobs to be sorted by company and then role name.
            // Create a copy of the jobs list that is sorted by that value.
            //
            $arrFinalJobs_SortedByDateCompanyRole = array();
            if (countJobRecords($this->arrLatestJobs) > 0) {
                foreach ($this->arrLatestJobs as $job) {
                    // Need to add uniq key of job site id to the end or it will collapse duplicate job titles that
                    // are actually multiple open posts
                    $arrFinalJobs_SortedByDateCompanyRole [$job['job_site_date'] . $job['key_company_role'] . "-" . $job['key_jobsite_siteid']] = $job;
                }
            }
            ksort($arrFinalJobs_SortedByDateCompanyRole);
            $arrJobs_UserOutput_InterestedOrBlank = $arrFinalJobs_SortedByDateCompanyRole;

        }
        else
        {
            $filterUpdatedOrNewSinceLastRun = "isJobUpdatedTodayOrIsInterestedOrBlank";
            $arrJobs_UserOutput_InterestedOrBlank = $arrFinalJobs_SortedByCompanyRole;

        }

        $arrJobs_UpdatedOrInterested = array_filter($arrJobs_UserOutput_InterestedOrBlank, "isMarked_InterestedOrBlank");
        $this->writeRunsJobsToFile($this->classConfig->getFileDetails('output')['full_file_path'], $arrJobs_UpdatedOrInterested, "ClassJobsRunWrapper-UserOutputFile");
        $detailsMainResultsFile = $this->classConfig->getFileDetails('output');

        // Output all records that were automatically excluded
        $dataExcludedJobs = $this->_filterAndWriteListToFile_($arrFinalJobs_SortedByCompanyRole, "isMarked_NotInterested", array("", "ExcludedJobs", "CSV"), "excluded jobs", null, true);

        // Output only new records that haven't been looked at yet
        $this->_outputFilteredJobsListToFile_($arrFinalJobs_SortedByCompanyRole, "isMarked_InterestedOrBlank", "-AllUnmarkedJobs", "CSV");
        $this->_outputFilteredJobsListToFile_($arrFinalJobs_SortedByCompanyRole, "isMarked_InterestedOrBlank", "-AllUnmarkedJobs", "HTML", null, $this->getKeysForHTMLOutput(), true);
        $detailsHTMLFile = $this->__getAlternateOutputFileDetails__("HTML", "", "-AllUnmarkedJobs");

        $arrResultFilesToCombine = array($detailsMainResultsFile, $dataExcludedJobs['file_details']);
        $arrFilesToAttach = array($detailsHTMLFile);

        foreach($this->classConfig->arrFileDetails['user_input_files'] as $inputfile)
        {
            array_push($arrResultFilesToCombine, $inputfile['details']);

        }
//        foreach(array_keys($GLOBALS['USERDATA']) as $key)
//        {
//            $tmpfile = $this->_outputSearchTokensList_($GLOBALS['USERDATA'][$key], $key, $key);
//            array_push($arrResultFilesToCombine, $tmpfile);
//        }
//
        $xlsDetails = $this->__getAlternateOutputFileDetails__("xls", "", "AllResults");
        $xlsOutputFile = $this->_combineCSVsToExcel($xlsDetails, $arrResultFilesToCombine);
        array_push($arrFilesToAttach, $xlsOutputFile);

        $GLOBALS['logger']->logSectionHeader("" . PHP_EOL, \Scooper\C__SECTION_END__, \Scooper\C__NAPPSECONDLEVEL__);


        //
        // Output debugging / interim files if asked to
        //

        if($this->is_OutputInterimFiles() == true) {
            $GLOBALS['logger']->logSectionHeader("DEBUG ONLY:  Writing out interim, developer files (user does not ever see these)..." . PHP_EOL, \Scooper\C__SECTION_BEGIN__, \Scooper\C__NAPPSECONDLEVEL__);

            //
            // Now, output the various subsets of the total jobs list
            //

            $this->_filterAndWriteListToFile_($arrFinalJobs_SortedByCompanyRole, "isJobUpdatedTodayOrIsInterestedOrBlank", array("", "", "CSV"), "updated today", null, false);

            // Output all job records and their values
            $this->_filterAndWriteListToFile_($arrFinalJobs_SortedByCompanyRole, null, array("", "-AllJobs", "CSV"), "all jobs", null, false);

/*
            // Output only records that are new or not marked as excluded (aka "Yes" or "Maybe")
            $this->_outputFilteredJobsListToFile_($arrFinalJobs_SortedByCompanyRole, "isMarked_InterestedOrBlank", "-AllActiveJobs", "CSV");
            $this->_outputFilteredJobsListToFile_($arrFinalJobs_SortedByCompanyRole, "isMarked_InterestedOrBlank", "-AllActiveJobs", "HTML", null, $this->getKeysForHTMLOutput());

            $this->_outputFilteredJobsListToFile_($arrFinalJobs_SortedByCompanyRole, "isJobUpdatedToday", "-UpdatedJobs");
            $this->_outputFilteredJobsListToFile_($arrFinalJobs_SortedByCompanyRole, "isJobUpdatedTodayNotInterested", "-UpdatedExcludedJobs");
*/
            $GLOBALS['logger']->logSectionHeader("" . PHP_EOL, \Scooper\C__SECTION_END__, \Scooper\C__NAPPSECONDLEVEL__);
        }


        $GLOBALS['logger']->logSectionHeader("Generating email content for user" . PHP_EOL, \Scooper\C__SECTION_BEGIN__, \Scooper\C__NAPPSECONDLEVEL__);

        $strResultCountsText = $this->getListingCountsByPlugin("text", $arrFinalJobs_SortedByCompanyRole);
        $strErrs = $GLOBALS['logger']->getCumulativeErrorsAsString();
        $strErrsResult = "";
        if ($strErrs != "" && $strErrs != null) {
            $strErrsResult = $strErrsResult . PHP_EOL . "------------ ERRORS FOUND ------------" . PHP_EOL . $strErrs . PHP_EOL . PHP_EOL . "----------------------------------------" . PHP_EOL . PHP_EOL;
        }

        $strResultText = "Job Scooper Results for " . date("D, M d") . PHP_EOL . $strResultCountsText . PHP_EOL . $strErrsResult;

        $GLOBALS['logger']->logLine($strResultText, \Scooper\C__DISPLAY_SUMMARY__);

        $strResultCountsHTML = $this->getListingCountsByPlugin("html", $arrFinalJobs_SortedByCompanyRole);
        $strErrHTML = preg_replace("/\n/", ("<br>" . chr(10) . chr(13)), $strErrsResult);
        $strResultHTML = $strResultCountsHTML . PHP_EOL . "<pre>" . $strErrHTML . "</pre>" . PHP_EOL;

        $GLOBALS['logger']->logSectionHeader("" . PHP_EOL, \Scooper\C__SECTION_END__, \Scooper\C__NAPPSECONDLEVEL__);

        //
        // Send the email notification out for the completed job
        //
        $this->sendJobCompletedEmail($strResultText, $strResultHTML, $detailsHTMLFile, $arrFilesToAttach);

        $s3 = array("bucket" => \Scooper\get_PharseOptionValue("s3_bucket"), "region" => \Scooper\get_PharseOptionValue("s3_region") );

        if(!is_null($s3['bucket']) && !is_null($s3['region']))
        {
            $s3 = new S3Publisher($s3['bucket'], $s3['region']);
            $s3->publishOutputFiles($GLOBALS['OPTS']['output_subfolder']['directory']);
        }

        //
        // If the user has not asked us to keep interim files around
        // after we're done processing, then delete the interim HTML file
        //
        if ($this->is_OutputInterimFiles() != true) {
            foreach ($arrFilesToAttach as $fileDetail) {
                if (file_exists($fileDetail['full_file_path']) && is_file($fileDetail ['full_file_path'])) {
                    $GLOBALS['logger']->logLine("Deleting local attachment file " . $fileDetail['full_file_path'] . PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
                    unlink($fileDetail['full_file_path']);
                }
            }
        }

        $GLOBALS['logger']->logLine(PHP_EOL."**************  DONE.  Cleaning up.  **************  ".PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
    }

    function getConfig() { return $this->classConfig; }


    private function _setSearchesForRun_()
    {
        $GLOBALS['logger']->logLine(PHP_EOL."Setting up searches for this specific run.".PHP_EOL, \Scooper\C__DISPLAY_SECTION_START__);

        //
        // let's start with the searches specified with the details in the the config.ini
        //
        $arrPossibleSearchesForRun = $this->classConfig->getSearchConfiguration('searches');

        if(isset($arrPossibleSearchesForRun))
        {
            if(count($arrPossibleSearchesForRun) > 0)
                for($z = 0; $z < count($arrPossibleSearchesForRun) ; $z++)
                {
                    $curSearch = $arrPossibleSearchesForRun[$z];

                    $strIncludeKey = 'include_'.$curSearch['site_name'];

                    $valInclude = \Scooper\get_PharseOptionValue($strIncludeKey);

                    if(!isset($valInclude) || $valInclude == 0)
                    {
                        $GLOBALS['logger']->logLine($curSearch['site_name'] . " excluded, so dropping its searches from the run.", \Scooper\C__DISPLAY_ITEM_START__);

                        $arrPossibleSearchesForRun[$z]['key'] = 'EXCLUDED_FOR_RUN__' . $arrPossibleSearchesForRun[$z]['key'];
                    }
                    else
                    {
                        // keep the search
                        $this->arrSearchesToReturn[] = $arrPossibleSearchesForRun[$z];
                    }

                }
        }

        return;

    }

    private function loadUserInputJobsFromCSV()
    {
        $arrAllJobsLoadedFromSrc = null;

        $arrFiles = $this->classConfig->getInputFilesByType("jobs");
//        $arrAllJobsLoadedFromSrc = $this->loadJobsListFromCSVs($this->arrJobCSVUserInputFiles);
        $arrAllJobsLoadedFromSrc = $this->loadJobsListFromCSVs($arrFiles);
        if($arrAllJobsLoadedFromSrc )
        {
            $this->normalizeJobList($arrAllJobsLoadedFromSrc);
            $this->arrUserInputJobs = $arrAllJobsLoadedFromSrc;
        }

        if($this->is_OutputInterimFiles() == true)
        {
            $strDebugInputCSV = $this->classConfig->getFileDetails('output_subfolder')['directory'] . \Scooper\getDefaultFileName("", "_Jobs_From_UserInput", "csv");
            $this->writeJobsListToFile($strDebugInputCSV, $arrAllJobsLoadedFromSrc, true, false, "ClassJobRunner-loadUserInputJobsFromCSV");
        }

        // These will be used at the beginning and end of
        // job processing to filter out jobs we'd previous seen
        // and to make sure our notes get updated on active jobs
        // that we'd seen previously
        //
        //
        // Set a global var with an array of all input cSV jobs marked new or not marked as excluded (aka "Yes" or "Maybe")
        //

        $this->arrUserInputJobs_Active = array_filter($arrAllJobsLoadedFromSrc, "isMarked_InterestedOrBlank");
        $GLOBALS['logger']->logLine(count($this->arrUserInputJobs_Active). " active job listings loaded from user input CSVs.", \Scooper\C__DISPLAY_SUMMARY__);

        //
        // Set a global var with an array of all input CSV jobs that are not in the first set (aka marked Not Interested & Not Blank)
        //
        $this->arrUserInputJobs_Inactive = array_filter($arrAllJobsLoadedFromSrc, "isMarked_NotInterestedAndNotBlank");
        $GLOBALS['logger']->logLine(count($this->arrUserInputJobs_Inactive). " inactive job listings loaded from user input CSVs.", \Scooper\C__DISPLAY_SUMMARY__);

    }

    private function writeRunsJobsToFile($strFileOut, $arrJobsToOutput, $strLogDescriptor, $strExt = "CSV", $keysToOutput = null)
    {

        $this->writeJobsListToFile($strFileOut, $arrJobsToOutput, true, false, "ClassJobRunner-".$strLogDescriptor, $strExt, $keysToOutput);

        if($strExt == "HTML")
            $this->_addCSSStyleToHTMLFile_($strFileOut);

    }

    private function __getAlternateOutputFileDetails__($ext, $strNamePrepend = "", $strNameAppend = "")
    {
        $detailsRet = $this->classConfig->getFileDetails('output_subfolder');
        $detailsRet['file_extension'] = $ext;
        $strTempPath = \Scooper\getFullPathFromFileDetails($detailsRet, $strNamePrepend , $strNameAppend);
        $detailsRet= \Scooper\parseFilePath($strTempPath, false);
        return $detailsRet;
    }

    private function _outputFilteredJobsListToFile_($arrJobsList, $strFilterToApply, $strFileNameAppend, $strExt = "CSV", $strFilterDescription = null, $keysToOutput = null, $fOverrideInterimFileOption = false)
    {
        $ret = $this->_filterAndWriteListToFile_($arrJobsList, $strFilterToApply, array("", $strFileNameAppend, $strExt), $strFilterDescription, $keysToOutput, $fOverrideInterimFileOption);
        if(isset($ret) && isset($ret['data']))
        {
            return $ret['data'];
        }

        return null;
    }

//    private function _outputSearchTokensList_($arrData, $strFileNamePrepend, $dataKeyName = "Unknown")
//    {
////        $details = $this->__getAlternateOutputFileDetails__("CSV", $strFileNamePrepend, "");
////        $filename = $details['full_file_path'];
////        array_unshift($arrData, array($dataKeyName));
////        $objPHPExcel = new PHPExcel();
////        $objPHPExcel->getActiveSheet()->fromArray(array($dataKeyName), null, 'A1');
////        $objPHPExcel->getActiveSheet()->fromArray($arrData, null, 'B1');
////        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "CSV");
////
//////            $spreadsheet->removeSheetByIndex(0);
////        $objWriter->save($filename);
////
////        return $filename;
//    }
//
//    private function _filterAndWriteListToFile_($arrJobsList, $strFilterToApply, $arrOutputSettings = array('prepend'=> "", 'append'=>"", 'ext'=>array('CSV'), 'array_keys_for_output' => null, 'override_output_flag' =>false ), $strFilterDescription = null )
    private function _filterAndWriteListToFile_($arrJobsList, $strFilterToApply, $arrFilePrePostExt, $strFilterDescription = null, $keysToOutput = null, $fOverrideInterimFileOption = false)
    {
        if(!isset($strFilterDescription) && isset($strFilterToApply))
        {
            $strFilterDescription = $strFilterToApply;
        }

        if($arrJobsList == null) { $arrJobsList = $this->arrLatestJobs; }

        $dataRet = array('name'=>$strFilterDescription, 'data'=>$arrJobsList, 'file_details'=>null);
        if(countJobRecords($arrJobsList) == 0) return $dataRet;

        $arrJobs = null;
        if($strFilterToApply == null || $strFilterToApply == "")
        {
            $arrJobs = $arrJobsList;
        }
        else
        {
            $arrJobs = array_filter($arrJobsList, $strFilterToApply);
        }

        $strFileNamePrepend = $arrFilePrePostExt[0];
        $strFileNameAppend = $arrFilePrePostExt[1];
        $strFileNameExt = $arrFilePrePostExt[2];

        if(!isset($strFileNameAppend) && isset($strFilterToApply))
        {
            $strFileNameAppend = $strFilterToApply;
        }

        if(!isset($strFileNameAppend) or strlen($strFileNameAppend) < 0)
        {
            throw new ErrorException("Missing required string to append to file name. File cannot be output since it will not be unique." . $strFilterToApply . " filtered jobs list.");
        }


        //
        // If the user hasn't asked for interim files to be written,
        // just return the filtered jobs.  Don't write the file.
        //
        if($fOverrideInterimFileOption == false && $this->is_OutputInterimFiles() != true) return $dataRet;


        $arrJobsOutput = array();

        if(strcasecmp($strFileNameExt, "HTML") == 0)
        {
            foreach($arrJobs as $job)
            {
                $job['job_title_linked'] = '<a href="'.$job['job_post_url'].'" target="new">'.$job['job_title'].'</a>';
                $arrJobsOutput[] = $job;
            }
        }
        else
        {
            $arrJobsOutput = \Scooper\array_copy($arrJobs);
        }

        $details = $this->__getAlternateOutputFileDetails__($strFileNameExt, $strFileNamePrepend, $strFileNameAppend);

        $strFilteredCSVOutputPath = $details['full_file_path'];
        $this->writeRunsJobsToFile($strFilteredCSVOutputPath, $arrJobsOutput, $strFilterToApply, $strFileNameExt, $keysToOutput);
        $dataRet['file_details'] = $details;
        $dataRet['data'] = $arrJobsOutput;

        $GLOBALS['logger']->logLine($strFilterDescription . " " . count($arrJobsOutput). " job listings output to  " . $strFilteredCSVOutputPath, \Scooper\C__DISPLAY_ITEM_RESULT__);

        return $dataRet;
    }

    //
    // Note:  This function does not take the user's input job listings into account at all.  It
    //        returns the pure new job listings from all the specified searches
    //
    protected function getLatestRawJobsFromAllSearches()
    {

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Download all the job listings for all the users searches
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $GLOBALS['logger']->logLine(PHP_EOL."**************  Starting Run of " . count($this->arrSearchesToReturn) . " Searches  **************  ".PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);


        //
        // the Multisite class handles the heavy lifting for us by executing all
        // the searches in the list and returning us the combined set of new jobs
        // (with the exception of Amazon for historical reasons)
        //

        //
        // TODO:  REMOVE LOCATION SET AND KEYWORD SET CALLS HERE.
        ///       All Searches will have been expanded before this point already as part
        //        of the new configuration class
        //

        $classMulti = new ClassMultiSiteSearch($this->classConfig->getFileDetails('output_subfolder')['directory']);
        $classMulti->addMultipleSearches($this->arrSearchesToReturn, null);
        $arrUpdatedJobs = $classMulti->updateJobsForAllPlugins();
        $this->arrLatestJobs_UnfilteredByUserInput = \Scooper\array_copy($arrUpdatedJobs);
        $this->arrLatestJobs = \Scooper\array_copy($arrUpdatedJobs);
//        addJobsToJobsList($this->arrLatestJobs, $arrUpdatedJobs);

//        $this->_markJobsList_SetAutoExcludedTitles_();
//        $strRawJobsListOutput = \Scooper\getFullPathFromFileDetails($this->classConfig->getFileDetails('output_subfolder'), "", "_rawjobslist_negkwds");
//        $this->writeRunsJobsToFile($strRawJobsListOutput, $this->arrLatestJobs_UnfilteredByUserInput, "rawjobslist_negkwds");



        if($this->is_OutputInterimFiles() == true) {

            //
            // Let's save off the unfiltered jobs list in case we need it later.  The $this->arrLatestJobs
            // will shortly have the user's input jobs applied to it
            //
            $strRawJobsListOutput = \Scooper\getFullPathFromFileDetails($this->classConfig->getFileDetails('output_subfolder'), "", "_rawjobslist_preuser_filtering");
            $this->writeRunsJobsToFile($strRawJobsListOutput, $this->arrLatestJobs_UnfilteredByUserInput, "RawJobsList_PreUserDataFiltering");
            $GLOBALS['logger']->logLine(count($this->arrLatestJobs_UnfilteredByUserInput). " raw, latest job listings from " . count($this->arrSearchesToReturn) . " search(es) downloaded to " . $strRawJobsListOutput, \Scooper\C__DISPLAY_SUMMARY__);
        }

        $detailsBodyContentFile = null;



    }


    function sendJobCompletedEmail($strBodyText = null, $strBodyHTML = null, $detailsHTMLBodyInclude = null, $arrDetailsAttachFiles = array())
    {
        if(isset($GLOBALS['OPTS']['skip_notifications']) && $GLOBALS['OPTS']['skip_notifications'] == 1)
        {
            $GLOBALS['logger']->logLine(PHP_EOL."User set -send_notifications = false so skipping email notification.)".PHP_EOL, \Scooper\C__DISPLAY_NORMAL__);
            return null;
        }

        $messageHtml = "";
        $messageText = "";

        //
        // Setup the plaintext content
        //
        if($strBodyText != null && strlen($strBodyText) > 0)
        {

            //
            // Setup the plaintext message text value
            //
            $messageText = $strBodyText;
            $messageText .= PHP_EOL ;

            //
            // Setup the value for the html version of the message
            //
            $messageHtml  .= $strBodyHTML . "<br>" .PHP_EOL.  "<br>" .PHP_EOL;
            $messageHtml  .= '<H2>New Job Matches</H2>'.PHP_EOL. PHP_EOL;
            $content = $this->_getFullFileContents_($detailsHTMLBodyInclude);
            $messageHtml  .= $content . PHP_EOL. PHP_EOL. "</body></html>";

            $this->_wrapCSSStyleOnHTML_($messageHtml);
        }


        //
        // Add initial email address header values
        //
        $toEmails =$this->classConfig->getEmailsByType("to");
        if(!isset($toEmails) || count($toEmails) < 1 || strlen(current($toEmails)['address']) <= 0)
        {
            $GLOBALS['logger']->logLine("Could not find 'to:' email address in configuration file. Notification will not be sent.", \Scooper\C__DISPLAY_ERROR__);
            return false;
        }

        $bccEmails =$this->classConfig->getEmailsByType("bcc");
        $fromEmails =$this->classConfig->getEmailsByType("from");
        if(isset($fromEmails) && count($fromEmails) >= 1)
        {
            reset($fromEmails);
            $strFromAddys = current($fromEmails)['address'];
            if(count($fromEmails) > 1) $GLOBALS['logger']->logLine("Multiple 'from:' email addresses found. Notification will be from first one only (" . $strFromAddys . ").", \Scooper\C__DISPLAY_MOMENTARY_INTERUPPT__);
        }
        else
        {
            $GLOBALS['logger']->logLine("Could not find 'from:' email address in configuration file. Notification will not be sent.", \Scooper\C__DISPLAY_ERROR__);
            return false;
        }


        $mail = new PHPMailer();
        
        $smtpSettings = $this->classConfig->getSMTPSettings();
        
        if($smtpSettings != null && is_array($smtpSettings))
        {
            $mail->isSMTP();
            $properties = array_keys($smtpSettings);
            foreach($properties as $property)
            {
                $mail->$property = $smtpSettings[$property];
            }

        }
        else
        {
            $mail->isSendmail();
        }

        $strToAddys = "<none>";
        if(isset($toEmails) && count($toEmails) > 0)
        {
            reset($toEmails);
            $strToAddys = "";
            foreach($toEmails as $to)
            {
                $mail->addAddress($to['address'], $to['name']);
                $strToAddys .= (strlen($strToAddys) <= 0 ? "" : ", ") . $to['address'];
            }
        }

        $mail->addBCC("dev@bryanselner.com", 'Jobs for ' . $strToAddys);
        $strBCCAddys = "dev@bryanselner.com";
        if(isset($bccEmails) && count($bccEmails) > 0)
        {
            foreach($bccEmails as $bcc)
            {
                $mail->addBCC($bcc['address'], $bcc['name']);
                $strBCCAddys .= ", " . $bcc['address'];
            }
        }

        $mail->addReplyTo("dev@bryanselner.com", "dev@bryanselner.com" );
        $mail->setFrom(current($fromEmails)['address'], current($fromEmails)['name']);
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );


        $mail->WordWrap = 120;                                          // Set word wrap to 120 characters
        foreach($arrDetailsAttachFiles as $detailsAttach)
            if(isset($detailsAttach) && isset($detailsAttach['full_file_path']))
                $mail->addAttachment($detailsAttach['full_file_path']);        // Add attachments

        $mail->isHTML(true);                                            // Set email format to HTML
        reset($toEmails);

        $mail->Subject = "Newly matched jobs: " . $this->_getRunDateRange_() . " for " . current($toEmails)['name'];

        $mail->Body    = $messageHtml;
        $mail->AltBody = $messageText;

        $ret = $mail->send();
        if($ret != true)
        {
            $GLOBALS['logger']->logLine("Failed to send notification email with error = ".$mail->ErrorInfo, \Scooper\C__DISPLAY_ERROR__);
        }
        else
        {
            $GLOBALS['logger']->logLine("Email notification sent to '" . $strToAddys . "' from '" . $strFromAddys . "' with BCCs to '" . $strBCCAddys ."'", \Scooper\C__DISPLAY_ITEM_RESULT__);
        }
        return $ret;

    }


    private function _getFullFileContents_($detailsFile)
    {
        $content = null;
        $filePath = $detailsFile['full_file_path'];

        if(strlen($filePath) < 0)
        {
            $GLOBALS['logger']->logLine("Unable to get contents from '". var_export($detailsFile, true) ."' to include in email.  Failing notification.", \Scooper\C__DISPLAY_ERROR__);
            return null;
        }

        # Open a file
        $file = fopen( $filePath, "r" );
        if( $file == false )
        {
            $GLOBALS['logger']->logLine("Unable to open file '". $filePath ."' for to get contents for notification mail.  Failing notification.", \Scooper\C__DISPLAY_ERROR__);
            return null;
        }

        # Read the file into a variable
        $size = filesize($filePath);
        $content = fread( $file, $size);

        return $content;
    }


    function parseJobsListForPage($objSimpHTML)
    {
        throw new ErrorException("parseJobsListForPage not supported for class" . get_class($this));
    }
    function parseTotalResultsCount($objSimpHTML)
    {
        throw new ErrorException("parseTotalResultsCount not supported for class " . get_class($this));
    }


    private function getListingCountsByPlugin($fLayoutType, $arrPluginJobsUnfiltered = null)
    {

        $arrCounts = null;
        $arrExcluded = null;
        $arrNoJobUpdates = null;

        $strOut = "                ";
        $arrHeaders = array("New", "Updated", "Auto-Filtered", "For Review" , "Total");

        $arrSitesSearched = null;
        //
        // First, build an array of all the possible job sites
        // and set them to "false", meaning they weren't searched
        //
        foreach( $GLOBALS['JOBSITE_PLUGINS'] as $plugin_setup)
        {
            $arrSitesSearched[$plugin_setup['name']] = false;
        }

        //
        // Now go through the list of searches that were run and
        // set the value to "true" for any job sites that were run
        //
        foreach($this->arrSearchesToReturn as $searchDetails)
        {
            $arrSitesSearched[strtolower($searchDetails['site_name'])] = true;
        }

        if($arrPluginJobsUnfiltered == null || !isset($arrPluginJobsUnfiltered) || !is_array($arrPluginJobsUnfiltered))
            $arrPluginJobsUnfiltered = $this->arrLatestJobs_UnfilteredByUserInput;

        foreach( $GLOBALS['JOBSITE_PLUGINS'] as $plugin_setup)
        {
            $countPluginJobs = 0;
            $strName = $plugin_setup['name'];
            $fWasSearched = $arrSitesSearched[$plugin_setup['name']];
            if($fWasSearched)
            {
                $classPlug = new $plugin_setup['class_name'](null, null);
                if($arrPluginJobsUnfiltered == null || !is_array($arrPluginJobsUnfiltered) || countJobRecords($arrPluginJobsUnfiltered) == 0)
                {
                    $countUpdated = 0;
                    $arrPluginJobs = array();
                }
                else
                {
                    $arrPluginJobs = array_filter($arrPluginJobsUnfiltered, array($classPlug, "isJobListingMine"));
                    $countPluginJobs = countJobRecords($arrPluginJobs);
                    $countUpdated = countJobRecords(array_filter($arrPluginJobs, "isJobUpdatedToday"));
                }

                if($countUpdated == 0)
                {
                    $arrNoJobUpdates[$strName] = $strName . " (" . $countPluginJobs . " total jobs)";
                }
                else
                {
                    $arrCounts[$strName]['name'] = $strName;
                    $arrCounts[$strName]['new_today'] = count(array_filter($arrPluginJobs, "isNewJobToday_Interested_IsBlank"));
                    $arrCounts[$strName]['updated_today'] = $countUpdated;
                    $arrCounts[$strName]['total_not_interested'] = count(array_filter($arrPluginJobs, "isMarked_NotInterested"));
                    $arrCounts[$strName]['total_active'] = count(array_filter($arrPluginJobs, "isMarked_InterestedOrBlank"));
                    $arrCounts[$strName]['total_listings'] = count($arrPluginJobs);
                }
            }
            else
            {
                $arrExcluded[$strName] = $strName;
            }
        }


        if($this->arrUserInputJobs != null && count($this->arrUserInputJobs) > 0)
        {
            $strName = C__RESULTS_INDEX_USER;
            $arrCounts[$strName]['name'] = $strName;
            $arrCounts[$strName]['new_today'] = count(array_filter($this->arrUserInputJobs, "isNewJobToday_Interested_IsBlank"));
            $arrCounts[$strName]['updated_today'] = count(array_filter($this->arrUserInputJobs, "isJobUpdatedToday"));
            $arrCounts[$strName]['total_not_interested'] = count(array_filter($this->arrUserInputJobs, "isMarked_NotInterested"));
            $arrCounts[$strName]['total_active'] = count(array_filter($this->arrUserInputJobs, "isMarked_InterestedOrBlank"));
            $arrCounts[$strName]['total_listings'] = count($this->arrUserInputJobs);
        }

        if($arrPluginJobsUnfiltered != null && count($arrPluginJobsUnfiltered) > 0)
        {
            $strName = C__RESULTS_INDEX_ALL;
            $arrCounts[$strName]['name'] = $strName;
            $arrCounts[$strName]['new_today'] = count(array_filter($arrPluginJobsUnfiltered, "isNewJobToday_Interested_IsBlank"));
            $arrCounts[$strName]['updated_today'] = count(array_filter($arrPluginJobsUnfiltered, "isJobUpdatedToday"));
            $arrCounts[$strName]['total_not_interested'] = count(array_filter($arrPluginJobsUnfiltered, "isMarked_NotInterested"));
            $arrCounts[$strName]['total_active'] = count(array_filter($arrPluginJobsUnfiltered, "isMarked_InterestedOrBlank"));
            $arrCounts[$strName]['total_listings'] = count($arrPluginJobsUnfiltered);
        }


        switch ($fLayoutType)
        {
            case "html":
                $content = $this->_getResultsTextHTML_($arrHeaders, $arrCounts, $arrNoJobUpdates, $arrExcluded);
                break;

            default:
            case "text":
                $content = $this->_getResultsTextPlain_($arrHeaders, $arrCounts, $arrNoJobUpdates, $arrExcluded);
                break;

        }

        return $content;
    }

    private function _printResultsLine_($arrRow, $strType="TEXT")
    {
        if($arrRow == null || !isset($arrRow) || !is_array($arrRow)) return "";

        $strOut = "";
        $fFirstCol = true;

        // Fixup the names for our special case values
        switch($arrRow['name'])
        {
            case C__RESULTS_INDEX_ALL:
                $arrRow['name'] = "Total";
                break;
            case C__RESULTS_INDEX_USER:
                $arrRow['name'] = "User Input";
                break;
        }

        if($strType == "HTML")
        {
            $strOut .=  PHP_EOL . "<tr class='job_scooper'>". PHP_EOL;
        }

        foreach($arrRow as $value)
        {
            switch ($strType)
            {
                case "HTML":
                    if($fFirstCol == true)
                    {
                        $strOut .= "<td class='job_scooper' width='20%' align='left'>" . $value . "</td>" . PHP_EOL;
                        $fFirstCol = false;
                    }
                    else
                        $strOut .= "<td class='job_scooper' width='10%' align='center'>" . $value . "</td>" . PHP_EOL;
                    break;

                case "TEXT":
                default:
                    $strOut = $strOut . sprintf("%-18s", $value);
                    break;
            }
        }
        if($strType == "HTML")
        {
            $strOut .=  PHP_EOL . "</tr>". PHP_EOL;
        }

        $strOut .=  PHP_EOL;
        return $strOut;
    }

    private function _getResultsTextPlain_($arrHeaders, $arrCounts, $arrNoJobUpdates, $arrExcluded)
    {
        $strOut = "";
        $arrCounts_TotalAll = null;
        $arrCounts_TotalUser = null;

        if($arrCounts != null && count($arrCounts) > 0)
        {
            $strOut = $strOut . sprintf("%-18s", "Site");
            foreach($arrHeaders as $value)
            {
                $strOut = $strOut . sprintf("%-18s", $value);
            }
            $strOut .=  PHP_EOL . sprintf("%'-100s","") . PHP_EOL;

            usort($arrCounts, "sortByCountDesc");
            foreach($arrCounts as $site)
            {
                if($site['name'] == C__RESULTS_INDEX_ALL) {
                    $arrCounts_TotalAll = $site;
                } elseif($site['name'] == C__RESULTS_INDEX_USER) {
                    $arrCounts_TotalUser = $site;
                }
                else
                {
                    $strOut .= $this->_printResultsLine_($site, "TEXT");
                }
            }


            $strOut .= sprintf("%'=100s","") . PHP_EOL;
            $strOut .= $this->_printResultsLine_($arrCounts_TotalUser);
            $strOut .= $this->_printResultsLine_($arrCounts_TotalAll);
            $strOut .= PHP_EOL;
        }

        if($arrNoJobUpdates != null && count($arrNoJobUpdates) > 0)
        {
            sort($arrNoJobUpdates);
            $strOut = $strOut . PHP_EOL .  "No jobs were updated for " . \Scooper\getTodayAsString() . " on these sites: " . PHP_EOL;

            foreach($arrNoJobUpdates as $site)
            {
                $strOut = $strOut . "     - ". $site .PHP_EOL;
            }

        }

        if($arrExcluded != null && count($arrExcluded) > 0)
        {
            sort($arrExcluded);
            $strExcluded = getArrayValuesAsString($arrExcluded, ", ", "Sites excluded by user or settings: ", false);
            $strOut .= $strExcluded;
        }


        return $strOut;
    }

    private function _getRunDateRange_()
    {
        $strRangeStartDate = "";
        $startDate = new DateTime();
        $strMod = "-".$GLOBALS['OPTS']['number_days']." days";
        $startDate = $startDate->modify($strMod);
        $today = new DateTime();
        $strDateRange = $startDate->format('D, M d') . " - " . $today->format('D, M d');
        return $strDateRange;
    }

    private function _getResultsTextHTML_($arrHeaders, $arrCounts, $arrNoJobUpdates, $arrExcluded)
    {
        $arrCounts_TotalAll = null;
        $arrCounts_TotalUser = null;
        $strOut = "<div class='job_scooper outer'>";

        $strOut  .= "<H2>Job Scooper Results: " . $this->_getRunDateRange_() . "</H2>".PHP_EOL. PHP_EOL;

        if($arrCounts != null && count($arrCounts) > 0)
        {
            $strOut .= "<table id='resultscount' class='job_scooper'>" . PHP_EOL . "<thead>". PHP_EOL;
            $strOut .= "<th class='job_scooper' width='20%' align='left'>Job Site</td>" . PHP_EOL;

            foreach($arrHeaders as $value)
            {
                $strOut .= "<th class='job_scooper' width='10%' align='center'>" . $value . "</th>" . PHP_EOL;
            }
            $strOut .=  PHP_EOL . "</thead>". PHP_EOL;

            usort($arrCounts, "sortByCountDesc");
            foreach($arrCounts as $site)
            {
                if($site['name'] == C__RESULTS_INDEX_ALL) {
                    $arrCounts_TotalAll = $site;
                } elseif($site['name'] == C__RESULTS_INDEX_USER) {
                    $arrCounts_TotalUser = $site;
                }
                else
                {
                    $strOut .= $this->_printResultsLine_($site, "HTML");
                }
            }

            $strOut .=  PHP_EOL . "<tr class='job_scooper totaluser'>". PHP_EOL;
            $strOut .= $this->_printResultsLine_($arrCounts_TotalUser, "HTML");
            $strOut .=  PHP_EOL . "</tr><tr class='job_scooper totalall'>". PHP_EOL;
            $strOut .= $this->_printResultsLine_($arrCounts_TotalAll, "HTML");
            $strOut .=  PHP_EOL . "</tr>". PHP_EOL;

            $strOut .=  PHP_EOL . "</table><br><br>". PHP_EOL. PHP_EOL;
        }

        if($arrNoJobUpdates != null && count($arrNoJobUpdates) > 0)
        {
            sort($arrNoJobUpdates);
            $strOut .=  PHP_EOL . "<div class='job_scooper section'>". PHP_EOL;
            $strOut .=  PHP_EOL .  "No updated jobs for " . \Scooper\getTodayAsString() . " on these sites: " . PHP_EOL;
            $strOut .=  PHP_EOL . "<ul class='job_scooper'>". PHP_EOL;

            foreach($arrNoJobUpdates as $site)
            {
                $strOut .=  "<li>". $site . "</li>". PHP_EOL;
            }

            $strOut .=  PHP_EOL . "</ul></div><br><br>". PHP_EOL;
        }

        if($arrExcluded != null && count($arrExcluded) > 0)
        {
            sort($arrExcluded);

            $strOut .=  PHP_EOL . "<div class='job_scooper section'>". PHP_EOL;
            $strExcluded = getArrayValuesAsString($arrExcluded, ", ", "", false);

            $strOut .=  PHP_EOL .  "<span style=\"font-size: xx-small;color: #8e959c;\">Excluded sites for this run:" . PHP_EOL;
            $strOut .= $strExcluded;
            $strOut .= "</span>" . PHP_EOL;


        }
        $strOut .= "</div";

        return $strOut;
    }

    private function _addCSSStyleToHTMLFile_($strFilePath)
    {
        $strHTMLContent = file_get_contents($strFilePath);
        $retWrapped = $this->_wrapCSSStyleOnHTML_($strHTMLContent);
        file_put_contents($strFilePath, $retWrapped);
    }

    private function _wrapCSSStyleOnHTML_($strHTML)
    {
        $cssToInlineStyles = new \TijsVerkoyen\CssToInlineStyles\CssToInlineStyles();
        $css = file_get_contents(dirname(dirname(__FILE__)) . '/include/CSVTableStyle.css');
        $cssToInlineStyles->setHTML($strHTML);
        $cssToInlineStyles->setCSS($css);
        return $cssToInlineStyles->convert();
    }


    private function getKeysForHTMLOutput()
    {
        return array(
            'company',
//            'job_title',
            'job_title_linked',
//            'job_post_url',
//            'job_site_date' =>'',
//            'interested',
//            'match_notes',
//            'status',
//            'last_status_update',
            'location',
//            'job_site_category',
//            'job_site',
//            'job_id',
//            'key_jobsite_siteid',
//            'key_company_role',
//            'date_last_updated',
        );
    }



    private function _markJobsList_withAutoItems_()
    {
        $this->_markJobsList_SetLikelyDuplicatePosts_();
        $this->_markJobsList_SearchKeywordsNotFound_();
        $this->_markJobsList_SetAutoExcludedTitles_();
        $this->_markJobsList_SetAutoExcludedCompaniesFromRegex_();
    }




    private function _markJobsList_SetLikelyDuplicatePosts_()
    {
        if(count($this->arrLatestJobs) == 0) return;

        $nJobsMatched = 0;

        $arrKeys_CompanyAndRole = array_column ( $this->arrLatestJobs, 'key_company_role');
        $arrKeys_JobSiteAndJobID = array_column ( $this->arrLatestJobs, 'key_jobsite_siteid');


        $arrUniqIds = array_unique($arrKeys_CompanyAndRole);
        $nUniqJobs = countAssociativeArrayValues($arrUniqIds);
        $arrOneJobListingPerCompanyAndRole = array_unique_multidimensional(array_combine($arrKeys_JobSiteAndJobID, $arrKeys_CompanyAndRole));
        $arrLookup_JobListing_ByCompanyRole = array_flip($arrOneJobListingPerCompanyAndRole);

        $GLOBALS['logger']->logLine("Marking Duplicate Job Roles" , \Scooper\C__DISPLAY_SECTION_START__);
        $GLOBALS['logger']->logLine("Auto-marking" . $nUniqJobs . " duplicated froms from " . countAssociativeArrayValues($this->arrLatestJobs) . " total jobs based on company/role pairing. " , \Scooper\C__DISPLAY_ITEM_DETAIL__);

        foreach($this->arrLatestJobs as $job)
        {
            $strCurrentJobIndex = getArrayKeyValueForJob($job);
            if(!isMarkedInterested_IsBlank($job))
            {
                continue;  // only mark dupes that haven't yet been marked with anything
            }

            $indexPrevListingForCompanyRole = $arrLookup_JobListing_ByCompanyRole[$job['key_company_role']];
            // Another listing already exists with that title at that company
            // (and we're not going to be updating the record we're checking)
            if($indexPrevListingForCompanyRole != null && strcasecmp($indexPrevListingForCompanyRole, $job['key_jobsite_siteid'])!=0)
            {

                //
                // Add a note to the previous listing that it had a new duplicate
                //
                appendJobColumnData($this->arrLatestJobs[$indexPrevListingForCompanyRole], 'match_notes', "|", $this->getNotesWithDupeIDAdded($this->arrLatestJobs[$indexPrevListingForCompanyRole]['match_notes'], $job['key_jobsite_siteid'] ));
                $this->arrLatestJobs[$indexPrevListingForCompanyRole] ['date_last_updated'] = \Scooper\getTodayAsString();

                $this->arrLatestJobs[$strCurrentJobIndex]['interested'] =  C__STR_TAG_DUPLICATE_POST__ . " " . C__STR_TAG_AUTOMARKEDJOB__;
                appendJobColumnData($this->arrLatestJobs[$strCurrentJobIndex], 'match_notes', "|", $this->getNotesWithDupeIDAdded($this->arrLatestJobs[$strCurrentJobIndex]['match_notes'], $indexPrevListingForCompanyRole ));
                $this->arrLatestJobs[$strCurrentJobIndex]['date_last_updated'] = \Scooper\getTodayAsString();

                $nJobsMatched++;
            }

        }

        $strTotalRowsText = "/".count($this->arrLatestJobs);
        $GLOBALS['logger']->logLine("Marked  ".$nJobsMatched .$strTotalRowsText ." roles as likely duplicates based on company/role. " , \Scooper\C__DISPLAY_ITEM_RESULT__);

    }

    private function _markJobsList_SetAutoExcludedCompaniesFromRegex_()
    {
        if(count($this->arrLatestJobs) == 0) return;

        $nJobsNotMarked = 0;
        $nJobsMarkedAutoExcluded = 0;

        $GLOBALS['logger']->logLine("Excluding Jobs by Companies Regex Matches", \Scooper\C__DISPLAY_ITEM_START__);
        $GLOBALS['logger']->logLine("Checking ".count($this->arrLatestJobs) ." roles against ". count($GLOBALS['USERDATA']['companies_regex_to_filter']) ." excluded companies.", \Scooper\C__DISPLAY_ITEM_DETAIL__);
        $arrJobs_AutoUpdatable= array_filter($this->arrLatestJobs, "isJobAutoUpdatable");
        $nJobsSkipped = count($this->arrLatestJobs) - count($arrJobs_AutoUpdatable);

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
                        $strJobIndex = getArrayKeyValueForJob($job);
                        $this->arrLatestJobs[$strJobIndex]['interested'] = 'No (Wrong Company)' . C__STR_TAG_AUTOMARKEDJOB__;
                        appendJobColumnData($this->arrLatestJobs[$strJobIndex], 'match_notes', "|", "Matched regex[". $rxInput ."]");
                        appendJobColumnData($this->arrLatestJobs[$strJobIndex], 'match_details',"|", "excluded_company");
                        $this->arrLatestJobs[$strJobIndex]['date_last_updated'] = \Scooper\getTodayAsString();
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
//                  $GLOBALS['logger']->logLine("Company '".$job['company'] ."' was not found in the companies exclusion regex list.  Keeping for review." , \Scooper\C__DISPLAY_ITEM_DETAIL__);

            }
        }
        $GLOBALS['logger']->logLine("Jobs marked not interested via companies regex: marked ".$nJobsMarkedAutoExcluded . "/" . countAssociativeArrayValues($arrJobs_AutoUpdatable) .", skipped " . $nJobsSkipped . "/" . countAssociativeArrayValues($arrJobs_AutoUpdatable) .", not marked ". $nJobsNotMarked . "/" . countAssociativeArrayValues($arrJobs_AutoUpdatable).")" , \Scooper\C__DISPLAY_ITEM_RESULT__);
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
        $ret = array("skipped" => array(), "matched" => array(), "notmatched" => array());
        if(count($arrJobs) == 0) return $ret;

        $GLOBALS['logger']->logLine("Checking ".count($arrJobs) ." roles against ". count($keywordsToMatch) ." keywords in titles. [_getJobsList_MatchingJobTitleKeywords_]", \Scooper\C__DISPLAY_ITEM_DETAIL__);
        $arrMatchedTitles = array();
        $arrNotMatchedTitles = array();
        $arrTitlesWithBlanks= array_filter($arrJobs, "isMarkedInterested_IsBlank");
        $ret["skipped"] = array_filter($arrJobs, "isMarkedInterested_NotBlank");

        try
        {
            $arrTitlesTokened = tokenizeMultiDimensionArray($arrTitlesWithBlanks,  "jobList", "job_title", "key_jobsite_siteid");

            foreach($arrTitlesTokened as $job)
            {
                $arrKeywordsMatched = array();
                $strJobIndex = getArrayKeyValueForJob($job);
                $job['job_title_tokenized'] = join(" ", explode("|", $job['tokenized']));
                $this->arrLatestJobs[$strJobIndex]['job_title_tokenized'] = $job['job_title_tokenized'];

                foreach($keywordsToMatch as $kywdtoken)
                {
                    $kwdTokenMatches = array();

                    $matched = substr_count_multi($job['job_title_tokenized'], $kywdtoken, $kwdTokenMatches, true);
                    if(count($kwdTokenMatches) > 0)
                    {
                        $strTitleTokenMatches = getArrayValuesAsString(array_values($kwdTokenMatches), " ", "", false );

                        if(count($kwdTokenMatches) === count($kywdtoken))
                        {
                            $arrKeywordsMatched[$strTitleTokenMatches] = $kwdTokenMatches;
                        }
                        else
                        {
                            // do nothing
                        }
                    }
                }

                if(countAssociativeArrayValues($arrKeywordsMatched) > 0)
                {
                    $job['keywords_matched'] = $arrKeywordsMatched;
                    $ret['matched'][$strJobIndex] = $job;
                }
                else
                {
                    $job['keywords_matched'] = $arrKeywordsMatched;
                    $ret['notmatched'][$strJobIndex] = $job;
                }
            }
        }
        catch (Exception $ex)
        {
            $GLOBALS['logger']->logLine('ERROR:  Failed to verify titles against keywords [' . $logTagString . '] due to error: '. $ex->getMessage(), \Scooper\C__DISPLAY_ERROR__);
            if(isDebug()) { throw $ex; }
        }
        $GLOBALS['logger']->logLine("Processed " . countAssociativeArrayValues($arrJobs) . " titles for auto-marking [" . $logTagString . "]: skipped " . countAssociativeArrayValues($ret['skipped']). "/" . countAssociativeArrayValues($arrJobs) ."; matched ". countAssociativeArrayValues($ret['matched']) . "/" . countAssociativeArrayValues($arrJobs) ."; not matched " . countAssociativeArrayValues($ret['notmatched']). "/" . countAssociativeArrayValues($arrJobs)  , \Scooper\C__DISPLAY_ITEM_RESULT__);

        return $ret;
    }


    private function _markJobsList_SearchKeywordsNotFound_()
    {
        $arrKwdSet = array();
        $arrJobsStillActive = array_filter($this->arrLatestJobs, "isMarkedInterested_IsBlank");
        $nStartingBlankCount = countAssociativeArrayValues($arrJobsStillActive);
        foreach($this->arrSearchesToReturn as $search)
        {
            foreach($search['tokenized_keywords'] as $kwdset)
            {
                $arrKwdSet[$kwdset] = explode(" ", $kwdset);
            }
            $arrKwdSet = \Scooper\my_merge_add_new_keys($arrKwdSet, $arrKwdSet);
        }

        $ret = $this->_getJobsList_MatchingJobTitleKeywords_($arrJobsStillActive, $arrKwdSet, "TitleKeywordSearchMatch");
        foreach($ret['notmatched'] as $job)
        {
            $strJobIndex = getArrayKeyValueForJob($job);
            $this->arrLatestJobs[$strJobIndex]['interested'] = NO_TITLE_MATCHES;
            $this->arrLatestJobs[$strJobIndex]['date_last_updated'] = \Scooper\getTodayAsString();
            appendJobColumnData($this->arrLatestJobs[$strJobIndex], 'match_notes', "|", "title keywords not matched to terms [". getArrayValuesAsString($arrKwdSet, "|", "", false)  ."]");
            appendJobColumnData($this->arrLatestJobs[$strJobIndex], 'match_details',"|", NO_TITLE_MATCHES);
        }

        $nEndingBlankCount = countAssociativeArrayValues(array_filter($this->arrLatestJobs, "isMarkedInterested_IsBlank"));
        $GLOBALS['logger']->logLine("Processed " . $nStartingBlankCount . "/" . countAssociativeArrayValues($this->arrLatestJobs) . " jobs marking if did not match title keyword search:  updated ". ($nStartingBlankCount - $nEndingBlankCount) . "/" . $nStartingBlankCount  . ", still active ". $nEndingBlankCount . "/" . $nStartingBlankCount, \Scooper\C__DISPLAY_ITEM_RESULT__);

    }

    private function _markJobsList_SetAutoExcludedTitles_()
    {
        $arrJobsStillActive = array_filter($this->arrLatestJobs, "isMarkedInterested_IsBlank");
        $nStartingBlankCount = countAssociativeArrayValues($arrJobsStillActive);

        $ret = $this->_getJobsList_MatchingJobTitleKeywords_($arrJobsStillActive, $GLOBALS['USERDATA']['title_negative_keyword_tokens'], "TitleNegativeKeywords");
        foreach($ret['matched'] as $job)
        {
            $strJobIndex = getArrayKeyValueForJob($job);
            $this->arrLatestJobs[$strJobIndex]['interested'] = TITLE_NEG_KWD_MATCH;
            $this->arrLatestJobs[$strJobIndex]['date_last_updated'] = \Scooper\getTodayAsString();
            appendJobColumnData($this->arrLatestJobs[$strJobIndex], 'match_notes', "|", "matched negative keyword title[". getArrayValuesAsString($job['keywords_matched'], "|", "", false)  ."]");
            appendJobColumnData($this->arrLatestJobs[$strJobIndex], 'match_details',"|", TITLE_NEG_KWD_MATCH);
        }
        $nEndingBlankCount = countAssociativeArrayValues(array_filter($this->arrLatestJobs, "isMarkedInterested_IsBlank"));
        $GLOBALS['logger']->logLine("Processed " . $nStartingBlankCount . "/" . countAssociativeArrayValues($this->arrLatestJobs) . " jobs marking negative keyword matches:  updated ". ($nStartingBlankCount - $nEndingBlankCount) . "/" . $nStartingBlankCount  . ", still active ". $nEndingBlankCount . "/" . $nStartingBlankCount, \Scooper\C__DISPLAY_ITEM_RESULT__);


    }



} 