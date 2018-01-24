<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\GeoLocation as ChildGeoLocation;
use JobScooper\DataAccess\GeoLocationQuery as ChildGeoLocationQuery;
use JobScooper\DataAccess\Map\GeoLocationTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'geolocation' table.
 *
 *
 *
 * @method     ChildGeoLocationQuery orderByGeoLocationId($order = Criteria::ASC) Order by the geolocation_id column
 * @method     ChildGeoLocationQuery orderByDisplayName($order = Criteria::ASC) Order by the display_name column
 * @method     ChildGeoLocationQuery orderByGeoLocationKey($order = Criteria::ASC) Order by the geolocation_key column
 * @method     ChildGeoLocationQuery orderByPlace($order = Criteria::ASC) Order by the place column
 * @method     ChildGeoLocationQuery orderByCounty($order = Criteria::ASC) Order by the county column
 * @method     ChildGeoLocationQuery orderByRegion($order = Criteria::ASC) Order by the region column
 * @method     ChildGeoLocationQuery orderByRegionCode($order = Criteria::ASC) Order by the regioncode column
 * @method     ChildGeoLocationQuery orderByCountry($order = Criteria::ASC) Order by the country column
 * @method     ChildGeoLocationQuery orderByCountryCode($order = Criteria::ASC) Order by the countrycode column
 * @method     ChildGeoLocationQuery orderByLatitude($order = Criteria::ASC) Order by the latitude column
 * @method     ChildGeoLocationQuery orderByLongitude($order = Criteria::ASC) Order by the longitude column
 * @method     ChildGeoLocationQuery orderByAlternateNames($order = Criteria::ASC) Order by the alternate_names column
 *
 * @method     ChildGeoLocationQuery groupByGeoLocationId() Group by the geolocation_id column
 * @method     ChildGeoLocationQuery groupByDisplayName() Group by the display_name column
 * @method     ChildGeoLocationQuery groupByGeoLocationKey() Group by the geolocation_key column
 * @method     ChildGeoLocationQuery groupByPlace() Group by the place column
 * @method     ChildGeoLocationQuery groupByCounty() Group by the county column
 * @method     ChildGeoLocationQuery groupByRegion() Group by the region column
 * @method     ChildGeoLocationQuery groupByRegionCode() Group by the regioncode column
 * @method     ChildGeoLocationQuery groupByCountry() Group by the country column
 * @method     ChildGeoLocationQuery groupByCountryCode() Group by the countrycode column
 * @method     ChildGeoLocationQuery groupByLatitude() Group by the latitude column
 * @method     ChildGeoLocationQuery groupByLongitude() Group by the longitude column
 * @method     ChildGeoLocationQuery groupByAlternateNames() Group by the alternate_names column
 *
 * @method     ChildGeoLocationQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildGeoLocationQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildGeoLocationQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildGeoLocationQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildGeoLocationQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildGeoLocationQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildGeoLocationQuery leftJoinJobPosting($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobPosting relation
 * @method     ChildGeoLocationQuery rightJoinJobPosting($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobPosting relation
 * @method     ChildGeoLocationQuery innerJoinJobPosting($relationAlias = null) Adds a INNER JOIN clause to the query using the JobPosting relation
 *
 * @method     ChildGeoLocationQuery joinWithJobPosting($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobPosting relation
 *
 * @method     ChildGeoLocationQuery leftJoinWithJobPosting() Adds a LEFT JOIN clause and with to the query using the JobPosting relation
 * @method     ChildGeoLocationQuery rightJoinWithJobPosting() Adds a RIGHT JOIN clause and with to the query using the JobPosting relation
 * @method     ChildGeoLocationQuery innerJoinWithJobPosting() Adds a INNER JOIN clause and with to the query using the JobPosting relation
 *
 * @method     ChildGeoLocationQuery leftJoinUserSearchPair($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserSearchPair relation
 * @method     ChildGeoLocationQuery rightJoinUserSearchPair($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserSearchPair relation
 * @method     ChildGeoLocationQuery innerJoinUserSearchPair($relationAlias = null) Adds a INNER JOIN clause to the query using the UserSearchPair relation
 *
 * @method     ChildGeoLocationQuery joinWithUserSearchPair($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserSearchPair relation
 *
 * @method     ChildGeoLocationQuery leftJoinWithUserSearchPair() Adds a LEFT JOIN clause and with to the query using the UserSearchPair relation
 * @method     ChildGeoLocationQuery rightJoinWithUserSearchPair() Adds a RIGHT JOIN clause and with to the query using the UserSearchPair relation
 * @method     ChildGeoLocationQuery innerJoinWithUserSearchPair() Adds a INNER JOIN clause and with to the query using the UserSearchPair relation
 *
 * @method     \JobScooper\DataAccess\JobPostingQuery|\JobScooper\DataAccess\UserSearchPairQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildGeoLocation findOne(ConnectionInterface $con = null) Return the first ChildGeoLocation matching the query
 * @method     ChildGeoLocation findOneOrCreate(ConnectionInterface $con = null) Return the first ChildGeoLocation matching the query, or a new ChildGeoLocation object populated from the query conditions when no match is found
 *
 * @method     ChildGeoLocation findOneByGeoLocationId(int $geolocation_id) Return the first ChildGeoLocation filtered by the geolocation_id column
 * @method     ChildGeoLocation findOneByDisplayName(string $display_name) Return the first ChildGeoLocation filtered by the display_name column
 * @method     ChildGeoLocation findOneByGeoLocationKey(string $geolocation_key) Return the first ChildGeoLocation filtered by the geolocation_key column
 * @method     ChildGeoLocation findOneByPlace(string $place) Return the first ChildGeoLocation filtered by the place column
 * @method     ChildGeoLocation findOneByCounty(string $county) Return the first ChildGeoLocation filtered by the county column
 * @method     ChildGeoLocation findOneByRegion(string $region) Return the first ChildGeoLocation filtered by the region column
 * @method     ChildGeoLocation findOneByRegionCode(string $regioncode) Return the first ChildGeoLocation filtered by the regioncode column
 * @method     ChildGeoLocation findOneByCountry(string $country) Return the first ChildGeoLocation filtered by the country column
 * @method     ChildGeoLocation findOneByCountryCode(string $countrycode) Return the first ChildGeoLocation filtered by the countrycode column
 * @method     ChildGeoLocation findOneByLatitude(double $latitude) Return the first ChildGeoLocation filtered by the latitude column
 * @method     ChildGeoLocation findOneByLongitude(double $longitude) Return the first ChildGeoLocation filtered by the longitude column
 * @method     ChildGeoLocation findOneByAlternateNames(array $alternate_names) Return the first ChildGeoLocation filtered by the alternate_names column *

 * @method     ChildGeoLocation requirePk($key, ConnectionInterface $con = null) Return the ChildGeoLocation by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOne(ConnectionInterface $con = null) Return the first ChildGeoLocation matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildGeoLocation requireOneByGeoLocationId(int $geolocation_id) Return the first ChildGeoLocation filtered by the geolocation_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByDisplayName(string $display_name) Return the first ChildGeoLocation filtered by the display_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByGeoLocationKey(string $geolocation_key) Return the first ChildGeoLocation filtered by the geolocation_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByPlace(string $place) Return the first ChildGeoLocation filtered by the place column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByCounty(string $county) Return the first ChildGeoLocation filtered by the county column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByRegion(string $region) Return the first ChildGeoLocation filtered by the region column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByRegionCode(string $regioncode) Return the first ChildGeoLocation filtered by the regioncode column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByCountry(string $country) Return the first ChildGeoLocation filtered by the country column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByCountryCode(string $countrycode) Return the first ChildGeoLocation filtered by the countrycode column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByLatitude(double $latitude) Return the first ChildGeoLocation filtered by the latitude column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByLongitude(double $longitude) Return the first ChildGeoLocation filtered by the longitude column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildGeoLocation requireOneByAlternateNames(array $alternate_names) Return the first ChildGeoLocation filtered by the alternate_names column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildGeoLocation[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildGeoLocation objects based on current ModelCriteria
 * @method     ChildGeoLocation[]|ObjectCollection findByGeoLocationId(int $geolocation_id) Return ChildGeoLocation objects filtered by the geolocation_id column
 * @method     ChildGeoLocation[]|ObjectCollection findByDisplayName(string $display_name) Return ChildGeoLocation objects filtered by the display_name column
 * @method     ChildGeoLocation[]|ObjectCollection findByGeoLocationKey(string $geolocation_key) Return ChildGeoLocation objects filtered by the geolocation_key column
 * @method     ChildGeoLocation[]|ObjectCollection findByPlace(string $place) Return ChildGeoLocation objects filtered by the place column
 * @method     ChildGeoLocation[]|ObjectCollection findByCounty(string $county) Return ChildGeoLocation objects filtered by the county column
 * @method     ChildGeoLocation[]|ObjectCollection findByRegion(string $region) Return ChildGeoLocation objects filtered by the region column
 * @method     ChildGeoLocation[]|ObjectCollection findByRegionCode(string $regioncode) Return ChildGeoLocation objects filtered by the regioncode column
 * @method     ChildGeoLocation[]|ObjectCollection findByCountry(string $country) Return ChildGeoLocation objects filtered by the country column
 * @method     ChildGeoLocation[]|ObjectCollection findByCountryCode(string $countrycode) Return ChildGeoLocation objects filtered by the countrycode column
 * @method     ChildGeoLocation[]|ObjectCollection findByLatitude(double $latitude) Return ChildGeoLocation objects filtered by the latitude column
 * @method     ChildGeoLocation[]|ObjectCollection findByLongitude(double $longitude) Return ChildGeoLocation objects filtered by the longitude column
 * @method     ChildGeoLocation[]|ObjectCollection findByAlternateNames(array $alternate_names) Return ChildGeoLocation objects filtered by the alternate_names column
 * @method     ChildGeoLocation[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class GeoLocationQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\GeoLocationQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\GeoLocation', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildGeoLocationQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildGeoLocationQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildGeoLocationQuery) {
            return $criteria;
        }
        $query = new ChildGeoLocationQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildGeoLocation|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(GeoLocationTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = GeoLocationTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
            // the object is already in the instance pool
            return $obj;
        }

        return $this->findPkSimple($key, $con);
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildGeoLocation A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT geolocation_id, display_name, geolocation_key, place, county, region, regioncode, country, countrycode, latitude, longitude, alternate_names FROM geolocation WHERE geolocation_id = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildGeoLocation $obj */
            $obj = new ChildGeoLocation();
            $obj->hydrate($row);
            GeoLocationTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildGeoLocation|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, ConnectionInterface $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the geolocation_id column
     *
     * Example usage:
     * <code>
     * $query->filterByGeoLocationId(1234); // WHERE geolocation_id = 1234
     * $query->filterByGeoLocationId(array(12, 34)); // WHERE geolocation_id IN (12, 34)
     * $query->filterByGeoLocationId(array('min' => 12)); // WHERE geolocation_id > 12
     * </code>
     *
     * @param     mixed $geoLocationId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByGeoLocationId($geoLocationId = null, $comparison = null)
    {
        if (is_array($geoLocationId)) {
            $useMinMax = false;
            if (isset($geoLocationId['min'])) {
                $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $geoLocationId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($geoLocationId['max'])) {
                $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $geoLocationId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $geoLocationId, $comparison);
    }

    /**
     * Filter the query on the display_name column
     *
     * Example usage:
     * <code>
     * $query->filterByDisplayName('fooValue');   // WHERE display_name = 'fooValue'
     * $query->filterByDisplayName('%fooValue%', Criteria::LIKE); // WHERE display_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $displayName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByDisplayName($displayName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($displayName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_DISPLAY_NAME, $displayName, $comparison);
    }

    /**
     * Filter the query on the geolocation_key column
     *
     * Example usage:
     * <code>
     * $query->filterByGeoLocationKey('fooValue');   // WHERE geolocation_key = 'fooValue'
     * $query->filterByGeoLocationKey('%fooValue%', Criteria::LIKE); // WHERE geolocation_key LIKE '%fooValue%'
     * </code>
     *
     * @param     string $geoLocationKey The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByGeoLocationKey($geoLocationKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($geoLocationKey)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_KEY, $geoLocationKey, $comparison);
    }

    /**
     * Filter the query on the place column
     *
     * Example usage:
     * <code>
     * $query->filterByPlace('fooValue');   // WHERE place = 'fooValue'
     * $query->filterByPlace('%fooValue%', Criteria::LIKE); // WHERE place LIKE '%fooValue%'
     * </code>
     *
     * @param     string $place The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByPlace($place = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($place)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_PLACE, $place, $comparison);
    }

    /**
     * Filter the query on the county column
     *
     * Example usage:
     * <code>
     * $query->filterByCounty('fooValue');   // WHERE county = 'fooValue'
     * $query->filterByCounty('%fooValue%', Criteria::LIKE); // WHERE county LIKE '%fooValue%'
     * </code>
     *
     * @param     string $county The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByCounty($county = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($county)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_COUNTY, $county, $comparison);
    }

    /**
     * Filter the query on the region column
     *
     * Example usage:
     * <code>
     * $query->filterByRegion('fooValue');   // WHERE region = 'fooValue'
     * $query->filterByRegion('%fooValue%', Criteria::LIKE); // WHERE region LIKE '%fooValue%'
     * </code>
     *
     * @param     string $region The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByRegion($region = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($region)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_REGION, $region, $comparison);
    }

    /**
     * Filter the query on the regioncode column
     *
     * Example usage:
     * <code>
     * $query->filterByRegionCode('fooValue');   // WHERE regioncode = 'fooValue'
     * $query->filterByRegionCode('%fooValue%', Criteria::LIKE); // WHERE regioncode LIKE '%fooValue%'
     * </code>
     *
     * @param     string $regionCode The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByRegionCode($regionCode = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($regionCode)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_REGIONCODE, $regionCode, $comparison);
    }

    /**
     * Filter the query on the country column
     *
     * Example usage:
     * <code>
     * $query->filterByCountry('fooValue');   // WHERE country = 'fooValue'
     * $query->filterByCountry('%fooValue%', Criteria::LIKE); // WHERE country LIKE '%fooValue%'
     * </code>
     *
     * @param     string $country The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByCountry($country = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($country)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_COUNTRY, $country, $comparison);
    }

    /**
     * Filter the query on the countrycode column
     *
     * Example usage:
     * <code>
     * $query->filterByCountryCode('fooValue');   // WHERE countrycode = 'fooValue'
     * $query->filterByCountryCode('%fooValue%', Criteria::LIKE); // WHERE countrycode LIKE '%fooValue%'
     * </code>
     *
     * @param     string $countryCode The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByCountryCode($countryCode = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($countryCode)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_COUNTRYCODE, $countryCode, $comparison);
    }

    /**
     * Filter the query on the latitude column
     *
     * Example usage:
     * <code>
     * $query->filterByLatitude(1234); // WHERE latitude = 1234
     * $query->filterByLatitude(array(12, 34)); // WHERE latitude IN (12, 34)
     * $query->filterByLatitude(array('min' => 12)); // WHERE latitude > 12
     * </code>
     *
     * @param     mixed $latitude The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByLatitude($latitude = null, $comparison = null)
    {
        if (is_array($latitude)) {
            $useMinMax = false;
            if (isset($latitude['min'])) {
                $this->addUsingAlias(GeoLocationTableMap::COL_LATITUDE, $latitude['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($latitude['max'])) {
                $this->addUsingAlias(GeoLocationTableMap::COL_LATITUDE, $latitude['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_LATITUDE, $latitude, $comparison);
    }

    /**
     * Filter the query on the longitude column
     *
     * Example usage:
     * <code>
     * $query->filterByLongitude(1234); // WHERE longitude = 1234
     * $query->filterByLongitude(array(12, 34)); // WHERE longitude IN (12, 34)
     * $query->filterByLongitude(array('min' => 12)); // WHERE longitude > 12
     * </code>
     *
     * @param     mixed $longitude The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByLongitude($longitude = null, $comparison = null)
    {
        if (is_array($longitude)) {
            $useMinMax = false;
            if (isset($longitude['min'])) {
                $this->addUsingAlias(GeoLocationTableMap::COL_LONGITUDE, $longitude['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($longitude['max'])) {
                $this->addUsingAlias(GeoLocationTableMap::COL_LONGITUDE, $longitude['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_LONGITUDE, $longitude, $comparison);
    }

    /**
     * Filter the query on the alternate_names column
     *
     * @param     array $alternateNames The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByAlternateNames($alternateNames = null, $comparison = null)
    {
        $key = $this->getAliasedColName(GeoLocationTableMap::COL_ALTERNATE_NAMES);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($alternateNames as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($alternateNames as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($alternateNames as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::NOT_LIKE);
                } else {
                    $this->add($key, $value, Criteria::NOT_LIKE);
                }
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_ALTERNATE_NAMES, $alternateNames, $comparison);
    }

    /**
     * Filter the query on the alternate_names column
     * @param     mixed $alternateNames The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByAlternateName($alternateNames = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($alternateNames)) {
                $alternateNames = '%| ' . $alternateNames . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $alternateNames = '%| ' . $alternateNames . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(GeoLocationTableMap::COL_ALTERNATE_NAMES);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $alternateNames, $comparison);
            } else {
                $this->addAnd($key, $alternateNames, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(GeoLocationTableMap::COL_ALTERNATE_NAMES, $alternateNames, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobPosting object
     *
     * @param \JobScooper\DataAccess\JobPosting|ObjectCollection $jobPosting the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByJobPosting($jobPosting, $comparison = null)
    {
        if ($jobPosting instanceof \JobScooper\DataAccess\JobPosting) {
            return $this
                ->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $jobPosting->getGeoLocationId(), $comparison);
        } elseif ($jobPosting instanceof ObjectCollection) {
            return $this
                ->useJobPostingQuery()
                ->filterByPrimaryKeys($jobPosting->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByJobPosting() only accepts arguments of type \JobScooper\DataAccess\JobPosting or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the JobPosting relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function joinJobPosting($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('JobPosting');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'JobPosting');
        }

        return $this;
    }

    /**
     * Use the JobPosting relation JobPosting object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\JobPostingQuery A secondary query class using the current class as primary query
     */
    public function useJobPostingQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinJobPosting($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'JobPosting', '\JobScooper\DataAccess\JobPostingQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\UserSearchPair object
     *
     * @param \JobScooper\DataAccess\UserSearchPair|ObjectCollection $userSearchPair the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildGeoLocationQuery The current query, for fluid interface
     */
    public function filterByUserSearchPair($userSearchPair, $comparison = null)
    {
        if ($userSearchPair instanceof \JobScooper\DataAccess\UserSearchPair) {
            return $this
                ->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $userSearchPair->getGeoLocationId(), $comparison);
        } elseif ($userSearchPair instanceof ObjectCollection) {
            return $this
                ->useUserSearchPairQuery()
                ->filterByPrimaryKeys($userSearchPair->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserSearchPair() only accepts arguments of type \JobScooper\DataAccess\UserSearchPair or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserSearchPair relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function joinUserSearchPair($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserSearchPair');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'UserSearchPair');
        }

        return $this;
    }

    /**
     * Use the UserSearchPair relation UserSearchPair object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserSearchPairQuery A secondary query class using the current class as primary query
     */
    public function useUserSearchPairQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserSearchPair($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserSearchPair', '\JobScooper\DataAccess\UserSearchPairQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildGeoLocation $geoLocation Object to remove from the list of results
     *
     * @return $this|ChildGeoLocationQuery The current query, for fluid interface
     */
    public function prune($geoLocation = null)
    {
        if ($geoLocation) {
            $this->addUsingAlias(GeoLocationTableMap::COL_GEOLOCATION_ID, $geoLocation->getGeoLocationId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the geolocation table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GeoLocationTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            GeoLocationTableMap::clearInstancePool();
            GeoLocationTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    /**
     * Performs a DELETE on the database based on the current ModelCriteria
     *
     * @param ConnectionInterface $con the connection to use
     * @return int             The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                         if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public function delete(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(GeoLocationTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(GeoLocationTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            GeoLocationTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            GeoLocationTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // geocodable behavior

    /**
     * Adds distance from a given origin column to query.
     *
     * @param double $latitude       The latitude of the origin point.
     * @param double $longitude      The longitude of the origin point.
     * @param double $unit           The unit measure.
     *
     * @return \JobScooper\DataAccess\GeoLocationQuery The current query, for fluid interface
     */
    public function withDistance($latitude, $longitude, $unit = GeoLocationTableMap::KILOMETERS_UNIT)
    {
        if (GeoLocationTableMap::MILES_UNIT === $unit) {
            $earthRadius = 3959;
        } elseif (GeoLocationTableMap::NAUTICAL_MILES_UNIT === $unit) {
            $earthRadius = 3440;
        } else {
            $earthRadius = 6371;
        }

        $sql = 'ABS(%s * ACOS( ROUND(%s * COS(RADIANS(%s)) * COS(RADIANS(%s) - %s) + %s * SIN(RADIANS(%s)),14)))';
        $preparedSql = sprintf($sql,
            $earthRadius,
            cos(deg2rad($latitude)),
            $this->getAliasedColName(GeoLocationTableMap::COL_LATITUDE),
            $this->getAliasedColName(GeoLocationTableMap::COL_LONGITUDE),
            deg2rad($longitude),
            sin(deg2rad($latitude)),
            $this->getAliasedColName(GeoLocationTableMap::COL_LATITUDE)
        );

        return $this
            ->withColumn($preparedSql, 'Distance');
    }

    /**
     * Filters objects by distance from a given origin.
     *
     * @param double $latitude       The latitude of the origin point.
     * @param double $longitude      The longitude of the origin point.
     * @param double $distance       The distance between the origin and the objects to find.
     * @param double $unit           The unit measure.
     * @param Criteria $comparison   Comparison sign (default is: `<`).
     *
     * @return \JobScooper\DataAccess\GeoLocationQuery The current query, for fluid interface
     */
    public function filterByDistanceFrom($latitude, $longitude, $distance, $unit = GeoLocationTableMap::KILOMETERS_UNIT, $comparison = Criteria::LESS_THAN)
    {
        if (GeoLocationTableMap::MILES_UNIT === $unit) {
            $earthRadius = 3959;
        } elseif (GeoLocationTableMap::NAUTICAL_MILES_UNIT === $unit) {
            $earthRadius = 3440;
        } else {
            $earthRadius = 6371;
        }

        $sql = 'ABS(%s * ACOS( ROUND (%s * COS(RADIANS(%s)) * COS(RADIANS(%s) - %s) + %s * SIN(RADIANS(%s)),14)))';
        $preparedSql = sprintf($sql,
            $earthRadius,
            cos(deg2rad($latitude)),
            $this->getAliasedColName(GeoLocationTableMap::COL_LATITUDE),
            $this->getAliasedColName(GeoLocationTableMap::COL_LONGITUDE),
            deg2rad($longitude),
            sin(deg2rad($latitude)),
            $this->getAliasedColName(GeoLocationTableMap::COL_LATITUDE)
        );

        return $this
            ->withColumn($preparedSql, 'Distance')
            ->where(sprintf('%s %s ?', $preparedSql, $comparison), $distance, PDO::PARAM_STR)
            ;
    }
    /**
     * Filters objects near a given \JobScooper\DataAccess\GeoLocation object.
     *
     * @param \JobScooper\DataAccess\GeoLocation $geolocation A \JobScooper\DataAccess\GeoLocation object.
     * @param double $distance The distance between the origin and the objects to find.
     * @param double $unit     The unit measure.
     *
     * @return \JobScooper\DataAccess\GeoLocationQuery The current query, for fluid interface
     */
    public function filterNear(\JobScooper\DataAccess\GeoLocation $geolocation, $distance = 5, $unit = GeoLocationTableMap::KILOMETERS_UNIT)
    {
        return $this
            ->filterByDistanceFrom(
                $geolocation->getLatitude(),
                $geolocation->getLongitude(),
                $distance, $unit
            );
    }

} // GeoLocationQuery
