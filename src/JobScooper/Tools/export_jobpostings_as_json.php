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

namespace JobScooper\Tools;


use JobScooper\DataAccess\UserSearchSiteRunQuery;
use Propel\Runtime\Map\TableMap;

define('__ROOT__', dirname(__FILE__));
ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
define('MAX_FILE_SIZE', 5000000);
const C__APPNAME__ = "jobs_scooper";
define('__APP_VERSION__', "v4.1.0-use-propel-orm");
$lineEnding = ini_get('auto_detect_line_endings');
ini_set('auto_detect_line_endings', true);

$autoload = realpath(join(DIRECTORY_SEPARATOR, array("..", "..", "..", 'vendor', 'autoload.php')));
if (file_exists($autoload)) {
    require_once($autoload);
} else {
    trigger_error("Composer required to run this app.");
}

$GLOBALS['logger'] = new \JobScooper\Manager\LoggingManager(C__APPNAME__);
$propelConfig = realpath(join(DIRECTORY_SEPARATOR, array("..", "..", "..", 'Config', 'config.php')));
if (file_exists($propelConfig)) {
    require_once($propelConfig);
} else {
    trigger_error("Missing runtime configuration file at {$propelConfig} for your setup. ");
}


LogMessage("Getting all jobs from the database...");

const RESULTS_PER_PAGE = 1000;

$allJobsPager = \JobScooper\DataAccess\JobPostingQuery::create()
    ->leftJoinWithGeoLocationFromJP()
    ->paginate($page = 1, $maxPerPage = RESULTS_PER_PAGE);

if (!$allJobsPager->isEmpty()) {
    LogMessage("Exporting " . $allJobsPager->getNbResults() . " job postings to JSON");

    $nJobsExported = 0;
    $outFile = generateOutputFileName("all_job_postings", "json", false);
    $outdir = dirname($outFile);
    LogMessage("... exporting to files at {$outdir}...");

    while ($allJobsPager->getPage() <= $allJobsPager->getLastPage())
    {
        $arrOutputList = array();
        $nJobsExportPageEnd = $nJobsExported + RESULTS_PER_PAGE;
        $outFile = generateOutputFileName("job_postings-{$nJobsExported}-{$nJobsExportPageEnd}", "json", false);
        LogMessage("... exporting job postings {$nJobsExported} - {$nJobsExportPageEnd}...");
        foreach ($allJobsPager as $job) {
            $arrJob = $job->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, array(), true);
            if (array_key_exists('Location', $arrJob) && array_key_exists('JobPostings', $arrJob['Location']))
                unset($arrJob['Location']['JobPostings']);
            $arrOutputList[$job->getKeySiteAndPostId()] = $arrJob;
        }
        $pageJsonData = encodeJSON($arrOutputList);
        file_put_contents($outFile, $pageJsonData);
        $nJobsExported += RESULTS_PER_PAGE;
        $allJobsPager->getNextPage();

    }
}



