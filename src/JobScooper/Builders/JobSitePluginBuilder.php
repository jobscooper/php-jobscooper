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

namespace JobScooper\Builders;


use Exception;
use JobScooper\DataAccess\JobSiteRecordQuery;

class JobSitePluginBuilder
{
	/**
	 * @param bool $requireEnabled
	 * @param null $onlyJobSiteKey
	 *
	 * @return array|mixed|null
	 */
	static function getJobSites($requireEnabled=true, $onlyJobSiteKey=null)
    {
	    $sitesBySiteKey = getCacheAsArray("all_jobsites_and_plugins");
		if(empty($sitesBySiteKey)) {

			$plugins = new JobSitePluginBuilder();

			$query = JobSiteRecordQuery::create();

			if ($requireEnabled === true) {
				$query->filterByisDisabled(false);
			}

			$sites = $query
				->find()
				->toKeyIndex('JobSiteKey');

			setAsCacheData("all_jobsites_and_plugins", $sites);

			if (is_null($sitesBySiteKey))
				return array();
		}


	    // find just the site we want if specified and return it or null
		if(!empty($onlyJobSiteKey))
		{
			if(array_key_exists($onlyJobSiteKey, $sitesBySiteKey))
				return $sitesBySiteKey[$onlyJobSiteKey];
			else
				return null;
		}

		// return the full array of sites
	    return $sitesBySiteKey;
    }

	/**
	 * @param bool $fOptimizeBySiteRunOrder
	 *
	 * @return array|mixed|null
	 */
	static function getIncludedJobSites($fOptimizeBySiteRunOrder=false)
    {
	    $sites = getCacheAsArray("included_jobsites");
	    if (is_null($sites))
	    {
	    	$sites = JobSitePluginBuilder::getJobSitesCmdLineIncludedInRun();
		    JobSitePluginBuilder::setIncludedJobSites($sites);
	    }

	    if($fOptimizeBySiteRunOrder === true)
	    {
	    	$sitesToSort = array();
	    	foreach($sites as $k => $v)
	    		$sitesToSort[$k] = $v->toArray();
		    $tmpArrSorted = array_orderby($sitesToSort, "isDisabled", SORT_DESC, "ResultsFilterType", SORT_ASC);

		    // now use the temp sorted array's keys and replace the
		    // values with the actual JobSite objects instead of the
		    // array version we used for sorting
		    //
		    $sorted = array_replace($tmpArrSorted, $sites);

		    return $sorted;
	    }
	    return $sites;
    }

	/**
	 * @param $sitesInclude
	 */
	static function setIncludedJobSites($sitesInclude)
	{
		$GLOBALS[JOBSCOOPER_CACHES_ROOT]["included_jobsites"] = $sitesInclude;
	}

	/**
	 * @return array
	 */
	static function getExcludedJobSites()
	{
		$allSites = JobSitePluginBuilder::getJobSites();
		$inclSites = JobSitePluginBuilder::getIncludedJobSites();

		return array_diff_key($allSites, $inclSites);
	}

	/**
	 * @return array|mixed|null
	 */
	static function getJobSitesCmdLineIncludedInRun()
	{
		$cmdLineSites = getConfigurationSetting("command_line_args.jobsite");
		$sites = self::getJobSites(true);
		if(in_array("all", $cmdLineSites))
			return $sites;

		$includedSites = array_filter($cmdLineSites, function ($v) use ($sites){
			$ret = array_key_exists(strtolower($v), $sites);
			return $ret;
		});

		return array_intersect_key($sites, array_combine($includedSites, $includedSites));
	}

	/**
	 * @param array $setExcluded
	 */
	static function setSitesAsExcluded($setExcluded=array())
	{
		if(empty($setExcluded))
			return;

		$inputIncludedSites = self::getIncludedJobSites();
		$toRemoveFromInc = array_intersect_key($inputIncludedSites, $setExcluded);
		foreach(array_keys($toRemoveFromInc) as $remove)
			unset($inputIncludedSites[$remove]);
		JobSitePluginBuilder::setIncludedJobSites($inputIncludedSites);
	}

	/**
	 * @param $countryCodes
	 */
	static function filterJobSitesByCountryCodes($countryCodes)
	{
		$ccRun = join(", ", $countryCodes);
		$includedSites = JobSitePluginBuilder::getIncludedJobSites();
		$sitesOutOfSearchArea = array();

		foreach ($includedSites as $jobsiteKey => $site) {
			$ccSite = $site->getSupportedCountryCodes();
			if (empty($ccSite))
				$sitesOutOfSearchArea[$jobsiteKey] = $includedSites[$jobsiteKey];
			else {
				$matches = null;
				$fResult = substr_count_multi($ccRun, $ccSite, $matches);
				if (empty($matches) || (count($matches) == 1 && empty($matches[0]))) {
					$sitesOutOfSearchArea[$jobsiteKey] = $site;
				}
			}
		}

		if(!empty($sitesOutOfSearchArea)) {
			JobSitePluginBuilder::setSitesAsExcluded($sitesOutOfSearchArea);
			LogMessage("Skipping searches for " . getArrayDebugOutput(array_keys($sitesOutOfSearchArea)) . " because they do not cover country codes = (" . $ccRun . ").");
		}
	}

	/**
	 *
	 */
	static function updateDBResultFilterTypes()
	{
		$sites = JobSitePluginBuilder::getJobSites();

		foreach ($sites as $jobsiteKey => $site) {
			$plugin = $site->getPlugin();
			if (!empty($plugin))
			{
				if ($plugin->isBitFlagSet(C__JOB_KEYWORD_URL_PARAMETER_NOT_SUPPORTED))
				{
					if ($plugin->isBitFlagSet(C__JOB_LOCATION_URL_PARAMETER_NOT_SUPPORTED))
						$site->setResultsFilterType("all-only");
					else
						$site->setResultsFilterType("all-by-location");
				}
				else
				{
					$site->setResultsFilterType("user-filtered");
				}
				$site->save();
			}
		}

		if(!empty($sites)) {
			LogMessage("Updated ResultsFilterType value in database for " . count($sites) . " jobsite records.");
		}
	}




	protected $_renderer = null;
    protected $_dirPluginsRoot = null;
    protected $_dirJsonConfigs = null;
    protected $_configJsonFiles = array();
    protected $_jsonPluginSetups = array();


	function __construct()
    {
	    $sitesBySiteKey = getCacheAsArray("all_jobsites_and_plugins");
		if(!empty($sitesBySiteKey))
			return;

	    $pathPluginDirectory = __ROOT__.DIRECTORY_SEPARATOR."plugins";
        if (is_null($pathPluginDirectory) || strlen($pathPluginDirectory) == 0)
            throw new Exception("Path to plugins source directory was not set.");

        if(!is_dir($pathPluginDirectory))
	        throw new Exception(sprintf("Unable to access the plugin directory '%s'", $pathPluginDirectory));

        $this->_dirPluginsRoot = realpath($pathPluginDirectory . DIRECTORY_SEPARATOR);

        $this->_dirJsonConfigs = realpath($this->_dirPluginsRoot . DIRECTORY_SEPARATOR . "json_plugins" . DIRECTORY_SEPARATOR);

        $this->_renderer = loadTemplate(__ROOT__ . '/assets/templates/eval_jsonplugin.tmpl');


        $this->_loadJsonPluginConfigFiles_();
        $this->_initializeAllJsonPlugins();

        $this->_syncDatabaseJobSitePluginClassList();

    }

    private function _syncDatabaseJobSitePluginClassList()
    {
        LogMessage('Adding any missing declared jobsite plugins and jobsite records in database...');

        $classListBySite = getAllPluginClassesByJobSiteKey();

	    $all_jobsites_by_key = \JobScooper\DataAccess\JobSiteRecordQuery::create()
            ->filterByIsDisabled(false)
            ->find()
            ->toKeyIndex("JobSiteKey");

        $jobsitesToAdd = array_diff_key($classListBySite, $all_jobsites_by_key);
        if (!empty($jobsitesToAdd)) {

            LogMessage('Adding ' . getArrayValuesAsString($jobsitesToAdd));

            foreach ($jobsitesToAdd as $jobSiteKey => $pluginClass) {
                $dbrec = JobSiteRecordQuery::create()
                    ->filterByJobSiteKey($jobSiteKey)
                    ->findOneOrCreate();

                $dbrec->setJobSiteKey($jobSiteKey);
                $dbrec->setPluginClassName($pluginClass);
                $dbrec->setDisplayName(str_replace("Plugin", "", $pluginClass));
                $dbrec->save();
            }
        }

        $jobsitesToDisable = array_diff_key($all_jobsites_by_key, $classListBySite);
        if (!empty($jobsitesToDisable))
        {
	        LogMessage('Disabling ' . getArrayValuesAsString($jobsitesToDisable));
        	foreach($jobsitesToDisable as $jobSiteKey =>$jobsite)
	        {
		        $all_jobsites_by_key[$jobSiteKey]->setIsDisabled(true);
		        $all_jobsites_by_key[$jobSiteKey]->save();
	        }
        }

        $nEnabledSites = count($all_jobsites_by_key)-count($jobsitesToDisable);
        LogMessage("Loaded {$nEnabledSites} enabled jobsite plugins.");
	    setAsCacheData("all_jobsites_and_plugins", $all_jobsites_by_key);

    }

    private function _initializeAllJsonPlugins()
    {
        $arrAddedPlugins = null;
        LogMessage('Initializing all job site plugins...');

        LogMessage('Generating classes for ' . count($this->_jsonPluginSetups) . ' JSON-loaded plugins...');
        foreach (array('Abstract', 'Plugin') as $type) {
            $plugins = array_filter($this->_jsonPluginSetups, function ($val) use ($type) {
                $matched = preg_match("/^" . $type . "/", $val['PhpClassName']);
                return ($matched > 0);
            });

            foreach (array_keys($plugins) as $agentkey) {
                LogMessage("Running eval statement for class " . $plugins[$agentkey]['PhpClassName']);
                try {
                	if(!in_array($plugins[$agentkey]['PhpClassName'], get_declared_classes()))
	                {
	                    $evalStmt = $this->_getClassInstantiationCode($plugins[$agentkey]);

	                    $success = eval($evalStmt);
	                    if ($success === false)
	                        throw new \Exception("Failed to initialize the plugin eval code for " . $agentkey . ": " . error_get_last()['message']);
	                }
                } catch (\Exception $ex) {
                    handleException($ex);
                }
            }

            LogMessage("Added " . count($plugins) . " " . ($type === "Abstract" ? $type : "json") . " plugins: ");
        }

    }


	/**
	 * @throws \Exception
	 */
	private function _loadJsonPluginConfigFiles_()
    {
        $this->_configJsonFiles = glob($this->_dirJsonConfigs . DIRECTORY_SEPARATOR . "*.json");
        foreach ($this->_configJsonFiles as $f) {
            $dataPlugins = loadJSON($f, null, true);
			if(empty($dataPlugins))
				throw new \Exception("Unable to load JSON plugin data file from " . $f . ": " . json_last_error_msg());
            $plugsToInit = array();
            if (array_key_exists('jobsite_plugins', $dataPlugins)) {
                $plugsToInit = array_values($dataPlugins['jobsite_plugins']);
            } else {
                $plugsToInit[] = $dataPlugins;

            }

            foreach ($plugsToInit as $config) {
                $jsonPlugin = $this->_parsePluginConfig_($config);
                // replace non letter or digits with separator

                $this->_jsonPluginSetups[$jsonPlugin['PhpClassName']] = $jsonPlugin;
            }
        }

    }

    private function _parsePluginConfig_($arrConfigData)
    {

        $pluginData = array();

        setArrayItem($pluginData, 'PhpClassName', $arrConfigData, 'PhpClassName');
        if (empty($pluginData['PhpClassName']))
            $pluginData['PhpClassName'] = "Plugin" . $arrConfigData['JobSiteName'];

        setArrayItem($pluginData, 'JobPostingBaseUrl', $arrConfigData, 'BaseURL');
        setArrayItem($pluginData, 'SearchUrlFormat', $arrConfigData, 'SourceURL');
//        setArrayItem($pluginData,'CountryCodes', $arrConfigData, 'CountryCodes');

        if (array_key_exists("Pagination", $arrConfigData)) {
            setArrayItem($pluginData, 'JobListingsPerPage', $arrConfigData['Pagination'], 'PageLimit');
            setArrayItem($pluginData, 'additionalLoadDelaySeconds', $arrConfigData['Pagination'], 'PageDelaySeconds');


            if (array_key_exists("Type", $arrConfigData['Pagination'])) {

                $pluginData['PaginationType'] = strtoupper($arrConfigData['Pagination']['Type']);
                switch (strtoupper($arrConfigData['Pagination']['Type'])) {
                    case 'NEXT-BUTTON':
                        $pluginData['arrListingTagSetup']['NextButton'] = array(
                            'selector' => $arrConfigData['Pagination']['Selector'],
                            'index' => $arrConfigData['Pagination']['Index'],
                            'type' => 'CSS'
                        );
                        break;

                    case 'LOAD-MORE':
                        $pluginData['arrListingTagSetup']['LoadMoreControl'] = array(
                            'selector' => $arrConfigData['Pagination']['Selector'],
                            'index' => $arrConfigData['Pagination']['Index'],
                            'type' => 'CSS'
                        );
                        break;

                    default:
                        break;
                }
            }
        }
        foreach (array_keys($arrConfigData) as $datakey) {
            if (!array_key_exists($datakey, $pluginData) && !in_array($datakey, array("Collections", "Fields")))
                setArrayItem($pluginData, $datakey, $arrConfigData, $datakey);
        }


        if (array_key_exists("Collections", $arrConfigData) && !is_null($arrConfigData['Collections']) && is_array($arrConfigData['Collections']) && count($arrConfigData['Collections']) > 0 && array_key_exists("Fields", $arrConfigData['Collections'][0]))
        {
            if(!is_array($pluginData['arrListingTagSetup']))
                    $pluginData['arrListingTagSetup'] = array();
            foreach ($arrConfigData['Collections'] as $coll) {
                foreach ($coll['Fields'] as $field) {

                    if ((strcasecmp($field['Extract'], "HTML") == 0) || (strcasecmp($field['Extract'], "ATTR") == 0)) {
                        $field['Type'] = 'CSS';
                    } elseif (strcasecmp($field['Extract'], "TEXT") == 0) {
                        $field['Type'] = 'CSS';
                        $field['Attribute'] = 'text';
                    }


                    $name = getArrayItem('Name', $field);

                    $pluginData['arrListingTagSetup'][$name] = array();
                    $MAP_VALUES = array(
                        ['selector', 'Selector'],
                        ['index', 'Index'],
                        ['return_attribute', 'Attribute'],
                        ['type', 'Type'],
                        ['field', 'Field'],
                        ['value', 'Value'],
                        ['return_value_regex', 'Pattern'],
                        ['return_value_callback', 'Callback'],
                        ['callback_parameter', 'CallbackParameter']
                    );

                    foreach ($MAP_VALUES as $mapping) {
                        setArrayItem($pluginData['arrListingTagSetup'][$name], $mapping[0], $field, $mapping[0]);
                    }

                    foreach ($MAP_VALUES as $mapping) {
                        setArrayItem($pluginData['arrListingTagSetup'][$name], $mapping[0], $field, $mapping[1]);
                    }
                }
            }
        }

        LogMessage("Loaded JSON config for new plugin: " . $pluginData['JobSiteName']);

        return $pluginData;

    }


    private function _getArrayItemForEval($pluginConfig, $key, $quoteItems = true)
    {
        $flags = null;
        if (array_key_exists($key, $pluginConfig) && !is_null($pluginConfig[$key]) && is_array($pluginConfig[$key]) && count($pluginConfig[$key]) >= 1) {
            $flags = "array()";

            $start = "[";
            $glue = ", ";
            $end = "]";

            if ($quoteItems === true) {
                $start = "[\"";
                $glue = "\", \"";
                $end = "\"]";
            }

            $flags = $start . join($glue, array_values($pluginConfig[$key])) . $end;
        }
        return $flags;

    }

    private function _getClassInstantiationCode($pluginConfig)
    {

        $PluginExtendsClassName = "JobScooper\Plugins\Classes\AjaxHtmlSimplePlugin";
        $evalConfig = array();
        $PhpClassName = "Plugin" . $pluginConfig['JobSiteName'];

        $arrayProps = array();
        $stringProps = array();
        $numericProps = array();
        $otherProps = array();
	    $stringProps["JobSiteKey"] = cleanupSlugPart($pluginConfig['JobSiteName']);

        foreach (array_keys($pluginConfig) as $key) {
            switch ($key) {
                case 'AdditionalFlags':
                    $arrayProps['additionalBitFlags'] = $this->_getArrayItemForEval($pluginConfig, 'AdditionalFlags', $quoteItems = false);
                    break;

                case 'PluginExtendsClassName':
                    $PluginExtendsClassName = $pluginConfig[$key];
                    unset($pluginConfig[$key]);
                    break;

                case 'PhpClassName':
                    $PhpClassName = $pluginConfig[$key];
                    unset($pluginConfig[$key]);
                    break;

                case is_numeric($pluginConfig[$key]):
                    $numericProps[$key] = $pluginConfig[$key];
                    break;

                case is_string($pluginConfig[$key]):
                    $stringProps[$key] = $pluginConfig[$key];
                    break;

                case is_array($pluginConfig[$key]):
                    if (!empty($pluginConfig[$key]))
                        $arrayProps[$key] = var_export($pluginConfig[$key], true);
                    break;

                default:
                    $otherProps = $pluginConfig[$key];
                    break;
            }
        }
        $data = array(
            'PhpClassName' => $PhpClassName,
            'PluginExtendsClassName' => $PluginExtendsClassName,
            'string_properties' => $stringProps,
            'numeric_properties' => $numericProps,
            'array_properties' => $arrayProps,
            'other_properties' => $otherProps,
        );

        $evalStmt = call_user_func($this->_renderer, $data);

        return $evalStmt;
    }
}
