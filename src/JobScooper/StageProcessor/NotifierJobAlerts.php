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
namespace JobScooper\StageProcessor;




//Import PHPMailer classes into the global namespace
use JobScooper\Manager\JobsMailManager;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use JobScooper\DataAccess\JobPosting as JobPosting;
use PHPExcel;
use PHPExcel_IOFactory;
use PHPExcel_Worksheet;
use PHPExcel_Style_Alignment;
use PHPExcel_Style_Fill;
use ErrorException;
use InvalidArgumentException;

class NotifierJobAlerts extends JobsMailManager
{
    protected $siteName = "NotifierJobAlerts";
    protected $arrAllUnnotifiedJobs = array();
    private $_arrJobSitesForRun = null;

    function __construct()
    {
        parent::__construct(false);
    }

    function __destruct()
    {
        if(isset($GLOBALS['logger'])) { logLine("Closing ".$this->siteName." instance of class " . get_class($this), \C__DISPLAY_ITEM_START__); }
    }

    private function _combineCSVsToExcel($outfileDetails, $arrCSVFiles)
    {
        $spreadsheet = new PHPExcel();
        $objWriter = PHPExcel_IOFactory::createWriter($spreadsheet, "Excel2007");
        logLine("Creating output XLS file '" . $outfileDetails['full_file_path'] . "'." . PHP_EOL, \C__DISPLAY_ITEM_RESULT__);
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
                $colCount = count($this->getKeysForUserCSVOutput());
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


                logLine("Added data from CSV '" . $csvFile['full_file_path'] . "' to output XLS file." . PHP_EOL, \C__DISPLAY_ITEM_RESULT__);
            }
        }

        $spreadsheet->removeSheetByIndex(0);
        $objWriter->save($outfileDetails['full_file_path']);


        return $outfileDetails;

    }


    protected function _isIncludedJobSite($var)
    {
        $sites = $this->_getJobSitesRunRecently();

        return in_array(cleanupSlugPart($var->getJobPosting()->getJobSiteKey()), $sites);

    }

    function processNotifications()
    {
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        // Output the full jobs list into a file and into files for different cuts at the jobs list data
        //
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $GLOBALS['logger']->logSectionHeader("Writing Results Files", \C__DISPLAY_SECTION_START__, \C__NAPPFIRSTLEVEL__);
        $GLOBALS['logger']->logSectionHeader("Files Sent To User", \C__DISPLAY_SECTION_START__, \C__NAPPSECONDLEVEL__);
        $class = null;


        //
        // Output the final files we'll send to the user
        //

        // Output all records that match the user's interest and are still active
        $detailsMainResultsXLSFile = getFilePathDetailsFromString(generateOutputFileName("results", "xls", true, 'notifications'), \C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED);;
        $arrFilesToAttach = array();
        $arrResultFilesToCombine = array();

        $this->arrAllUnnotifiedJobs = getAllUserMatchesNotNotified();
        if(is_null($this->arrAllUnnotifiedJobs) || count($this->arrAllUnnotifiedJobs) <= 0)
        {
            LogLine("No new jobs found to notify user about.", C__DISPLAY_WARNING__);
            return false;
        }

        $arrJobsToNotify = array_filter($this->arrAllUnnotifiedJobs, array($this, '_isIncludedJobSite') );
        $detailsHTMLFile = null;

        //
        // For our final output, we want the jobs to be sorted by company and then role name.
        // Create a copy of the jobs list that is sorted by that value.
        //
        $arrMatchedJobs = array_filter($arrJobsToNotify, "isUserJobMatch");
        $arrExcludedJobs = array_filter($arrJobsToNotify, "isNotUserJobMatch");

        logLine(PHP_EOL . "Writing final list of " . count($arrJobsToNotify) . " jobs to output files." . PHP_EOL, \C__DISPLAY_NORMAL__);

        $arrExcludedJobs = array_filter($arrJobsToNotify, "isExcluded");
        $arrMatchedAndNotExcludedJobs = array_filter($arrMatchedJobs, "isUserJobMatchAndNotExcluded");

        $detailsMatchOnlyCSV = parseFilePath($this->_filterAndWriteListToFile_($arrMatchedAndNotExcludedJobs, "Matches", "CSV"));
        $detailsMatchExcludedCSV = parseFilePath($this->_filterAndWriteListToFile_($arrExcludedJobs, "ExcludedMatches", "CSV"));
        $detailsHTMLFile = parseFilePath($this->_filterAndWriteListToFile_($arrMatchedAndNotExcludedJobs, "Matches", "HTML"));

        $arrResultFilesToCombine[] = $detailsMatchOnlyCSV;
        $arrFilesToAttach[] = $detailsMatchOnlyCSV;
        $arrResultFilesToCombine[] = $detailsMatchExcludedCSV;
        $arrFilesToAttach[] = $detailsMatchExcludedCSV;
        $arrFilesToAttach[] =  $detailsHTMLFile;


        $detailsExcludedCSVFile = parseFilePath($this->_filterAndWriteListToFile_($arrExcludedJobs, "-finalexcludedjobs", "CSV"));

        if ((filesize($detailsExcludedCSVFile['full_file_path']) < 10 * 1024 * 1024) || isDebug()) {
            $arrFilesToAttach[] = $detailsExcludedCSVFile;
        }

        $GLOBALS['logger']->logSectionHeader("" . PHP_EOL, \C__SECTION_END__, \C__NAPPSECONDLEVEL__);

        $xlsOutputFile = $this->_combineCSVsToExcel($detailsMainResultsXLSFile, $arrResultFilesToCombine);
        array_push($arrFilesToAttach, $xlsOutputFile);


        $GLOBALS['logger']->logSectionHeader("Generating text email content for user" . PHP_EOL, \C__SECTION_BEGIN__, \C__NAPPSECONDLEVEL__);

        $strResultCountsText = $this->getListingCountsByPlugin("text", $arrJobsToNotify, $arrExcludedJobs);
        $strResultText = "Job Scooper Results for ". getRunDateRange() . PHP_EOL . $strResultCountsText . PHP_EOL;

        $GLOBALS['logger']->logSectionHeader("Generating html email content for user" . PHP_EOL, \C__SECTION_BEGIN__, \C__NAPPSECONDLEVEL__);


        $messageHtml = $this->getListingCountsByPlugin("html", $arrMatchedJobs, $arrExcludedJobs, $detailsHTMLFile);

        $messageHtml = $this->addMailCssToHTML($messageHtml);
        $subject = "New Job Postings: " . getRunDateRange();

        $GLOBALS['logger']->logSectionHeader("Generating text html content for user" . PHP_EOL, \C__SECTION_BEGIN__, \C__NAPPSECONDLEVEL__);

        LogPlainText($strResultText, \C__DISPLAY_SUMMARY__);

        //
        // Send the email notification out for the completed job
        //
        $GLOBALS['logger']->logSectionHeader("Sending email to user..." . PHP_EOL, \C__SECTION_BEGIN__, \C__NAPPSECONDLEVEL__);

        try {
            $ret = $this->sendEmail($strResultText, $messageHtml, $arrFilesToAttach, $subject, "results");
            if($ret !== false || $ret !== null)
            {
                if(!isDebug()) {
                    $arrToMarkNotified = array_from_orm_object_list_by_array_keys($arrJobsToNotify, array("JobPostingId"));
                    $ids = array_column($arrToMarkNotified, "JobPostingId");
                    $rowsAffected = 0;
                    foreach(array_chunk($ids, 100) as $arrChunkIds)
                    {
                        $results = \JobScooper\DataAccess\UserJobMatchQuery::create()
                            ->filterByJobPostingId($arrChunkIds)
                            ->update(array('UserNotificationState' => 'sent'), null, true);
                        $rowsAffected .= count($results);
                    }
                    if ($rowsAffected != count($arrToMarkNotified))
                        LogLine("Warning:  marked only {count($rowsAffected)} of {count($arrToMarkNotified)} UserJobMatch records as notified.");
                }
            }

        } catch (Exception $ex)
        {
            throw $ex;
        }

        //
        // We only keep interim files around in debug mode, so
        // after we're done processing, delete the interim HTML file
        //
        if (isDebug() !== true) {
            foreach ($arrFilesToAttach as $fileDetail) {
                if (file_exists($fileDetail['full_file_path']) && is_file($fileDetail ['full_file_path'])) {
                    LogLine("Deleting local attachment file " . $fileDetail['full_file_path'] . PHP_EOL, \C__DISPLAY_NORMAL__);
                    unlink($fileDetail['full_file_path']);
                }
            }
        }
        $GLOBALS['logger']->logSectionHeader("" . PHP_EOL, \C__SECTION_END__, \C__NAPPSECONDLEVEL__);

        LogLine(PHP_EOL."**************  DONE.  Cleaning up.  **************  ".PHP_EOL, \C__DISPLAY_NORMAL__);

        return $ret;
    }


    public function writeRunsJobsToFile($strFileOut, $arrJobsToOutput)
    {
        $fileDetails = parseFilePath($strFileOut);

        if($fileDetails['file_extension'] == "HTML")
            $keysToOutput = $this->getKeysForHTMLOutput();
        else
            $keysToOutput = $this->getKeysForUserCSVOutput(!isDebug());

        if(is_null($keysToOutput))
            $keysToOutput = array();

        if(!$strFileOut || strlen($strFileOut) <= 0)
        {
            throw new ErrorException("Error: writeJobsListToFile called without an output file path to use.");
        }

        if(count($strFileOut) == 0)
        {
            LogLine("Warning: writeJobsListToFile had no records to write to  " . $strFileOut, \C__DISPLAY_ITEM_DETAIL__);

        }

        $arrRecordsToOutput = $this->_convertToJobsArrays($arrJobsToOutput);

        $classCombined = new \SimpleCSV($strFileOut , "w");
        if (!is_array($arrRecordsToOutput))
        {
            $arrRecordsToOutput = array();
        }
        else
        {
            $this->sortJobsCSVArrayByCompanyRole($arrRecordsToOutput);
        }

        if ($keysToOutput == null && count($arrRecordsToOutput) > 0)
        {
            $exampleRec = $arrRecordsToOutput[array_keys($arrRecordsToOutput)[0]];

            $arrKeys = array_keys($exampleRec);
            $arrKeysInOrder = array();
            $tmpKeyOrderWithDupes = array_merge($keysToOutput, $arrKeys);
            foreach($tmpKeyOrderWithDupes as $key)
            {
                if(!in_array($key, $arrKeysInOrder))
                    $arrKeysInOrder[] = $key;
            }
            $keysToOutput = $arrKeysInOrder;
        }
        elseif($keysToOutput == null)
        {
            $keysToOutput = getEmptyJobListingRecord();
        }

        if($arrRecordsToOutput != null && count($arrRecordsToOutput) > 0)
        {
            foreach($arrRecordsToOutput as $reckey => $rec)
            {
                $out = array();
                foreach($keysToOutput as $k)
                {
                    $out[$k] = $rec[$k];
                }
                $arrRecordsToOutput[$reckey] = array_copy($out);
            }
        }

        if($fileDetails['file_extension'] == 'HTML')
        {
            $classCombined->writeArrayToHTMLFile($arrRecordsToOutput, $keysToOutput, null);

        }
        else
        {
            array_unshift($arrRecordsToOutput, $keysToOutput);
            $objPHPExcel = new PHPExcel();
            $objPHPExcel->getActiveSheet()->fromArray($arrRecordsToOutput, null, 'A1');
            $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, "CSV");

//            $spreadsheet->removeSheetByIndex(0);
            $objWriter->save($strFileOut);

//            $classCombined->writeArrayToCSVFile($arrJobsRecordsToUse, $keysToOutput, $this->arrKeysForDeduping);
        }
        LogLine("Jobs list had  ". count($arrRecordsToOutput) . " jobs and was written to " . $strFileOut , \C__DISPLAY_ITEM_START__);

        if($strExt == "HTML")
            $this->addMailCssToHTMLFile($strFileOut);

        return $arrJobsToOutput;

    }

    private function _filterAndWriteListToFile_($arrJobsList, $strFileNameBase, $strExt = "CSV")
    {
        $filePath = getDefaultJobsOutputFileName("", $strFileNameBase, $strExt, "_", 'notifications');


        if(countJobRecords($arrJobsList) == 0) return $arrJobsList;

        $this->writeRunsJobsToFile($filePath, $arrJobsList);

        LogLine("Wrote " . count($arrJobsList). " job listings output to  " . $filePath, \C__DISPLAY_ITEM_RESULT__);

        return $filePath;

    }

    private function _getFullFileContents_($detailsFile)
    {
        $content = null;
        $filePath = $detailsFile['full_file_path'];

        if(strlen($filePath) < 0)
        {
            LogLine("Unable to get contents from '". var_export($detailsFile, true) ."' to assets in email.  Failing notification.", \C__DISPLAY_ERROR__);
            return null;
        }

        # Open a file
        $file = fopen( $filePath, "r" );
        if( $file == false )
        {
            LogLine("Unable to open file '". $filePath ."' for to get contents for notification mail.  Failing notification.", \C__DISPLAY_ERROR__);
            return null;
        }

        # Read the file into a variable
        $size = filesize($filePath);
        $content = fread( $file, $size);

        return $content;
    }


    private function _getJobSitesRunRecently()
    {
        if(is_null($this->_arrJobSitesForRun)) {
            $this->_arrJobSitesForRun = getAllJobSitesThatWereLastRun();

            $sites = array_map(function ($var) {
                return $var->getJobPosting()->getJobSiteKey();
            }, $this->arrAllUnnotifiedJobs);
            $uniqSites = array_unique($sites);

            $this->_arrJobSitesForRun = array_merge($this->_arrJobSitesForRun, $uniqSites);
        }

        return $this->_arrJobSitesForRun;

    }
    private function getListingCountsByPlugin($fLayoutType, $arrMatchedJobs = null, $arrExcludedJobs = null, $detailsHTMLBodyInclude = null)
    {

        $arrCounts = array();
        $arrExcluded = null;

        $strOut = "                ";
        $arrHeaders = array("New Matches to Review", "Matches Auto-Excluded", "Total Jobs");

        $arrFailedPluginsReport = getFailedSearchesByPlugin();


        foreach($this->_getJobSitesRunRecently() as $plugin) {

            $arrPluginJobMatches  = array();
            if ($arrMatchedJobs != null && is_array($arrMatchedJobs) && countJobRecords($arrMatchedJobs) > 0) {
                $arrPluginJobMatches = array_filter($arrMatchedJobs, function ($var) use ($plugin) { return (strcasecmp($var->getJobPosting()->getJobSiteKey(), $plugin) == 0); } );
            }

            $arrPluginExcludesJobs  = array();
            if ($arrExcludedJobs != null && is_array($arrExcludedJobs) && countJobRecords($arrExcludedJobs) > 0) {
                $arrPluginExcludesJobs = array_filter($arrExcludedJobs, function ($var) use ($plugin) { return (strcasecmp($var->getJobPosting()->getJobSiteKey(), $plugin) == 0); } );
            }

            $arrPluginJobs = $arrPluginJobMatches + $arrPluginExcludesJobs;

            $arrCounts[$plugin]['name'] = $plugin;
            $arrCounts[$plugin]['matches_to_review'] = count(array_filter($arrPluginJobs, "isUserJobMatchAndNotExcluded"));
            $arrCounts[$plugin]['matches_excluded'] = count(array_filter($arrPluginJobs, "isUserJobMatchButExcluded"));
            $arrCounts[$plugin]['total_listings'] = count($arrPluginJobs);
            $arrCounts[$plugin]['had_error'] = false;

            //
            // if the plugin also errored, then add an asterisk to the name
            // for refernce in the email
            //
            if(!is_null($arrFailedPluginsReport) && in_array($plugin, array_keys($arrFailedPluginsReport)) === true)
            {
                $arrCounts[$plugin]['name'] = "**" . $plugin;
                $arrCounts[$plugin]['had_error'] = true;
            }
        }

        usort($arrCounts, "sortByErrorThenCount");


        switch ($fLayoutType)
        {
            case "html":
                $content = $this->_getResultsTextHTML_($arrHeaders, $arrCounts, $detailsHTMLBodyInclude);
                break;

            default:
            case "text":
                $content = $this->_getResultsTextPlain_($arrHeaders, $arrCounts);
                break;

        }

        return $content;
    }

    private function _printResultsLine_($arrRow, $strType="TEXT")
    {
        if($arrRow == null || !isset($arrRow) || !is_array($arrRow)) return "";

        $strOut = "";
        $fFirstCol = true;

        $style = 'class="job_scooper"';

        if ($arrRow['had_error'] == true)
        {
            $style = ' class="job_scooper jobsite_error" style="color=Grey;"';
            unset($arrRow['had_error']);
        }

        if($strType == "HTML")
        {
            $strOut .=  PHP_EOL . "<tr " . $style .">". PHP_EOL;
        }

        foreach($arrRow as $value)
        {
            switch ($strType)
            {
                case "HTML":
                    if($fFirstCol == true)
                    {
                        $strOut .= "<td width='20%' align='left'><span " . $style . " >" . $value . "</span></td>" . PHP_EOL;
                        $fFirstCol = false;
                    }
                    else
                        $strOut .= "<td width='10%' align='center'><span " . $style . " >" . $value . "</span></td>" . PHP_EOL;
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

    private function _getResultsTextPlain_($arrHeaders, $arrCounts)
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

            foreach($arrCounts as $site)
            {
                $strOut .= $this->_printResultsLine_($site, "TEXT");
            }


            $strOut .= sprintf("%'=100s","") . PHP_EOL;
            $strOut .= $this->_printResultsLine_($arrCounts_TotalUser);
            $strOut .= $this->_printResultsLine_($arrCounts_TotalAll);
            $strOut .= PHP_EOL;
        }

        if($GLOBALS['USERDATA']['configuration_settings']['excluded_sites'] != null && count($GLOBALS['USERDATA']['configuration_settings']['excluded_sites']) > 0)
        {
            sort($GLOBALS['USERDATA']['configuration_settings']['excluded_sites']);
            $strExcluded = getArrayValuesAsString($GLOBALS['USERDATA']['configuration_settings']['excluded_sites'], ", ", "Sites excluded by user or settings: ", false);
            $strOut .= $strExcluded;
        }


        return $strOut;
    }


    private function _getResultsTextHTML_($arrHeaders, $arrCounts, $detailsHTMLBodyInclude = null)
    {
        $arrCounts_TotalAll = null;
        $arrCounts_TotalUser = null;
        $strOut = "<div class='job_scooper outer'>";

        $strOut  .= "<H1>Job Postings to Review for " . getRunDateRange() . "</H1>".PHP_EOL;
        $strOut  .= "<div style=\"width: 100%; text-align: right;\"><span style=\"font-size: xx-small; color: #49332D;\">Generated by " . gethostname() . " running " . __APP_VERSION__. " on " . getTodayAsString() . ".</span></div>" . PHP_EOL;

        //
        // Include the contents of the HTML file if passed
        //
        if(!is_null($detailsHTMLBodyInclude) && array_key_exists('has_file', $detailsHTMLBodyInclude) && $detailsHTMLBodyInclude['has_file'] == true ) {
            $strOut .= PHP_EOL . "<div class=\"job_scooper section\">" . PHP_EOL;
            $strOut .= $this->_getFullFileContents_($detailsHTMLBodyInclude);
            $strOut .= PHP_EOL . PHP_EOL;
            $strOut .= "</div>";
            $strOut .= "<br>" . PHP_EOL . "<br>" . PHP_EOL;
        }
        else
        {
            $strOut .= PHP_EOL . "<div class=\"job_scooper section\">" . PHP_EOL;
            $strOut  .= "No new jobs were found that matched your search terms.". PHP_EOL. PHP_EOL;
            $strOut .= PHP_EOL . PHP_EOL;
            $strOut .= "</div>";
            $strOut .= "<br>" . PHP_EOL . "<br>" . PHP_EOL;
        }


        if($arrCounts != null && count($arrCounts) > 0)
        {
            $strOut  .= "<H2>Search Results by Job Site</H2>".PHP_EOL. PHP_EOL;
            $strOut .= "<table id='resultscount' class='job_scooper'>" . PHP_EOL . "<thead>". PHP_EOL;
            $strOut .= "<th class='job_scooper' width='20%' align='left'>Job Site</td>" . PHP_EOL;

            foreach($arrHeaders as $value)
            {
                $strOut .= "<th class='job_scooper' width='10%' align='center'>" . $value . "</th>" . PHP_EOL;
            }
            $strOut .=  PHP_EOL . "</thead>". PHP_EOL;

            foreach($arrCounts as $site)
            {
                $strOut .= $this->_printResultsLine_($site, "HTML");
            }

            $strOut .=  PHP_EOL . "<tr class='job_scooper totaluser'>". PHP_EOL;
            $strOut .= $this->_printResultsLine_($arrCounts_TotalUser, "HTML");
            $strOut .=  PHP_EOL . "</tr><tr class='job_scooper totalall'>". PHP_EOL;
            $strOut .= $this->_printResultsLine_($arrCounts_TotalAll, "HTML");
            $strOut .=  PHP_EOL . "</tr>". PHP_EOL;

            $strOut .=  PHP_EOL . "</table>". PHP_EOL. PHP_EOL;
        }


        if($GLOBALS['USERDATA']['configuration_settings']['excluded_sites'] != null && count($GLOBALS['USERDATA']['configuration_settings']['excluded_sites']) > 0)
        {
            $strOut .=  PHP_EOL . "<div class=\"job_scooper section\">". PHP_EOL;
            sort($GLOBALS['USERDATA']['configuration_settings']['excluded_sites']);

            $strExcluded = getArrayValuesAsString($GLOBALS['USERDATA']['configuration_settings']['excluded_sites'], ", ", "", false);
            $strOut  .= "<H2>Excluded Job Sites</H2>".PHP_EOL. PHP_EOL;

            $strOut .=  PHP_EOL .  "<span style=\"font-size: xx-small; \">Excluded sites for this run:" . $strExcluded . "</span>" . PHP_EOL;
            $strOut .= "</div>";
            $strOut .= "<br>" . PHP_EOL . "<br>" . PHP_EOL;
        }


        return $strOut;
    }

    private function getKeysForHTMLOutput()
    {
        return array(
            'Company',
            'JobTitleLinked',
            'LocationDisplayValue'
        );
    }

    private function getKeysForUserCSVOutput($optimizedView=true)
    {
        $match = new \JobScooper\DataAccess\UserJobMatch();
        $jobPost = new JobPosting();
        $allKeys = array_merge(array_keys($jobPost->toArray()), array_keys($match->toArray()));


        if($optimizedView) {
            $retKeys = array_diff($allKeys, array('AppRunId', 'UserJobMatchId', 'UserNotificationState', 'TitleTokens', 'JobTitleLinked', "FirstSeenAt", "RemovedAt", "UpdatedAt", "KeySiteAndPostID", "KeyCompanyAndTitle"));
        }
        else {
            $retKeys = $allKeys;
        }
        return array_unique($retKeys);
    }

    private function _convertToJobsArrays($arrJobObjects)
    {
        $arrRet = array();
        foreach($arrJobObjects as $jobMatch)
        {
            $item = $jobMatch->toFlatArray();
            $arrRet[$item['KeySiteAndPostID']] = $item;
        }

        return $arrRet;

    }



    function sortJobsCSVArrayByCompanyRole(&$arrJobList)
    {

        if (countJobRecords($arrJobList) > 0) {
            $arrFinalJobIDs_SortedByCompanyRole = array();
            $finalJobIDs_CompanyRole = array_column($arrJobList, 'KeyCompanyAndTitle', 'KeySiteAndPostID');
            foreach (array_keys($finalJobIDs_CompanyRole) as $key) {
                // Need to add uniq key of job site id to the end or it will collapse duplicate job titles that
                // are actually multiple open posts
                $arrFinalJobIDs_SortedByCompanyRole[$finalJobIDs_CompanyRole[$key] . "-" . $key] = $key;
            }

            ksort($arrFinalJobIDs_SortedByCompanyRole);
            $arrFinalJobs_SortedByCompanyRole = array();
            foreach ($arrFinalJobIDs_SortedByCompanyRole as $jobid) {
                $arrFinalJobs_SortedByCompanyRole[$jobid] = $arrJobList[$jobid];
            }
            $arrJobList = $arrFinalJobs_SortedByCompanyRole;
        }

    }




}