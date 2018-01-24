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

namespace JobScooper\BasePlugin\Interfaces;

// Declare the interface 'iJobSitePlugin'
use JobScooper\Utils\SimpleHTMLHelper;

interface IJobSitePlugin
{
    /**
     * parseTotalResultsCount
     *
     * If the site does not show the total number of results
     * then set the plugin flag to C__JOB_PAGECOUNT_NOTAPPLICABLE__
     * in the Constants.php file and just comment out this function.
     *
     * parseTotalResultsCount returns the total number of listings that
     * the search returned by parsing the value from the returned HTML
     * *
     * @param $objSimpHTML
     * @return string|null
     */
    function parseTotalResultsCount(SimpleHTMLHelper $objSimpHTML);

    /**
     * /**
     * parseJobsListForPage
     *
     * This does the heavy lifting of parsing each job record from the
     * page's HTML it was passed.
     * *
     * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
     *
     * @return array|null|void
     * @throws \Exception
     */
    function parseJobsListForPage(SimpleHTMLHelper $objSimpHTML);


}