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
use JobScooper\DataAccess\GeoLocation;

/**
 * Based on Geocoder\StringFormatter by William Durand <william.durand1@gmail.com>
 */
class GeoLocationFormatter
{
    const STREET_NUMBER = '%n';

    const STREET_NAME = '%S';

    const LOCALITY = '%L';

    const POSTAL_CODE = '%z';

    const SUB_LOCALITY = '%D';

    const REGION = '%R';
    const REGIONCODE = '%r';

    const ADMIN_LEVEL = '%A';

    const ADMIN_LEVEL_CODE = '%a';

    const COUNTRY = '%C';

    const COUNTRY_CODE = '%c';

    const TIMEZONE = '%T';

    /**
     * Transform an `Address` instance into a string representation.
     *
     * @param GeoLocation $location
     * @param string   $format
     *
     * @return string
     */
    public function format(GeoLocation $location, $format)
    {
        $replace = [
            self::LOCALITY => $location->getPlace(),
            self::REGION => $location->getRegion(),
            self::REGIONCODE => $location->getRegionCode(),
            self::ADMIN_LEVEL."1" => $location->getRegion(),
            self::ADMIN_LEVEL_CODE."1" => $location->getRegionCode(),
            self::ADMIN_LEVEL."2" => $location->getCounty(),
            self::ADMIN_LEVEL_CODE."2" => "",
            self::COUNTRY => $location->getCountry(),
            self::COUNTRY_CODE => $location->getCountryCode()
        ];

        return strtr($format, $replace);
    }
}