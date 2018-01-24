<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\JobSiteRecord as ChildJobSiteRecord;
use JobScooper\DataAccess\JobSiteRecordQuery as ChildJobSiteRecordQuery;
use JobScooper\DataAccess\Map\JobSiteRecordTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'job_site' table.
 *
 *
 *
 * @method     ChildJobSiteRecordQuery orderByJobSiteKey($order = Criteria::ASC) Order by the jobsite_key column
 * @method     ChildJobSiteRecordQuery orderByPluginClassName($order = Criteria::ASC) Order by the plugin_class_name column
 * @method     ChildJobSiteRecordQuery orderByDisplayName($order = Criteria::ASC) Order by the display_name column
 * @method     ChildJobSiteRecordQuery orderByisDisabled($order = Criteria::ASC) Order by the is_disabled column
 * @method     ChildJobSiteRecordQuery orderByResultsFilterType($order = Criteria::ASC) Order by the results_filter_type column
 *
 * @method     ChildJobSiteRecordQuery groupByJobSiteKey() Group by the jobsite_key column
 * @method     ChildJobSiteRecordQuery groupByPluginClassName() Group by the plugin_class_name column
 * @method     ChildJobSiteRecordQuery groupByDisplayName() Group by the display_name column
 * @method     ChildJobSiteRecordQuery groupByisDisabled() Group by the is_disabled column
 * @method     ChildJobSiteRecordQuery groupByResultsFilterType() Group by the results_filter_type column
 *
 * @method     ChildJobSiteRecordQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildJobSiteRecordQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildJobSiteRecordQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildJobSiteRecordQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildJobSiteRecordQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildJobSiteRecordQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildJobSiteRecordQuery leftJoinJobPosting($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobPosting relation
 * @method     ChildJobSiteRecordQuery rightJoinJobPosting($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobPosting relation
 * @method     ChildJobSiteRecordQuery innerJoinJobPosting($relationAlias = null) Adds a INNER JOIN clause to the query using the JobPosting relation
 *
 * @method     ChildJobSiteRecordQuery joinWithJobPosting($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobPosting relation
 *
 * @method     ChildJobSiteRecordQuery leftJoinWithJobPosting() Adds a LEFT JOIN clause and with to the query using the JobPosting relation
 * @method     ChildJobSiteRecordQuery rightJoinWithJobPosting() Adds a RIGHT JOIN clause and with to the query using the JobPosting relation
 * @method     ChildJobSiteRecordQuery innerJoinWithJobPosting() Adds a INNER JOIN clause and with to the query using the JobPosting relation
 *
 * @method     ChildJobSiteRecordQuery leftJoinUserSearchSiteRun($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserSearchSiteRun relation
 * @method     ChildJobSiteRecordQuery rightJoinUserSearchSiteRun($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserSearchSiteRun relation
 * @method     ChildJobSiteRecordQuery innerJoinUserSearchSiteRun($relationAlias = null) Adds a INNER JOIN clause to the query using the UserSearchSiteRun relation
 *
 * @method     ChildJobSiteRecordQuery joinWithUserSearchSiteRun($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserSearchSiteRun relation
 *
 * @method     ChildJobSiteRecordQuery leftJoinWithUserSearchSiteRun() Adds a LEFT JOIN clause and with to the query using the UserSearchSiteRun relation
 * @method     ChildJobSiteRecordQuery rightJoinWithUserSearchSiteRun() Adds a RIGHT JOIN clause and with to the query using the UserSearchSiteRun relation
 * @method     ChildJobSiteRecordQuery innerJoinWithUserSearchSiteRun() Adds a INNER JOIN clause and with to the query using the UserSearchSiteRun relation
 *
 * @method     \JobScooper\DataAccess\JobPostingQuery|\JobScooper\DataAccess\UserSearchSiteRunQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildJobSiteRecord findOne(ConnectionInterface $con = null) Return the first ChildJobSiteRecord matching the query
 * @method     ChildJobSiteRecord findOneOrCreate(ConnectionInterface $con = null) Return the first ChildJobSiteRecord matching the query, or a new ChildJobSiteRecord object populated from the query conditions when no match is found
 *
 * @method     ChildJobSiteRecord findOneByJobSiteKey(string $jobsite_key) Return the first ChildJobSiteRecord filtered by the jobsite_key column
 * @method     ChildJobSiteRecord findOneByPluginClassName(string $plugin_class_name) Return the first ChildJobSiteRecord filtered by the plugin_class_name column
 * @method     ChildJobSiteRecord findOneByDisplayName(string $display_name) Return the first ChildJobSiteRecord filtered by the display_name column
 * @method     ChildJobSiteRecord findOneByisDisabled(boolean $is_disabled) Return the first ChildJobSiteRecord filtered by the is_disabled column
 * @method     ChildJobSiteRecord findOneByResultsFilterType(int $results_filter_type) Return the first ChildJobSiteRecord filtered by the results_filter_type column *

 * @method     ChildJobSiteRecord requirePk($key, ConnectionInterface $con = null) Return the ChildJobSiteRecord by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobSiteRecord requireOne(ConnectionInterface $con = null) Return the first ChildJobSiteRecord matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildJobSiteRecord requireOneByJobSiteKey(string $jobsite_key) Return the first ChildJobSiteRecord filtered by the jobsite_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobSiteRecord requireOneByPluginClassName(string $plugin_class_name) Return the first ChildJobSiteRecord filtered by the plugin_class_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobSiteRecord requireOneByDisplayName(string $display_name) Return the first ChildJobSiteRecord filtered by the display_name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobSiteRecord requireOneByisDisabled(boolean $is_disabled) Return the first ChildJobSiteRecord filtered by the is_disabled column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobSiteRecord requireOneByResultsFilterType(int $results_filter_type) Return the first ChildJobSiteRecord filtered by the results_filter_type column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildJobSiteRecord[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildJobSiteRecord objects based on current ModelCriteria
 * @method     ChildJobSiteRecord[]|ObjectCollection findByJobSiteKey(string $jobsite_key) Return ChildJobSiteRecord objects filtered by the jobsite_key column
 * @method     ChildJobSiteRecord[]|ObjectCollection findByPluginClassName(string $plugin_class_name) Return ChildJobSiteRecord objects filtered by the plugin_class_name column
 * @method     ChildJobSiteRecord[]|ObjectCollection findByDisplayName(string $display_name) Return ChildJobSiteRecord objects filtered by the display_name column
 * @method     ChildJobSiteRecord[]|ObjectCollection findByisDisabled(boolean $is_disabled) Return ChildJobSiteRecord objects filtered by the is_disabled column
 * @method     ChildJobSiteRecord[]|ObjectCollection findByResultsFilterType(int $results_filter_type) Return ChildJobSiteRecord objects filtered by the results_filter_type column
 * @method     ChildJobSiteRecord[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class JobSiteRecordQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\JobSiteRecordQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\JobSiteRecord', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildJobSiteRecordQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildJobSiteRecordQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildJobSiteRecordQuery) {
            return $criteria;
        }
        $query = new ChildJobSiteRecordQuery();
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
     * @return ChildJobSiteRecord|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = JobSiteRecordTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildJobSiteRecord A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT jobsite_key, plugin_class_name, display_name, is_disabled, results_filter_type FROM job_site WHERE jobsite_key = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildJobSiteRecord $obj */
            $obj = new ChildJobSiteRecord();
            $obj->hydrate($row);
            JobSiteRecordTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildJobSiteRecord|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_JOBSITE_KEY, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_JOBSITE_KEY, $keys, Criteria::IN);
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
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByJobSiteKey($jobSiteKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($jobSiteKey)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_JOBSITE_KEY, $jobSiteKey, $comparison);
    }

    /**
     * Filter the query on the plugin_class_name column
     *
     * Example usage:
     * <code>
     * $query->filterByPluginClassName('fooValue');   // WHERE plugin_class_name = 'fooValue'
     * $query->filterByPluginClassName('%fooValue%', Criteria::LIKE); // WHERE plugin_class_name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $pluginClassName The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByPluginClassName($pluginClassName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($pluginClassName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME, $pluginClassName, $comparison);
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
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByDisplayName($displayName = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($displayName)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_DISPLAY_NAME, $displayName, $comparison);
    }

    /**
     * Filter the query on the is_disabled column
     *
     * Example usage:
     * <code>
     * $query->filterByisDisabled(true); // WHERE is_disabled = true
     * $query->filterByisDisabled('yes'); // WHERE is_disabled = true
     * </code>
     *
     * @param     boolean|string $isDisabled The value to use as filter.
     *              Non-boolean arguments are converted using the following rules:
     *                * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *                * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     *              Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByisDisabled($isDisabled = null, $comparison = null)
    {
        if (is_string($isDisabled)) {
            $isDisabled = in_array(strtolower($isDisabled), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
        }

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_IS_DISABLED, $isDisabled, $comparison);
    }

    /**
     * Filter the query on the results_filter_type column
     *
     * @param     mixed $resultsFilterType The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByResultsFilterType($resultsFilterType = null, $comparison = null)
    {
        $valueSet = JobSiteRecordTableMap::getValueSet(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE);
        if (is_scalar($resultsFilterType)) {
            if (!in_array($resultsFilterType, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $resultsFilterType));
            }
            $resultsFilterType = array_search($resultsFilterType, $valueSet);
        } elseif (is_array($resultsFilterType)) {
            $convertedValues = array();
            foreach ($resultsFilterType as $value) {
                if (!in_array($value, $valueSet)) {
                    throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $value));
                }
                $convertedValues []= array_search($value, $valueSet);
            }
            $resultsFilterType = $convertedValues;
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE, $resultsFilterType, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobPosting object
     *
     * @param \JobScooper\DataAccess\JobPosting|ObjectCollection $jobPosting the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByJobPosting($jobPosting, $comparison = null)
    {
        if ($jobPosting instanceof \JobScooper\DataAccess\JobPosting) {
            return $this
                ->addUsingAlias(JobSiteRecordTableMap::COL_JOBSITE_KEY, $jobPosting->getJobSiteKey(), $comparison);
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
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
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
     * Filter the query by a related \JobScooper\DataAccess\UserSearchSiteRun object
     *
     * @param \JobScooper\DataAccess\UserSearchSiteRun|ObjectCollection $userSearchSiteRun the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function filterByUserSearchSiteRun($userSearchSiteRun, $comparison = null)
    {
        if ($userSearchSiteRun instanceof \JobScooper\DataAccess\UserSearchSiteRun) {
            return $this
                ->addUsingAlias(JobSiteRecordTableMap::COL_JOBSITE_KEY, $userSearchSiteRun->getJobSiteKey(), $comparison);
        } elseif ($userSearchSiteRun instanceof ObjectCollection) {
            return $this
                ->useUserSearchSiteRunQuery()
                ->filterByPrimaryKeys($userSearchSiteRun->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserSearchSiteRun() only accepts arguments of type \JobScooper\DataAccess\UserSearchSiteRun or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserSearchSiteRun relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function joinUserSearchSiteRun($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserSearchSiteRun');

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
            $this->addJoinObject($join, 'UserSearchSiteRun');
        }

        return $this;
    }

    /**
     * Use the UserSearchSiteRun relation UserSearchSiteRun object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserSearchSiteRunQuery A secondary query class using the current class as primary query
     */
    public function useUserSearchSiteRunQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserSearchSiteRun($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserSearchSiteRun', '\JobScooper\DataAccess\UserSearchSiteRunQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildJobSiteRecord $jobSiteRecord Object to remove from the list of results
     *
     * @return $this|ChildJobSiteRecordQuery The current query, for fluid interface
     */
    public function prune($jobSiteRecord = null)
    {
        if ($jobSiteRecord) {
            $this->addUsingAlias(JobSiteRecordTableMap::COL_JOBSITE_KEY, $jobSiteRecord->getJobSiteKey(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the job_site table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            JobSiteRecordTableMap::clearInstancePool();
            JobSiteRecordTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(JobSiteRecordTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            JobSiteRecordTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            JobSiteRecordTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

} // JobSiteRecordQuery
