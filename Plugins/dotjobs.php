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



/**
 * Class AbstractDotJobs
 *
 *       Used by dotjobs.json plugin configuration to override single method
 */
abstract class AbstractDotJobs extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected function goToEndOfResultsSetViaLoadMore($nTotalListings)
    {

        $this->selectorMoreListings = "#button_moreJobs";

        $js = "
            document.getElementById(\"direct_moreLessLinks_listingDiv\").setAttribute(\"data-num-items\", 50);
        ";

        $this->runJavaScriptSnippet($js, false);
        $this->JobListingsPerPage = 50;

        parent::goToEndOfResultsSetViaLoadMore($nTotalListings);
    }
}
