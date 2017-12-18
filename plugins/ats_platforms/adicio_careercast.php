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


abstract class AbstractAlternateAdicioCareerCast extends AbstractAdicioCareerCast
{
    protected $JobListingsPerPage = 20;
}

abstract class AbstractAdicioCareerCast extends \JobScooper\Plugins\Classes\AjaxHtmlSimplePlugin
{
    protected $JobSiteName = '';
    protected $JobPostingBaseUrl = '';
    protected $childSiteURLBase = '';
    protected $JobListingsPerPage = 50;
    protected $strBaseURLPathSuffix = "";
    protected $SearchUrlFormat = null;

    // postDate param below could also be modifiedDate =***NUMBER_DAYS***.  Unclear which is more correct when...
    //
    // BUGBUG: setting "search job title only" seems to not find jobs with just one word in the title.  "Pharmacy Intern" does not come back for "intern" like it should.  Therefore not setting the kwsJobTitleOnly=true flag.
    //
    protected $strBaseURLPathSection = "/jobs/results/keyword/***KEYWORDS***?view=List_Detail&SearchNetworks=US&networkView=national&location=***LOCATION***&radius=50&sort=PostDate+desc%2C+Priority+desc%2C+score+desc&rows=50&page=***PAGE_NUMBER***&postDate=***NUMBER_DAYS***";
#    protected $strBaseURLPathSection = "/jobs/search/results?kwsJobTitleOnly=true&view=List_Detail&networkView=national&radius=50&&sort=PostDate+desc%2C+Priority+desc%2C+score+desc&rows=50&page=***PAGE_NUMBER***&postDate=***NUMBER_DAYS***";
    protected $additionalLoadDelaySeconds = 10;
    protected $LocationType = 'location-city-comma-statecode-comma-countrycode';

    protected $arrListingTagSetup = array(
        'NoPostsFound' => array(array('tag' => 'div', 'attribute'=>'id', 'attribute_value' => 'aiAppWrapperBox'), array('tag' => 'form'), array('tag' => 'h2'), 'return_attribute' => 'text'),
        'TotalPostCount' => array()  # BUGBUG:  need this empty array so that the parent class doesn't auto-set to C__JOB_ITEMCOUNT_NOTAPPLICABLE__
    );

    function __construct()
    {
        $this->additionalBitFlags[] = C__JOB_KEYWORD_PARAMETER_SPACES_AS_DASHES;
        $this->PaginationType = C__PAGINATION_PAGE_VIA_URL;

        $this->JobPostingBaseUrl = $this->childSiteURLBase;
        $this->SearchUrlFormat = $this->childSiteURLBase . $this->strBaseURLPathSection . $this->strBaseURLPathSuffix;
        parent::__construct();
    }

    protected function getKeywordURLValue(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails) {

        return strtolower(parent::getKeywordURLValue($searchDetails));
    }

    /**
     * If the site does not have a URL parameter for number of days
     * then set the plugin flag to C__JOB_DAYS_VALUE_NOTAPPLICABLE__
     * in the LoadPlugins.php file and just comment out this function.
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
        $ret = "%5BNOW-1DAYS+TO+NOW%5D";

        if($days != null)
        {
            switch($days)
            {
                case ($days>1 && $days<8):
                    $ret = "%5BNOW-7DAYS+TO+NOW%5D";
                    break;

                case ($days>7):
                    $ret = "%5BNOW-28DAYS+TO+NOW%5D";
                    break;


                case $days<=1:
                default:
                    $ret = "%5BNOW-1DAYS+TO+NOW%5D";
                    break;

            }
        }

        return $ret;
    }




    /**
     * parseTotalResultsCount
     *
     * If the site does not show the total number of results
     * then set the plugin flag to C__JOB_PAGECOUNT_NOTAPPLICABLE__
     * in the LoadPlugins.php file and just comment out this function.
     *
     * parseTotalResultsCount returns the total number of listings that
     * the search returned by parsing the value from the returned HTML
     * *
     * @param $objSimpHTML
     * @return string|null
     */
    function parseTotalResultsCount($objSimpHTML)
    {
        $noResultsText = null;
        $totalItemsText = C__TOTAL_ITEMS_UNKNOWN__;

        //
        // Find the HTML node that holds the no results text
        //
        $resultsSection = $objSimpHTML->find("div#aiSearchResultsSuccess div h2");
        if($resultsSection && count($resultsSection) > 1 && isset($resultsSection[1]))
        {
            // get the text value of that node
            $noResultsText = $resultsSection[1]->text();
        }
        else
        {
            //
            // Find the HTML node that holds the no results text
            //
            $resultsSection = $objSimpHTML->find("div#arNoResultsContainer div h5");
            if($resultsSection && isset($resultsSection[0]))
            {
                // get the text value of that node
                $noResultsText = $resultsSection[0]->text();
            }

        }

        if(!is_null($noResultsText))
        {
            if(strcasecmp($noResultsText, "Oops! Nothing was found.") == 0)
            {
                $totalItemsText = 0;
            }

        }

        if($totalItemsText != 0) {
            //
            // Find the HTML node that holds the result count
            //
            $resultsSection = $objSimpHTML->find("span[id='retCountNumber']");

            if ($resultsSection && isset($resultsSection[0])) {
                // get the text value of that node
                $totalItemsText = $resultsSection[0]->text();
            }
        }

        return $totalItemsText;
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
        $item = null;

        // first looked for the detail view layout and parse that
        $nodesJobRows = $objSimpHTML->find('div[class="aiResultsWrapper"]');
        if(isset($nodesJobRows) && count($nodesJobRows) > 0 )
        {
            foreach($nodesJobRows as $node)
            {
                //
                // get a new record with all columns set to null
                //
                $item = getEmptyJobListingRecord();


                $divMain = $node->find("div[class='aiResultsMainDiv']");
                if(isset($divMain) && count($divMain) >= 1)
                {
                    $divID = $divMain[0]->attr['id'];
                    $item['JobSitePostId']= preg_replace('/[^\d]*/', "", $divID);
                }

                $titleLink = $node->find("a")[0];
                $item['Title'] = $titleLink->text();

                // If we couldn't parse the job title, it's not really a job
                // listing so just continue to the next one
                //
                if($item['Title'] == '') continue;


                $item['Url'] =  $titleLink->href;


                $detailLIs = $node->find("ul li");
                $item['Company'] = $detailLIs[0]->text();
                $item['Location'] = $detailLIs[1]->text();
                $item['PostedAt'] = $detailLIs[2]->text();
                $item['job_site_category'] = $detailLIs[3]->text();

                $ret[] = $item;
            }
        }
        else
        {
            // it's the brief layout, so use that



            $nodesJobRows= $objSimpHTML->find('tr[class="aiResultsRow"]');
            if(isset($nodesJobRows))
            {
                foreach($nodesJobRows as $node)
                {
                    //
                    // get a new record with all columns set to null
                    //
                    $item = getEmptyJobListingRecord();

                    $titleLink = $node->find("a")[0];
                    $item['Title'] = $titleLink->text();


                    $item['Url'] = $titleLink->href;
                    $arrURLParts= explode("-", $item['Url']);
                    $item['JobSitePostId'] = $arrURLParts[count($arrURLParts) - 2];


                    // If we couldn't parse the job title, it's not really a job
                    // listing so just continue to the next one
                    //
                    if($item['Title'] == '') continue;

                    $locNode = $node->find("td[class='aiResultsLocation']");
                    $item['Location'] = $locNode[0]->text();

                    $companyNode = $node->find("td[class='aiResultsCompany']");
                    $item['Company'] = $companyNode[0]->text();

                    $ret[] = $item;
                }
            }

            else
            {
                return parent::parseJobsListForPage($objSimpHTML);
            }
        }

        return $ret;
    }

}


abstract class AbstractOptimizedAdicioCareerCast extends AbstractAdicioCareerCast
{
    function __construct()
    {
        $this->strBaseURLPathSection = str_replace("/jobs/results/keyword/***KEYWORDS***", "/jobs/search/results", $this->strBaseURLPathSection);
        $this->strBaseURLPathSection = str_replace("&kwsMustContain=***KEYWORDS***", "", $this->strBaseURLPathSection);
        parent::__construct();
    }

}


class PluginMashable extends AbstractOptimizedAdicioCareerCast
{
    protected $JobSiteName = 'Mashable';
    protected $childSiteURLBase = 'http://jobs.mashable.com';
    // Note:  Mashable has a short list of jobs (< 500-1000 total) so we exclude keyword search here as an optimization.  We may download more jobs overall, but through fewer round trips to the servers
//    protected $strBaseURLPathSection = "/jobs/results/keyword/***KEYWORDS***?location=***LOCATION***&kwsMustContain=***KEYWORDS***&radius=50&view=List_Detail&sort=PostType+asc%2C+PostDate+desc%2C+IsFeatured+desc&rows=50&modifiedDate=***NUMBER_DAYS***%page=***PAGE_NUMBER***&PostDate=***NUMBER_DAYS***";
//    protected $strBaseURLPathSection = "/jobs/search/results?location=***LOCATION***&radius=50&view=List_Detail&sort=PostType+asc%2C+PostDate+desc%2C+IsFeatured+desc&rows=50&modifiedDate=***NUMBER_DAYS***%page=***PAGE_NUMBER***&PostDate=***NUMBER_DAYS***";
    protected $strBaseURLPathSection = "/jobs/search/results?location=***LOCATION***&radius=50&view=List_Detail&sort=PostType+asc%2C+PostDate+desc%2C+IsFeatured+desc&rows=50&modifiedDate=***NUMBER_DAYS***&SearchNetworks=US&networkView=national#PostDate=***NUMBER_DAYS***&page=***PAGE_NUMBER***";
    protected $LocationType = 'location-city-comma-state';

}

class PluginLocalworkCA extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'LocalworkCA';
    protected $childSiteURLBase = 'http://jobs.localwork.ca';
}

class PluginASME extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'ASME';
    protected $childSiteURLBase = 'http://jobsearch.asme.org';
}

class PluginJacksonville extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Jacksonville';
    protected $childSiteURLBase = 'http://http://jobs.jacksonville.com';
}


class PluginPolitico extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Politico';
    protected $childSiteURLBase = 'http://jobs.powerjobs.com';
}

class PluginIEEE extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'IEEE';
    protected $childSiteURLBase = 'http://jobs.ieee.org';
}
class PluginVariety extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Variety';
    protected $childSiteURLBase = 'http://jobs.variety.com';
}
class PluginCellCom extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'CellCom';
    protected $childSiteURLBase = 'http://jobs.cell.com';
}
class PluginCareerJet extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'CareerJet';
    protected $childSiteURLBase = 'http://www.careerjet.co.uk';
}
class PluginVirginiaPilot extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'VirginiaPilot';
    protected $childSiteURLBase = 'http://careers.hamptonroads.com';
}
class PluginHamptonRoads extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'HamptonRoads';
    protected $childSiteURLBase = 'http://careers.hamptonroads.com';
}
class PluginAnalyticTalent extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'AnalyticTalent';
    protected $childSiteURLBase = 'http://careers.analytictalent.com';
}

class PluginKenoshaNews extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'KenoshaNews';
    protected $childSiteURLBase = 'http://kenosha.careers.adicio.com';
}

class PluginTopekaCapitalJournal extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'TopekaCapitalJournal';
    protected $childSiteURLBase = 'http://jobs.cjonline.com';
}

class PluginRetailCareersNow extends AbstractAlternateAdicioCareerCast
{
    protected $JobSiteName = 'RetailCareersNow';
    protected $childSiteURLBase = 'http://retail.careers.adicio.com';
}
class PluginHealthJobs extends AbstractOptimizedAdicioCareerCast
{
    protected $JobSiteName = 'HealthJobs';
    protected $childSiteURLBase = 'http://healthjobs.careers.adicio.com';
}
class PluginPharmacyJobCenter extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'PharmacyJobCenter';
    protected $childSiteURLBase = 'http://pharmacy.careers.adicio.com';
}
class PluginKCBD extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'KCBD';
    protected $childSiteURLBase = 'http://kcbd.careers.adicio.com';
}
class PluginAfro extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Afro';
    protected $childSiteURLBase = 'http://afro.careers.adicio.com';
}
class PluginJamaCareerCenter extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'JamaCareerCenter';
    protected $childSiteURLBase = 'http://jama.careers.adicio.com';
}

class PluginSeacoastOnline extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'SeacoastOnline';
    protected $childSiteURLBase = 'http://seacoast.careers.adicio.com';
}

class PluginAlbuquerqueJournal extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'AlbuquerqueJournal';
    protected $childSiteURLBase = 'http://abqcareers.careers.adicio.com';
}

class PluginWestHawaiiToday extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'WestHawaiiToday';
    protected $childSiteURLBase = 'http://careers.westhawaiitoday.com';
}

class PluginDeadline extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Deadline';
    protected $childSiteURLBase = 'http://jobsearch.deadline.com';
}

class PluginLogCabinDemocrat extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'LogCabinDemocrat';
    protected $childSiteURLBase = 'jobs.thecabin.net';
}

class PluginPennEnergy extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'PennEnergy';
    protected $childSiteURLBase = 'http://careers.pennenergyjobs.com';
}

class PluginBig4Firms extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Big4Firms';
    protected $childSiteURLBase = 'http://careers.big4.com';
}

class PluginVindy extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'Vindy';
    protected $childSiteURLBase = 'http://careers.vindy.com';
}

class PluginCareerCast extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'CareerCast';
    protected $childSiteURLBase = 'http://www.careercast.com';
}

class PluginTheLancet extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'TheLancet';
    protected $childSiteURLBase = 'http://careers.thelancet.com';
}

class PluginVictoriaTXAdvocate extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'VictoriaTXAdvocate';
    protected $childSiteURLBase = 'http://jobs.crossroadsfinder.com';
}

class PluginTVB extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'TVB';
    protected $childSiteURLBase = 'http://postjobs.tvb.org';
}

class PluginOregonLive extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'OregonLive';
    protected $childSiteURLBase = 'http://jobs.oregonlive.com';
}

class PluginSHRM extends AbstractAdicioCareerCast
{
    protected $JobSiteName = 'SHRM';
    protected $childSiteURLBase = 'http://hrjobs.shrm.org';
}

class PluginCareerCastIT extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastIT";
    protected $childSiteURLBase = "http://it.careercast.com";
}

class PluginCareerCastHealthcare extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastHealthcare";
    protected $childSiteURLBase = "http://healthcare.careercast.com";
}

class PluginCareerCastNursing extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastNursing";
    protected $childSiteURLBase = "http://nursing.careercast.com";
}

class PluginCareerCastTempJobs extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastTempJobs";
    protected $childSiteURLBase = "http://tempjobs.careercast.com";
}

class PluginCareerCastMarketing extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastMarketing";
    protected $childSiteURLBase = "http://marketing.careercast.com";
}

class PluginCareerCastRetail extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastRetail";
    protected $childSiteURLBase = "http://retail.careercast.com";
}

class PluginCareerCastGreenNetwork extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastGreenNetwork";
    protected $childSiteURLBase = "http://green.careercast.com";
}

class PluginCareerCastDiversity extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastDiversity";
    protected $childSiteURLBase = "http://diversity.careercast.com";
}

class PluginCareerCastConstruction extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastConstruction";
    protected $childSiteURLBase = "http://construction.careercast.com";
}

class PluginCareerCastEnergy extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastEnergy";
    protected $childSiteURLBase = "http://energy.careercast.com";
}

class PluginCareerCastTrucking extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastTrucking";
    protected $childSiteURLBase = "http://trucking.careercast.com";
}

class PluginCareerCastDisability extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastDisability";
    protected $childSiteURLBase = "http://disability.careercast.com";
}

class PluginCareerCastHR extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastHR";
    protected $childSiteURLBase = "http://hr.careercast.com";
}

class PluginCareerCastVeteran extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastVeteran";
    protected $childSiteURLBase = "http://veteran.careercast.com";
}

class PluginCareerCastHospitality extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastHospitality";
    protected $childSiteURLBase = "http://hospitality.careercast.com";
}

class PluginCareerCastFinance extends AbstractAdicioCareerCast
{
    protected $JobSiteName = "CareerCastFinance";
    protected $childSiteURLBase = "http://finance.careercast.com";
}

