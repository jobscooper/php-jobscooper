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



class PluginBetalist extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = 'Betalist';
    protected $JobPostingBaseUrl = "https://betalist.com";
    protected $JobListingsPerPage = 500;

    // Note:  Betalist has a short list of jobs (< 500-1000 total) so we exclude keyword search here as an optimization.  The trick we use is putting a blank space in the search text box.
    //        The space returns all jobs whereas blank returns a special landing page.
    protected $SearchUrlFormat = "https://betalist.com/jobs?q=%20&hPP=500&p=***PAGE_NUMBER***&dFR%5Bcommitment%5D%5B0%5D=Full-Time&dFR%5Bcommitment%5D%5B1%5D=Part-Time&dFR%5Bcommitment%5D%5B2%5D=Contractor&dFR%5Bcommitment%5D%5B3%5D=Internship&is_v=1";
    #    protected $SearchUrlFormat = "https://betalist.com/jobs?q=***KEYWORDS***&hPP=500&p=***PAGE_NUMBER***&dFR%5Bcommitment%5D%5B0%5D=Full-Time&dFR%5Bcommitment%5D%5B1%5D=Part-Time&dFR%5Bcommitment%5D%5B2%5D=Contractor&dFR%5Bcommitment%5D%5B3%5D=Internship&is_v=1";

    protected $additionalBitFlags = [C__JOB_IGNORE_MISMATCHED_JOB_COUNTS];  // TODO:  Add Lat/Long support for BetaList location search.
    protected $PaginationType = C__PAGINATION_PAGE_VIA_URL;
    protected $additionalLoadDelaySeconds = 10;

    protected function getPageURLValue($nPage)
    {
        return ($nPage - 1);
    }

    function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        $nTotalResults = C__TOTAL_ITEMS_UNKNOWN__;

        //
        // Find the HTML node that holds the result count
        $nodeCounts = $objSimpHTML->find("span.ais-refinement-list--count");
        if ($nodeCounts != null && is_array($nodeCounts) && isset($nodeCounts[0])) {
            foreach ($nodeCounts as $spanCount) {
                $strVal = $spanCount->text();
                $nVal = intval(str_replace(",", "", $strVal));
                if ($nTotalResults == C__TOTAL_ITEMS_UNKNOWN__)
                    $nTotalResults = $nVal;
                else
                    $nTotalResults += $nVal;
            }
        } else {
            return 0;
        }


        $this->additionalLoadDelaySeconds = $this->additionalLoadDelaySeconds + intceil($this->JobListingsPerPage / 100) * 2;

//
// Betalist maxes out at a 1000 listings.  If we're over that, reduce the count to 1000 so we don't try to download more
//
        return ($nTotalResults > 1000) ? 1000 : $nTotalResults;

    }


    protected $arrListingTagSetup = array(
        'JobPostItem' => array('tag' => 'div', 'attribute' => 'class', 'attribute_value' => 'ais-hits--item'),
        'Title' => array('tag' => 'a', 'attribute' => 'class', 'attribute_value' => 'jobCard__details__title'),
        'Url' => array('tag' => 'a', 'attribute' => 'class', 'attribute_value' => 'jobCard__details__title'),
        'Company' => array(array('tag' => 'div', 'attribute' => 'class', 'attribute_value' => 'jobCard__details__company'), array('tag' => 'a')),
        'Location' => array('tag' => 'div', 'attribute' => 'class', 'attribute_value' => 'jobCard__details__location'),
        'regex_link_job_id' => '/jobs\/([^\/]+)/i'
    );

}

