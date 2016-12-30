<?php

/**
 * Copyright 2014-16 Bryan Selner
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
require_once(__ROOT__ . '/include/BaseJobsSitePlugin.php');


abstract class ClassBaseJobsAPIPlugin extends ClassBaseJobsSitePlugin
{
    protected $siteBaseURL = '';
    protected $siteName = '';
    protected $flagSettings = [ C__JOB_PAGECOUNT_NOTAPPLICABLE__, C__JOB_ITEMCOUNT_NOTAPPLICABLE__];

    protected $pluginResultsType = C__JOB_SEARCH_RESULTS_TYPE_JOBSAPI__;

    function getSearchJobsFromAPI($searchDetails) { return VALUE_NOT_SUPPORTED; }

}
abstract class ClassBaseServerHTMLJobSitePlugin extends ClassBaseJobsSitePlugin
{
    protected $pluginResultsType = C__JOB_SEARCH_RESULTS_TYPE_SERVERSIDE_WEBPAGE__;

}

abstract class ClassBaseClientSideHTMLJobSitePlugin extends ClassBaseJobsSitePlugin
{
    protected $pluginResultsType = C__JOB_SEARCH_RESULTS_TYPE_CLIENTSIDE_WEBPAGE__;
    protected $additionalFlags =[C__JOB_USE_SELENIUM];
}


abstract class ClassBaseXMLJobSitePlugin extends ClassBaseJobsSitePlugin
{
    protected $pluginResultsType = C__JOB_SEARCH_RESULTS_TYPE_SERVERSIDE_WEBPAGE__;
}