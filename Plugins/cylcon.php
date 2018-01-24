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
 * Class AbstractCylcon
 *
 *       Used by cyclcon.json plugin configuration to override single method
 */
abstract class AbstractCylcon extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
	/**
	 * @param \JobScooper\Utils\SimpleHTMLHelper $objSimpHTML
	 *
	 * @return array|null|void
	 * @throws \Exception
	 */
	function parseJobsListForPage(\JobScooper\Utils\SimpleHTMLHelper $objSimpHTML)
	{
		$ret = parent::parseJobsListForPage($objSimpHTML);
		if(!empty($ret) && count($ret) >= 1)
		{
			foreach($ret as $k => $item)
			{
				if(array_key_exists("JobSitePostId", $item) && empty($item["JobSitePostId"]) &&
					array_key_exists("Url", $item) && !empty($item["Url"])) {
					$item['JobSitePostId'] = $item['Url'];
				}
				$item['JobSitePostId'] = str_ireplace(array("RedirectWEB.php?q=", "=&token="), "", $item['Url']);
				if (strlen($item['JobSitePostId']) >= 1024)
					$item['JobSitePostId'] = substr($item['JobSitePostId'], strlen($item['JobSitePostId']) - 1023, 1023);
				$ret[$k] = $item;
			}
		}

		return $ret;
	}
}
