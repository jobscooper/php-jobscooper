<?php

namespace JobScooper\DataAccess\Map;

use JobScooper\DataAccess\GeoLocation;
use JobScooper\DataAccess\GeoLocationQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'geolocation' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class GeoLocationTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'JobScooper.DataAccess.Map.GeoLocationTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'geolocation';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\JobScooper\\DataAccess\\GeoLocation';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'JobScooper.DataAccess.GeoLocation';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 12;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 12;

    /**
     * the column name for the geolocation_id field
     */
    const COL_GEOLOCATION_ID = 'geolocation.geolocation_id';

    /**
     * the column name for the display_name field
     */
    const COL_DISPLAY_NAME = 'geolocation.display_name';

    /**
     * the column name for the geolocation_key field
     */
    const COL_GEOLOCATION_KEY = 'geolocation.geolocation_key';

    /**
     * the column name for the place field
     */
    const COL_PLACE = 'geolocation.place';

    /**
     * the column name for the county field
     */
    const COL_COUNTY = 'geolocation.county';

    /**
     * the column name for the region field
     */
    const COL_REGION = 'geolocation.region';

    /**
     * the column name for the regioncode field
     */
    const COL_REGIONCODE = 'geolocation.regioncode';

    /**
     * the column name for the country field
     */
    const COL_COUNTRY = 'geolocation.country';

    /**
     * the column name for the countrycode field
     */
    const COL_COUNTRYCODE = 'geolocation.countrycode';

    /**
     * the column name for the latitude field
     */
    const COL_LATITUDE = 'geolocation.latitude';

    /**
     * the column name for the longitude field
     */
    const COL_LONGITUDE = 'geolocation.longitude';

    /**
     * the column name for the alternate_names field
     */
    const COL_ALTERNATE_NAMES = 'geolocation.alternate_names';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    // geocodable behavior

    /**
     * Kilometers unit
     */
    const KILOMETERS_UNIT = 1.609344;

    /**
     * Miles unit
     */
    const MILES_UNIT = 1.1515;

    /**
     * Nautical miles unit
     */
    const NAUTICAL_MILES_UNIT = 0.8684;

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('GeoLocationId', 'DisplayName', 'GeoLocationKey', 'Place', 'County', 'Region', 'RegionCode', 'Country', 'CountryCode', 'Latitude', 'Longitude', 'AlternateNames', ),
        self::TYPE_CAMELNAME     => array('geoLocationId', 'displayName', 'geoLocationKey', 'place', 'county', 'region', 'regionCode', 'country', 'countryCode', 'latitude', 'longitude', 'alternateNames', ),
        self::TYPE_COLNAME       => array(GeoLocationTableMap::COL_GEOLOCATION_ID, GeoLocationTableMap::COL_DISPLAY_NAME, GeoLocationTableMap::COL_GEOLOCATION_KEY, GeoLocationTableMap::COL_PLACE, GeoLocationTableMap::COL_COUNTY, GeoLocationTableMap::COL_REGION, GeoLocationTableMap::COL_REGIONCODE, GeoLocationTableMap::COL_COUNTRY, GeoLocationTableMap::COL_COUNTRYCODE, GeoLocationTableMap::COL_LATITUDE, GeoLocationTableMap::COL_LONGITUDE, GeoLocationTableMap::COL_ALTERNATE_NAMES, ),
        self::TYPE_FIELDNAME     => array('geolocation_id', 'display_name', 'geolocation_key', 'place', 'county', 'region', 'regioncode', 'country', 'countrycode', 'latitude', 'longitude', 'alternate_names', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('GeoLocationId' => 0, 'DisplayName' => 1, 'GeoLocationKey' => 2, 'Place' => 3, 'County' => 4, 'Region' => 5, 'RegionCode' => 6, 'Country' => 7, 'CountryCode' => 8, 'Latitude' => 9, 'Longitude' => 10, 'AlternateNames' => 11, ),
        self::TYPE_CAMELNAME     => array('geoLocationId' => 0, 'displayName' => 1, 'geoLocationKey' => 2, 'place' => 3, 'county' => 4, 'region' => 5, 'regionCode' => 6, 'country' => 7, 'countryCode' => 8, 'latitude' => 9, 'longitude' => 10, 'alternateNames' => 11, ),
        self::TYPE_COLNAME       => array(GeoLocationTableMap::COL_GEOLOCATION_ID => 0, GeoLocationTableMap::COL_DISPLAY_NAME => 1, GeoLocationTableMap::COL_GEOLOCATION_KEY => 2, GeoLocationTableMap::COL_PLACE => 3, GeoLocationTableMap::COL_COUNTY => 4, GeoLocationTableMap::COL_REGION => 5, GeoLocationTableMap::COL_REGIONCODE => 6, GeoLocationTableMap::COL_COUNTRY => 7, GeoLocationTableMap::COL_COUNTRYCODE => 8, GeoLocationTableMap::COL_LATITUDE => 9, GeoLocationTableMap::COL_LONGITUDE => 10, GeoLocationTableMap::COL_ALTERNATE_NAMES => 11, ),
        self::TYPE_FIELDNAME     => array('geolocation_id' => 0, 'display_name' => 1, 'geolocation_key' => 2, 'place' => 3, 'county' => 4, 'region' => 5, 'regioncode' => 6, 'country' => 7, 'countrycode' => 8, 'latitude' => 9, 'longitude' => 10, 'alternate_names' => 11, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('geolocation');
        $this->setPhpName('GeoLocation');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\JobScooper\\DataAccess\\GeoLocation');
        $this->setPackage('JobScooper.DataAccess');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('geolocation_id', 'GeoLocationId', 'INTEGER', true, null, null);
        $this->addColumn('display_name', 'DisplayName', 'VARCHAR', true, 100, null);
        $this->getColumn('display_name')->setPrimaryString(true);
        $this->addColumn('geolocation_key', 'GeoLocationKey', 'VARCHAR', true, 100, null);
        $this->addColumn('place', 'Place', 'VARCHAR', false, 100, null);
        $this->addColumn('county', 'County', 'VARCHAR', false, 100, null);
        $this->addColumn('region', 'Region', 'VARCHAR', false, 100, null);
        $this->addColumn('regioncode', 'RegionCode', 'VARCHAR', false, 50, null);
        $this->addColumn('country', 'Country', 'VARCHAR', false, 100, null);
        $this->addColumn('countrycode', 'CountryCode', 'VARCHAR', false, 5, null);
        $this->addColumn('latitude', 'Latitude', 'FLOAT', false, null, null);
        $this->addColumn('longitude', 'Longitude', 'FLOAT', false, null, null);
        $this->addColumn('alternate_names', 'AlternateNames', 'ARRAY', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('JobPosting', '\\JobScooper\\DataAccess\\JobPosting', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':geolocation_id',
    1 => ':geolocation_id',
  ),
), null, null, 'JobPostings', false);
        $this->addRelation('UserSearchPair', '\\JobScooper\\DataAccess\\UserSearchPair', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':geolocation_id',
    1 => ':geolocation_id',
  ),
), null, null, 'UserSearchPairs', false);
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'geocodable' => array('auto_update' => 'false', 'latitude_column' => 'latitude', 'longitude_column' => 'longitude', 'type' => 'DOUBLE', 'size' => '11', 'scale' => '8', 'geocode_ip' => 'false', 'ip_column' => 'ip_address', 'geocode_address' => 'false', 'address_columns' => 'street,locality,region,postal_code,country', 'geocoder_provider' => '\Geocoder\Provider\OpenStreetMapProvider', 'geocoder_adapter' => '\Geocoder\HttpAdapter\CurlHttpAdapter', 'geocoder_api_key' => 'false', 'geocoder_api_key_provider' => 'false', ),
        );
    } // getBehaviors()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return string The primary key hash of the row
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        return (int) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)
        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? GeoLocationTableMap::CLASS_DEFAULT : GeoLocationTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     * @return array           (GeoLocation object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = GeoLocationTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = GeoLocationTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + GeoLocationTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = GeoLocationTableMap::OM_CLASS;
            /** @var GeoLocation $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            GeoLocationTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = GeoLocationTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = GeoLocationTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var GeoLocation $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                GeoLocationTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(GeoLocationTableMap::COL_GEOLOCATION_ID);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_DISPLAY_NAME);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_GEOLOCATION_KEY);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_PLACE);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_COUNTY);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_REGION);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_REGIONCODE);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_COUNTRY);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_COUNTRYCODE);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_LATITUDE);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_LONGITUDE);
            $criteria->addSelectColumn(GeoLocationTableMap::COL_ALTERNATE_NAMES);
        } else {
            $criteria->addSelectColumn($alias . '.geolocation_id');
            $criteria->addSelectColumn($alias . '.display_name');
            $criteria->addSelectColumn($alias . '.geolocation_key');
            $criteria->addSelectColumn($alias . '.place');
            $criteria->addSelectColumn($alias . '.county');
            $criteria->addSelectColumn($alias . '.region');
            $criteria->addSelectColumn($alias . '.regioncode');
            $criteria->addSelectColumn($alias . '.country');
            $criteria->addSelectColumn($alias . '.countrycode');
            $criteria->addSelectColumn($alias . '.latitude');
            $criteria->addSelectColumn($alias . '.longitude');
            $criteria->addSelectColumn($alias . '.alternate_names');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(GeoLocationTableMap::DATABASE_NAME)->getTable(GeoLocationTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(GeoLocationTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(GeoLocationTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new GeoLocationTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a GeoLocation or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or GeoLocation object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param  ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GeoLocationTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \JobScooper\DataAccess\GeoLocation) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(GeoLocationTableMap::DATABASE_NAME);
            $criteria->add(GeoLocationTableMap::COL_GEOLOCATION_ID, (array) $values, Criteria::IN);
        }

        $query = GeoLocationQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            GeoLocationTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                GeoLocationTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the geolocation table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return GeoLocationQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a GeoLocation or Criteria object.
     *
     * @param mixed               $criteria Criteria or GeoLocation object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GeoLocationTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from GeoLocation object
        }


        // Set the correct dbName
        $query = GeoLocationQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // GeoLocationTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
GeoLocationTableMap::buildTableMap();
