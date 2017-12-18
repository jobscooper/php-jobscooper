<?php

namespace JobScooper\DataAccess;

use \Exception;
use JobScooper\DataAccess\Base\JobSiteRecord as BaseJobSiteRecord;
use JobScooper\Plugins\Classes\BaseJobsSite;

/**
 * Skeleton subclass for representing a row from the 'job_site' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class JobSiteRecord extends BaseJobSiteRecord
{
	/**
	 * @var \JobScooper\Plugins\Classes\BaseJobsSite
	 */
	private $_pluginObject = null;


	/**
	 * @return \JobScooper\Plugins\Classes\BaseJobsSite|null
	 * @throws \Exception
	 */
	function getPlugin()
    {
	    $class = $this->getPluginClassName();
	    if (empty($class))
		    throw new \Exception("Missing jobsite plugin class name for " . $this->getJobSiteKey());

	    if (is_null($this->_pluginObject)) {
		    try {
			    if (!in_array($class, get_declared_classes())) {
				    LogError("Unable to find declared class " . $this->getPluginClassName() . "] for plugin " . $this->getJobSiteKey());
				    $this->_pluginObject = null;
				    $this->setisDisabled(true);
				    $this->save();
				    $this->_pluginObject = null;
			    }
			    else {

				    $this->_pluginObject = new $class();

				    setCacheItem("all_jobsites_and_plugins", $this->getJobSiteKey(), $this);

			    }

			    return $this->_pluginObject;
		    } catch (\Exception $ex) {
			    LogError("Error instantiating jobsite plugin for '" . $this->getJobSiteKey() . "' with class name [" . $this->getPluginClassName() . "]:  " . $ex->getMessage());
			    $this->_pluginObject = null;
		    }
	    }
	    else
	    	return $this->_pluginObject;


    }


	/**
	 * Derived method to catches calls to undefined methods.
	 *
	 *
	 * @param string $name
	 * @param mixed  $params
	 *
	 * @return array|string
	 */
	public function __call($method, $params)
	{
		if (method_exists($this, $method)) {
			return call_user_func(
				array($this, $method),
					$params
			);
		}
		else {
			$plugin = $this->getPlugin();
			if (!empty($plugin) &&
				is_a($plugin, "\JobScooper\Plugins\Classes\BaseJobsSite") &&
				method_exists($plugin, $method))
			{
				return call_user_func_array(
					array($plugin, $method),
					$params
				);
			}
		}
		return false;
	}

}
