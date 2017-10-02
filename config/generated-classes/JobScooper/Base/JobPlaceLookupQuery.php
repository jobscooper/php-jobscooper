<?php

namespace JobScooper\Base;

use \Exception;
use \PDO;
use JobScooper\JobPlaceLookup as ChildJobPlaceLookup;
use JobScooper\JobPlaceLookupQuery as ChildJobPlaceLookupQuery;
use JobScooper\Map\JobPlaceLookupTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'job_place_lookup' table.
 *
 *
 *
 * @method     ChildJobPlaceLookupQuery orderByPlaceAlternateName($order = Criteria::ASC) Order by the place_alternate_name column
 * @method     ChildJobPlaceLookupQuery orderByLocationId($order = Criteria::ASC) Order by the job_location_id column
 * @method     ChildJobPlaceLookupQuery orderBySlug($order = Criteria::ASC) Order by the slug column
 *
 * @method     ChildJobPlaceLookupQuery groupByPlaceAlternateName() Group by the place_alternate_name column
 * @method     ChildJobPlaceLookupQuery groupByLocationId() Group by the job_location_id column
 * @method     ChildJobPlaceLookupQuery groupBySlug() Group by the slug column
 *
 * @method     ChildJobPlaceLookupQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildJobPlaceLookupQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildJobPlaceLookupQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildJobPlaceLookupQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildJobPlaceLookupQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildJobPlaceLookupQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildJobPlaceLookupQuery leftJoinJobLocation($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobLocation relation
 * @method     ChildJobPlaceLookupQuery rightJoinJobLocation($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobLocation relation
 * @method     ChildJobPlaceLookupQuery innerJoinJobLocation($relationAlias = null) Adds a INNER JOIN clause to the query using the JobLocation relation
 *
 * @method     ChildJobPlaceLookupQuery joinWithJobLocation($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobLocation relation
 *
 * @method     ChildJobPlaceLookupQuery leftJoinWithJobLocation() Adds a LEFT JOIN clause and with to the query using the JobLocation relation
 * @method     ChildJobPlaceLookupQuery rightJoinWithJobLocation() Adds a RIGHT JOIN clause and with to the query using the JobLocation relation
 * @method     ChildJobPlaceLookupQuery innerJoinWithJobLocation() Adds a INNER JOIN clause and with to the query using the JobLocation relation
 *
 * @method     \JobScooper\JobLocationQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildJobPlaceLookup findOne(ConnectionInterface $con = null) Return the first ChildJobPlaceLookup matching the query
 * @method     ChildJobPlaceLookup findOneOrCreate(ConnectionInterface $con = null) Return the first ChildJobPlaceLookup matching the query, or a new ChildJobPlaceLookup object populated from the query conditions when no match is found
 *
 * @method     ChildJobPlaceLookup findOneByPlaceAlternateName(string $place_alternate_name) Return the first ChildJobPlaceLookup filtered by the place_alternate_name column
 * @method     ChildJobPlaceLookup findOneByLocationId(int $job_location_id) Return the first ChildJobPlaceLookup filtered by the job_location_id column
 * @method     ChildJobPlaceLookup findOneBySlug(string $slug) Return the first ChildJobPlaceLookup filtered by the slug column *

 * @method     ChildJobPlaceLookup requirePk($key, ConnectionInterface $con = null) Return the ChildJobPlaceLookup by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPlaceLookup requireOne(ConnectionInterface $con = null) Return the first ChildJobPlaceLookup matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildJobPlaceLookup requireOneByPlaceAlternateName(string $place_alternate_name) Return the first ChildJobPlaceLookup filtered by the place_alternate_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPlaceLookup requireOneByLocationId(int $job_location_id) Return the first ChildJobPlaceLookup filtered by the job_location_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPlaceLookup requireOneBySlug(string $slug) Return the first ChildJobPlaceLookup filtered by the slug column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildJobPlaceLookup[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildJobPlaceLookup objects based on current ModelCriteria
 * @method     ChildJobPlaceLookup[]|ObjectCollection findByPlaceAlternateName(string $place_alternate_name) Return ChildJobPlaceLookup objects filtered by the place_alternate_name column
 * @method     ChildJobPlaceLookup[]|ObjectCollection findByLocationId(int $job_location_id) Return ChildJobPlaceLookup objects filtered by the job_location_id column
 * @method     ChildJobPlaceLookup[]|ObjectCollection findBySlug(string $slug) Return ChildJobPlaceLookup objects filtered by the slug column
 * @method     ChildJobPlaceLookup[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class JobPlaceLookupQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\Base\JobPlaceLookupQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\JobPlaceLookup', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildJobPlaceLookupQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildJobPlaceLookupQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildJobPlaceLookupQuery) {
            return $criteria;
        }
        $query = new ChildJobPlaceLookupQuery();
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
     * $obj = $c->findPk(array(12, 34), $con);
     * </code>
     *
     * @param array[$place_alternate_name, $job_location_id] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildJobPlaceLookup|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(JobPlaceLookupTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = JobPlaceLookupTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildJobPlaceLookup A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT place_alternate_name, job_location_id, slug FROM job_place_lookup WHERE place_alternate_name = :p0 AND job_location_id = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_STR);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildJobPlaceLookup $obj */
            $obj = new ChildJobPlaceLookup();
            $obj->hydrate($row);
            JobPlaceLookupTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildJobPlaceLookup|array|mixed the result, formatted by the current formatter
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
     * $objs = $c->findPks(array(array(12, 56), array(832, 123), array(123, 456)), $con);
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
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(JobPlaceLookupTableMap::COL_PLACE_ALTERNATE_NAME, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(JobPlaceLookupTableMap::COL_PLACE_ALTERNATE_NAME, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
    }

    /**
     * Filter the query on the place_alternate_name column
     *
     * Example usage:
     * <code>
     * $query->filterByPlaceAlternateName('fooValue');   // WHERE place_alternate_name = 'fooValue'
     * $query->filterByPlaceAlternateName('%fooValue%', Criteria::LIKE); // WHERE place_alternate_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $placeAlternateName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function filterByPlaceAlternateName($placeAlternateName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($placeAlternateName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPlaceLookupTableMap::COL_PLACE_ALTERNATE_NAME, $placeAlternateName, $comparison);
    }

    /**
     * Filter the query on the job_location_id column
     *
     * Example usage:
     * <code>
     * $query->filterByLocationId(1234); // WHERE job_location_id = 1234
     * $query->filterByLocationId(array(12, 34)); // WHERE job_location_id IN (12, 34)
     * $query->filterByLocationId(array('min' => 12)); // WHERE job_location_id > 12
     * </code>
     *
     * @see       filterByJobLocation()
     *
     * @param     mixed $locationId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function filterByLocationId($locationId = null, $comparison = null)
    {
        if (is_array($locationId)) {
            $useMinMax = false;
            if (isset($locationId['min'])) {
                $this->addUsingAlias(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $locationId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($locationId['max'])) {
                $this->addUsingAlias(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $locationId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $locationId, $comparison);
    }

    /**
     * Filter the query on the slug column
     *
     * Example usage:
     * <code>
     * $query->filterBySlug('fooValue');   // WHERE slug = 'fooValue'
     * $query->filterBySlug('%fooValue%', Criteria::LIKE); // WHERE slug LIKE '%fooValue%'
     * </code>
     *
     * @param     string $slug The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function filterBySlug($slug = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($slug)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPlaceLookupTableMap::COL_SLUG, $slug, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\JobLocation object
     *
     * @param \JobScooper\JobLocation|ObjectCollection $jobLocation The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function filterByJobLocation($jobLocation, $comparison = null)
    {
        if ($jobLocation instanceof \JobScooper\JobLocation) {
            return $this
                ->addUsingAlias(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $jobLocation->getLocationId(), $comparison);
        } elseif ($jobLocation instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID, $jobLocation->toKeyValue('PrimaryKey', 'LocationId'), $comparison);
        } else {
            throw new PropelException('filterByJobLocation() only accepts arguments of type \JobScooper\JobLocation or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the JobLocation relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function joinJobLocation($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('JobLocation');

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
            $this->addJoinObject($join, 'JobLocation');
        }

        return $this;
    }

    /**
     * Use the JobLocation relation JobLocation object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\JobLocationQuery A secondary query class using the current class as primary query
     */
    public function useJobLocationQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinJobLocation($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'JobLocation', '\JobScooper\JobLocationQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildJobPlaceLookup $jobPlaceLookup Object to remove from the list of results
     *
     * @return $this|ChildJobPlaceLookupQuery The current query, for fluid interface
     */
    public function prune($jobPlaceLookup = null)
    {
        if ($jobPlaceLookup) {
            $this->addCond('pruneCond0', $this->getAliasedColName(JobPlaceLookupTableMap::COL_PLACE_ALTERNATE_NAME), $jobPlaceLookup->getPlaceAlternateName(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(JobPlaceLookupTableMap::COL_JOB_LOCATION_ID), $jobPlaceLookup->getLocationId(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the job_place_lookup table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(JobPlaceLookupTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            JobPlaceLookupTableMap::clearInstancePool();
            JobPlaceLookupTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(JobPlaceLookupTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(JobPlaceLookupTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            JobPlaceLookupTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            JobPlaceLookupTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // JobPlaceLookupQuery