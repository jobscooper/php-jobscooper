<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\UserJobMatch as ChildUserJobMatch;
use JobScooper\DataAccess\UserJobMatchQuery as ChildUserJobMatchQuery;
use JobScooper\DataAccess\Map\UserJobMatchTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'user_job_match' table.
 *
 *
 *
 * @method     ChildUserJobMatchQuery orderByUserJobMatchId($order = Criteria::ASC) Order by the user_job_match_id column
 * @method     ChildUserJobMatchQuery orderByUserSlug($order = Criteria::ASC) Order by the user_slug column
 * @method     ChildUserJobMatchQuery orderByJobPostingId($order = Criteria::ASC) Order by the jobposting_id column
 * @method     ChildUserJobMatchQuery orderByUserNotificationState($order = Criteria::ASC) Order by the user_notification_state column
 * @method     ChildUserJobMatchQuery orderByIsJobMatch($order = Criteria::ASC) Order by the is_job_match column
 * @method     ChildUserJobMatchQuery orderByIsExcluded($order = Criteria::ASC) Order by the is_excluded column
 * @method     ChildUserJobMatchQuery orderByIsIncludeInNotifications($order = Criteria::ASC) Order by the is_include_in_notifications column
 * @method     ChildUserJobMatchQuery orderByMatchedUserKeywords($order = Criteria::ASC) Order by the matched_user_keywords column
 * @method     ChildUserJobMatchQuery orderByMatchedNegativeTitleKeywords($order = Criteria::ASC) Order by the matched_negative_title_keywords column
 * @method     ChildUserJobMatchQuery orderByMatchedNegativeCompanyKeywords($order = Criteria::ASC) Order by the matched_negative_company_keywords column
 * @method     ChildUserJobMatchQuery orderByOutOfUserArea($order = Criteria::ASC) Order by the out_of_user_area column
 * @method     ChildUserJobMatchQuery orderByAppRunId($order = Criteria::ASC) Order by the app_run_id column
 *
 * @method     ChildUserJobMatchQuery groupByUserJobMatchId() Group by the user_job_match_id column
 * @method     ChildUserJobMatchQuery groupByUserSlug() Group by the user_slug column
 * @method     ChildUserJobMatchQuery groupByJobPostingId() Group by the jobposting_id column
 * @method     ChildUserJobMatchQuery groupByUserNotificationState() Group by the user_notification_state column
 * @method     ChildUserJobMatchQuery groupByIsJobMatch() Group by the is_job_match column
 * @method     ChildUserJobMatchQuery groupByIsExcluded() Group by the is_excluded column
 * @method     ChildUserJobMatchQuery groupByIsIncludeInNotifications() Group by the is_include_in_notifications column
 * @method     ChildUserJobMatchQuery groupByMatchedUserKeywords() Group by the matched_user_keywords column
 * @method     ChildUserJobMatchQuery groupByMatchedNegativeTitleKeywords() Group by the matched_negative_title_keywords column
 * @method     ChildUserJobMatchQuery groupByMatchedNegativeCompanyKeywords() Group by the matched_negative_company_keywords column
 * @method     ChildUserJobMatchQuery groupByOutOfUserArea() Group by the out_of_user_area column
 * @method     ChildUserJobMatchQuery groupByAppRunId() Group by the app_run_id column
 *
 * @method     ChildUserJobMatchQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserJobMatchQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserJobMatchQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserJobMatchQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserJobMatchQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserJobMatchQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserJobMatchQuery leftJoinUser($relationAlias = null) Adds a LEFT JOIN clause to the query using the User relation
 * @method     ChildUserJobMatchQuery rightJoinUser($relationAlias = null) Adds a RIGHT JOIN clause to the query using the User relation
 * @method     ChildUserJobMatchQuery innerJoinUser($relationAlias = null) Adds a INNER JOIN clause to the query using the User relation
 *
 * @method     ChildUserJobMatchQuery joinWithUser($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the User relation
 *
 * @method     ChildUserJobMatchQuery leftJoinWithUser() Adds a LEFT JOIN clause and with to the query using the User relation
 * @method     ChildUserJobMatchQuery rightJoinWithUser() Adds a RIGHT JOIN clause and with to the query using the User relation
 * @method     ChildUserJobMatchQuery innerJoinWithUser() Adds a INNER JOIN clause and with to the query using the User relation
 *
 * @method     ChildUserJobMatchQuery leftJoinJobPosting($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobPosting relation
 * @method     ChildUserJobMatchQuery rightJoinJobPosting($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobPosting relation
 * @method     ChildUserJobMatchQuery innerJoinJobPosting($relationAlias = null) Adds a INNER JOIN clause to the query using the JobPosting relation
 *
 * @method     ChildUserJobMatchQuery joinWithJobPosting($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobPosting relation
 *
 * @method     ChildUserJobMatchQuery leftJoinWithJobPosting() Adds a LEFT JOIN clause and with to the query using the JobPosting relation
 * @method     ChildUserJobMatchQuery rightJoinWithJobPosting() Adds a RIGHT JOIN clause and with to the query using the JobPosting relation
 * @method     ChildUserJobMatchQuery innerJoinWithJobPosting() Adds a INNER JOIN clause and with to the query using the JobPosting relation
 *
 * @method     \JobScooper\DataAccess\UserQuery|\JobScooper\DataAccess\JobPostingQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserJobMatch findOne(ConnectionInterface $con = null) Return the first ChildUserJobMatch matching the query
 * @method     ChildUserJobMatch findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserJobMatch matching the query, or a new ChildUserJobMatch object populated from the query conditions when no match is found
 *
 * @method     ChildUserJobMatch findOneByUserJobMatchId(int $user_job_match_id) Return the first ChildUserJobMatch filtered by the user_job_match_id column
 * @method     ChildUserJobMatch findOneByUserSlug(string $user_slug) Return the first ChildUserJobMatch filtered by the user_slug column
 * @method     ChildUserJobMatch findOneByJobPostingId(int $jobposting_id) Return the first ChildUserJobMatch filtered by the jobposting_id column
 * @method     ChildUserJobMatch findOneByUserNotificationState(int $user_notification_state) Return the first ChildUserJobMatch filtered by the user_notification_state column
 * @method     ChildUserJobMatch findOneByIsJobMatch(boolean $is_job_match) Return the first ChildUserJobMatch filtered by the is_job_match column
 * @method     ChildUserJobMatch findOneByIsExcluded(boolean $is_excluded) Return the first ChildUserJobMatch filtered by the is_excluded column
 * @method     ChildUserJobMatch findOneByIsIncludeInNotifications(boolean $is_include_in_notifications) Return the first ChildUserJobMatch filtered by the is_include_in_notifications column
 * @method     ChildUserJobMatch findOneByMatchedUserKeywords(array $matched_user_keywords) Return the first ChildUserJobMatch filtered by the matched_user_keywords column
 * @method     ChildUserJobMatch findOneByMatchedNegativeTitleKeywords(array $matched_negative_title_keywords) Return the first ChildUserJobMatch filtered by the matched_negative_title_keywords column
 * @method     ChildUserJobMatch findOneByMatchedNegativeCompanyKeywords(array $matched_negative_company_keywords) Return the first ChildUserJobMatch filtered by the matched_negative_company_keywords column
 * @method     ChildUserJobMatch findOneByOutOfUserArea(boolean $out_of_user_area) Return the first ChildUserJobMatch filtered by the out_of_user_area column
 * @method     ChildUserJobMatch findOneByAppRunId(string $app_run_id) Return the first ChildUserJobMatch filtered by the app_run_id column *

 * @method     ChildUserJobMatch requirePk($key, ConnectionInterface $con = null) Return the ChildUserJobMatch by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOne(ConnectionInterface $con = null) Return the first ChildUserJobMatch matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserJobMatch requireOneByUserJobMatchId(int $user_job_match_id) Return the first ChildUserJobMatch filtered by the user_job_match_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByUserSlug(string $user_slug) Return the first ChildUserJobMatch filtered by the user_slug column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByJobPostingId(int $jobposting_id) Return the first ChildUserJobMatch filtered by the jobposting_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByUserNotificationState(int $user_notification_state) Return the first ChildUserJobMatch filtered by the user_notification_state column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByIsJobMatch(boolean $is_job_match) Return the first ChildUserJobMatch filtered by the is_job_match column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByIsExcluded(boolean $is_excluded) Return the first ChildUserJobMatch filtered by the is_excluded column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByIsIncludeInNotifications(boolean $is_include_in_notifications) Return the first ChildUserJobMatch filtered by the is_include_in_notifications column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByMatchedUserKeywords(array $matched_user_keywords) Return the first ChildUserJobMatch filtered by the matched_user_keywords column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByMatchedNegativeTitleKeywords(array $matched_negative_title_keywords) Return the first ChildUserJobMatch filtered by the matched_negative_title_keywords column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByMatchedNegativeCompanyKeywords(array $matched_negative_company_keywords) Return the first ChildUserJobMatch filtered by the matched_negative_company_keywords column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByOutOfUserArea(boolean $out_of_user_area) Return the first ChildUserJobMatch filtered by the out_of_user_area column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserJobMatch requireOneByAppRunId(string $app_run_id) Return the first ChildUserJobMatch filtered by the app_run_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserJobMatch[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserJobMatch objects based on current ModelCriteria
 * @method     ChildUserJobMatch[]|ObjectCollection findByUserJobMatchId(int $user_job_match_id) Return ChildUserJobMatch objects filtered by the user_job_match_id column
 * @method     ChildUserJobMatch[]|ObjectCollection findByUserSlug(string $user_slug) Return ChildUserJobMatch objects filtered by the user_slug column
 * @method     ChildUserJobMatch[]|ObjectCollection findByJobPostingId(int $jobposting_id) Return ChildUserJobMatch objects filtered by the jobposting_id column
 * @method     ChildUserJobMatch[]|ObjectCollection findByUserNotificationState(int $user_notification_state) Return ChildUserJobMatch objects filtered by the user_notification_state column
 * @method     ChildUserJobMatch[]|ObjectCollection findByIsJobMatch(boolean $is_job_match) Return ChildUserJobMatch objects filtered by the is_job_match column
 * @method     ChildUserJobMatch[]|ObjectCollection findByIsExcluded(boolean $is_excluded) Return ChildUserJobMatch objects filtered by the is_excluded column
 * @method     ChildUserJobMatch[]|ObjectCollection findByIsIncludeInNotifications(boolean $is_include_in_notifications) Return ChildUserJobMatch objects filtered by the is_include_in_notifications column
 * @method     ChildUserJobMatch[]|ObjectCollection findByMatchedUserKeywords(array $matched_user_keywords) Return ChildUserJobMatch objects filtered by the matched_user_keywords column
 * @method     ChildUserJobMatch[]|ObjectCollection findByMatchedNegativeTitleKeywords(array $matched_negative_title_keywords) Return ChildUserJobMatch objects filtered by the matched_negative_title_keywords column
 * @method     ChildUserJobMatch[]|ObjectCollection findByMatchedNegativeCompanyKeywords(array $matched_negative_company_keywords) Return ChildUserJobMatch objects filtered by the matched_negative_company_keywords column
 * @method     ChildUserJobMatch[]|ObjectCollection findByOutOfUserArea(boolean $out_of_user_area) Return ChildUserJobMatch objects filtered by the out_of_user_area column
 * @method     ChildUserJobMatch[]|ObjectCollection findByAppRunId(string $app_run_id) Return ChildUserJobMatch objects filtered by the app_run_id column
 * @method     ChildUserJobMatch[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserJobMatchQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\UserJobMatchQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\UserJobMatch', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserJobMatchQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserJobMatchQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserJobMatchQuery) {
            return $criteria;
        }
        $query = new ChildUserJobMatchQuery();
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
     * @return ChildUserJobMatch|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserJobMatchTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserJobMatchTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildUserJobMatch A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_job_match_id, user_slug, jobposting_id, user_notification_state, is_job_match, is_excluded, is_include_in_notifications, matched_user_keywords, matched_negative_title_keywords, matched_negative_company_keywords, out_of_user_area, app_run_id FROM user_job_match WHERE user_job_match_id = :p0';
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
            /** @var ChildUserJobMatch $obj */
            $obj = new ChildUserJobMatch();
            $obj->hydrate($row);
            UserJobMatchTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildUserJobMatch|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(UserJobMatchTableMap::COL_USER_JOB_MATCH_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(UserJobMatchTableMap::COL_USER_JOB_MATCH_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the user_job_match_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserJobMatchId(1234); // WHERE user_job_match_id = 1234
     * $query->filterByUserJobMatchId(array(12, 34)); // WHERE user_job_match_id IN (12, 34)
     * $query->filterByUserJobMatchId(array('min' => 12)); // WHERE user_job_match_id > 12
     * </code>
     *
     * @param     mixed $userJobMatchId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByUserJobMatchId($userJobMatchId = null, $comparison = null)
    {
        if (is_array($userJobMatchId)) {
            $useMinMax = false;
            if (isset($userJobMatchId['min'])) {
                $this->addUsingAlias(UserJobMatchTableMap::COL_USER_JOB_MATCH_ID, $userJobMatchId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userJobMatchId['max'])) {
                $this->addUsingAlias(UserJobMatchTableMap::COL_USER_JOB_MATCH_ID, $userJobMatchId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_USER_JOB_MATCH_ID, $userJobMatchId, $comparison);
    }

    /**
     * Filter the query on the user_slug column
     *
     * Example usage:
     * <code>
     * $query->filterByUserSlug('fooValue');   // WHERE user_slug = 'fooValue'
     * $query->filterByUserSlug('%fooValue%', Criteria::LIKE); // WHERE user_slug LIKE '%fooValue%'
     * </code>
     *
     * @param     string $userSlug The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByUserSlug($userSlug = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($userSlug)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_USER_SLUG, $userSlug, $comparison);
    }

    /**
     * Filter the query on the jobposting_id column
     *
     * Example usage:
     * <code>
     * $query->filterByJobPostingId(1234); // WHERE jobposting_id = 1234
     * $query->filterByJobPostingId(array(12, 34)); // WHERE jobposting_id IN (12, 34)
     * $query->filterByJobPostingId(array('min' => 12)); // WHERE jobposting_id > 12
     * </code>
     *
     * @see       filterByJobPosting()
     *
     * @param     mixed $jobPostingId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByJobPostingId($jobPostingId = null, $comparison = null)
    {
        if (is_array($jobPostingId)) {
            $useMinMax = false;
            if (isset($jobPostingId['min'])) {
                $this->addUsingAlias(UserJobMatchTableMap::COL_JOBPOSTING_ID, $jobPostingId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($jobPostingId['max'])) {
                $this->addUsingAlias(UserJobMatchTableMap::COL_JOBPOSTING_ID, $jobPostingId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_JOBPOSTING_ID, $jobPostingId, $comparison);
    }

    /**
     * Filter the query on the user_notification_state column
     *
     * @param     mixed $userNotificationState The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByUserNotificationState($userNotificationState = null, $comparison = null)
    {
        $valueSet = UserJobMatchTableMap::getValueSet(UserJobMatchTableMap::COL_USER_NOTIFICATION_STATE);
        if (is_scalar($userNotificationState)) {
            if (!in_array($userNotificationState, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $userNotificationState));
            }
            $userNotificationState = array_search($userNotificationState, $valueSet);
        } elseif (is_array($userNotificationState)) {
            $convertedValues = array();
            foreach ($userNotificationState as $value) {
                if (!in_array($value, $valueSet)) {
                    throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $value));
                }
                $convertedValues []= array_search($value, $valueSet);
            }
            $userNotificationState = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_USER_NOTIFICATION_STATE, $userNotificationState, $comparison);
    }

    /**
     * Filter the query on the is_job_match column
     *
     * Example usage:
     * <code>
     * $query->filterByIsJobMatch(true); // WHERE is_job_match = true
     * $query->filterByIsJobMatch('yes'); // WHERE is_job_match = true
     * </code>
     *
     * @param     boolean|string $isJobMatch The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByIsJobMatch($isJobMatch = null, $comparison = null)
    {
        if (is_string($isJobMatch)) {
            $isJobMatch = in_array(strtolower($isJobMatch), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_IS_JOB_MATCH, $isJobMatch, $comparison);
    }

    /**
     * Filter the query on the is_excluded column
     *
     * Example usage:
     * <code>
     * $query->filterByIsExcluded(true); // WHERE is_excluded = true
     * $query->filterByIsExcluded('yes'); // WHERE is_excluded = true
     * </code>
     *
     * @param     boolean|string $isExcluded The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByIsExcluded($isExcluded = null, $comparison = null)
    {
        if (is_string($isExcluded)) {
            $isExcluded = in_array(strtolower($isExcluded), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_IS_EXCLUDED, $isExcluded, $comparison);
    }

    /**
     * Filter the query on the is_include_in_notifications column
     *
     * Example usage:
     * <code>
     * $query->filterByIsIncludeInNotifications(true); // WHERE is_include_in_notifications = true
     * $query->filterByIsIncludeInNotifications('yes'); // WHERE is_include_in_notifications = true
     * </code>
     *
     * @param     boolean|string $isIncludeInNotifications The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByIsIncludeInNotifications($isIncludeInNotifications = null, $comparison = null)
    {
        if (is_string($isIncludeInNotifications)) {
            $isIncludeInNotifications = in_array(strtolower($isIncludeInNotifications), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_IS_INCLUDE_IN_NOTIFICATIONS, $isIncludeInNotifications, $comparison);
    }

    /**
     * Filter the query on the matched_user_keywords column
     *
     * @param     array $matchedUserKeywords The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByMatchedUserKeywords($matchedUserKeywords = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserJobMatchTableMap::COL_MATCHED_USER_KEYWORDS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($matchedUserKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($matchedUserKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($matchedUserKeywords as $value) {
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

        return $this->addUsingAlias(UserJobMatchTableMap::COL_MATCHED_USER_KEYWORDS, $matchedUserKeywords, $comparison);
    }

    /**
     * Filter the query on the matched_user_keywords column
     * @param     mixed $matchedUserKeywords The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByMatchedUserKeyword($matchedUserKeywords = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($matchedUserKeywords)) {
                $matchedUserKeywords = '%| ' . $matchedUserKeywords . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $matchedUserKeywords = '%| ' . $matchedUserKeywords . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserJobMatchTableMap::COL_MATCHED_USER_KEYWORDS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $matchedUserKeywords, $comparison);
            } else {
                $this->addAnd($key, $matchedUserKeywords, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_MATCHED_USER_KEYWORDS, $matchedUserKeywords, $comparison);
    }

    /**
     * Filter the query on the matched_negative_title_keywords column
     *
     * @param     array $matchedNegativeTitleKeywords The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByMatchedNegativeTitleKeywords($matchedNegativeTitleKeywords = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_TITLE_KEYWORDS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($matchedNegativeTitleKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($matchedNegativeTitleKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($matchedNegativeTitleKeywords as $value) {
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

        return $this->addUsingAlias(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_TITLE_KEYWORDS, $matchedNegativeTitleKeywords, $comparison);
    }

    /**
     * Filter the query on the matched_negative_title_keywords column
     * @param     mixed $matchedNegativeTitleKeywords The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByMatchedNegativeTitleKeyword($matchedNegativeTitleKeywords = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($matchedNegativeTitleKeywords)) {
                $matchedNegativeTitleKeywords = '%| ' . $matchedNegativeTitleKeywords . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $matchedNegativeTitleKeywords = '%| ' . $matchedNegativeTitleKeywords . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_TITLE_KEYWORDS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $matchedNegativeTitleKeywords, $comparison);
            } else {
                $this->addAnd($key, $matchedNegativeTitleKeywords, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_TITLE_KEYWORDS, $matchedNegativeTitleKeywords, $comparison);
    }

    /**
     * Filter the query on the matched_negative_company_keywords column
     *
     * @param     array $matchedNegativeCompanyKeywords The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByMatchedNegativeCompanyKeywords($matchedNegativeCompanyKeywords = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_COMPANY_KEYWORDS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($matchedNegativeCompanyKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($matchedNegativeCompanyKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($matchedNegativeCompanyKeywords as $value) {
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

        return $this->addUsingAlias(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_COMPANY_KEYWORDS, $matchedNegativeCompanyKeywords, $comparison);
    }

    /**
     * Filter the query on the matched_negative_company_keywords column
     * @param     mixed $matchedNegativeCompanyKeywords The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByMatchedNegativeCompanyKeyword($matchedNegativeCompanyKeywords = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($matchedNegativeCompanyKeywords)) {
                $matchedNegativeCompanyKeywords = '%| ' . $matchedNegativeCompanyKeywords . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $matchedNegativeCompanyKeywords = '%| ' . $matchedNegativeCompanyKeywords . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_COMPANY_KEYWORDS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $matchedNegativeCompanyKeywords, $comparison);
            } else {
                $this->addAnd($key, $matchedNegativeCompanyKeywords, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_MATCHED_NEGATIVE_COMPANY_KEYWORDS, $matchedNegativeCompanyKeywords, $comparison);
    }

    /**
     * Filter the query on the out_of_user_area column
     *
     * Example usage:
     * <code>
     * $query->filterByOutOfUserArea(true); // WHERE out_of_user_area = true
     * $query->filterByOutOfUserArea('yes'); // WHERE out_of_user_area = true
     * </code>
     *
     * @param     boolean|string $outOfUserArea The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByOutOfUserArea($outOfUserArea = null, $comparison = null)
    {
        if (is_string($outOfUserArea)) {
            $outOfUserArea = in_array(strtolower($outOfUserArea), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_OUT_OF_USER_AREA, $outOfUserArea, $comparison);
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
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByAppRunId($appRunId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($appRunId)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserJobMatchTableMap::COL_APP_RUN_ID, $appRunId, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\User object
     *
     * @param \JobScooper\DataAccess\User|ObjectCollection $user The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByUser($user, $comparison = null)
    {
        if ($user instanceof \JobScooper\DataAccess\User) {
            return $this
                ->addUsingAlias(UserJobMatchTableMap::COL_USER_SLUG, $user->getUserSlug(), $comparison);
        } elseif ($user instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserJobMatchTableMap::COL_USER_SLUG, $user->toKeyValue('PrimaryKey', 'UserSlug'), $comparison);
        } else {
            throw new PropelException('filterByUser() only accepts arguments of type \JobScooper\DataAccess\User or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the User relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function joinUser($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('User');

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
            $this->addJoinObject($join, 'User');
        }

        return $this;
    }

    /**
     * Use the User relation User object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserQuery A secondary query class using the current class as primary query
     */
    public function useUserQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUser($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'User', '\JobScooper\DataAccess\UserQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobPosting object
     *
     * @param \JobScooper\DataAccess\JobPosting|ObjectCollection $jobPosting The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function filterByJobPosting($jobPosting, $comparison = null)
    {
        if ($jobPosting instanceof \JobScooper\DataAccess\JobPosting) {
            return $this
                ->addUsingAlias(UserJobMatchTableMap::COL_JOBPOSTING_ID, $jobPosting->getJobPostingId(), $comparison);
        } elseif ($jobPosting instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserJobMatchTableMap::COL_JOBPOSTING_ID, $jobPosting->toKeyValue('PrimaryKey', 'JobPostingId'), $comparison);
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
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function joinJobPosting($relationAlias = null, $joinType = Criteria::INNER_JOIN)
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
    public function useJobPostingQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinJobPosting($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'JobPosting', '\JobScooper\DataAccess\JobPostingQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUserJobMatch $userJobMatch Object to remove from the list of results
     *
     * @return $this|ChildUserJobMatchQuery The current query, for fluid interface
     */
    public function prune($userJobMatch = null)
    {
        if ($userJobMatch) {
            $this->addUsingAlias(UserJobMatchTableMap::COL_USER_JOB_MATCH_ID, $userJobMatch->getUserJobMatchId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user_job_match table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserJobMatchTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserJobMatchTableMap::clearInstancePool();
            UserJobMatchTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserJobMatchTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserJobMatchTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserJobMatchTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserJobMatchTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // UserJobMatchQuery
