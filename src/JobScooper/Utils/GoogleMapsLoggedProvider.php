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

use Geocoder\HttpAdapter\HttpAdapterInterface;
use Geocoder\Provider\GoogleMapsProvider;


class GoogleMapsLoggedProvider extends GoogleMapsProvider
{
    protected $callCounter = 0;

    public function __construct(HttpAdapterInterface $adapter, $locale = null, $region = null, $useSsl = false, $apiKey = null, $logger=null)
    {
        $this->setLogger($logger);
        parent::__construct($adapter, $locale, $region, $useSsl, $apiKey);
    }
    protected $logger = null;

    function setLogger($logger)
    {
        $this->logger = $logger;
    }
    protected function executeQuery($query)
    {
        $this->callCounter += 1;
	    $context = array("channel" => "geocoder", "numberGeoApiCalls" => $this->callCounter, "query" => $query, "call_count_for_run" => $this->callCounter);
	    $this->logger->log(\Monolog\Logger::INFO, "Google Geocoder Called (" . $this->callCounter . " times)", $context);
	    return parent::executeQuery($query);
    }
}