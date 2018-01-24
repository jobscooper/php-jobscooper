<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\UserSearchSiteRun as ChildUserSearchSiteRun;
use JobScooper\DataAccess\UserSearchSiteRunQuery as ChildUserSearchSiteRunQuery;
use JobScooper\DataAccess\Map\UserSearchSiteRunTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'user_search_site_run' table.
 *
 *
 *
 * @method     ChildUserSearchSiteRunQuery orderByUserSearchSiteRunId($order = Criteria::ASC) Order by the user_search_site_run_id column
 * @method     ChildUserSearchSiteRunQuery orderByUserSearchPairId($order = Criteria::ASC) Order by the user_search_pair_id column
 * @method     ChildUserSearchSiteRunQuery orderByJobSiteKey($order = Criteria::ASC) Order by the jobsite_key column
 * @method     ChildUserSearchSiteRunQuery orderByAppRunId($order = Criteria::ASC) Order by the app_run_id column
 * @method     ChildUserSearchSiteRunQuery orderByUserSearchSiteRunKey($order = Criteria::ASC) Order by the user_search_site_run_key column
 * @method     ChildUserSearchSiteRunQuery orderByStartedAt($order = Criteria::ASC) Order by the date_started column
 * @method     ChildUserSearchSiteRunQuery orderByEndedAt($order = Criteria::ASC) Order by the date_ended column
 * @method     ChildUserSearchSiteRunQuery orderBySearchStartUrl($order = Criteria::ASC) Order by the search_start_url column
 * @method     ChildUserSearchSiteRunQuery orderByRunResultCode($order = Criteria::ASC) Order by the run_result_code column
 * @method     ChildUserSearchSiteRunQuery orderByRunErrorDetails($order = Criteria::ASC) Order by the run_error_details column
 * @method     ChildUserSearchSiteRunQuery orderByRunErrorPageHtml($order = Criteria::ASC) Order by the run_error_page_html column
 *
 * @method     ChildUserSearchSiteRunQuery groupByUserSearchSiteRunId() Group by the user_search_site_run_id column
 * @method     ChildUserSearchSiteRunQuery groupByUserSearchPairId() Group by the user_search_pair_id column
 * @method     ChildUserSearchSiteRunQuery groupByJobSiteKey() Group by the jobsite_key column
 * @method     ChildUserSearchSiteRunQuery groupByAppRunId() Group by the app_run_id column
 * @method     ChildUserSearchSiteRunQuery groupByUserSearchSiteRunKey() Group by the user_search_site_run_key column
 * @method     ChildUserSearchSiteRunQuery groupByStartedAt() Group by the date_started column
 * @method     ChildUserSearchSiteRunQuery groupByEndedAt() Group by the date_ended column
 * @method     ChildUserSearchSiteRunQuery groupBySearchStartUrl() Group by the search_start_url column
 * @method     ChildUserSearchSiteRunQuery groupByRunResultCode() Group by the run_result_code column
 * @method     ChildUserSearchSiteRunQuery groupByRunErrorDetails() Group by the run_error_details column
 * @method     ChildUserSearchSiteRunQuery groupByRunErrorPageHtml() Group by the run_error_page_html column
 *
 * @method     ChildUserSearchSiteRunQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserSearchSiteRunQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserSearchSiteRunQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserSearchSiteRunQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserSearchSiteRunQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserSearchSiteRunQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserSearchSiteRunQuery leftJoinJobSiteFromUSSR($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobSiteFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery rightJoinJobSiteFromUSSR($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobSiteFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery innerJoinJobSiteFromUSSR($relationAlias = null) Adds a INNER JOIN clause to the query using the JobSiteFromUSSR relation
 *
 * @method     ChildUserSearchSiteRunQuery joinWithJobSiteFromUSSR($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobSiteFromUSSR relation
 *
 * @method     ChildUserSearchSiteRunQuery leftJoinWithJobSiteFromUSSR() Adds a LEFT JOIN clause and with to the query using the JobSiteFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery rightJoinWithJobSiteFromUSSR() Adds a RIGHT JOIN clause and with to the query using the JobSiteFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery innerJoinWithJobSiteFromUSSR() Adds a INNER JOIN clause and with to the query using the JobSiteFromUSSR relation
 *
 * @method     ChildUserSearchSiteRunQuery leftJoinUserSearchPairFromUSSR($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserSearchPairFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery rightJoinUserSearchPairFromUSSR($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserSearchPairFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery innerJoinUserSearchPairFromUSSR($relationAlias = null) Adds a INNER JOIN clause to the query using the UserSearchPairFromUSSR relation
 *
 * @method     ChildUserSearchSiteRunQuery joinWithUserSearchPairFromUSSR($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserSearchPairFromUSSR relation
 *
 * @method     ChildUserSearchSiteRunQuery leftJoinWithUserSearchPairFromUSSR() Adds a LEFT JOIN clause and with to the query using the UserSearchPairFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery rightJoinWithUserSearchPairFromUSSR() Adds a RIGHT JOIN clause and with to the query using the UserSearchPairFromUSSR relation
 * @method     ChildUserSearchSiteRunQuery innerJoinWithUserSearchPairFromUSSR() Adds a INNER JOIN clause and with to the query using the UserSearchPairFromUSSR relation
 *
 * @method     \JobScooper\DataAccess\JobSiteRecordQuery|\JobScooper\DataAccess\UserSearchPairQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserSearchSiteRun findOne(ConnectionInterface $con = null) Return the first ChildUserSearchSiteRun matching the query
 * @method     ChildUserSearchSiteRun findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserSearchSiteRun matching the query, or a new ChildUserSearchSiteRun object populated from the query conditions when no match is found
 *
 * @method     ChildUserSearchSiteRun findOneByUserSearchSiteRunId(int $user_search_site_run_id) Return the first ChildUserSearchSiteRun filtered by the user_search_site_run_id column
 * @method     ChildUserSearchSiteRun findOneByUserSearchPairId(int $user_search_pair_id) Return the first ChildUserSearchSiteRun filtered by the user_search_pair_id column
 * @method     ChildUserSearchSiteRun findOneByJobSiteKey(string $jobsite_key) Return the first ChildUserSearchSiteRun filtered by the jobsite_key column
 * @method     ChildUserSearchSiteRun findOneByAppRunId(string $app_run_id) Return the first ChildUserSearchSiteRun filtered by the app_run_id column
 * @method     ChildUserSearchSiteRun findOneByUserSearchSiteRunKey(string $user_search_site_run_key) Return the first ChildUserSearchSiteRun filtered by the user_search_site_run_key column
 * @method     ChildUserSearchSiteRun findOneByStartedAt(string $date_started) Return the first ChildUserSearchSiteRun filtered by the date_started column
 * @method     ChildUserSearchSiteRun findOneByEndedAt(string $date_ended) Return the first ChildUserSearchSiteRun filtered by the date_ended column
 * @method     ChildUserSearchSiteRun findOneBySearchStartUrl(string $search_start_url) Return the first ChildUserSearchSiteRun filtered by the search_start_url column
 * @method     ChildUserSearchSiteRun findOneByRunResultCode(int $run_result_code) Return the first ChildUserSearchSiteRun filtered by the run_result_code column
 * @method     ChildUserSearchSiteRun findOneByRunErrorDetails(array $run_error_details) Return the first ChildUserSearchSiteRun filtered by the run_error_details column
 * @method     ChildUserSearchSiteRun findOneByRunErrorPageHtml(string $run_error_page_html) Return the first ChildUserSearchSiteRun filtered by the run_error_page_html column *

 * @method     ChildUserSearchSiteRun requirePk($key, ConnectionInterface $con = null) Return the ChildUserSearchSiteRun by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOne(ConnectionInterface $con = null) Return the first ChildUserSearchSiteRun matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserSearchSiteRun requireOneByUserSearchSiteRunId(int $user_search_site_run_id) Return the first ChildUserSearchSiteRun filtered by the user_search_site_run_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByUserSearchPairId(int $user_search_pair_id) Return the first ChildUserSearchSiteRun filtered by the user_search_pair_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByJobSiteKey(string $jobsite_key) Return the first ChildUserSearchSiteRun filtered by the jobsite_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByAppRunId(string $app_run_id) Return the first ChildUserSearchSiteRun filtered by the app_run_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByUserSearchSiteRunKey(string $user_search_site_run_key) Return the first ChildUserSearchSiteRun filtered by the user_search_site_run_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByStartedAt(string $date_started) Return the first ChildUserSearchSiteRun filtered by the date_started column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByEndedAt(string $date_ended) Return the first ChildUserSearchSiteRun filtered by the date_ended column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneBySearchStartUrl(string $search_start_url) Return the first ChildUserSearchSiteRun filtered by the search_start_url column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByRunResultCode(int $run_result_code) Return the first ChildUserSearchSiteRun filtered by the run_result_code column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByRunErrorDetails(array $run_error_details) Return the first ChildUserSearchSiteRun filtered by the run_error_details column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserSearchSiteRun requireOneByRunErrorPageHtml(string $run_error_page_html) Return the first ChildUserSearchSiteRun filtered by the run_error_page_html column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserSearchSiteRun[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserSearchSiteRun objects based on current ModelCriteria
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByUserSearchSiteRunId(int $user_search_site_run_id) Return ChildUserSearchSiteRun objects filtered by the user_search_site_run_id column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByUserSearchPairId(int $user_search_pair_id) Return ChildUserSearchSiteRun objects filtered by the user_search_pair_id column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByJobSiteKey(string $jobsite_key) Return ChildUserSearchSiteRun objects filtered by the jobsite_key column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByAppRunId(string $app_run_id) Return ChildUserSearchSiteRun objects filtered by the app_run_id column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByUserSearchSiteRunKey(string $user_search_site_run_key) Return ChildUserSearchSiteRun objects filtered by the user_search_site_run_key column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByStartedAt(string $date_started) Return ChildUserSearchSiteRun objects filtered by the date_started column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByEndedAt(string $date_ended) Return ChildUserSearchSiteRun objects filtered by the date_ended column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findBySearchStartUrl(string $search_start_url) Return ChildUserSearchSiteRun objects filtered by the search_start_url column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByRunResultCode(int $run_result_code) Return ChildUserSearchSiteRun objects filtered by the run_result_code column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByRunErrorDetails(array $run_error_details) Return ChildUserSearchSiteRun objects filtered by the run_error_details column
 * @method     ChildUserSearchSiteRun[]|ObjectCollection findByRunErrorPageHtml(string $run_error_page_html) Return ChildUserSearchSiteRun objects filtered by the run_error_page_html column
 * @method     ChildUserSearchSiteRun[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserSearchSiteRunQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\UserSearchSiteRunQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\UserSearchSiteRun', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserSearchSiteRunQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserSearchSiteRunQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserSearchSiteRunQuery) {
            return $criteria;
        }
        $query = new ChildUserSearchSiteRunQuery();
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
     * @return ChildUserSearchSiteRun|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserSearchSiteRunTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserSearchSiteRunTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildUserSearchSiteRun A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_search_site_run_id, user_search_pair_id, jobsite_key, app_run_id, user_search_site_run_key, date_started, date_ended, search_start_url, run_result_code, run_error_details, run_error_page_html FROM user_search_site_run WHERE user_search_site_run_id = :p0';
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
            /** @var ChildUserSearchSiteRun $obj */
            $obj = new ChildUserSearchSiteRun();
            $obj->hydrate($row);
            UserSearchSiteRunTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildUserSearchSiteRun|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the user_search_site_run_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserSearchSiteRunId(1234); // WHERE user_search_site_run_id = 1234
     * $query->filterByUserSearchSiteRunId(array(12, 34)); // WHERE user_search_site_run_id IN (12, 34)
     * $query->filterByUserSearchSiteRunId(array('min' => 12)); // WHERE user_search_site_run_id > 12
     * </code>
     *
     * @param     mixed $userSearchSiteRunId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByUserSearchSiteRunId($userSearchSiteRunId = null, $comparison = null)
    {
        if (is_array($userSearchSiteRunId)) {
            $useMinMax = false;
            if (isset($userSearchSiteRunId['min'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, $userSearchSiteRunId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userSearchSiteRunId['max'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, $userSearchSiteRunId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, $userSearchSiteRunId, $comparison);
    }

    /**
     * Filter the query on the user_search_pair_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserSearchPairId(1234); // WHERE user_search_pair_id = 1234
     * $query->filterByUserSearchPairId(array(12, 34)); // WHERE user_search_pair_id IN (12, 34)
     * $query->filterByUserSearchPairId(array('min' => 12)); // WHERE user_search_pair_id > 12
     * </code>
     *
     * @see       filterByUserSearchPairFromUSSR()
     *
     * @param     mixed $userSearchPairId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByUserSearchPairId($userSearchPairId = null, $comparison = null)
    {
        if (is_array($userSearchPairId)) {
            $useMinMax = false;
            if (isset($userSearchPairId['min'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID, $userSearchPairId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userSearchPairId['max'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID, $userSearchPairId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID, $userSearchPairId, $comparison);
    }

    /**
     * Filter the query on the jobsite_key column
     *
     * Example usage:
     * <code>
     * $query->filterByJobSiteKey('fooValue');   // WHERE jobsite_key = 'fooValue'
     * $query->filterByJobSiteKey('%fooValue%', Criteria::LIKE); // WHERE jobsite_key LIKE '%fooValue%'
     * </code>
     *
     * @param     string $jobSiteKey The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByJobSiteKey($jobSiteKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($jobSiteKey)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_JOBSITE_KEY, $jobSiteKey, $comparison);
    }

    /**
     * Filter the query on the app_run_id column
     *
     * Example usage:
     * <code>
     * $query->filterByAppRunId('fooValue');   // WHERE app_run_id = 'fooValue'
     * $query->filterByAppRunId('%fooValue%', Criteria::LIKE); // WHERE app_run_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $appRunId The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByAppRunId($appRunId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($appRunId)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_APP_RUN_ID, $appRunId, $comparison);
    }

    /**
     * Filter the query on the user_search_site_run_key column
     *
     * Example usage:
     * <code>
     * $query->filterByUserSearchSiteRunKey('fooValue');   // WHERE user_search_site_run_key = 'fooValue'
     * $query->filterByUserSearchSiteRunKey('%fooValue%', Criteria::LIKE); // WHERE user_search_site_run_key LIKE '%fooValue%'
     * </code>
     *
     * @param     string $userSearchSiteRunKey The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByUserSearchSiteRunKey($userSearchSiteRunKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($userSearchSiteRunKey)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_KEY, $userSearchSiteRunKey, $comparison);
    }

    /**
     * Filter the query on the date_started column
     *
     * Example usage:
     * <code>
     * $query->filterByStartedAt('2011-03-14'); // WHERE date_started = '2011-03-14'
     * $query->filterByStartedAt('now'); // WHERE date_started = '2011-03-14'
     * $query->filterByStartedAt(array('max' => 'yesterday')); // WHERE date_started > '2011-03-13'
     * </code>
     *
     * @param     mixed $startedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByStartedAt($startedAt = null, $comparison = null)
    {
        if (is_array($startedAt)) {
            $useMinMax = false;
            if (isset($startedAt['min'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_DATE_STARTED, $startedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($startedAt['max'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_DATE_STARTED, $startedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_DATE_STARTED, $startedAt, $comparison);
    }

    /**
     * Filter the query on the date_ended column
     *
     * Example usage:
     * <code>
     * $query->filterByEndedAt('2011-03-14'); // WHERE date_ended = '2011-03-14'
     * $query->filterByEndedAt('now'); // WHERE date_ended = '2011-03-14'
     * $query->filterByEndedAt(array('max' => 'yesterday')); // WHERE date_ended > '2011-03-13'
     * </code>
     *
     * @param     mixed $endedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByEndedAt($endedAt = null, $comparison = null)
    {
        if (is_array($endedAt)) {
            $useMinMax = false;
            if (isset($endedAt['min'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_DATE_ENDED, $endedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($endedAt['max'])) {
                $this->addUsingAlias(UserSearchSiteRunTableMap::COL_DATE_ENDED, $endedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_DATE_ENDED, $endedAt, $comparison);
    }

    /**
     * Filter the query on the search_start_url column
     *
     * Example usage:
     * <code>
     * $query->filterBySearchStartUrl('fooValue');   // WHERE search_start_url = 'fooValue'
     * $query->filterBySearchStartUrl('%fooValue%', Criteria::LIKE); // WHERE search_start_url LIKE '%fooValue%'
     * </code>
     *
     * @param     string $searchStartUrl The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterBySearchStartUrl($searchStartUrl = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($searchStartUrl)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_SEARCH_START_URL, $searchStartUrl, $comparison);
    }

    /**
     * Filter the query on the run_result_code column
     *
     * @param     mixed $runResultCode The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByRunResultCode($runResultCode = null, $comparison = null)
    {
        $valueSet = UserSearchSiteRunTableMap::getValueSet(UserSearchSiteRunTableMap::COL_RUN_RESULT_CODE);
        if (is_scalar($runResultCode)) {
            if (!in_array($runResultCode, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $runResultCode));
            }
            $runResultCode = array_search($runResultCode, $valueSet);
        } elseif (is_array($runResultCode)) {
            $convertedValues = array();
            foreach ($runResultCode as $value) {
                if (!in_array($value, $valueSet)) {
                    throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $value));
                }
                $convertedValues []= array_search($value, $valueSet);
            }
            $runResultCode = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_RUN_RESULT_CODE, $runResultCode, $comparison);
    }

    /**
     * Filter the query on the run_error_details column
     *
     * @param     array $runErrorDetails The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByRunErrorDetails($runErrorDetails = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($runErrorDetails as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($runErrorDetails as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($runErrorDetails as $value) {
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

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS, $runErrorDetails, $comparison);
    }

    /**
     * Filter the query on the run_error_details column
     * @param     mixed $runErrorDetails The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByRunErrorDetail($runErrorDetails = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($runErrorDetails)) {
                $runErrorDetails = '%| ' . $runErrorDetails . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $runErrorDetails = '%| ' . $runErrorDetails . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $runErrorDetails, $comparison);
            } else {
                $this->addAnd($key, $runErrorDetails, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS, $runErrorDetails, $comparison);
    }

    /**
     * Filter the query on the run_error_page_html column
     *
     * Example usage:
     * <code>
     * $query->filterByRunErrorPageHtml('fooValue');   // WHERE run_error_page_html = 'fooValue'
     * $query->filterByRunErrorPageHtml('%fooValue%', Criteria::LIKE); // WHERE run_error_page_html LIKE '%fooValue%'
     * </code>
     *
     * @param     string $runErrorPageHtml The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByRunErrorPageHtml($runErrorPageHtml = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($runErrorPageHtml)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_RUN_ERROR_PAGE_HTML, $runErrorPageHtml, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobSiteRecord object
     *
     * @param \JobScooper\DataAccess\JobSiteRecord|ObjectCollection $jobSiteRecord The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByJobSiteFromUSSR($jobSiteRecord, $comparison = null)
    {
        if ($jobSiteRecord instanceof \JobScooper\DataAccess\JobSiteRecord) {
            return $this
                ->addUsingAlias(UserSearchSiteRunTableMap::COL_JOBSITE_KEY, $jobSiteRecord->getJobSiteKey(), $comparison);
        } elseif ($jobSiteRecord instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserSearchSiteRunTableMap::COL_JOBSITE_KEY, $jobSiteRecord->toKeyValue('PrimaryKey', 'JobSiteKey'), $comparison);
        } else {
            throw new PropelException('filterByJobSiteFromUSSR() only accepts arguments of type \JobScooper\DataAccess\JobSiteRecord or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the JobSiteFromUSSR relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function joinJobSiteFromUSSR($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('JobSiteFromUSSR');

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
            $this->addJoinObject($join, 'JobSiteFromUSSR');
        }

        return $this;
    }

    /**
     * Use the JobSiteFromUSSR relation JobSiteRecord object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\JobSiteRecordQuery A secondary query class using the current class as primary query
     */
    public function useJobSiteFromUSSRQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinJobSiteFromUSSR($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'JobSiteFromUSSR', '\JobScooper\DataAccess\JobSiteRecordQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\UserSearchPair object
     *
     * @param \JobScooper\DataAccess\UserSearchPair|ObjectCollection $userSearchPair The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterByUserSearchPairFromUSSR($userSearchPair, $comparison = null)
    {
        if ($userSearchPair instanceof \JobScooper\DataAccess\UserSearchPair) {
            return $this
                ->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID, $userSearchPair->getUserSearchPairId(), $comparison);
        } elseif ($userSearchPair instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID, $userSearchPair->toKeyValue('PrimaryKey', 'UserSearchPairId'), $comparison);
        } else {
            throw new PropelException('filterByUserSearchPairFromUSSR() only accepts arguments of type \JobScooper\DataAccess\UserSearchPair or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserSearchPairFromUSSR relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function joinUserSearchPairFromUSSR($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserSearchPairFromUSSR');

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
            $this->addJoinObject($join, 'UserSearchPairFromUSSR');
        }

        return $this;
    }

    /**
     * Use the UserSearchPairFromUSSR relation UserSearchPair object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserSearchPairQuery A secondary query class using the current class as primary query
     */
    public function useUserSearchPairFromUSSRQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserSearchPairFromUSSR($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserSearchPairFromUSSR', '\JobScooper\DataAccess\UserSearchPairQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUserSearchSiteRun $userSearchSiteRun Object to remove from the list of results
     *
     * @return $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function prune($userSearchSiteRun = null)
    {
        if ($userSearchSiteRun) {
            $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, $userSearchSiteRun->getUserSearchSiteRunId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user_search_site_run table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserSearchSiteRunTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserSearchSiteRunTableMap::clearInstancePool();
            UserSearchSiteRunTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserSearchSiteRunTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserSearchSiteRunTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserSearchSiteRunTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserSearchSiteRunTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // sluggable behavior

    /**
     * Filter the query on the slug column
     *
     * @param     string $slug The value to use as filter.
     *
     * @return    $this|ChildUserSearchSiteRunQuery The current query, for fluid interface
     */
    public function filterBySlug($slug)
    {
        return $this->addUsingAlias(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_KEY, $slug, Criteria::EQUAL);
    }

    /**
     * Find one object based on its slug
     *
     * @param     string $slug The value to use as filter.
     * @param     ConnectionInterface $con The optional connection object
     *
     * @return    ChildUserSearchSiteRun the result, formatted by the current formatter
     */
    public function findOneBySlug($slug, $con = null)
    {
        return $this->filterBySlug($slug)->findOne($con);
    }

} // UserSearchSiteRunQuery
