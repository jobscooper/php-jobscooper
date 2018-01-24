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


class PluginGoogle extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    // BUGBUG: currently does not handle pagination of job listings


    protected $JobSiteName = 'Google';
    protected $JobPostingBaseUrl = 'https://careers.google.com/jobs';
    protected $prevURL = 'https://careers.google.com/jobs';
    protected $additionalBitFlags = [C__JOB_ITEMCOUNT_NOTAPPLICABLE__];
	protected $SearchUrlFormat = "https://careers.google.com/jobs#j=***KEYWORDS***&t=sq&q=j&so=dt_pd&li=20&l=false&jlo=en-US&***LOCATION:&jl={Latitude}%3A{Longitude}%3A{Place}%2C+{CountryCode}%3A%3ALOCALITY&jld=10***";

    protected $CountryCodes = ["US", "UK"];

    protected $additionalLoadDelaySeconds = 6;
    protected $nextPageScript = "var elem = document.getElementById('gjsrpn');  if (elem != null) { console.log('attempting next button click on element ID gjsrpn'); elem.click(); };";

    protected $arrListingTagSetup = array(
        'NextButton' => array('selector' => 'button[aria-label=\'Next page\']')
    );


	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        $ret = null;

        $nodesJobs= $objSimpHTML->find("*[role='listitem']");

        if(!$nodesJobs) return null;

        foreach($nodesJobs as $node)
        {
            $item = getEmptyJobListingRecord();

	        $item = $this->getJobFactsFromMicrodata($node, $item);

	        $item['JobSitePostId'] = $node->getAttribute("data-job-id");

            $subNode = $node->find("h2 a");
            if(!empty($subNode))
            {
                $item['Url'] = empty($item['Url']) ? $subNode[0]->getAttribute("href") : $item['Url'];
                $item['Title'] = empty($item['Title']) ? $subNode[0]->getAttribute("title") : $item['Title'];
            }

            $subNode = $node->find("div.summary span.location");
	        if(!empty($subNode) && !empty($locval = $subNode[0]->text()))
                $item['Location'] = $locval;

            $subNode = $node->find("div.summary span[class='secondary-text']");
            if(!empty($subNode) && !empty($coval = $subNode[0]->text()))
                 $item['Company'] = $coval;

            $ret[] = $item;

        }

        return $ret;
    }

}
