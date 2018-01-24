<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\User as ChildUser;
use JobScooper\DataAccess\UserQuery as ChildUserQuery;
use JobScooper\DataAccess\Map\UserTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'user' table.
 *
 *
 *
 * @method     ChildUserQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildUserQuery orderByUserSlug($order = Criteria::ASC) Order by the user_slug column
 * @method     ChildUserQuery orderByEmailAddress($order = Criteria::ASC) Order by the email_address column
 * @method     ChildUserQuery orderByName($order = Criteria::ASC) Order by the name column
 * @method     ChildUserQuery orderBySearchKeywords($order = Criteria::ASC) Order by the search_keywords column
 * @method     ChildUserQuery orderBySearchLocations($order = Criteria::ASC) Order by the search_locations column
 * @method     ChildUserQuery orderByInputFilesJson($order = Criteria::ASC) Order by the input_files_json column
 *
 * @method     ChildUserQuery groupByUserId() Group by the user_id column
 * @method     ChildUserQuery groupByUserSlug() Group by the user_slug column
 * @method     ChildUserQuery groupByEmailAddress() Group by the email_address column
 * @method     ChildUserQuery groupByName() Group by the name column
 * @method     ChildUserQuery groupBySearchKeywords() Group by the search_keywords column
 * @method     ChildUserQuery groupBySearchLocations() Group by the search_locations column
 * @method     ChildUserQuery groupByInputFilesJson() Group by the input_files_json column
 *
 * @method     ChildUserQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserQuery leftJoinUserSearchPair($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserSearchPair relation
 * @method     ChildUserQuery rightJoinUserSearchPair($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserSearchPair relation
 * @method     ChildUserQuery innerJoinUserSearchPair($relationAlias = null) Adds a INNER JOIN clause to the query using the UserSearchPair relation
 *
 * @method     ChildUserQuery joinWithUserSearchPair($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserSearchPair relation
 *
 * @method     ChildUserQuery leftJoinWithUserSearchPair() Adds a LEFT JOIN clause and with to the query using the UserSearchPair relation
 * @method     ChildUserQuery rightJoinWithUserSearchPair() Adds a RIGHT JOIN clause and with to the query using the UserSearchPair relation
 * @method     ChildUserQuery innerJoinWithUserSearchPair() Adds a INNER JOIN clause and with to the query using the UserSearchPair relation
 *
 * @method     ChildUserQuery leftJoinUserJobMatch($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserJobMatch relation
 * @method     ChildUserQuery rightJoinUserJobMatch($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserJobMatch relation
 * @method     ChildUserQuery innerJoinUserJobMatch($relationAlias = null) Adds a INNER JOIN clause to the query using the UserJobMatch relation
 *
 * @method     ChildUserQuery joinWithUserJobMatch($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserJobMatch relation
 *
 * @method     ChildUserQuery leftJoinWithUserJobMatch() Adds a LEFT JOIN clause and with to the query using the UserJobMatch relation
 * @method     ChildUserQuery rightJoinWithUserJobMatch() Adds a RIGHT JOIN clause and with to the query using the UserJobMatch relation
 * @method     ChildUserQuery innerJoinWithUserJobMatch() Adds a INNER JOIN clause and with to the query using the UserJobMatch relation
 *
 * @method     \JobScooper\DataAccess\UserSearchPairQuery|\JobScooper\DataAccess\UserJobMatchQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUser findOne(ConnectionInterface $con = null) Return the first ChildUser matching the query
 * @method     ChildUser findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUser matching the query, or a new ChildUser object populated from the query conditions when no match is found
 *
 * @method     ChildUser findOneByUserId(int $user_id) Return the first ChildUser filtered by the user_id column
 * @method     ChildUser findOneByUserSlug(string $user_slug) Return the first ChildUser filtered by the user_slug column
 * @method     ChildUser findOneByEmailAddress(string $email_address) Return the first ChildUser filtered by the email_address column
 * @method     ChildUser findOneByName(string $name) Return the first ChildUser filtered by the name column
 * @method     ChildUser findOneBySearchKeywords(array $search_keywords) Return the first ChildUser filtered by the search_keywords column
 * @method     ChildUser findOneBySearchLocations(array $search_locations) Return the first ChildUser filtered by the search_locations column
 * @method     ChildUser findOneByInputFilesJson(string $input_files_json) Return the first ChildUser filtered by the input_files_json column *

 * @method     ChildUser requirePk($key, ConnectionInterface $con = null) Return the ChildUser by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOne(ConnectionInterface $con = null) Return the first ChildUser matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser requireOneByUserId(int $user_id) Return the first ChildUser filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByUserSlug(string $user_slug) Return the first ChildUser filtered by the user_slug column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByEmailAddress(string $email_address) Return the first ChildUser filtered by the email_address column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByName(string $name) Return the first ChildUser filtered by the name column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneBySearchKeywords(array $search_keywords) Return the first ChildUser filtered by the search_keywords column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneBySearchLocations(array $search_locations) Return the first ChildUser filtered by the search_locations column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUser requireOneByInputFilesJson(string $input_files_json) Return the first ChildUser filtered by the input_files_json column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUser[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUser objects based on current ModelCriteria
 * @method     ChildUser[]|ObjectCollection findByUserId(int $user_id) Return ChildUser objects filtered by the user_id column
 * @method     ChildUser[]|ObjectCollection findByUserSlug(string $user_slug) Return ChildUser objects filtered by the user_slug column
 * @method     ChildUser[]|ObjectCollection findByEmailAddress(string $email_address) Return ChildUser objects filtered by the email_address column
 * @method     ChildUser[]|ObjectCollection findByName(string $name) Return ChildUser objects filtered by the name column
 * @method     ChildUser[]|ObjectCollection findBySearchKeywords(array $search_keywords) Return ChildUser objects filtered by the search_keywords column
 * @method     ChildUser[]|ObjectCollection findBySearchLocations(array $search_locations) Return ChildUser objects filtered by the search_locations column
 * @method     ChildUser[]|ObjectCollection findByInputFilesJson(string $input_files_json) Return ChildUser objects filtered by the input_files_json column
 * @method     ChildUser[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\UserQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\User', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserQuery) {
            return $criteria;
        }
        $query = new ChildUserQuery();
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserTableMap::getInstanceFromPool(null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key)))) {
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
     * @return ChildUser A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_id, user_slug, email_address, name, search_keywords, search_locations, input_files_json FROM user WHERE user_id = :p0';
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
            /** @var ChildUser $obj */
            $obj = new ChildUser();
            $obj->hydrate($row);
            UserTableMap::addInstanceToPool($obj, null === $key || is_scalar($key) || is_callable([$key, '__toString']) ? (string) $key : $key);
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
     * @return ChildUser|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(UserTableMap::COL_USER_ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(UserTableMap::COL_USER_ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the user_id column
     *
     * Example usage:
     * <code>
     * $query->filterByUserId(1234); // WHERE user_id = 1234
     * $query->filterByUserId(array(12, 34)); // WHERE user_id IN (12, 34)
     * $query->filterByUserId(array('min' => 12)); // WHERE user_id > 12
     * </code>
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(UserTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(UserTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_USER_ID, $userId, $comparison);
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
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByUserSlug($userSlug = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($userSlug)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_USER_SLUG, $userSlug, $comparison);
    }

    /**
     * Filter the query on the email_address column
     *
     * Example usage:
     * <code>
     * $query->filterByEmailAddress('fooValue');   // WHERE email_address = 'fooValue'
     * $query->filterByEmailAddress('%fooValue%', Criteria::LIKE); // WHERE email_address LIKE '%fooValue%'
     * </code>
     *
     * @param     string $emailAddress The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByEmailAddress($emailAddress = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($emailAddress)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_EMAIL_ADDRESS, $emailAddress, $comparison);
    }

    /**
     * Filter the query on the name column
     *
     * Example usage:
     * <code>
     * $query->filterByName('fooValue');   // WHERE name = 'fooValue'
     * $query->filterByName('%fooValue%', Criteria::LIKE); // WHERE name LIKE '%fooValue%'
     * </code>
     *
     * @param     string $name The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByName($name = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($name)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_NAME, $name, $comparison);
    }

    /**
     * Filter the query on the search_keywords column
     *
     * @param     array $searchKeywords The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterBySearchKeywords($searchKeywords = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserTableMap::COL_SEARCH_KEYWORDS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($searchKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($searchKeywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($searchKeywords as $value) {
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

        return $this->addUsingAlias(UserTableMap::COL_SEARCH_KEYWORDS, $searchKeywords, $comparison);
    }

    /**
     * Filter the query on the search_keywords column
     * @param     mixed $searchKeywords The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterBySearchKeyword($searchKeywords = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($searchKeywords)) {
                $searchKeywords = '%| ' . $searchKeywords . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $searchKeywords = '%| ' . $searchKeywords . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserTableMap::COL_SEARCH_KEYWORDS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $searchKeywords, $comparison);
            } else {
                $this->addAnd($key, $searchKeywords, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserTableMap::COL_SEARCH_KEYWORDS, $searchKeywords, $comparison);
    }

    /**
     * Filter the query on the search_locations column
     *
     * @param     array $searchLocations The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterBySearchLocations($searchLocations = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserTableMap::COL_SEARCH_LOCATIONS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($searchLocations as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($searchLocations as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($searchLocations as $value) {
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

        return $this->addUsingAlias(UserTableMap::COL_SEARCH_LOCATIONS, $searchLocations, $comparison);
    }

    /**
     * Filter the query on the search_locations column
     * @param     mixed $searchLocations The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterBySearchLocation($searchLocations = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($searchLocations)) {
                $searchLocations = '%| ' . $searchLocations . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $searchLocations = '%| ' . $searchLocations . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserTableMap::COL_SEARCH_LOCATIONS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $searchLocations, $comparison);
            } else {
                $this->addAnd($key, $searchLocations, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserTableMap::COL_SEARCH_LOCATIONS, $searchLocations, $comparison);
    }

    /**
     * Filter the query on the input_files_json column
     *
     * Example usage:
     * <code>
     * $query->filterByInputFilesJson('fooValue');   // WHERE input_files_json = 'fooValue'
     * $query->filterByInputFilesJson('%fooValue%', Criteria::LIKE); // WHERE input_files_json LIKE '%fooValue%'
     * </code>
     *
     * @param     string $inputFilesJson The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterByInputFilesJson($inputFilesJson = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($inputFilesJson)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserTableMap::COL_INPUT_FILES_JSON, $inputFilesJson, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\UserSearchPair object
     *
     * @param \JobScooper\DataAccess\UserSearchPair|ObjectCollection $userSearchPair the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserQuery The current query, for fluid interface
     */
    public function filterByUserSearchPair($userSearchPair, $comparison = null)
    {
        if ($userSearchPair instanceof \JobScooper\DataAccess\UserSearchPair) {
            return $this
                ->addUsingAlias(UserTableMap::COL_USER_ID, $userSearchPair->getUserId(), $comparison);
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
     * @return $this|ChildUserQuery The current query, for fluid interface
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
     * Filter the query by a related \JobScooper\DataAccess\UserJobMatch object
     *
     * @param \JobScooper\DataAccess\UserJobMatch|ObjectCollection $userJobMatch the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserQuery The current query, for fluid interface
     */
    public function filterByUserJobMatch($userJobMatch, $comparison = null)
    {
        if ($userJobMatch instanceof \JobScooper\DataAccess\UserJobMatch) {
            return $this
                ->addUsingAlias(UserTableMap::COL_USER_ID, $userJobMatch->getUserId(), $comparison);
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
     * @return $this|ChildUserQuery The current query, for fluid interface
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
     * Filter the query by a related JobPosting object
     * using the user_job_match table as cross reference
     *
     * @param JobPosting $jobPosting the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserQuery The current query, for fluid interface
     */
    public function filterByJobPostingFromUJM($jobPosting, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useUserJobMatchQuery()
            ->filterByJobPostingFromUJM($jobPosting, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUser $user Object to remove from the list of results
     *
     * @return $this|ChildUserQuery The current query, for fluid interface
     */
    public function prune($user = null)
    {
        if ($user) {
            $this->addUsingAlias(UserTableMap::COL_USER_ID, $user->getUserId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserTableMap::clearInstancePool();
            UserTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // sluggable behavior

    /**
     * Filter the query on the slug column
     *
     * @param     string $slug The value to use as filter.
     *
     * @return    $this|ChildUserQuery The current query, for fluid interface
     */
    public function filterBySlug($slug)
    {
        return $this->addUsingAlias(UserTableMap::COL_USER_SLUG, $slug, Criteria::EQUAL);
    }

    /**
     * Find one object based on its slug
     *
     * @param     string $slug The value to use as filter.
     * @param     ConnectionInterface $con The optional connection object
     *
     * @return    ChildUser the result, formatted by the current formatter
     */
    public function findOneBySlug($slug, $con = null)
    {
        return $this->filterBySlug($slug)->findOne($con);
    }

} // UserQuery
