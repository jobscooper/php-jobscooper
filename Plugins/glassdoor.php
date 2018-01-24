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
 * Class AbstractDice
 *
 *       Used by dice.json plugin configuration to override single method
 */
abstract class AbstractGlassdoor extends \JobScooper\BasePlugin\Classes\AjaxHtmlSimplePlugin
{
    function __construct()
    {

        parent::__construct();
        $this->_flags_ &= ~C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED;

    }


	function doFirstPageLoad(\JobScooper\DataAccess\UserSearchSiteRun $searchDetails)
    {

        $js = "
            fetch('https://www.glassdoor.com/findPopularLocationAjax.htm?term=" . $this->getGeoLocationURLValue($searchDetails) . "&maxLocationsToReturn=10').then(function(response) {
              if(response.ok) {
                response.json().then(function(json) {
                  if(json.length > 0)
                  {
                    console.log('query returned ' + json[0]['longName']);
                    document.getElementById('sc.location').value = json[0]['longName'];
                    document.getElementById('HeroSearchButton').click();
                  }
                  else 
                    console.log('No location was found.'); 
                })
            }
            else 
                console.log('location query failed.'); 
        });
        ";

        $this->selenium->getPageHTML($searchDetails->getSearchStartUrl());

        $this->runJavaScriptSnippet($js, false);
        sleep($this->additionalLoadDelaySeconds + 2);

        $html = $this->getActiveWebdriver()->getPageSource();
        return $html;

    }


}
