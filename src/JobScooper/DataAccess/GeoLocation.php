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

use JobScooper\DataAccess\Base\GeoLocation as BaseGeoLocation;
use JobScooper\DataAccess\Map\GeoLocationTableMap;
use JobScooper\Manager\GeoLocationManager;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

class GeoLocation extends BaseGeoLocation
{
    public function save(ConnectionInterface $con = null, $skipReload = false)
    {
        try {
            return parent::save($con, $skipReload);
        }
        catch (PropelException $ex)
        {
            handleException($ex, "Failed to save GeoLocation: %s", true);
        }
    }

    public function postSave(ConnectionInterface $con = null)
    {
        parent::postSave($con);
    }

    public function setRegion($v)
    {
        parent::setRegion($v);

        $newCode = $this->getRegionCodeFromRegion($v);

        if (is_null($this->getRegionCode()))
            parent::setRegionCode($newCode);
    }

    public function setRegionCode($v)
    {
        if (!is_null($v) && strlen($v) > 0)
            $v = strtoupper($v);

        parent::setRegionCode($v);

        $newState = $this->getRegionFromRegionCode($v);

        if (is_null($this->getRegion()))
            $this->setRegion($newState);

    }

    public function setCountryCode($v)
    {
        if (!is_null($v) && strlen($v) > 0)
            $v = strtoupper($v);

        parent::setCountryCode($v);

    }

    public function setDisplayName($v)
    {
        parent::setDisplayName($v);
        $this->addAlternateName($v);
    }

    public function addAlternateNames($value)
    {
        if(!is_null($value) && is_array($value))
            $names = $value;
        else {
            $names = preg_split("/\s*\|\s*/", $value, $limit = -1, PREG_SPLIT_NO_EMPTY);
        }
        foreach($names as $name)
        {
            $this->addAlternateName($name);
        }
    }

    public function setAlternateNames($value)
    {
        if(!is_null($value) && is_array($value))
            $value = array_unique($value);
        parent::setAlternateNames($value);
    }

    public function formatLocation($formatString)
    {
        return replaceTokensInString($formatString, $this->toArray());
    }

    public function formatLocationByLocationType($locFormatNeeded)
    {
        switch ($locFormatNeeded)
        {

            case 'location-city':
                $locFormatString = "{PLACE}";
                break;

            case 'location-city-comma-statecode':
                $locFormatString = "{PLACE}, {REGIONCODE}";
                break;

            case 'location-city-space-statecode':
                $locFormatString = "{PLACE} {REGIONCODE}";
                break;

            case 'location-city-dash-statecode':
                $locFormatString = "{PLACE}-{REGIONCODE}";
                break;

            case 'location-city-comma-nospace-statecode':
                $locFormatString = "{PLACE},{REGIONCODE}";
                break;

            case 'location-city-comma-statecode-underscores-and-dashes':
                $locFormatString = "{PLACE}__2c-{REGIONCODE}";
                break;

            case 'location-city-comma-state':
                $locFormatString = "{PLACE}, {REGION}";
                break;

            case 'location-city-comma-state-country':
                $locFormatString = "{PLACE}, {REGION}, {COUNTRY}";
                break;

            case 'location-city-comma-state-country-no-commas':
                $locFormatString = "{PLACE} {REGION} {COUNTRY}";
                break;

            case 'location-city-comma-state-comma-country':
                $locFormatString = "{PLACE}, {REGION}, {COUNTRY}";
                break;

            case 'location-city-comma-statecode-comma-country':
                $locFormatString = "{PLACE}, {REGIONCODE}, {COUNTRY}";
                break;

            case 'location-city-comma-state-comma-countrycode':
                $locFormatString = "{PLACE}, {REGION}, {COUNTRYCODE}";
                break;

            case 'location-city-comma-country':
                $locFormatString = "{PLACE}, {COUNTRY}";
                break;

            case 'location-city--comma-countrycode':
                $locFormatString = "{PLACE}, {COUNTRYCODE}";
                break;

            case 'location-city-comma-statecode-comma-countrycode':
                $locFormatString = "{PLACE}, {REGIONCODE}, {COUNTRYCODE}";
                break;

            case 'location-countrycode':
                $locFormatString = "{COUNTRYCODE}";
                break;

            case 'location-city-country-no-commas':
                $locFormatString = "{PLACE} {COUNTRY}";
                break;

            case 'location-state':
                $locFormatString = "{REGION}";
                break;

            case 'location-statecode':
                $locFormatString = "{REGIONCODE}";
                break;

            default:
                $locFormatString = "{DISPLAYNAME}";
                break;
        }

        return $this->formatLocation($locFormatString);
    }

    function fromGeocode($geocode)
    {
        $arrVals = array();
        foreach(array_keys($geocode) as $field) {
            switch ($field) {
                case 'latitude':
                case 'longitude':
                case 'place':
                case 'country':
                case 'countrycode':
                case 'region':
                case 'regioncode':
                case 'county':
                    $arrVals[$field] = $geocode[$field];
                    break;

                case 'primary_name':
                    $arrVals['display_name'] = $geocode['primary_name'];
                    break;

                case 'alternate_names':
                    $names = $this->getAlternateNames();
                    $arrVals['alternate_names'] = $geocode['alternate_names'];
                    if (is_null($names) || count($names) == 0) {
                        $mergednames = array_merge($arrVals['alternate_names'], $names);
                        $arrVals['alternate_names'] = array_unique($mergednames);
                    }
                    break;

                default:
            }
        }
        $this->fromArray($arrVals, GeoLocationTableMap::TYPE_FIELDNAME);
    }



    function getRegionCodeFromRegion($code)
    {

        $STATE_CODES = array(
            "AL" => "ALABAMA",
            "AK" => "ALASKA",
            "AS" => "AMERICAN-SAMOA",
            "AZ" => "ARIZONA",
            "AR" => "ARKANSAS",
            "CA" => "CALIFORNIA",
            "CO" => "COLORADO",
            "CT" => "CONNECTICUT",
            "DE" => "DELAWARE",
            "DC" => "DISTRICT-OF-COLUMBIA",
            "FM" => "FEDERATED-STATES-OF-MICRONESIA",
            "FL" => "FLORIDA",
            "GA" => "GEORGIA",
            "GU" => "GUAM",
            "HI" => "HAWAII",
            "ID" => "IDAHO",
            "IL" => "ILLINOIS",
            "IN" => "INDIANA",
            "IA" => "IOWA",
            "KS" => "KANSAS",
            "KY" => "KENTUCKY",
            "LA" => "LOUISIANA",
            "ME" => "MAINE",
            "MH" => "MARSHALL-ISLANDS",
            "MD" => "MARYLAND",
            "MA" => "MASSACHUSETTS",
            "MI" => "MICHIGAN",
            "MN" => "MINNESOTA",
            "MS" => "MISSISSIPPI",
            "MO" => "MISSOURI",
            "MT" => "MONTANA",
            "NE" => "NEBRASKA",
            "NV" => "NEVADA",
            "NH" => "NEW-HAMPSHIRE",
            "NJ" => "NEW-JERSEY",
            "NM" => "NEW-MEXICO",
            "NY" => "NEW-YORK",
            "NC" => "NORTH-CAROLINA",
            "ND" => "NORTH-DAKOTA",
            "MP" => "NORTHERN-MARIANA-ISLANDS",
            "OH" => "OHIO",
            "OK" => "OKLAHOMA",
            "OR" => "OREGON",
            "PW" => "PALAU",
            "PA" => "PENNSYLVANIA",
            "PR" => "PUERTO-RICO",
            "RI" => "RHODE-ISLAND",
            "SC" => "SOUTH-CAROLINA",
            "SD" => "SOUTH-DAKOTA",
            "TN" => "TENNESSEE",
            "TX" => "TEXAS",
            "UT" => "UTAH",
            "VT" => "VERMONT",
            "VI" => "VIRGIN-ISLANDS",
            "VA" => "VIRGINIA",
            "WA" => "WASHINGTON",
            "WV" => "WEST-VIRGINIA",
            "WI" => "WISCONSIN",
            "WY" => "WYOMING"
        );

        $slug= strtoupper(cleanupSlugPart($code));
        if(array_key_exists($slug, $STATE_CODES))
            return $STATE_CODES[$slug];
        return null;
    }

    function getRegionFromRegionCode($state)
    {

        $STATE_CODES = array(
            "ALABAMA" => "AL",
            "ALASKA" => "AK",
            "AMERICAN-SAMOA" => "AS",
            "ARIZONA" => "AZ",
            "ARKANSAS" => "AR",
            "CALIFORNIA" => "CA",
            "COLORADO" => "CO",
            "CONNECTICUT" => "CT",
            "DELAWARE" => "DE",
            "DISTRICT-OF-COLUMBIA" => "DC",
            "FEDERATED-STATES-OF-MICRONESIA" => "FM",
            "FLORIDA" => "FL",
            "GEORGIA" => "GA",
            "GUAM" => "GU",
            "HAWAII" => "HI",
            "IDAHO" => "ID",
            "ILLINOIS" => "IL",
            "INDIANA" => "IN",
            "IOWA" => "IA",
            "KANSAS" => "KS",
            "KENTUCKY" => "KY",
            "LOUISIANA" => "LA",
            "MAINE" => "ME",
            "MARSHALL-ISLANDS" => "MH",
            "MARYLAND" => "MD",
            "MASSACHUSETTS" => "MA",
            "MICHIGAN" => "MI",
            "MINNESOTA" => "MN",
            "MISSISSIPPI" => "MS",
            "MISSOURI" => "MO",
            "MONTANA" => "MT",
            "NEBRASKA" => "NE",
            "NEVADA" => "NV",
            "NEW-HAMPSHIRE" => "NH",
            "NEW-JERSEY" => "NJ",
            "NEW-MEXICO" => "NM",
            "NEW-YORK" => "NY",
            "NORTH-CAROLINA" => "NC",
            "NORTH-DAKOTA" => "ND",
            "NORTHERN-MARIANA-ISLANDS" => "MP",
            "OHIO" => "OH",
            "OKLAHOMA" => "OK",
            "OREGON" => "OR",
            "PALAU" => "PW",
            "PENNSYLVANIA" => "PA",
            "PUERTO-RICO" => "PR",
            "RHODE-ISLAND" => "RI",
            "SOUTH-CAROLINA" => "SC",
            "SOUTH-DAKOTA" => "SD",
            "TENNESSEE" => "TN",
            "TEXAS" => "TX",
            "UTAH" => "UT",
            "VERMONT" => "VT",
            "VIRGIN-ISLANDS" => "VI",
            "VIRGINIA" => "VA",
            "WASHINGTON" => "WA",
            "WEST-VIRGINIA" => "WV",
            "WISCONSIN" => "WI",
            "WYOMING" => "WY"
        );

        $slug= strtoupper(cleanupSlugPart($state));
        if(array_key_exists($slug, $STATE_CODES))
            return $STATE_CODES[$slug];
        return null;
    }



}

