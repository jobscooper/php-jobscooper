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

namespace JobScooper\BasePlugin\Classes;


/**
 * Class JobsApiPlugin
 * @package JobScooper\BasePlugin\Classes
 */
class JobsApiPlugin extends BaseJobsSite
{
	/**
	 * JobsApiPlugin constructor.
	 *
	 * @param null $strBaseDir
	 */
	function __construct($strBaseDir = null)
    {
        $this->additionalBitFlags[] = C__JOB_PAGECOUNT_NOTAPPLICABLE__;
        $this->pluginResultsType = C__JOB_SEARCH_RESULTS_TYPE_JOBSAPI__;

        parent::__construct();

    }
}
