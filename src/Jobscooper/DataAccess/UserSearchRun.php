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

namespace JobScooper\DataAccess;
require_once __ROOT__ . "/bootstrap.php";

use Exception as Exception;
use JobScooper\DataAccess\Base\UserSearchRun as BaseUserSearchRun;
use JobScooper\DataAccess\Map\UserSearchRunTableMap;
use const JobScooper\Plugins\Base\VALUE_NOT_SUPPORTED;
use Propel\Runtime\Connection\ConnectionInterface;

/**
 * Skeleton subclass for representing a row from the 'user_search_run' table.
 *
 *
 *
 * You should add additional methods to this class to meet the
 * application requirements.  This class will only be generated as
 * long as it does not already exist in the output directory.
 *
 */
class SearchSettings extends \ArrayObject
{
    function __construct()
    {
        $arrFields = Array(
            'key' => null,
            'site_name' => null,
            'search_start_url' => null,
            'keywords_string_for_url' => null,
            'base_url_format' => null,
            'location_user_specified_override' => null,
            'location_search_value' => VALUE_NOT_SUPPORTED,
            'keyword_search_override' => null,
            'keywords_array' => null,
        );
        parent::__construct($arrFields, \ArrayObject::ARRAY_AS_PROPS);

        return $this;
    }
}



class OldUserSearchRun extends BaseUserSearchRun implements \ArrayAccess
{
    protected $searchSettingKeys = array(
        'search_start_url',
        'keywords_string_for_url',
        'base_url_format',
        'location_user_specified_override',
        'location_search_value',
        'location_set_key',
        'keyword_search_override',
        'keywords_array',
        'keywords_array_tokenized');



    private function _setOldNameToNewColumn($keyOldName, $arrDetails)
    {
        $valueSet = false;

        if (array_key_exists($keyOldName, $arrDetails) && !is_null($arrDetails[$keyOldName])) {
            switch ($keyOldName) {

                case 'key':
                    $this->setSearchKey($arrDetails[$keyOldName]);
                    $valueSet = true;
                    break;

                case 'site_name':
                    $this->setJobSiteKey($arrDetails[$keyOldName]);
                    $valueSet = true;
                    break;

                case in_array($keyOldName, $this->searchSettingKeys):
                    $settings = $this->getSearchSettings();
                    $settings[$keyOldName] = $arrDetails[$keyOldName];
                    $this->setSearchSettings($settings);
                    $valueSet = true;
                    break;

                default:
                    break;
            }
        }

        return $valueSet;
    }

    public function fromSearchDetailsArray($arrDetails)
    {
        if ($this->getSearchSettings()) {
            $this->setSearchSettings(new SearchSettings());
        }

        foreach (array_keys($arrDetails) as $key) {
            $this->_setOldNameToNewColumn($key, $arrDetails);
        }
    }


    public function set($name, $value)
    {

        switch ($name) {
            case in_array($name, $this->searchSettingKeys):
                $settings = $this->getSearchSettings();
                $settings[$name] = $value;
                $this->setSearchSettings($settings);
                break;

            case 'site_name':
                return $this->setJobSiteKey($value);
                break;

            case 'user_search_run_id':
                break;

            default:
                $throwEx = null;
                try {
                    $this->{$name} = $value;
                    $throwEx = null;
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                try {
                    $this->setByName($name, \JobScooper\DataAccess\Map\UserSearchRunTableMap::TYPE_FIELDNAME, $value);
                    $throwEx = null;
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                try {
                    $this->setByName($name, \JobScooper\DataAccess\Map\UserSearchRunTableMap::TYPE_COLNAME, $value);
                    $throwEx = null;
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                try {
                    $this->setByName($name, \JobScooper\DataAccess\Map\UserSearchRunTableMap::TYPE_CAMELNAME, $value);
                    $throwEx = null;
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                if (!is_null($throwEx))
                    throw $throwEx;

                break;
        }

    }

    public function &get($name)
    {

        switch ($name) {

            case in_array($name, $this->searchSettingKeys):
                $settings = $this->getSearchSettings();
                return $settings[$name];
                break;

            case 'site_name':
                return $this->getJobSiteKey();
                break;

            case 'key':
                return $this->getUserSearchRunKey();
                break;

            default:
                $throwEx = null;
                try {
                    return $this->{$name};
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                try {
                    return $this->getByName($name, \JobScooper\DataAccess\Map\UserSearchRunTableMap::TYPE_FIELDNAME);
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                try {
                    return $this->getByName($name, \JobScooper\DataAccess\Map\UserSearchRunTableMap::TYPE_COLNAME);
                } catch (Exception $ex) {
                    $throwEx = $ex;
                }

                break;
        }
    }



    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Whether or not an offset exists
     *
     * @param string An offset to check for
     * @access public
     * @return boolean
     * @abstracting ArrayAccess
     */
    public function offsetExists($offset)
    {
        return null !== $this->get($offset);
    }

    /**
     * Unsets an offset
     *
     * @param string The offset to unset
     * @access public
     * @abstracting ArrayAccess
     */
    public function offsetUnset($offset)
    {
        if ($this->offsetExists($offset)) {
            $this->set($offset, null);
        }
    }
}


class UserSearchRun extends OldUserSearchRun
{
    protected $userObject = null;

    public function __construct($arrSearchDetails = null, $outputDirectory = null)
    {
        parent::__construct();

        if ($this->getSearchSettings()) {
            $this->setSearchSettings(new SearchSettings());
        }
        $this->setAppRunId($GLOBALS['USERDATA']['configuration_settings']['app_run_id']);

        $this->userObject = $GLOBALS['USERDATA']['configuration_settings']['user_details'];
        $this->setUserSlug($this->userObject->getUserSlug());

        if (!is_null($arrSearchDetails) && is_array($arrSearchDetails) && count($arrSearchDetails) > 0) {
            $this->fromSearchDetailsArray($arrSearchDetails);
        }

    }

    public function getJobSitePluginObject()
    {
        return getPluginObjectForJobSite($this->getJobSiteKey());
    }

    public function getJobSiteObject()
    {
        $jobsiteObj = findOrCreateJobSitePlugin($this->getJobSiteKey());
        return $jobsiteObj;
    }

    function isSearchIncludedInRun()
    {
        return $this->getJobSiteObject()->isSearchIncludedInRun();
    }

    function failRunWithException($ex)
    {
        $line = null;
        $code = null;
        $msg = null;
        $file = null;

        if (!is_null($ex)) {
            $line = $ex->getLine();
            $code = $ex->getCode();
            $msg = $ex->getMessage();
            $file = $ex->getFile();
            $errexc = array(
                'error_details' => strval($ex),
                'exception_code' => $code,
                'exception_message' => $msg,
                'exception_line' => $line,
                'exception_file' => $file,
                'error_datetime' => new \DateTime()
            );
            $this->failRunWithErrorMessage($errexc);
        }
    }
    function failRunWithErrorMessage($err)
    {
        $arrV = object_to_array($err);
        $this->setRunResultCode("failed");
        parent::setRunErrorDetails($arrV);
    }

    function setRunSucceeded()
    {
        return $this->setRunResultCode('successful');
    }

    function setRunResultCode($val)
    {
        switch ($val) {
            case "failed":
                $this->setLastFailedAt(time());
                break;

            case 'successful':
                $this->_updateNextRunDate_();
                $this->setLastFailedAt(null);
                $this->setRunErrorDetails(array());
                break;

            case "skipped":
                break;

            case "not-run":
            case "excluded":
            default:
                break;
        }

        return parent::setRunResultCode($val);
    }

    public function setJobSiteKey($v)
    {
        $slug = cleanupSlugPart($v);
        parent::setJobSiteKey($slug);
    }

    public function shouldRunNow()
    {
        $nextTime = $this->getStartNextRunAfter();
        if (!is_null($nextTime))
            return (time() > $nextTime->getTimestamp());

        return true;
    }

    protected function createSlug()
    {
        // create the slug based on the `slug_pattern` and the object properties
        $slug = $this->createRawSlug();
        // truncate the slug to accommodate the size of the slug column
        $slug = $this->limitSlugSize($slug);
//        // add an incremental index to make sure the slug is unique
//        $slug = $this->makeSlugUnique($slug);

        return $slug;
    }

    private function _updateNextRunDate_()
    {
        if (!is_null($this->getLastRunAt())) {
            $nextDate = $this->getLastRunAt();
            if (is_null($nextDate))
                $nextDate = new \DateTime();
            date_add($nextDate, date_interval_create_from_date_string('18 hours'));

            $this->setStartNextRunAfter($nextDate);
        }
    }



    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {

        if ($this->isColumnModified(UserSearchRunTableMap::COL_USER_SEARCH_RUN_ID)) {
            $this->setUserSearchRunId(null);
        }

        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;

    }

}