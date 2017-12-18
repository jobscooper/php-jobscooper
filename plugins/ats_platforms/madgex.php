<?php

/**
 * Copyright 2014-17 Bryan Selner
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
use JobScooper\DataAccess\UserSearchSiteRun;

abstract class AbstractMadgexATS extends \JobScooper\Plugins\Classes\AjaxHtmlSimplePlugin
{

    function __construct()
    {
        $this->prevURL = $this->childSiteURLBase;
        $this->PaginationType = C__PAGINATION_PAGE_VIA_URL;

        $this->JobPostingBaseUrl = $this->childSiteURLBase;
        $this->SearchUrlFormat = $this->childSiteURLBase . $this->SearchUrlFormat;
        parent::__construct();
    }

    protected $JobSiteName = 'madgexats';
    protected $SearchUrlFormat = '?Keywords=***KEYWORDS***&radialtown=***LOCATION***&LocationId=&RadialLocation=50&NearFacetsShown=true&countrycode=***COUNTRYCODE***&Page=***PAGE_NUMBER***';
    protected $locationid = null;

    protected $LocationType = 'location-city-comma-state';

    protected function getPageURLfromBaseFmt(UserSearchSiteRun $searchDetails, $nPage = null, $nItem = null)
    {
        $strURL = parent::getPageURLfromBaseFmt($searchDetails, $nPage, $nItem);
        $location = $searchDetails->getGeoLocation();
        $ccode = "";
        if(!empty($location))
            $ccode = $location->getCountryCode();
        $strURL = str_ireplace("***COUNTRYCODE***", $ccode, $strURL);

        if (!empty($this->locationid))
            $strURL = $strURL . "&LocationID=" . $this->locationid;

        $strURL = preg_replace('/[Ppage]{4}=\d+/', 'Page=***PAGE_NUMBER***', $strURL);
        return $strURL;

    }

    static function checkNoJobResults($var)
    {
        return noJobStringMatch($var, "Found 0 jobs");
    }

    protected $arrListingTagSetup = array(
        'NoPostsFound'    => array('selector' => 'h1#searching', 'return_attribute' => 'text', 'return_value_callback' => 'checkNoJobResults'),
        'TotalPostCount'        => array('selector' => 'h1#searching', 'return_attribute' => 'text', 'return_value_regex' =>  '/\b(\d+)\b/i'),
        'JobPostItem'      => array('selector' => 'li.lister__item'),
        'Title'                 => array('selector' => 'h3 a.js-clickable-area-link span[itemprop="title"]', 'return_attribute' => 'text'),
        'Url'                  => array('selector' => 'h3 a.js-clickable-area-link ', 'return_attribute' => 'href'),
        'Company'               => array('selector' => 'li[itemprop="hiringOrganization"]', 'return_attribute' => 'text'),
        'Location'              => array('selector' => 'li[itemprop="location"]', 'return_attribute' => 'text'),
        'JobSitePostId'                =>  array('selector' => 'li.lister__item', 'return_attribute' => 'id', 'return_value_regex' =>  '/item\-(\d+)/i'),
        'job_posted_date'       => array('selector' => 'li.job-actions__action pipe', 'index=0'),
        'company_logo'          => array('selector' => 'img.lister__logo', 'return_attribute' => 'src'),
        'NextButton'           => array('selector' => 'li.paginator__item a[rel="next"]')
    );

    protected function getGeoLocationURLValue(UserSearchSiteRun $searchDetails)
    {
        $ret = parent::getGeoLocationURLValue($searchDetails);
        if (stristr($ret, "%2C+washington") !== false)
            $ret .= "+state";
        return $ret;
    }

    function parseAndRedirectToLocation(&$objSimpHTML)
    {
        $locationSelectNode = $objSimpHTML->find("h2");
        if (!is_null($locationSelectNode) && count($locationSelectNode) == 1)
        {
            if(stristr($locationSelectNode[0]->text(), "select a location") !== false)
            {
                $nodeLocs = $objSimpHTML->find("li.lap-larger__item a");
                if (!is_null($nodeLocs) && count($nodeLocs) > 1)
                {
                    try
                    {
                        $newUrlPath = $nodeLocs[0]->href;
                        $newUrlPath = str_ireplace("&amp;", "&", $newUrlPath);
                        $arrMatches = array();
                        $matched = preg_match('/.*LocationId=(\d+).*/', $newUrlPath, $arrMatches);
                        if ($matched !== false && count($arrMatches) > 1)
                        {
                            $this->locationid = $arrMatches[1];
                        }
                        $url = parse_url($this->childSiteURLBase, PHP_URL_SCHEME) . "://" . parse_url($this->childSiteURLBase, PHP_URL_HOST) . $newUrlPath . "&RadialLocation=50";
	                    $curSearch = getConfigurationSetting('current_user_search_details');
	                    if(!empty($curSearch))
		                    $curSearch->setSearchStartUrl($url);

	                    $objSimpHTML = $this->getSimpleHtmlDomFromSelenium($url);
                    } catch (Exception $ex) {
                        handleException(new Exception("Failed to parseAndRedirectToLocation", $ex->getCode(), $ex), null, true);
                    }
                }
            }
        }
    }

    function parseTotalResultsCount($objSimpHTML)
    {

        $this->parseAndRedirectToLocation($objSimpHTML);

        return parent::parseTotalResultsCount($objSimpHTML);
    }
}

class PluginTheGuardian extends AbstractMadgexATS
{
    protected $JobSiteName = 'theguardian';
    protected $childSiteURLBase = 'https://jobs.theguardian.com/searchjobs';
}

class PluginMediaBistro extends AbstractMadgexATS
{
    protected $JobSiteName = 'mediabistro';
    protected $childSiteURLBase = 'https://www.mediabistro.com/jobs/search';
}
class PluginWashingtonPost extends AbstractMadgexATS
{
    protected $JobSiteName = 'washingtonpost';
    protected $childSiteURLBase = 'https://jobs.washingtonpost.com/searchjobs';
}

class PluginJobfinderUSA extends AbstractMadgexATS
{
    protected $JobSiteName = 'jobfinderusa';
    protected $childSiteURLBase = "https://www.jobfinderusa.com/searchjobs/";
}

class PluginGreatJobSpot extends AbstractMadgexATS
{
    protected $JobSiteName = 'greatjobspot';
    protected $childSiteURLBase = "https://www.greatjobspot.com/searchjobs/";
}


class PluginExecAppointments extends AbstractMadgexATS
{
    protected $JobSiteName = 'execappointments';
    protected $childSiteURLBase = "https://www.exec-appointments.com/searchjobs/";
}

class PluginEconomist extends AbstractMadgexATS
{
    protected $JobSiteName = 'economist';
    protected $childSiteURLBase = "http://jobs.economist.com/searchjobs";
}

class PluginStarTribune extends AbstractMadgexATS
{
    protected $JobSiteName = 'startribune';
    protected $childSiteURLBase = "http://jobs.startribune.com/searchjobs/";
}

