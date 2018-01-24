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

namespace JobScooper\DataAccess;

use JobScooper\DataAccess\Base\GeoLocation as BaseGeoLocation;
use JobScooper\DataAccess\Map\GeoLocationTableMap;
use JobScooper\Utils\GeoLocationFormatter;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;

class GeoLocation extends BaseGeoLocation
{
	static $STATE_CODES = array(
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

	static $STATES_BY_CODE = array(
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


	static $COUNTRY_CODE_REMAPPINGS = array(
		"GB" => "UK"
	);

	public function getCountryCode()
	{
		$ret = parent::getCountryCode();
		if(!empty($ret) && array_key_exists(strtoupper($ret), GeoLocation::$COUNTRY_CODE_REMAPPINGS))
			return GeoLocation::$COUNTRY_CODE_REMAPPINGS[$ret];

		return $ret;
	}

	public function setCountryCode($value)
	{
		if(!empty($value) && in_array(strtoupper($value), GeoLocation::$COUNTRY_CODE_REMAPPINGS))
			$value = array_search(strtoupper($value), GeoLocation::$COUNTRY_CODE_REMAPPINGS);
		parent::setCountryCode($value);
	}


	function updateDisplayName()
    {
        $dispVal = "";

        if(!empty($this->getPlace()))
            $dispVal = "%L";

        if($this->getCountryCode() == "US")
        {
            if(!empty($this->getRegionCode()))
                $dispVal .= " %r %c";
            else
                $dispVal .= " %c";
        }
        else {
            if (!empty($this->getRegion()))
                $dispVal .= " %R %c";
            else
                $dispVal .= " %c";
        }

        $this->setDisplayName($this->format($dispVal));
    }

    function setAutoPopulatedFields()
    {
        $this->updateDisplayName();

        $this->setGeoLocationKey(strtolower($this->format("%c_%r_%L")));

        if($this->isNew())
        {
            $this->setAlternateNames($this->getVariants());
        }

    }
    function preSave(ConnectionInterface $con = null)
    {
        return parent::preSave($con);
    }

	/**
	 * @param \Propel\Runtime\Connection\ConnectionInterface|null $con
	 *
	 * @return int
	 * @throws \Exception
	 */
	public function save(ConnectionInterface $con = null)
    {
        try {
            return parent::save($con);
        }
        catch (PropelException $ex)
        {
            handleException($ex, "Failed to save GeoLocation " . $this->getDisplayName() . ".  Error: %s", true);
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

        if (!empty($this->getRegionCode()))
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


    function fromGeocode($geocode)
    {
        $arrVals = array();
        foreach(array_keys($geocode) as $field) {
            switch (strtolower($field)) {
                case 'latitude':
                case 'longitude':
                case 'place':
                case 'country':
                case 'countrycode':
                case 'region':
                case 'regioncode':
                case 'county':
                    $arrVals[strtolower($field)] = $geocode[$field];
                    break;

                case 'primary_name':
                    $arrVals['display_name'] = $geocode['primary_name'];
                    break;

                case 'city':
                    $arrVals['place'] = $geocode['city'];
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

	    if(strcasecmp($arrVals['country'], $arrVals['countrycode']) == 0)
		    $arrVals['countrycode'] = null;

	    if(strcasecmp($arrVals['region'], $arrVals['regioncode']) == 0)
		    $arrVals['regioncode'] = null;


	    $this->fromArray($arrVals, GeoLocationTableMap::TYPE_FIELDNAME);
        $this->setAutoPopulatedFields();
    }

    public function toFlatArrayForCSV()
    {
        $arrItem = $this->toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false);
        updateColumnsForCSVFlatArray($arrItem, new GeoLocationTableMap());

        return $arrItem;
    }

    function getRegionCodeFromRegion($code)
    {
        $slug= strtoupper(cleanupSlugPart($code));
        if(array_key_exists($slug, GeoLocation::$STATE_CODES))
            return GeoLocation::$STATE_CODES[$slug];
        return null;
    }

    function getRegionFromRegionCode($state)
    {
        $slug= strtoupper(cleanupSlugPart($state));
        if(array_key_exists($slug, GeoLocation::$STATES_BY_CODE))
            return GeoLocation::$STATES_BY_CODE[$slug];
        return null;
    }

    public function format($fmt)
    {
        $formatter = new GeoLocationFormatter();
        return $formatter->format($this, $fmt);
    }



    public function getVariants()
    {
        $altFormats = array(
            'location-place' => '%L',
            'location-place-state' => '%L %R',
            'location-place-state-country' => '%L %R %C',
            'location-place-state-countrycode' => '%L %R %c',
            'location-place-statecode' => '%L %r',
            'location-place-statecode-country' => '%L %r %C',
            'location-place-statecode-countrycode' => '%L %r %c',
            'location-place-country' => '%L %C',
            'location-place-countrycode' => '%L %c',
        );

        $retNames = array();

        foreach(array_keys($altFormats) as $k)
        {
            $retNames[$k] = $this->format($altFormats[$k]);
        }

        return $retNames;
    }


    public function formatLocationByLocationType($locFormatNeeded)
    {
        switch ($locFormatNeeded)
        {

            case 'location-city':
                $locFormatString = "%L";
                break;

            case 'location-city-comma-statecode':
                $locFormatString = "%L, %r";
                break;

            case 'location-city-space-statecode':
                $locFormatString = "%L %r";
                break;

            case 'location-city-dash-statecode':
                $locFormatString = "%L-%r";
                break;

            case 'location-city-comma-nospace-statecode':
                $locFormatString = "%L,%r";
                break;

            case 'location-city-comma-statecode-underscores-and-dashes':
                $locFormatString = "%L__2c-%r";
                break;

            case 'location-city-comma-state':
                $locFormatString = "%L, %R";
                break;

            case 'location-city-comma-state-country':
                $locFormatString = "%L, %R, %C";
                break;

            case 'location-city-comma-state-country-no-commas':
                $locFormatString = "%L %R %C";
                break;

            case 'location-city-comma-state-comma-country':
                $locFormatString = "%L, %R, %C";
                break;

            case 'location-city-comma-statecode-comma-country':
                $locFormatString = "%L, %r, %C";
                break;

            case 'location-city-comma-state-comma-countrycode':
                $locFormatString = "%L, %R, %c";
                break;

            case 'location-city-comma-country':
                $locFormatString = "%L, %C";
                break;

            case 'location-city-comma-countrycode':
                $locFormatString = "%L, %c";
                break;

            case 'location-city-comma-statecode-comma-countrycode':
                $locFormatString = "%L, %r, %c";
                break;

            case 'location-countrycode':
                $locFormatString = "%c";
                break;

            case 'location-city-country-no-commas':
                $locFormatString = "%L %C";
                break;

            case 'location-state':
                $locFormatString = "%R";
                break;

            case 'location-statecode':
                $locFormatString = "%r";
                break;

            default:
                return $this->getDisplayName();
                break;
        }

        return $this->format($locFormatString);
    }

}


