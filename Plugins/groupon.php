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


class PluginGroupon extends \JobScooper\BasePlugin\Classes\AjaxHtmlPlugin
{
    protected $JobSiteName = 'Groupon';
    protected $JobPostingBaseUrl = 'https://jobs.groupon.com';
    protected $SearchUrlFormat = "https://jobs.groupon.com/locations/***LOCATION***";
    protected $additionalBitFlags = [C__JOB_PAGECOUNT_NOTAPPLICABLE__, C__JOB_ITEMCOUNT_NOTAPPLICABLE__];
    protected $PaginationType = C__PAGINATION_NONE;
    protected $LocationType = 'location-city';


	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null|void
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        $ret = null;


        $nodesJobs= $objSimpHTML->find("div[class='body-text'] ul[class='block-grid'] li[class='ng-scope'] a[class='ng-binding']");

        $nCounter = -1;

        foreach($nodesJobs as $node)
        {
            $nCounter += 1;
            if($nCounter < 2)
            {
                continue;
            }

            $item = getEmptyJobListingRecord();

            $item['Title'] = $node->text();
            $item['Url'] = $node->href;
            $item['Company'] = $this->JobSiteName;
            $item['JobSitePostId'] = $this->getIDFromLink('/\/jobs\/([^\/]+)/i', $item['Url']);
            if($item['Title'] == '') continue;
            $ret[] = $item;

        }

        return $ret;
    }

}