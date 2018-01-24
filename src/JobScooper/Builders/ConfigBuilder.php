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
namespace JobScooper\Builders;

use JobScooper\DataAccess\UserQuery;
use JobScooper\DataAccess\User;
use JobScooper\Manager\LocationManager;
use JobScooper\Manager\LoggingManager;
use Propel\Common\Config\ConfigurationManager;
use Propel\Runtime\Exception\InvalidArgumentException;
use \SplFileInfo;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHLAK\Config\Config;
use \Propel\Runtime\Propel;


$GLOBALS['CACHES'] = array('LOCATION_MANAGER' =>null, 'GEOCODER_ENABLED' => true);


/**
 * Class ConfigBuilder
 * @package JobScooper\Builders
 */
class ConfigBuilder
{
	private $_rootOutputDirInfo = null;

	/**
	 * ConfigBuilder constructor.
	 *
	 * @param null $iniFile
	 */
	public function __construct($iniFile = null)
    {
	    if(empty($iniFile))
		    $iniFile = getConfigurationSetting("command_line_args.configfile");

	    if(empty($iniFile))
		    throw new \InvalidArgumentException("Missing user configuration settings file definition.  You must specify the configuration file on the command line.  Aborting.");

	    $envDirOut = getenv('JOBSCOOPER_OUTPUT');
	    if(!empty($envDirOut))
		    setConfigurationSetting("output_directories.root", $envDirOut);

	    $Config = new Config($iniFile,true,"imports");
	    setConfigurationSetting("config_file_settings", $Config->getAll());
    }

    protected $nNumDaysToSearch = -1;
    public $arrConfigFileDetails = array('output' => null, 'output_subfolder' => null, 'config_ini' => null);
    protected $allConfigFileSettings = null;

	/**
	 * @throws \ErrorException
	 * @throws \Exception
	 */
	function initialize()
    {
	    $debug = getConfigurationSetting("command_line_args.debug");
	    setConfigurationSetting("debug", $debug);

        startLogSection("Setting up configuration... ");

        $now = new \DateTime();
        setConfigurationSetting('app_run_id', $now->format('Ymd_His_') .__APP_VERSION__);


	    $file_name = getConfigurationSetting("command_line_args.configfile");
        $this->arrConfigFileDetails = new SplFileInfo($file_name);

	    $rootOutputPath = getConfigurationSetting("output_directories.root");
	    $rootOutputDir = parsePathDetailsFromString($rootOutputPath, C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED);
	    if($rootOutputDir->isDir() !== true)
	    {
		    $outputpath = sprintf("%s%s%s", $this->arrConfigFileDetails->getPathname(), DIRECTORY_SEPARATOR, "output");
		    $rootOutputDir = parsePathDetailsFromString($outputpath, C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED);
		    setConfigurationSetting("output_directories.root", $rootOutputDir->getPathname());
	    }

	    $this->_setupPropelForRun();

        // Now setup all the output folders
        $this->__setupOutputFolders__();

        $strOutfileArrString = getArrayValuesAsString(getConfigurationSetting("output_directories"));
        LogMessage("Output folders configured: " . $strOutfileArrString);


        endLogSection("Loaded configuration details from " . $this->arrConfigFileDetails->getPathname());

	    startLogSection("Configuring specific settings for this run... ");
        $this->_setupRunFromConfig_();

        setConfigurationSetting('number_days', 1);

        endLogSection("Finished setting up run.");

    }

	/**
	 * @throws \ErrorException
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function __setupOutputFolders__()
    {
	    $arrOututDirs = getConfigurationSetting("output_directories");
	    $outputDirectory = $arrOututDirs['root'];
	    if (empty($outputDirectory)) {
		    throw new \ErrorException("Required value for the output folder {$outputDirectory} was not specified. Exiting.");
	    }

        $globalDirs = ["debug", "logs"];
        foreach ($globalDirs as $d) {
            $path = join(DIRECTORY_SEPARATOR, array($outputDirectory, getTodayAsString("-"), $d));
            $details = parsePathDetailsFromString($path, \C__FILEPATH_CREATE_DIRECTORY_PATH_IF_NEEDED);
	        $arrOututDirs[$d] = realpath($details->getPathname());
        }

		setConfigurationSetting('output_directories', $arrOututDirs);

	    if (!isset($GLOBALS['logger']))
		    $GLOBALS['logger'] = new LoggingManager(getOutputDirectory('logs'));
        $GLOBALS['logger']->addFileHandlers(getOutputDirectory('logs'));
	    $this->_setupPropelLogging();
    }

	/**
	 * @throws \ErrorException
	 * @throws \Exception
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _setupRunFromConfig_()
    {

	    //
	    // Load the global search data that will be used to create
	    // and configure all searches
	    //
	    $this->_parseGlobalSearchParameters();

	    //
	    // First load the user email information.  We set this first because it is used
	    // to send error email if something goes wrong anywhere further along our run
	    //
	    $this->_parseUserConfigs();
		$this->_parseAlertReceipients();

        LogMessage("Loaded all configuration settings from " . $this->arrConfigFileDetails->getPathname());

	    // Note:  this must happen before any of the job site plugins are instantiated
	    $this->_parsePluginSettings();

	    $this->_instantiateLocationManager();
	    $this->_parseSeleniumParameters();

	    if(count(JobSitePluginBuilder::getIncludedJobSites()) == 0)
        {
            LogError("No job site plugins could be loaded for the given search geographic locations.  Aborting.");
            return;
        }

    }

	/**
	 * @return \JobScooper\Manager\LocationManager
	 */
	private function _instantiateLocationManager()
    {
        $cache = LocationManager::getLocationManager();
        if(empty($cache)) {
            LocationManager::create();
            $cache = LocationManager::getLocationManager();
        }

        return $cache;
    }


	/**
	 *
	 */
	private function _setupPropelForRun()
    {
	    $cfgDatabase = null;
	    $cfgSettingsFile = $this->_getSetting("propel.configuration_file");
	    if(!empty($cfgSettingsFile)) {
		    LogMessage("Loading Propel configuration file: " . $cfgSettingsFile);
		    $propelCfg = new ConfigurationManager($cfgSettingsFile);
		    $cfgDatabase = $propelCfg->getConfigProperty('database.connections');
		    if (!empty($cfgDatabase)) {
			    LogMessage("Using Propel Connection Settings from Propel config: " . getArrayDebugOutput($cfgDatabase));
		    }
	    }

	    if (empty($cfgDatabase))
	    {
		    $cfgDatabase = $this->_getSetting("propel.database.connections");
		    if(!empty($cfgDatabase))
			    LogMessage("Using Propel Connection Settings from Jobscooper Config: " . getArrayDebugOutput($cfgDatabase));
	    }

	    if (empty($cfgDatabase))
		    throw new InvalidArgumentException("No Propel database connection definitions were found in the config files.  You must define at least one connection's settings under propel.database.connections.");

	    foreach ($cfgDatabase as $key => $setting) {
		    $serviceContainer = \Propel\Runtime\Propel::getServiceContainer();
		    $serviceContainer->checkVersion('2.0.0-dev');
		    $serviceContainer->setAdapterClass($key, $setting['adapter']);
		    $manager = new \Propel\Runtime\Connection\ConnectionManagerSingle();
		    $manager->setConfiguration(array(
			    'dsn'         => $setting['dsn'],
			    'user'        => $setting['user'],
			    'password'    => $setting['password'],
			    'classname'   => '\\Propel\\Runtime\\Connection\\ConnectionWrapper',
			    'model_paths' =>
				    array(
					    0 => 'src',
					    1 => 'vendor',
				    ),
		    ));
		    $manager->setName($key);
		    $serviceContainer->setConnectionManager($key, $manager);
		    $serviceContainer->setDefaultDatasource($key);
	    }

    }

	/**
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _setupPropelLogging()
    {
    LogDebug("Configuring Propel logging...");
        $defaultLogger = $GLOBALS['logger'];
        if(is_null($defaultLogger)) {
            $pathLog = getOutputDirectory('logs') . '/propel-' .getTodayAsString("-").'.log';
            LogWarning("Could not find global logger object so configuring propel logging separately at {$pathLog}");
            $defaultLogger = new Logger('defaultLogger');
            $defaultLogger->pushHandler(new StreamHandler($pathLog, Logger::DEBUG));
            $defaultLogger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
        }

        $serviceContainer = Propel::getServiceContainer();
        $serviceContainer->setLogger('defaultLogger', $defaultLogger);
        if(isDebug()) {
            $con = Propel::getWriteConnection(\JobScooper\DataAccess\Map\JobPostingTableMap::DATABASE_NAME);
            $con->useDebug(true);
            LogMessage("Enabled debug logging for Propel.");
        }
    }

	/**
	 * @param $keyPath
	 *
	 * @return array|mixed
	 */
	private function _getSetting($keyPath)
	{
		if(is_array($keyPath))
		{
			$ret = array();
			foreach ($keyPath as $key) {
				$ret[$key] = $this->_getSetting($key);
			}
			return $ret;
		}

		return getConfigurationSetting("config_file_settings." . $keyPath);
	}

	/**
	 *
	 */
	private function _parsePluginSettings()
    {
        LogMessage("Loading plugin setup information from config file...");

        setConfigurationSetting("plugin_settings", $this->_getSetting("plugin_settings"));
    }

	/**
	 *
	 */
	private function _parseGlobalSearchParameters()
    {
        LogMessage("Loading global search settings from config file...");

	    $gsoset = $this->_getSetting('global_search_options');
		if(!empty($gsoset))
		{
            // This must happen first so that the search locations can be geocoded
            if (array_key_exists('google_maps_api_key', $gsoset)) {
                setConfigurationSetting('google_maps_api_key', $gsoset['google_maps_api_key']);
            }

            $allJobSitesByKey = JobSitePluginBuilder::getAllJobSites();
            foreach ($gsoset as $gsoKey => $gso)
            {
                if(!empty($gso))
                {
                    switch (strtoupper($gsoKey))
                    {
                        case 'EXCLUDED_JOBSITES':
                            if (is_string($gso)) {
                                $gso = preg_split("/\s*,\s*/", $gso);
	                            $gso = array_combine(array_values($gso), $gso);
                            }
                            if (!is_array($gso)) {
                                $gso = array($gso => $gso);
                            }
							setConfigurationSetting("config_excluded_sites", $gso);
                            break;

                        default:
                            setConfigurationSetting($gsoKey, $gso);
                            break;
                    }
               }
            }
        }
    }

	/**
	 * @throws \ErrorException
	 */
	private function _parseSeleniumParameters()
    {
        LogDebug("Loading Selenium settings from config file...");
        $settings = $this->_getSetting("selenium");


	    $settings['autostart'] = filter_var($settings['autostart'], FILTER_VALIDATE_BOOLEAN);

        if (!array_key_exists('server', $settings)) {
            throw new \ErrorException("Configuration missing for [selenium] [server] in the config INI files.");
        }
        elseif (strcasecmp("localhost", $settings['server']) === 0)
        {
            throw new \ErrorException("Invalid server value for [selenium] [server] in the config INI files. You must use named hosts, not localhost.");
        }

        if (!array_key_exists('port', $settings))
	        $settings['port'] = "80";

	    $settings['host_location'] = 'http://' . $settings['server'] . ":" . $settings['port'];

	    setConfigurationSetting("selenium", $settings);

    }

	/**
	 * @throws \Exception
	 * @throws \Propel\Runtime\Exception\PropelException
	 */
	private function _parseUserConfigs()
	{
		LogMessage("Creating or updating users based on config file settings...");

		setConfigurationSetting('alerts.configuration.smtp', $this->_getSetting("alerts.configuration.smtp"));

		$userList = array();

		//
		// Configure the primary user for the config file and set it
		//
		$config_users = $this->_getSetting("users");
		$user_recs = array();
		if (empty($config_users))
			throw new \Exception("No users found in configuration settings.  Aborting.");
		else {
			foreach ($config_users as $key_user => $config_user) {
				$updatedUser = UserQuery::findOrCreateUserByUserSlug(cleanupSlugPart($key_user), $config_user, $overwriteFacts = true);
				if (empty($updatedUser))
					throw new \Exception("Failed to create or update user based on config section users.{$key_user}.");
				$user_recs[$key_user] = $updatedUser;
			}
		}

		setConfigurationSetting("users_for_run", $user_recs);

		$currentUser = null;
		$cmd_line_user_to_run = getConfigurationSetting('command_line_args.user');

		// First try to pull the user from the database by that userslug value.  Use that user
		// if we find one.  This allows a dev to override the local config file data if needed
		if(!empty($cmd_line_user_to_run)) {
			$currentUser = UserQuery::create()
				->findOneByUserSlug($cmd_line_user_to_run);
		}

		// if we didn't match a user, look for one as the key name in a config file section under [users.*]
		if(empty($currentUser) && array_key_exists($cmd_line_user_to_run, $user_recs))
			$currentUser = $user_recs[$cmd_line_user_to_run];

		// if we specified a single user to run, reduce the set of users for run to just that single instance
		if(!empty($currentUser)) {
			$user_recs = array($cmd_line_user_to_run => $currentUser);
			setConfigurationSetting("users_for_run", $user_recs);

			LogMessage("Limiting users run to single, specified user: {$cmd_line_user_to_run}");
		}
		elseif(!empty($cmd_line_user_to_run))
		{
			throw new \Exception("Unable to find user matching {$cmd_line_user_to_run} that was specified for the run.");
		}

		if (empty($user_recs))
				throw new \Exception("No email address or user has been found to send results notifications.  Aborting.");

	}

	/**
	 * @throws \Exception
	 */
	private function _parseAlertReceipients()
	{
		LogMessage("Configuring contacts for alerts...");

		setConfigurationSetting('alerts.configuration.smtp', $this->_getSetting("alerts.configuration.smtp"));


		$keysAlertsTypes = array("alerts.errors.to", "alerts.errors.from", "alerts.results.from");
		foreach($keysAlertsTypes as $alertKey) {
			$arrOtherUserFacts = $this->_getSetting($alertKey);
			if (empty($arrOtherUserFacts))
				continue;

			$nextUser = array();
			$otherUser = UserQuery::findUserByEmailAddress($arrOtherUserFacts['email'], $arrOtherUserFacts, false);
			if (!empty($otherUser))
			{
				$nextUser = $otherUser->toArray();
				$nextUser["User"] = $otherUser;
			}
			else
			{
				$nextUser = $arrOtherUserFacts;
				$nextUser['User'] = null;
			}
			setConfigurationSetting($alertKey, $nextUser);
		}
	}


}