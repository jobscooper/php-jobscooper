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


use const JobScooper\BasePlugin\Classes\VALUE_NOT_SUPPORTED;
use const \JobScooper\BasePlugin\Classes\BASE_URL_TAG_KEYWORDS;
use const \JobScooper\BasePlugin\Classes\BASE_URL_TAG_LOCATION;

class PluginSimplyHired extends \JobScooper\BasePlugin\Classes\ServerHtmlPlugin
{
    protected $JobPostingBaseUrl = 'http://www.simplyhired.com';
    protected $JobSiteName = 'SimplyHired';
    protected $JobListingsPerPage = 10;
    protected $LocationType = 'location-city-comma-statecode';
    protected $strKeywordDelimiter = "or";
    protected $SearchUrlFormat = "http://www.simplyhired.com/search?q=***KEYWORDS***&l=***LOCATION***&fdb=***NUMBER_DAYS***&&ws=25&mi=50&sb=dd&pn=***PAGE_NUMBER***";
    protected $PaginationType = C__PAGINATION_PAGE_VIA_URL;
    
    function getItemURLValue($nItem)
    {
        if($nItem == null || $nItem == 1) { return 0; }

        return $nItem;
    }


    /**
     * If the site does not have a URL parameter for number of days
     * then set the plugin flag to C__JOB_DAYS_VALUE_NOTAPPLICABLE__
     * in the Constants.php file and just comment out this function.
     *
     * getDaysURLValue returns the value that is used to replace
     * the ***DAYS*** token in the search URL for the number of
     * days requested.
     *
     * @param $days
     * @return int|string
     */
    function getDaysURLValue($days = null)
    {
        $ret = "";

        if($days != null)
        {
            switch($days)
            {
                case ($days==1):
                    $ret = "1";
                    break;

                case ($days>1 && $days<8):
                    $ret = "7";
                    break;

                case ($days>14 && $days < 30):
                    $ret = "14";
                    break;

                case ($days>=30):
                    $ret = "30";
                    break;


                default:
                    $ret = "";
                    break;

            }
        }

        return $ret;
    }



    protected function getPageURLfromBaseFmt(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails, $nPage = null, $nItem = null)
    {
        $searchDetailsBackup = $searchDetails->copy();
        $strURL = $this->SearchUrlFormat;


	    $numDays = getConfigurationSetting('number_days');
        $strURL = str_ireplace("***NUMBER_DAYS***", $this->getDaysURLValue($numDays), $strURL);
        $strURL = str_ireplace("***PAGE_NUMBER***", $this->getPageURLValue($nPage), $strURL);
        $strURL = str_ireplace("***ITEM_NUMBER***", $this->getItemURLValue($nItem), $strURL);
        $strURL = str_ireplace(BASE_URL_TAG_KEYWORDS, $this->getKeywordURLValue($searchDetails), $strURL);


        $nSubtermMatches = substr_count($strURL, BASE_URL_TAG_LOCATION);

        if (!$this->isBitFlagSet(C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED) && $nSubtermMatches > 0) {
            $strURL = str_ireplace(BASE_URL_TAG_LOCATION, $this->getGeoLocationURLValue($searchDetails), $strURL);
            if ($strURL == null) {
                throw new \ErrorException("Location value is required for " . $this->JobSiteName . ", but was not set for the search '" . $searchDetails->getUserSearchSiteRunKey() . "'." . " Aborting all searches for " . $this->JobSiteName);
            }
        }

        $nSubtermMatches = substr_count($strURL, BASE_URL_TAG_LOCATION);

        if(!$this->isBitFlagSet(C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED) && $nSubtermMatches > 0)
        {
            $loc = $searchDetails->getGeoLocation();
            $locTypeNeeded = $this->getGeoLocationSettingType();
            if(empty($locTypeNeeded) || $locTypeNeeded == VALUE_NOT_SUPPORTED)
            {
                $msg = "Failed to run search:  search is missing the required location type of " . $this->getGeoLocationSettingType() ." set.  Skipping search '". $searchDetails->getUserSearchSiteRunKey() .".";
                LogError($msg);
                throw new IndexOutOfBoundsException($msg);
            }
            else
            {
                $strLocationValue = $loc->formatLocationByLocationType($locTypeNeeded);
                if (empty($strLocationValue) || $strLocationValue == VALUE_NOT_SUPPORTED) {
                    LogMessage("Plugin for '" . $searchDetails->getJobSiteKey() . "' did not have the required location type of " . $locTypeNeeded . " set.   Skipping search '" . $searchDetails->getUserSearchSiteRunKey() . ".");
                    return "";
                }
                $strURL = str_ireplace(BASE_URL_TAG_LOCATION, $strLocationValue, $strURL);
            }
        }

        if($strURL == null) {
            throw new ErrorException("Location value is required for " . $this->JobSiteName . ", but was not set for the search '" . $searchDetails->getUserSearchSiteRunKey() ."'.". " Aborting all searches for ". $this->JobSiteName);
        }

        $searchDetails = $searchDetailsBackup->copy();

        return $strURL;
    }


    function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        $node = $objSimpHTML->find("div[class='result-headline'] div[class='hidden-sm-down'] div");
        if($node && isset($node) && is_array($node))
        {
            $arrParts = explode(" ", $node[0]->text());
            return $arrParts[3];
        }

        return null;
    }


	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null|void
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {

        $ret = null;
        $nodesJobs= $objSimpHTML->find('div[class="js-job"]');

        foreach($nodesJobs as $node)
        {

            $item = getEmptyJobListingRecord();

            $titlelink = $node->find('a[class="card-link js-job-link"]');
            $item['Title'] = combineTextAllChildren($titlelink[0]);;
            $item['Url'] = $this->JobPostingBaseUrl . $titlelink[0]->href;

            if($item['Title'] == '') continue;

            $datenode = $node->find('span[class="serp-timestamp"]');
            if(isset($datenode) && is_array($datenode))
            {
                $item['PostedAt'] = $datenode[0]->text();
            }

            $companynode = $node->find('span[class="serp-company"]');
            if(isset($companynode ) && is_array($companynode ))
            {
                $item['Company'] = combineTextAllChildren($companynode [0]);
            }

            $locnode = $node->find('span[class="serp-location"] span span[class="serp-location"]');
            if(isset($locnode) && is_array($locnode))
            {
                $item['Location'] = combineTextAllChildren($locnode[0]);
            }

            $item['JobSitePostId'] = $this->getIDFromLink('/\/a\/job-details\/\?a=([^\/]+)/i', $item['Url']);

            $ret[] = $item;
        }

        return $ret;
    }








}


?>
