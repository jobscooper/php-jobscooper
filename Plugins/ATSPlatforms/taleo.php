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

use \JobScooper\Utils\SimpleHTMLHelper;


/**
 * Class AbstractTaleo
 */
abstract class AbstractTaleo extends \JobScooper\BasePlugin\Classes\ServerHtmlPlugin
{
    protected $use1ToTDForCount = False;
    protected $taleoOrgID = null;
    protected $JobListingsPerPage = 50;
    protected $arrResultsCountTag = array('type' =>null, 'value'=>null, 'index'=>null);
    protected $JobSiteName = null;
    protected $server = null;

	/**
	 * AbstractTaleo constructor.
	 */
	function __construct()
    {
        $this->PaginationType = C__PAGINATION_PAGE_VIA_URL;
        
        if(!isset($this->server) or strlen($this->server) <= 0) {
            $this->server = "https://ch.tbe.taleo.net/CH11/ats/careers/searchResults.jsp";
        }

        if(empty($this->SearchUrlFormat) && isset($this->taleoOrgID) and strlen($this->taleoOrgID) > 0)
        {
            $this->SearchUrlFormat = $this->server.'?org=' . $this->taleoOrgID . '&cws=1***ITEM_NUMBER***';
        }

        if(!isset($this->JobSiteName) && strlen($this->JobSiteName) <= 0)
        {
            $strPluginSite = get_class($this);
            $strPluginSite = str_replace("Plugin", "", $strPluginSite);
            $this->JobSiteName = $strPluginSite;
        }

        return parent::__construct();
    }

	/**
	 * @param $objSimpHTML
	 *
	 * @return array|null|string|void
	 */
	function parseTotalResultsCount(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
    {
        if($this->use1ToTDForCount)
            return $this->parseTotalResultsCountFrom1ToTD($objSimpHTML);
        else
            return $this->parseTotalResultsCountFromTaleoCommonDivTable($objSimpHTML, $this->arrResultsCountTag['type'], $this->arrResultsCountTag['value'], $this->arrResultsCountTag['index']);
    }



    protected $SearchUrlFormat = null;

	/**
	 * @param $nItem
	 *
	 * @return int|string
	 */
	function getItemURLValue($nItem)
    {
        $strFirst = "&act=first&rowFrom=" . $this->JobListingsPerPage;
        $strNext = "&act=next&rowFrom=";

        if($nItem == null || $nItem == 1) { return $strFirst; }

        $ret = $nItem - $this->JobListingsPerPage;
        if($ret < 0) return $strFirst;

        return $strNext . $ret;
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


        $nodesJobs= $objSimpHTML->find('table[id="cws-search-results"] tr');
        $counter = 0;

        foreach($nodesJobs as $node)
        {
            if($counter == 0)
            {
                $counter++;
                continue;
            } // skip the header row
            $counter++;

            $item = getEmptyJobListingRecord();
            $item['Company'] = $this->JobSiteName;
            $item['Url'] = $node->find("td a")[0]->href;
            $item['Title'] = $node->find("td a")[0]->text();
            $item['JobSitePostId'] = explode("rid=", $item['Url'])[1];
            if($item['Title'] == '') continue;

            $tds = $node->find("td");
            if(isset($tds) && isset($tds[1])) $item['Location'] = $node->find("td")[1]->text();
            if(isset($tds) && isset($tds[2]))$item['job_site_category'] = $tds[2]->text();

            $ret[] = $item;
        }

        return $ret;
    }

	/**
	 * @param $objSimpHTML
	 *
	 * @return null
	 */
	function parseTotalResultsCountFrom1ToTD(SimpleHTMLHelper $objSimpHTML) {
        $nodes = $objSimpHTML->find('td[class="nowrapRegular"] b');
        if($nodes && count($nodes)>=2)
        {
            return $nodes[1]->text();
        }

        return null;
    }


	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 * @param                                    $divTagType
	 * @param                                    $divTagValue
	 * @param                                    $trIndex
	 *
	 * @return array|null
	 * @throws \Exception
	 */
	function parseTotalResultsCountFromTaleoCommonDivTable(SimpleHTMLHelper $objSimpHTML, $divTagType, $divTagValue, $trIndex)
    {
        $strTotalItemsCount = null;
        $node = $objSimpHTML->find("#wrapper div.avada-row table tbody tr td b");
        if(!empty($node)) {
            $text = $node[0];
            $strTotalItemsCount = $node[0]->text();
        } else {

            $nodeHelper = new SimpleHTMLHelper($objSimpHTML);
            $node = $nodeHelper->find("div[" . $divTagType . "='" . $divTagValue . "'] table tbody tr");
            if (!empty($node) && !empty($trIndex) && count($node) > $trIndex + 1)
                $node = $node[$trIndex];
            else
                return null;

            $nodeSecond = new SimpleHTMLHelper($node);
            $nodeThird = $nodeSecond->find("td");
            if (!empty($nodeThird) && count($nodeThird) >= 1) {
                $totalItemsText = $nodeThird[0]->text();
                $arrItemsFirstSplit = explode("found ", trim($totalItemsText));
                $strTotalItemsCount = explode(" matching", trim($arrItemsFirstSplit[1]));
            }
        }

        return $strTotalItemsCount;
    }

}


/**
 * Class PluginEntercom
 */
class PluginEntercom extends AbstractTaleoATS
{
	protected $JobSiteName = "Entercom";
	protected $JobListingsPerPage = 100;
	protected $SearchUrlFormat = "https://chk.tbe.taleo.net/chk05/ats/careers/v2/searchResults?org=ENTERCOM&cws=37";

	protected $arrListingTagSetup = array(
		'JobPostCount' => array('selector'=>'span.oracletaleocwsv2-panel-number'),
		'JobPostItem' => array('selector'=>'div.oracletaleocwsv2-accordion-head-info'),
		'Title' => array('selector' => 'h4 a'),
		'Url' => array('selector' => 'h4 a', 'index' => 0, 'return_attribute' => 'href'),
		'Location' => array('selector' => 'div', 'index' => 1),
		'Category' => array('selector' => 'div', 'index' => 0),
		'JobSitePostId' => array('selector' => 'h4 a', 'index' => 0, 'return_attribute' => 'href', 'return_value_regex' =>  '/rid=(.*)/i')
	);
}

/**
 * Class AbstractTaleoATS
 */
class AbstractTaleoATS extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
	protected $use1ToTDForCount = True;
	protected $JobListingsPerPage = 100;
	protected $PaginationType = C__PAGINATION_PAGE_VIA_NEXTBUTTON;

	protected $arrBaseListingTagSetup = array(
		'JobPostCount' => array('selector'=>'span.oracletaleocwsv2-panel-number'),
		'JobPostItem' => array('selector'=>'div.oracletaleocwsv2-accordion-head-info'),
		'NextButton' => array('selector' => 'a.next'),
		'Title' => array('selector' => 'h4 a'),
		'Url' => array('selector' => 'h4 a', 'index' => 0, 'return_attribute' => 'href'),
		'Location' => array('selector' => 'div', 'index' => 1),
		'Category' => array('selector' => 'div', 'index' => 0),
		'JobSitePostId' => array('selector' => 'h4 a', 'index' => 0, 'return_attribute' => 'href', 'return_value_regex' =>  '/rid=(.*)/i')
	);


}

//
//class PluginSeattleGenetics extends AbstractTaleo
//{
//    protected $server = "http://chp.tbe.taleo.net/chp04/ats/careers/searchResults.jsp";
//    protected $taleoOrgID = "SEAGEN";
//
//    function parseTotalResultsCountFromTaleoCommonDivTable($objSimpHTML, $divTagType, $divTagValue, $trIndex)
//    {
//        $nodeHelper = new SimpleHTMLHelper($objSimpHTML);
//        $node = $nodeHelper->get("div.inner table tbody tr[2] td b", 0, true);
//        $totalItemsText = $node->innertext();
//
//        return $totalItemsText;
//    }
//
//    protected $arrResultsCountTag = array('type' =>'class', 'value'=>'inner', 'index'=>0);
//}

/**
 * Class PluginInternetBrands
 */
class PluginInternetBrands extends AbstractTaleo
{
	protected $JobPostingBaseUrl = 'http://www.internetbrands.com/work-with-us/';
	protected $taleoOrgID = "CARSDIRECT";
	protected $arrResultsCountTag = array('type' =>'class', 'value'=>'avada-row', 'index'=>1);
}

/**
 * Class PluginTraderJoes
 */
class PluginTraderJoes extends AbstractTaleo
{
	protected $use1ToTDForCount = True;
	protected $JobPostingBaseUrl = 'http://www.traderjoes.com/careers/index.asp';
	protected $taleoOrgID = "TRADERJOES";
	protected $arrResultsCountTag = array('type' =>'id', 'value'=>'taleoContent', 'index'=>1);


	/**
	 * @param $arrItem
	 *
	 * @return array
	 */
	function cleanupJobItemFields($arrItem)
	{
		if(array_key_exists('Location', $arrItem) && !empty($arrItem['Location']))
			$arrItem['Location'] = preg_replace("/Store #\d+ - /", "", $arrItem['Location']);
		return parent::cleanupJobItemFields($arrItem);
	}
}

/**
 * Class PluginPorch
 */
class PluginPorch extends AbstractTaleo
{
	protected $use1ToTDForCount = True;
	protected $JobPostingBaseUrl = 'http://about.porch.com/careers';
	protected $taleoOrgID = "PORCH";
	protected $arrResultsCountTag = array('type' =>'id', 'value'=>'summary', 'index'=>1);
}
