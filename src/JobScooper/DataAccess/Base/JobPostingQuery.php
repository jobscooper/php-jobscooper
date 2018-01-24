<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\JobPosting as ChildJobPosting;
use JobScooper\DataAccess\JobPostingQuery as ChildJobPostingQuery;
use JobScooper\DataAccess\Map\JobPostingTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'jobposting' table.
 *
 *
 *
 * @method     ChildJobPostingQuery orderByJobPostingId($order = Criteria::ASC) Order by the jobposting_id column
 * @method     ChildJobPostingQuery orderByJobSiteKey($order = Criteria::ASC) Order by the jobsite_key column
 * @method     ChildJobPostingQuery orderByJobSitePostId($order = Criteria::ASC) Order by the jobsite_post_id column
 * @method     ChildJobPostingQuery orderByTitle($order = Criteria::ASC) Order by the title column
 * @method     ChildJobPostingQuery orderByUrl($order = Criteria::ASC) Order by the url column
 * @method     ChildJobPostingQuery orderByEmploymentType($order = Criteria::ASC) Order by the employment_type column
 * @method     ChildJobPostingQuery orderByPayRange($order = Criteria::ASC) Order by the pay_range column
 * @method     ChildJobPostingQuery orderByCompany($order = Criteria::ASC) Order by the company column
 * @method     ChildJobPostingQuery orderByLocation($order = Criteria::ASC) Order by the location column
 * @method     ChildJobPostingQuery orderByDepartment($order = Criteria::ASC) Order by the department column
 * @method     ChildJobPostingQuery orderByCategory($order = Criteria::ASC) Order by the category column
 * @method     ChildJobPostingQuery orderByUpdatedAt($order = Criteria::ASC) Order by the last_updated_at column
 * @method     ChildJobPostingQuery orderByPostedAt($order = Criteria::ASC) Order by the job_posted_date column
 * @method     ChildJobPostingQuery orderByFirstSeenAt($order = Criteria::ASC) Order by the first_seen_at column
 * @method     ChildJobPostingQuery orderByRemovedAt($order = Criteria::ASC) Order by the post_removed_at column
 * @method     ChildJobPostingQuery orderByLocationDisplayValue($order = Criteria::ASC) Order by the location_display_value column
 * @method     ChildJobPostingQuery orderByGeoLocationId($order = Criteria::ASC) Order by the geolocation_id column
 * @method     ChildJobPostingQuery orderByDuplicatesJobPostingId($order = Criteria::ASC) Order by the duplicates_posting_id column
 * @method     ChildJobPostingQuery orderByKeyCompanyAndTitle($order = Criteria::ASC) Order by the key_company_and_title column
 *
 * @method     ChildJobPostingQuery groupByJobPostingId() Group by the jobposting_id column
 * @method     ChildJobPostingQuery groupByJobSiteKey() Group by the jobsite_key column
 * @method     ChildJobPostingQuery groupByJobSitePostId() Group by the jobsite_post_id column
 * @method     ChildJobPostingQuery groupByTitle() Group by the title column
 * @method     ChildJobPostingQuery groupByUrl() Group by the url column
 * @method     ChildJobPostingQuery groupByEmploymentType() Group by the employment_type column
 * @method     ChildJobPostingQuery groupByPayRange() Group by the pay_range column
 * @method     ChildJobPostingQuery groupByCompany() Group by the company column
 * @method     ChildJobPostingQuery groupByLocation() Group by the location column
 * @method     ChildJobPostingQuery groupByDepartment() Group by the department column
 * @method     ChildJobPostingQuery groupByCategory() Group by the category column
 * @method     ChildJobPostingQuery groupByUpdatedAt() Group by the last_updated_at column
 * @method     ChildJobPostingQuery groupByPostedAt() Group by the job_posted_date column
 * @method     ChildJobPostingQuery groupByFirstSeenAt() Group by the first_seen_at column
 * @method     ChildJobPostingQuery groupByRemovedAt() Group by the post_removed_at column
 * @method     ChildJobPostingQuery groupByLocationDisplayValue() Group by the location_display_value column
 * @method     ChildJobPostingQuery groupByGeoLocationId() Group by the geolocation_id column
 * @method     ChildJobPostingQuery groupByDuplicatesJobPostingId() Group by the duplicates_posting_id column
 * @method     ChildJobPostingQuery groupByKeyCompanyAndTitle() Group by the key_company_and_title column
 *
 * @method     ChildJobPostingQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildJobPostingQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildJobPostingQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildJobPostingQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildJobPostingQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildJobPostingQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildJobPostingQuery leftJoinJobSiteFromJP($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobSiteFromJP relation
 * @method     ChildJobPostingQuery rightJoinJobSiteFromJP($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobSiteFromJP relation
 * @method     ChildJobPostingQuery innerJoinJobSiteFromJP($relationAlias = null) Adds a INNER JOIN clause to the query using the JobSiteFromJP relation
 *
 * @method     ChildJobPostingQuery joinWithJobSiteFromJP($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobSiteFromJP relation
 *
 * @method     ChildJobPostingQuery leftJoinWithJobSiteFromJP() Adds a LEFT JOIN clause and with to the query using the JobSiteFromJP relation
 * @method     ChildJobPostingQuery rightJoinWithJobSiteFromJP() Adds a RIGHT JOIN clause and with to the query using the JobSiteFromJP relation
 * @method     ChildJobPostingQuery innerJoinWithJobSiteFromJP() Adds a INNER JOIN clause and with to the query using the JobSiteFromJP relation
 *
 * @method     ChildJobPostingQuery leftJoinGeoLocationFromJP($relationAlias = null) Adds a LEFT JOIN clause to the query using the GeoLocationFromJP relation
 * @method     ChildJobPostingQuery rightJoinGeoLocationFromJP($relationAlias = null) Adds a RIGHT JOIN clause to the query using the GeoLocationFromJP relation
 * @method     ChildJobPostingQuery innerJoinGeoLocationFromJP($relationAlias = null) Adds a INNER JOIN clause to the query using the GeoLocationFromJP relation
 *
 * @method     ChildJobPostingQuery joinWithGeoLocationFromJP($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the GeoLocationFromJP relation
 *
 * @method     ChildJobPostingQuery leftJoinWithGeoLocationFromJP() Adds a LEFT JOIN clause and with to the query using the GeoLocationFromJP relation
 * @method     ChildJobPostingQuery rightJoinWithGeoLocationFromJP() Adds a RIGHT JOIN clause and with to the query using the GeoLocationFromJP relation
 * @method     ChildJobPostingQuery innerJoinWithGeoLocationFromJP() Adds a INNER JOIN clause and with to the query using the GeoLocationFromJP relation
 *
 * @method     ChildJobPostingQuery leftJoinDuplicateJobPosting($relationAlias = null) Adds a LEFT JOIN clause to the query using the DuplicateJobPosting relation
 * @method     ChildJobPostingQuery rightJoinDuplicateJobPosting($relationAlias = null) Adds a RIGHT JOIN clause to the query using the DuplicateJobPosting relation
 * @method     ChildJobPostingQuery innerJoinDuplicateJobPosting($relationAlias = null) Adds a INNER JOIN clause to the query using the DuplicateJobPosting relation
 *
 * @method     ChildJobPostingQuery joinWithDuplicateJobPosting($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the DuplicateJobPosting relation
 *
 * @method     ChildJobPostingQuery leftJoinWithDuplicateJobPosting() Adds a LEFT JOIN clause and with to the query using the DuplicateJobPosting relation
 * @method     ChildJobPostingQuery rightJoinWithDuplicateJobPosting() Adds a RIGHT JOIN clause and with to the query using the DuplicateJobPosting relation
 * @method     ChildJobPostingQuery innerJoinWithDuplicateJobPosting() Adds a INNER JOIN clause and with to the query using the DuplicateJobPosting relation
 *
 * @method     ChildJobPostingQuery leftJoinJobPostingRelatedByJobPostingId($relationAlias = null) Adds a LEFT JOIN clause to the query using the JobPostingRelatedByJobPostingId relation
 * @method     ChildJobPostingQuery rightJoinJobPostingRelatedByJobPostingId($relationAlias = null) Adds a RIGHT JOIN clause to the query using the JobPostingRelatedByJobPostingId relation
 * @method     ChildJobPostingQuery innerJoinJobPostingRelatedByJobPostingId($relationAlias = null) Adds a INNER JOIN clause to the query using the JobPostingRelatedByJobPostingId relation
 *
 * @method     ChildJobPostingQuery joinWithJobPostingRelatedByJobPostingId($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the JobPostingRelatedByJobPostingId relation
 *
 * @method     ChildJobPostingQuery leftJoinWithJobPostingRelatedByJobPostingId() Adds a LEFT JOIN clause and with to the query using the JobPostingRelatedByJobPostingId relation
 * @method     ChildJobPostingQuery rightJoinWithJobPostingRelatedByJobPostingId() Adds a RIGHT JOIN clause and with to the query using the JobPostingRelatedByJobPostingId relation
 * @method     ChildJobPostingQuery innerJoinWithJobPostingRelatedByJobPostingId() Adds a INNER JOIN clause and with to the query using the JobPostingRelatedByJobPostingId relation
 *
 * @method     ChildJobPostingQuery leftJoinUserJobMatch($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserJobMatch relation
 * @method     ChildJobPostingQuery rightJoinUserJobMatch($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserJobMatch relation
 * @method     ChildJobPostingQuery innerJoinUserJobMatch($relationAlias = null) Adds a INNER JOIN clause to the query using the UserJobMatch relation
 *
 * @method     ChildJobPostingQuery joinWithUserJobMatch($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserJobMatch relation
 *
 * @method     ChildJobPostingQuery leftJoinWithUserJobMatch() Adds a LEFT JOIN clause and with to the query using the UserJobMatch relation
 * @method     ChildJobPostingQuery rightJoinWithUserJobMatch() Adds a RIGHT JOIN clause and with to the query using the UserJobMatch relation
 * @method     ChildJobPostingQuery innerJoinWithUserJobMatch() Adds a INNER JOIN clause and with to the query using the UserJobMatch relation
 *
 * @method     \JobScooper\DataAccess\JobSiteRecordQuery|\JobScooper\DataAccess\GeoLocationQuery|\JobScooper\DataAccess\JobPostingQuery|\JobScooper\DataAccess\UserJobMatchQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildJobPosting findOne(ConnectionInterface $con = null) Return the first ChildJobPosting matching the query
 * @method     ChildJobPosting findOneOrCreate(ConnectionInterface $con = null) Return the first ChildJobPosting matching the query, or a new ChildJobPosting object populated from the query conditions when no match is found
 *
 * @method     ChildJobPosting findOneByJobPostingId(int $jobposting_id) Return the first ChildJobPosting filtered by the jobposting_id column
 * @method     ChildJobPosting findOneByJobSiteKey(string $jobsite_key) Return the first ChildJobPosting filtered by the jobsite_key column
 * @method     ChildJobPosting findOneByJobSitePostId(string $jobsite_post_id) Return the first ChildJobPosting filtered by the jobsite_post_id column
 * @method     ChildJobPosting findOneByTitle(string $title) Return the first ChildJobPosting filtered by the title column
 * @method     ChildJobPosting findOneByUrl(string $url) Return the first ChildJobPosting filtered by the url column
 * @method     ChildJobPosting findOneByEmploymentType(string $employment_type) Return the first ChildJobPosting filtered by the employment_type column
 * @method     ChildJobPosting findOneByPayRange(string $pay_range) Return the first ChildJobPosting filtered by the pay_range column
 * @method     ChildJobPosting findOneByCompany(string $company) Return the first ChildJobPosting filtered by the company column
 * @method     ChildJobPosting findOneByLocation(string $location) Return the first ChildJobPosting filtered by the location column
 * @method     ChildJobPosting findOneByDepartment(string $department) Return the first ChildJobPosting filtered by the department column
 * @method     ChildJobPosting findOneByCategory(string $category) Return the first ChildJobPosting filtered by the category column
 * @method     ChildJobPosting findOneByUpdatedAt(string $last_updated_at) Return the first ChildJobPosting filtered by the last_updated_at column
 * @method     ChildJobPosting findOneByPostedAt(string $job_posted_date) Return the first ChildJobPosting filtered by the job_posted_date column
 * @method     ChildJobPosting findOneByFirstSeenAt(string $first_seen_at) Return the first ChildJobPosting filtered by the first_seen_at column
 * @method     ChildJobPosting findOneByRemovedAt(string $post_removed_at) Return the first ChildJobPosting filtered by the post_removed_at column
 * @method     ChildJobPosting findOneByLocationDisplayValue(string $location_display_value) Return the first ChildJobPosting filtered by the location_display_value column
 * @method     ChildJobPosting findOneByGeoLocationId(int $geolocation_id) Return the first ChildJobPosting filtered by the geolocation_id column
 * @method     ChildJobPosting findOneByDuplicatesJobPostingId(int $duplicates_posting_id) Return the first ChildJobPosting filtered by the duplicates_posting_id column
 * @method     ChildJobPosting findOneByKeyCompanyAndTitle(string $key_company_and_title) Return the first ChildJobPosting filtered by the key_company_and_title column *

 * @method     ChildJobPosting requirePk($key, ConnectionInterface $con = null) Return the ChildJobPosting by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOne(ConnectionInterface $con = null) Return the first ChildJobPosting matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildJobPosting requireOneByJobPostingId(int $jobposting_id) Return the first ChildJobPosting filtered by the jobposting_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByJobSiteKey(string $jobsite_key) Return the first ChildJobPosting filtered by the jobsite_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByJobSitePostId(string $jobsite_post_id) Return the first ChildJobPosting filtered by the jobsite_post_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByTitle(string $title) Return the first ChildJobPosting filtered by the title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByUrl(string $url) Return the first ChildJobPosting filtered by the url column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByEmploymentType(string $employment_type) Return the first ChildJobPosting filtered by the employment_type column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByPayRange(string $pay_range) Return the first ChildJobPosting filtered by the pay_range column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByCompany(string $company) Return the first ChildJobPosting filtered by the company column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByLocation(string $location) Return the first ChildJobPosting filtered by the location column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByDepartment(string $department) Return the first ChildJobPosting filtered by the department column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByCategory(string $category) Return the first ChildJobPosting filtered by the category column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByUpdatedAt(string $last_updated_at) Return the first ChildJobPosting filtered by the last_updated_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByPostedAt(string $job_posted_date) Return the first ChildJobPosting filtered by the job_posted_date column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByFirstSeenAt(string $first_seen_at) Return the first ChildJobPosting filtered by the first_seen_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByRemovedAt(string $post_removed_at) Return the first ChildJobPosting filtered by the post_removed_at column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByLocationDisplayValue(string $location_display_value) Return the first ChildJobPosting filtered by the location_display_value column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByGeoLocationId(int $geolocation_id) Return the first ChildJobPosting filtered by the geolocation_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByDuplicatesJobPostingId(int $duplicates_posting_id) Return the first ChildJobPosting filtered by the duplicates_posting_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildJobPosting requireOneByKeyCompanyAndTitle(string $key_company_and_title) Return the first ChildJobPosting filtered by the key_company_and_title column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildJobPosting[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildJobPosting objects based on current ModelCriteria
 * @method     ChildJobPosting[]|ObjectCollection findByJobPostingId(int $jobposting_id) Return ChildJobPosting objects filtered by the jobposting_id column
 * @method     ChildJobPosting[]|ObjectCollection findByJobSiteKey(string $jobsite_key) Return ChildJobPosting objects filtered by the jobsite_key column
 * @method     ChildJobPosting[]|ObjectCollection findByJobSitePostId(string $jobsite_post_id) Return ChildJobPosting objects filtered by the jobsite_post_id column
 * @method     ChildJobPosting[]|ObjectCollection findByTitle(string $title) Return ChildJobPosting objects filtered by the title column
 * @method     ChildJobPosting[]|ObjectCollection findByUrl(string $url) Return ChildJobPosting objects filtered by the url column
 * @method     ChildJobPosting[]|ObjectCollection findByEmploymentType(string $employment_type) Return ChildJobPosting objects filtered by the employment_type column
 * @method     ChildJobPosting[]|ObjectCollection findByPayRange(string $pay_range) Return ChildJobPosting objects filtered by the pay_range column
 * @method     ChildJobPosting[]|ObjectCollection findByCompany(string $company) Return ChildJobPosting objects filtered by the company column
 * @method     ChildJobPosting[]|ObjectCollection findByLocation(string $location) Return ChildJobPosting objects filtered by the location column
 * @method     ChildJobPosting[]|ObjectCollection findByDepartment(string $department) Return ChildJobPosting objects filtered by the department column
 * @method     ChildJobPosting[]|ObjectCollection findByCategory(string $category) Return ChildJobPosting objects filtered by the category column
 * @method     ChildJobPosting[]|ObjectCollection findByUpdatedAt(string $last_updated_at) Return ChildJobPosting objects filtered by the last_updated_at column
 * @method     ChildJobPosting[]|ObjectCollection findByPostedAt(string $job_posted_date) Return ChildJobPosting objects filtered by the job_posted_date column
 * @method     ChildJobPosting[]|ObjectCollection findByFirstSeenAt(string $first_seen_at) Return ChildJobPosting objects filtered by the first_seen_at column
 * @method     ChildJobPosting[]|ObjectCollection findByRemovedAt(string $post_removed_at) Return ChildJobPosting objects filtered by the post_removed_at column
 * @method     ChildJobPosting[]|ObjectCollection findByLocationDisplayValue(string $location_display_value) Return ChildJobPosting objects filtered by the location_display_value column
 * @method     ChildJobPosting[]|ObjectCollection findByGeoLocationId(int $geolocation_id) Return ChildJobPosting objects filtered by the geolocation_id column
 * @method     ChildJobPosting[]|ObjectCollection findByDuplicatesJobPostingId(int $duplicates_posting_id) Return ChildJobPosting objects filtered by the duplicates_posting_id column
 * @method     ChildJobPosting[]|ObjectCollection findByKeyCompanyAndTitle(string $key_company_and_title) Return ChildJobPosting objects filtered by the key_company_and_title column
 * @method     ChildJobPosting[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class JobPostingQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\JobPostingQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\JobPosting', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildJobPostingQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildJobPostingQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildJobPostingQuery) {
            return $criteria;
        }
        $query = new ChildJobPostingQuery();
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
     * @return ChildJobPosting|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(JobPostingTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = JobPostingTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildJobPosting A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT jobposting_id, jobsite_key, jobsite_post_id, title, url, employment_type, pay_range, company, location, department, category, last_updated_at, job_posted_date, first_seen_at, post_removed_at, location_display_value, geolocation_id, duplicates_posting_id, key_company_and_title FROM jobposting WHERE jobposting_id = :p0';
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
            /** @var ChildJobPosting $obj */
            $obj = new ChildJobPosting();
            $obj->hydrate($row);
            JobPostingTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildJobPosting|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $keys, Criteria::IN);
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
     * @param     mixed $jobPostingId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByJobPostingId($jobPostingId = null, $comparison = null)
    {
        if (is_array($jobPostingId)) {
            $useMinMax = false;
            if (isset($jobPostingId['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $jobPostingId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($jobPostingId['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $jobPostingId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $jobPostingId, $comparison);
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
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByJobSiteKey($jobSiteKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($jobSiteKey)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_JOBSITE_KEY, $jobSiteKey, $comparison);
    }

    /**
     * Filter the query on the jobsite_post_id column
     *
     * Example usage:
     * <code>
     * $query->filterByJobSitePostId('fooValue');   // WHERE jobsite_post_id = 'fooValue'
     * $query->filterByJobSitePostId('%fooValue%', Criteria::LIKE); // WHERE jobsite_post_id LIKE '%fooValue%'
     * </code>
     *
     * @param     string $jobSitePostId The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByJobSitePostId($jobSitePostId = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($jobSitePostId)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_JOBSITE_POST_ID, $jobSitePostId, $comparison);
    }

    /**
     * Filter the query on the title column
     *
     * Example usage:
     * <code>
     * $query->filterByTitle('fooValue');   // WHERE title = 'fooValue'
     * $query->filterByTitle('%fooValue%', Criteria::LIKE); // WHERE title LIKE '%fooValue%'
     * </code>
     *
     * @param     string $title The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByTitle($title = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($title)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_TITLE, $title, $comparison);
    }

    /**
     * Filter the query on the url column
     *
     * Example usage:
     * <code>
     * $query->filterByUrl('fooValue');   // WHERE url = 'fooValue'
     * $query->filterByUrl('%fooValue%', Criteria::LIKE); // WHERE url LIKE '%fooValue%'
     * </code>
     *
     * @param     string $url The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByUrl($url = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($url)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_URL, $url, $comparison);
    }

    /**
     * Filter the query on the employment_type column
     *
     * Example usage:
     * <code>
     * $query->filterByEmploymentType('fooValue');   // WHERE employment_type = 'fooValue'
     * $query->filterByEmploymentType('%fooValue%', Criteria::LIKE); // WHERE employment_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $employmentType The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByEmploymentType($employmentType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($employmentType)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_EMPLOYMENT_TYPE, $employmentType, $comparison);
    }

    /**
     * Filter the query on the pay_range column
     *
     * Example usage:
     * <code>
     * $query->filterByPayRange('fooValue');   // WHERE pay_range = 'fooValue'
     * $query->filterByPayRange('%fooValue%', Criteria::LIKE); // WHERE pay_range LIKE '%fooValue%'
     * </code>
     *
     * @param     string $payRange The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByPayRange($payRange = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($payRange)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_PAY_RANGE, $payRange, $comparison);
    }

    /**
     * Filter the query on the company column
     *
     * Example usage:
     * <code>
     * $query->filterByCompany('fooValue');   // WHERE company = 'fooValue'
     * $query->filterByCompany('%fooValue%', Criteria::LIKE); // WHERE company LIKE '%fooValue%'
     * </code>
     *
     * @param     string $company The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByCompany($company = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($company)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_COMPANY, $company, $comparison);
    }

    /**
     * Filter the query on the location column
     *
     * Example usage:
     * <code>
     * $query->filterByLocation('fooValue');   // WHERE location = 'fooValue'
     * $query->filterByLocation('%fooValue%', Criteria::LIKE); // WHERE location LIKE '%fooValue%'
     * </code>
     *
     * @param     string $location The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByLocation($location = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($location)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_LOCATION, $location, $comparison);
    }

    /**
     * Filter the query on the department column
     *
     * Example usage:
     * <code>
     * $query->filterByDepartment('fooValue');   // WHERE department = 'fooValue'
     * $query->filterByDepartment('%fooValue%', Criteria::LIKE); // WHERE department LIKE '%fooValue%'
     * </code>
     *
     * @param     string $department The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByDepartment($department = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($department)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_DEPARTMENT, $department, $comparison);
    }

    /**
     * Filter the query on the category column
     *
     * Example usage:
     * <code>
     * $query->filterByCategory('fooValue');   // WHERE category = 'fooValue'
     * $query->filterByCategory('%fooValue%', Criteria::LIKE); // WHERE category LIKE '%fooValue%'
     * </code>
     *
     * @param     string $category The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByCategory($category = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($category)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_CATEGORY, $category, $comparison);
    }

    /**
     * Filter the query on the last_updated_at column
     *
     * Example usage:
     * <code>
     * $query->filterByUpdatedAt('2011-03-14'); // WHERE last_updated_at = '2011-03-14'
     * $query->filterByUpdatedAt('now'); // WHERE last_updated_at = '2011-03-14'
     * $query->filterByUpdatedAt(array('max' => 'yesterday')); // WHERE last_updated_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $updatedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByUpdatedAt($updatedAt = null, $comparison = null)
    {
        if (is_array($updatedAt)) {
            $useMinMax = false;
            if (isset($updatedAt['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_LAST_UPDATED_AT, $updatedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($updatedAt['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_LAST_UPDATED_AT, $updatedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_LAST_UPDATED_AT, $updatedAt, $comparison);
    }

    /**
     * Filter the query on the job_posted_date column
     *
     * Example usage:
     * <code>
     * $query->filterByPostedAt('2011-03-14'); // WHERE job_posted_date = '2011-03-14'
     * $query->filterByPostedAt('now'); // WHERE job_posted_date = '2011-03-14'
     * $query->filterByPostedAt(array('max' => 'yesterday')); // WHERE job_posted_date > '2011-03-13'
     * </code>
     *
     * @param     mixed $postedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByPostedAt($postedAt = null, $comparison = null)
    {
        if (is_array($postedAt)) {
            $useMinMax = false;
            if (isset($postedAt['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_JOB_POSTED_DATE, $postedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($postedAt['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_JOB_POSTED_DATE, $postedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_JOB_POSTED_DATE, $postedAt, $comparison);
    }

    /**
     * Filter the query on the first_seen_at column
     *
     * Example usage:
     * <code>
     * $query->filterByFirstSeenAt('2011-03-14'); // WHERE first_seen_at = '2011-03-14'
     * $query->filterByFirstSeenAt('now'); // WHERE first_seen_at = '2011-03-14'
     * $query->filterByFirstSeenAt(array('max' => 'yesterday')); // WHERE first_seen_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $firstSeenAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByFirstSeenAt($firstSeenAt = null, $comparison = null)
    {
        if (is_array($firstSeenAt)) {
            $useMinMax = false;
            if (isset($firstSeenAt['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_FIRST_SEEN_AT, $firstSeenAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($firstSeenAt['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_FIRST_SEEN_AT, $firstSeenAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_FIRST_SEEN_AT, $firstSeenAt, $comparison);
    }

    /**
     * Filter the query on the post_removed_at column
     *
     * Example usage:
     * <code>
     * $query->filterByRemovedAt('2011-03-14'); // WHERE post_removed_at = '2011-03-14'
     * $query->filterByRemovedAt('now'); // WHERE post_removed_at = '2011-03-14'
     * $query->filterByRemovedAt(array('max' => 'yesterday')); // WHERE post_removed_at > '2011-03-13'
     * </code>
     *
     * @param     mixed $removedAt The value to use as filter.
     *              Values can be integers (unix timestamps), DateTime objects, or strings.
     *              Empty strings are treated as NULL.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByRemovedAt($removedAt = null, $comparison = null)
    {
        if (is_array($removedAt)) {
            $useMinMax = false;
            if (isset($removedAt['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_POST_REMOVED_AT, $removedAt['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($removedAt['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_POST_REMOVED_AT, $removedAt['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_POST_REMOVED_AT, $removedAt, $comparison);
    }

    /**
     * Filter the query on the location_display_value column
     *
     * Example usage:
     * <code>
     * $query->filterByLocationDisplayValue('fooValue');   // WHERE location_display_value = 'fooValue'
     * $query->filterByLocationDisplayValue('%fooValue%', Criteria::LIKE); // WHERE location_display_value LIKE '%fooValue%'
     * </code>
     *
     * @param     string $locationDisplayValue The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByLocationDisplayValue($locationDisplayValue = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($locationDisplayValue)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_LOCATION_DISPLAY_VALUE, $locationDisplayValue, $comparison);
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
     * @see       filterByGeoLocationFromJP()
     *
     * @param     mixed $geoLocationId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByGeoLocationId($geoLocationId = null, $comparison = null)
    {
        if (is_array($geoLocationId)) {
            $useMinMax = false;
            if (isset($geoLocationId['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_GEOLOCATION_ID, $geoLocationId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($geoLocationId['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_GEOLOCATION_ID, $geoLocationId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_GEOLOCATION_ID, $geoLocationId, $comparison);
    }

    /**
     * Filter the query on the duplicates_posting_id column
     *
     * Example usage:
     * <code>
     * $query->filterByDuplicatesJobPostingId(1234); // WHERE duplicates_posting_id = 1234
     * $query->filterByDuplicatesJobPostingId(array(12, 34)); // WHERE duplicates_posting_id IN (12, 34)
     * $query->filterByDuplicatesJobPostingId(array('min' => 12)); // WHERE duplicates_posting_id > 12
     * </code>
     *
     * @see       filterByDuplicateJobPosting()
     *
     * @param     mixed $duplicatesJobPostingId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByDuplicatesJobPostingId($duplicatesJobPostingId = null, $comparison = null)
    {
        if (is_array($duplicatesJobPostingId)) {
            $useMinMax = false;
            if (isset($duplicatesJobPostingId['min'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_DUPLICATES_POSTING_ID, $duplicatesJobPostingId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($duplicatesJobPostingId['max'])) {
                $this->addUsingAlias(JobPostingTableMap::COL_DUPLICATES_POSTING_ID, $duplicatesJobPostingId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_DUPLICATES_POSTING_ID, $duplicatesJobPostingId, $comparison);
    }

    /**
     * Filter the query on the key_company_and_title column
     *
     * Example usage:
     * <code>
     * $query->filterByKeyCompanyAndTitle('fooValue');   // WHERE key_company_and_title = 'fooValue'
     * $query->filterByKeyCompanyAndTitle('%fooValue%', Criteria::LIKE); // WHERE key_company_and_title LIKE '%fooValue%'
     * </code>
     *
     * @param     string $keyCompanyAndTitle The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByKeyCompanyAndTitle($keyCompanyAndTitle = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($keyCompanyAndTitle)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(JobPostingTableMap::COL_KEY_COMPANY_AND_TITLE, $keyCompanyAndTitle, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobSiteRecord object
     *
     * @param \JobScooper\DataAccess\JobSiteRecord|ObjectCollection $jobSiteRecord The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByJobSiteFromJP($jobSiteRecord, $comparison = null)
    {
        if ($jobSiteRecord instanceof \JobScooper\DataAccess\JobSiteRecord) {
            return $this
                ->addUsingAlias(JobPostingTableMap::COL_JOBSITE_KEY, $jobSiteRecord->getJobSiteKey(), $comparison);
        } elseif ($jobSiteRecord instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(JobPostingTableMap::COL_JOBSITE_KEY, $jobSiteRecord->toKeyValue('PrimaryKey', 'JobSiteKey'), $comparison);
        } else {
            throw new PropelException('filterByJobSiteFromJP() only accepts arguments of type \JobScooper\DataAccess\JobSiteRecord or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the JobSiteFromJP relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function joinJobSiteFromJP($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('JobSiteFromJP');

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
            $this->addJoinObject($join, 'JobSiteFromJP');
        }

        return $this;
    }

    /**
     * Use the JobSiteFromJP relation JobSiteRecord object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\JobSiteRecordQuery A secondary query class using the current class as primary query
     */
    public function useJobSiteFromJPQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinJobSiteFromJP($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'JobSiteFromJP', '\JobScooper\DataAccess\JobSiteRecordQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\GeoLocation object
     *
     * @param \JobScooper\DataAccess\GeoLocation|ObjectCollection $geoLocation The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByGeoLocationFromJP($geoLocation, $comparison = null)
    {
        if ($geoLocation instanceof \JobScooper\DataAccess\GeoLocation) {
            return $this
                ->addUsingAlias(JobPostingTableMap::COL_GEOLOCATION_ID, $geoLocation->getGeoLocationId(), $comparison);
        } elseif ($geoLocation instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(JobPostingTableMap::COL_GEOLOCATION_ID, $geoLocation->toKeyValue('PrimaryKey', 'GeoLocationId'), $comparison);
        } else {
            throw new PropelException('filterByGeoLocationFromJP() only accepts arguments of type \JobScooper\DataAccess\GeoLocation or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the GeoLocationFromJP relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function joinGeoLocationFromJP($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('GeoLocationFromJP');

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
            $this->addJoinObject($join, 'GeoLocationFromJP');
        }

        return $this;
    }

    /**
     * Use the GeoLocationFromJP relation GeoLocation object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\GeoLocationQuery A secondary query class using the current class as primary query
     */
    public function useGeoLocationFromJPQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinGeoLocationFromJP($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'GeoLocationFromJP', '\JobScooper\DataAccess\GeoLocationQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobPosting object
     *
     * @param \JobScooper\DataAccess\JobPosting|ObjectCollection $jobPosting The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByDuplicateJobPosting($jobPosting, $comparison = null)
    {
        if ($jobPosting instanceof \JobScooper\DataAccess\JobPosting) {
            return $this
                ->addUsingAlias(JobPostingTableMap::COL_DUPLICATES_POSTING_ID, $jobPosting->getJobPostingId(), $comparison);
        } elseif ($jobPosting instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(JobPostingTableMap::COL_DUPLICATES_POSTING_ID, $jobPosting->toKeyValue('PrimaryKey', 'JobPostingId'), $comparison);
        } else {
            throw new PropelException('filterByDuplicateJobPosting() only accepts arguments of type \JobScooper\DataAccess\JobPosting or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the DuplicateJobPosting relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function joinDuplicateJobPosting($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('DuplicateJobPosting');

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
            $this->addJoinObject($join, 'DuplicateJobPosting');
        }

        return $this;
    }

    /**
     * Use the DuplicateJobPosting relation JobPosting object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\JobPostingQuery A secondary query class using the current class as primary query
     */
    public function useDuplicateJobPostingQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinDuplicateJobPosting($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'DuplicateJobPosting', '\JobScooper\DataAccess\JobPostingQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\JobPosting object
     *
     * @param \JobScooper\DataAccess\JobPosting|ObjectCollection $jobPosting the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByJobPostingRelatedByJobPostingId($jobPosting, $comparison = null)
    {
        if ($jobPosting instanceof \JobScooper\DataAccess\JobPosting) {
            return $this
                ->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $jobPosting->getDuplicatesJobPostingId(), $comparison);
        } elseif ($jobPosting instanceof ObjectCollection) {
            return $this
                ->useJobPostingRelatedByJobPostingIdQuery()
                ->filterByPrimaryKeys($jobPosting->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByJobPostingRelatedByJobPostingId() only accepts arguments of type \JobScooper\DataAccess\JobPosting or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the JobPostingRelatedByJobPostingId relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function joinJobPostingRelatedByJobPostingId($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('JobPostingRelatedByJobPostingId');

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
            $this->addJoinObject($join, 'JobPostingRelatedByJobPostingId');
        }

        return $this;
    }

    /**
     * Use the JobPostingRelatedByJobPostingId relation JobPosting object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\JobPostingQuery A secondary query class using the current class as primary query
     */
    public function useJobPostingRelatedByJobPostingIdQuery($relationAlias = null, $joinType = Criteria::LEFT_JOIN)
    {
        return $this
            ->joinJobPostingRelatedByJobPostingId($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'JobPostingRelatedByJobPostingId', '\JobScooper\DataAccess\JobPostingQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\UserJobMatch object
     *
     * @param \JobScooper\DataAccess\UserJobMatch|ObjectCollection $userJobMatch the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByUserJobMatch($userJobMatch, $comparison = null)
    {
        if ($userJobMatch instanceof \JobScooper\DataAccess\UserJobMatch) {
            return $this
                ->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $userJobMatch->getJobPostingId(), $comparison);
        } elseif ($userJobMatch instanceof ObjectCollection) {
            return $this
                ->useUserJobMatchQuery()
                ->filterByPrimaryKeys($userJobMatch->getPrimaryKeys())
                ->endUse();
        } else {
            throw new PropelException('filterByUserJobMatch() only accepts arguments of type \JobScooper\DataAccess\UserJobMatch or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserJobMatch relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function joinUserJobMatch($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserJobMatch');

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
            $this->addJoinObject($join, 'UserJobMatch');
        }

        return $this;
    }

    /**
     * Use the UserJobMatch relation UserJobMatch object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserJobMatchQuery A secondary query class using the current class as primary query
     */
    public function useUserJobMatchQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserJobMatch($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserJobMatch', '\JobScooper\DataAccess\UserJobMatchQuery');
    }

    /**
     * Filter the query by a related User object
     * using the user_job_match table as cross reference
     *
     * @param User $user the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildJobPostingQuery The current query, for fluid interface
     */
    public function filterByUserFromUJM($user, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useUserJobMatchQuery()
            ->filterByUserFromUJM($user, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildJobPosting $jobPosting Object to remove from the list of results
     *
     * @return $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function prune($jobPosting = null)
    {
        if ($jobPosting) {
            $this->addUsingAlias(JobPostingTableMap::COL_JOBPOSTING_ID, $jobPosting->getJobPostingId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the jobposting table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(JobPostingTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            JobPostingTableMap::clearInstancePool();
            JobPostingTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(JobPostingTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(JobPostingTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            JobPostingTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            JobPostingTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // timestampable behavior

    /**
     * Filter by the latest updated
     *
     * @param      int $nbDays Maximum age of the latest update in days
     *
     * @return     $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function recentlyUpdated($nbDays = 7)
    {
        return $this->addUsingAlias(JobPostingTableMap::COL_LAST_UPDATED_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by update date desc
     *
     * @return     $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function lastUpdatedFirst()
    {
        return $this->addDescendingOrderByColumn(JobPostingTableMap::COL_LAST_UPDATED_AT);
    }

    /**
     * Order by update date asc
     *
     * @return     $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function firstUpdatedFirst()
    {
        return $this->addAscendingOrderByColumn(JobPostingTableMap::COL_LAST_UPDATED_AT);
    }

    /**
     * Order by create date desc
     *
     * @return     $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function lastCreatedFirst()
    {
        return $this->addDescendingOrderByColumn(JobPostingTableMap::COL_FIRST_SEEN_AT);
    }

    /**
     * Filter by the latest created
     *
     * @param      int $nbDays Maximum age of in days
     *
     * @return     $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function recentlyCreated($nbDays = 7)
    {
        return $this->addUsingAlias(JobPostingTableMap::COL_FIRST_SEEN_AT, time() - $nbDays * 24 * 60 * 60, Criteria::GREATER_EQUAL);
    }

    /**
     * Order by create date asc
     *
     * @return     $this|ChildJobPostingQuery The current query, for fluid interface
     */
    public function firstCreatedFirst()
    {
        return $this->addAscendingOrderByColumn(JobPostingTableMap::COL_FIRST_SEEN_AT);
    }

} // JobPostingQuery
