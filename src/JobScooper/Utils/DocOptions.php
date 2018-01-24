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

namespace JobScooper\Utils;

class DocOptions extends PropertyObject
{
    public $arguments = array();

private $doc = "{APP_RUN_COMMAND}

Usage:
  {APP_RUN_COMMAND} <configfile> [--user=<config_user_key>] [--jobsite=<jobsitekey>]... [--stages=<stage_numbers>] [--recap] [--delete] [--debug] [--disable-notifications] 
  {APP_RUN_COMMAND} <configfile> [--debug] [--disable-notifications]
  {APP_RUN_COMMAND} (-h | --help)
  {APP_RUN_COMMAND} --version

Options:
  -h --help                 Show this screen.
  --version                 Show version.
  --user=<config_user_key>  Which set of user configuration settings should we run.  
  --stages=<stage_numbers>  Comma-separated list of stage numbers to run from 1 - 4. [default: 1,2,3]
  --jobsite=<jobsitekey>    Comma-separated list of jobsites to run by JobSiteKey. [default: all]
  --debug  Show debug output. [default: false]
  --delete  Removes the user data associated with specified users.
  --recap  Sends a weekly jobs recap notification to specified users.  
  --disable-notifications   Do not send email alerts for new jobs. [default: false]
";

    public function __construct($commandFile)
    {
        $file = new \SplFileInfo($commandFile);
        $opts = str_ireplace("{APP_RUN_COMMAND}", $file->getFilename(), $this->doc);

        $args = \Docopt::handle($opts, array('version'=>__APP_VERSION__));
        foreach($args->args as $k => $v) {
            $argkey = $k;
            $argkey = cleanupTextValue($argkey, "<", ">");
            $argkey = cleanupTextValue($argkey, "--", "");

            $argval = $v;

            if(is_array($argval) && in_array($argkey, array("jobsite", "stages")))
	            $argval = strtolower(join(",", $argval));
            if (is_string($argval)) {
                $argval = cleanupTextValue($argval, "\"", "\"");
	            if(in_array($argkey, array("jobsite", "stages")) && is_string($argval))
	                $argval = strtolower($argval);
                $arrVals = preg_split("/\s*,\s*/", $argval);
                if (count($arrVals) > 1)
                    $argval = $arrVals;

                if(in_array($argkey, array("jobsite", "stages")) && !is_array($argval) && !empty($argval))
                	$argval = array($argval);
            }

            $this->arguments[$argkey] = $argval;

        }
    }

    public function getAll()
    {
        return $this->arguments;
    }

    static function get($argKey)
    {
        $arguments = getConfigurationSetting('command_line_args');
        if(!empty($arguments) && array_key_exists($argKey, $arguments))
            return $arguments[$argKey];

        return null;
    }

    static function equalsTrue($argKey)
    {
        $cmdline = DocOptions::get($argKey);
        if(is_null($cmdline))
            return false;

        return filter_var($cmdline, FILTER_VALIDATE_BOOLEAN);
    }

}

