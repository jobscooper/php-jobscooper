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


ini_set('error_reporting', E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
$lineEnding = ini_get('auto_detect_line_endings');
ini_set('auto_detect_line_endings', true);
date_default_timezone_set("America/Los_Angeles");

const C__APPNAME__ = "jobs_scooper";
const __APP_VERSION__ = "v4.1.0-use-propel-orm";
const C__SRC_LOCATION = "https://github.com/selner/job_scooper_v4";
const C__STR_USER_AGENT__ = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.152 Safari/537.36";

define('MAX_FILE_SIZE', 5000000);
define('__ROOT__', dirname(__FILE__));

$autoload = join(DIRECTORY_SEPARATOR, array(__ROOT__, 'vendor', 'autoload.php'));
if (file_exists($autoload)) {
    require_once($autoload);
} else {
    trigger_error("Composer required to run this app.");
}

$cmdline = new \JobScooper\Utils\DocOptions(__FILE__);
$arguments = $cmdline->getAll();

setConfigurationSetting('command_line_args', $arguments);

$config = new \JobScooper\Builders\ConfigBuilder($arguments["configfile"]);

$GLOBALS['logger'] = new \JobScooper\Manager\LoggingManager(C__APPNAME__);

$classRunJobs = new \JobScooper\Manager\StageManager();
$classRunJobs->runAll();
