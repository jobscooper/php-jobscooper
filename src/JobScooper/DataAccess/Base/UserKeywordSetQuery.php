<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\UserKeywordSet as ChildUserKeywordSet;
use JobScooper\DataAccess\UserKeywordSetQuery as ChildUserKeywordSetQuery;
use JobScooper\DataAccess\Map\UserKeywordSetTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'user_keyword_set' table.
 *
 *
 *
 * @method     ChildUserKeywordSetQuery orderByUserId($order = Criteria::ASC) Order by the user_id column
 * @method     ChildUserKeywordSetQuery orderByUserKeywordSetKey($order = Criteria::ASC) Order by the user_keyword_set_key column
 * @method     ChildUserKeywordSetQuery orderBySearchKeyFromConfig($order = Criteria::ASC) Order by the search_key_from_config column
 * @method     ChildUserKeywordSetQuery orderByKeywords($order = Criteria::ASC) Order by the keywords column
 * @method     ChildUserKeywordSetQuery orderByKeywordTokens($order = Criteria::ASC) Order by the keyword_tokens column
 *
 * @method     ChildUserKeywordSetQuery groupByUserId() Group by the user_id column
 * @method     ChildUserKeywordSetQuery groupByUserKeywordSetKey() Group by the user_keyword_set_key column
 * @method     ChildUserKeywordSetQuery groupBySearchKeyFromConfig() Group by the search_key_from_config column
 * @method     ChildUserKeywordSetQuery groupByKeywords() Group by the keywords column
 * @method     ChildUserKeywordSetQuery groupByKeywordTokens() Group by the keyword_tokens column
 *
 * @method     ChildUserKeywordSetQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildUserKeywordSetQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildUserKeywordSetQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildUserKeywordSetQuery leftJoinWith($relation) Adds a LEFT JOIN clause and with to the query
 * @method     ChildUserKeywordSetQuery rightJoinWith($relation) Adds a RIGHT JOIN clause and with to the query
 * @method     ChildUserKeywordSetQuery innerJoinWith($relation) Adds a INNER JOIN clause and with to the query
 *
 * @method     ChildUserKeywordSetQuery leftJoinUserFromUKS($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserFromUKS relation
 * @method     ChildUserKeywordSetQuery rightJoinUserFromUKS($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserFromUKS relation
 * @method     ChildUserKeywordSetQuery innerJoinUserFromUKS($relationAlias = null) Adds a INNER JOIN clause to the query using the UserFromUKS relation
 *
 * @method     ChildUserKeywordSetQuery joinWithUserFromUKS($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserFromUKS relation
 *
 * @method     ChildUserKeywordSetQuery leftJoinWithUserFromUKS() Adds a LEFT JOIN clause and with to the query using the UserFromUKS relation
 * @method     ChildUserKeywordSetQuery rightJoinWithUserFromUKS() Adds a RIGHT JOIN clause and with to the query using the UserFromUKS relation
 * @method     ChildUserKeywordSetQuery innerJoinWithUserFromUKS() Adds a INNER JOIN clause and with to the query using the UserFromUKS relation
 *
 * @method     ChildUserKeywordSetQuery leftJoinUserSearch($relationAlias = null) Adds a LEFT JOIN clause to the query using the UserSearch relation
 * @method     ChildUserKeywordSetQuery rightJoinUserSearch($relationAlias = null) Adds a RIGHT JOIN clause to the query using the UserSearch relation
 * @method     ChildUserKeywordSetQuery innerJoinUserSearch($relationAlias = null) Adds a INNER JOIN clause to the query using the UserSearch relation
 *
 * @method     ChildUserKeywordSetQuery joinWithUserSearch($joinType = Criteria::INNER_JOIN) Adds a join clause and with to the query using the UserSearch relation
 *
 * @method     ChildUserKeywordSetQuery leftJoinWithUserSearch() Adds a LEFT JOIN clause and with to the query using the UserSearch relation
 * @method     ChildUserKeywordSetQuery rightJoinWithUserSearch() Adds a RIGHT JOIN clause and with to the query using the UserSearch relation
 * @method     ChildUserKeywordSetQuery innerJoinWithUserSearch() Adds a INNER JOIN clause and with to the query using the UserSearch relation
 *
 * @method     \JobScooper\DataAccess\UserQuery|\JobScooper\DataAccess\UserSearchQuery endUse() Finalizes a secondary criteria and merges it with its primary Criteria
 *
 * @method     ChildUserKeywordSet findOne(ConnectionInterface $con = null) Return the first ChildUserKeywordSet matching the query
 * @method     ChildUserKeywordSet findOneOrCreate(ConnectionInterface $con = null) Return the first ChildUserKeywordSet matching the query, or a new ChildUserKeywordSet object populated from the query conditions when no match is found
 *
 * @method     ChildUserKeywordSet findOneByUserId(int $user_id) Return the first ChildUserKeywordSet filtered by the user_id column
 * @method     ChildUserKeywordSet findOneByUserKeywordSetKey(string $user_keyword_set_key) Return the first ChildUserKeywordSet filtered by the user_keyword_set_key column
 * @method     ChildUserKeywordSet findOneBySearchKeyFromConfig(string $search_key_from_config) Return the first ChildUserKeywordSet filtered by the search_key_from_config column
 * @method     ChildUserKeywordSet findOneByKeywords(array $keywords) Return the first ChildUserKeywordSet filtered by the keywords column
 * @method     ChildUserKeywordSet findOneByKeywordTokens(array $keyword_tokens) Return the first ChildUserKeywordSet filtered by the keyword_tokens column *

 * @method     ChildUserKeywordSet requirePk($key, ConnectionInterface $con = null) Return the ChildUserKeywordSet by primary key and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserKeywordSet requireOne(ConnectionInterface $con = null) Return the first ChildUserKeywordSet matching the query and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserKeywordSet requireOneByUserId(int $user_id) Return the first ChildUserKeywordSet filtered by the user_id column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserKeywordSet requireOneByUserKeywordSetKey(string $user_keyword_set_key) Return the first ChildUserKeywordSet filtered by the user_keyword_set_key column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserKeywordSet requireOneBySearchKeyFromConfig(string $search_key_from_config) Return the first ChildUserKeywordSet filtered by the search_key_from_config column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserKeywordSet requireOneByKeywords(array $keywords) Return the first ChildUserKeywordSet filtered by the keywords column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 * @method     ChildUserKeywordSet requireOneByKeywordTokens(array $keyword_tokens) Return the first ChildUserKeywordSet filtered by the keyword_tokens column and throws \Propel\Runtime\Exception\EntityNotFoundException when not found
 *
 * @method     ChildUserKeywordSet[]|ObjectCollection find(ConnectionInterface $con = null) Return ChildUserKeywordSet objects based on current ModelCriteria
 * @method     ChildUserKeywordSet[]|ObjectCollection findByUserId(int $user_id) Return ChildUserKeywordSet objects filtered by the user_id column
 * @method     ChildUserKeywordSet[]|ObjectCollection findByUserKeywordSetKey(string $user_keyword_set_key) Return ChildUserKeywordSet objects filtered by the user_keyword_set_key column
 * @method     ChildUserKeywordSet[]|ObjectCollection findBySearchKeyFromConfig(string $search_key_from_config) Return ChildUserKeywordSet objects filtered by the search_key_from_config column
 * @method     ChildUserKeywordSet[]|ObjectCollection findByKeywords(array $keywords) Return ChildUserKeywordSet objects filtered by the keywords column
 * @method     ChildUserKeywordSet[]|ObjectCollection findByKeywordTokens(array $keyword_tokens) Return ChildUserKeywordSet objects filtered by the keyword_tokens column
 * @method     ChildUserKeywordSet[]|\Propel\Runtime\Util\PropelModelPager paginate($page = 1, $maxPerPage = 10, ConnectionInterface $con = null) Issue a SELECT query based on the current ModelCriteria and uses a page and a maximum number of results per page to compute an offset and a limit
 *
 */
abstract class UserKeywordSetQuery extends ModelCriteria
{
    protected $entityNotFoundExceptionClass = '\\Propel\\Runtime\\Exception\\EntityNotFoundException';

    /**
     * Initializes internal state of \JobScooper\DataAccess\Base\UserKeywordSetQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'default', $modelName = '\\JobScooper\\DataAccess\\UserKeywordSet', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildUserKeywordSetQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildUserKeywordSetQuery
     */
    public static function create($modelAlias = null, Criteria $criteria = null)
    {
        if ($criteria instanceof ChildUserKeywordSetQuery) {
            return $criteria;
        }
        $query = new ChildUserKeywordSetQuery();
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
     * @param array[$user_id, $user_keyword_set_key] $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildUserKeywordSet|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, ConnectionInterface $con = null)
    {
        if ($key === null) {
            return null;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(UserKeywordSetTableMap::DATABASE_NAME);
        }

        $this->basePreSelect($con);

        if (
            $this->formatter || $this->modelAlias || $this->with || $this->select
            || $this->selectColumns || $this->asColumns || $this->selectModifiers
            || $this->map || $this->having || $this->joins
        ) {
            return $this->findPkComplex($key, $con);
        }

        if ((null !== ($obj = UserKeywordSetTableMap::getInstanceFromPool(serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]))))) {
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
     * @return ChildUserKeywordSet A model object, or null if the key is not found
     */
    protected function findPkSimple($key, ConnectionInterface $con)
    {
        $sql = 'SELECT user_id, user_keyword_set_key, search_key_from_config, keywords, keyword_tokens FROM user_keyword_set WHERE user_id = :p0 AND user_keyword_set_key = :p1';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key[0], PDO::PARAM_INT);
            $stmt->bindValue(':p1', $key[1], PDO::PARAM_STR);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            /** @var ChildUserKeywordSet $obj */
            $obj = new ChildUserKeywordSet();
            $obj->hydrate($row);
            UserKeywordSetTableMap::addInstanceToPool($obj, serialize([(null === $key[0] || is_scalar($key[0]) || is_callable([$key[0], '__toString']) ? (string) $key[0] : $key[0]), (null === $key[1] || is_scalar($key[1]) || is_callable([$key[1], '__toString']) ? (string) $key[1] : $key[1])]));
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
     * @return ChildUserKeywordSet|array|mixed the result, formatted by the current formatter
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
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {
        $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $key[0], Criteria::EQUAL);
        $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_KEYWORD_SET_KEY, $key[1], Criteria::EQUAL);

        return $this;
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {
        if (empty($keys)) {
            return $this->add(null, '1<>1', Criteria::CUSTOM);
        }
        foreach ($keys as $key) {
            $cton0 = $this->getNewCriterion(UserKeywordSetTableMap::COL_USER_ID, $key[0], Criteria::EQUAL);
            $cton1 = $this->getNewCriterion(UserKeywordSetTableMap::COL_USER_KEYWORD_SET_KEY, $key[1], Criteria::EQUAL);
            $cton0->addAnd($cton1);
            $this->addOr($cton0);
        }

        return $this;
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
     * @see       filterByUserFromUKS()
     *
     * @param     mixed $userId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByUserId($userId = null, $comparison = null)
    {
        if (is_array($userId)) {
            $useMinMax = false;
            if (isset($userId['min'])) {
                $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $userId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($userId['max'])) {
                $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $userId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $userId, $comparison);
    }

    /**
     * Filter the query on the user_keyword_set_key column
     *
     * Example usage:
     * <code>
     * $query->filterByUserKeywordSetKey('fooValue');   // WHERE user_keyword_set_key = 'fooValue'
     * $query->filterByUserKeywordSetKey('%fooValue%', Criteria::LIKE); // WHERE user_keyword_set_key LIKE '%fooValue%'
     * </code>
     *
     * @param     string $userKeywordSetKey The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByUserKeywordSetKey($userKeywordSetKey = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($userKeywordSetKey)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_KEYWORD_SET_KEY, $userKeywordSetKey, $comparison);
    }

    /**
     * Filter the query on the search_key_from_config column
     *
     * Example usage:
     * <code>
     * $query->filterBySearchKeyFromConfig('fooValue');   // WHERE search_key_from_config = 'fooValue'
     * $query->filterBySearchKeyFromConfig('%fooValue%', Criteria::LIKE); // WHERE search_key_from_config LIKE '%fooValue%'
     * </code>
     *
     * @param     string $searchKeyFromConfig The value to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterBySearchKeyFromConfig($searchKeyFromConfig = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($searchKeyFromConfig)) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_SEARCH_KEY_FROM_CONFIG, $searchKeyFromConfig, $comparison);
    }

    /**
     * Filter the query on the keywords column
     *
     * @param     array $keywords The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByKeywords($keywords = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserKeywordSetTableMap::COL_KEYWORDS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($keywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($keywords as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($keywords as $value) {
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

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_KEYWORDS, $keywords, $comparison);
    }

    /**
     * Filter the query on the keywords column
     * @param     mixed $keywords The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByKeyword($keywords = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($keywords)) {
                $keywords = '%| ' . $keywords . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $keywords = '%| ' . $keywords . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserKeywordSetTableMap::COL_KEYWORDS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $keywords, $comparison);
            } else {
                $this->addAnd($key, $keywords, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_KEYWORDS, $keywords, $comparison);
    }

    /**
     * Filter the query on the keyword_tokens column
     *
     * @param     array $keywordTokens The values to use as filter.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByKeywordTokens($keywordTokens = null, $comparison = null)
    {
        $key = $this->getAliasedColName(UserKeywordSetTableMap::COL_KEYWORD_TOKENS);
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            foreach ($keywordTokens as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addAnd($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_SOME) {
            foreach ($keywordTokens as $value) {
                $value = '%| ' . $value . ' |%';
                if ($this->containsKey($key)) {
                    $this->addOr($key, $value, Criteria::LIKE);
                } else {
                    $this->add($key, $value, Criteria::LIKE);
                }
            }

            return $this;
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            foreach ($keywordTokens as $value) {
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

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_KEYWORD_TOKENS, $keywordTokens, $comparison);
    }

    /**
     * Filter the query on the keyword_tokens column
     * @param     mixed $keywordTokens The value to use as filter
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::CONTAINS_ALL
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByKeywordToken($keywordTokens = null, $comparison = null)
    {
        if (null === $comparison || $comparison == Criteria::CONTAINS_ALL) {
            if (is_scalar($keywordTokens)) {
                $keywordTokens = '%| ' . $keywordTokens . ' |%';
                $comparison = Criteria::LIKE;
            }
        } elseif ($comparison == Criteria::CONTAINS_NONE) {
            $keywordTokens = '%| ' . $keywordTokens . ' |%';
            $comparison = Criteria::NOT_LIKE;
            $key = $this->getAliasedColName(UserKeywordSetTableMap::COL_KEYWORD_TOKENS);
            if ($this->containsKey($key)) {
                $this->addAnd($key, $keywordTokens, $comparison);
            } else {
                $this->addAnd($key, $keywordTokens, $comparison);
            }
            $this->addOr($key, null, Criteria::ISNULL);

            return $this;
        }

        return $this->addUsingAlias(UserKeywordSetTableMap::COL_KEYWORD_TOKENS, $keywordTokens, $comparison);
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\User object
     *
     * @param \JobScooper\DataAccess\User|ObjectCollection $user The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @throws \Propel\Runtime\Exception\PropelException
     *
     * @return ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByUserFromUKS($user, $comparison = null)
    {
        if ($user instanceof \JobScooper\DataAccess\User) {
            return $this
                ->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $user->getUserId(), $comparison);
        } elseif ($user instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $user->toKeyValue('PrimaryKey', 'UserId'), $comparison);
        } else {
            throw new PropelException('filterByUserFromUKS() only accepts arguments of type \JobScooper\DataAccess\User or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserFromUKS relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function joinUserFromUKS($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserFromUKS');

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
            $this->addJoinObject($join, 'UserFromUKS');
        }

        return $this;
    }

    /**
     * Use the UserFromUKS relation User object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserQuery A secondary query class using the current class as primary query
     */
    public function useUserFromUKSQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserFromUKS($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserFromUKS', '\JobScooper\DataAccess\UserQuery');
    }

    /**
     * Filter the query by a related \JobScooper\DataAccess\UserSearch object
     *
     * @param \JobScooper\DataAccess\UserSearch|ObjectCollection $userSearch the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByUserSearch($userSearch, $comparison = null)
    {
        if ($userSearch instanceof \JobScooper\DataAccess\UserSearch) {
            return $this
                ->addUsingAlias(UserKeywordSetTableMap::COL_USER_KEYWORD_SET_KEY, $userSearch->getUserKeywordSetKey(), $comparison)
                ->addUsingAlias(UserKeywordSetTableMap::COL_USER_ID, $userSearch->getUserId(), $comparison);
        } else {
            throw new PropelException('filterByUserSearch() only accepts arguments of type \JobScooper\DataAccess\UserSearch');
        }
    }

    /**
     * Adds a JOIN clause to the query using the UserSearch relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function joinUserSearch($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('UserSearch');

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
            $this->addJoinObject($join, 'UserSearch');
        }

        return $this;
    }

    /**
     * Use the UserSearch relation UserSearch object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return \JobScooper\DataAccess\UserSearchQuery A secondary query class using the current class as primary query
     */
    public function useUserSearchQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinUserSearch($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'UserSearch', '\JobScooper\DataAccess\UserSearchQuery');
    }

    /**
     * Filter the query by a related User object
     * using the user_search table as cross reference
     *
     * @param User $user the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByUserFromUS($user, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useUserSearchQuery()
            ->filterByUserFromUS($user, $comparison)
            ->endUse();
    }

    /**
     * Filter the query by a related GeoLocation object
     * using the user_search table as cross reference
     *
     * @param GeoLocation $geoLocation the related object to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterByGeoLocationFromUS($geoLocation, $comparison = Criteria::EQUAL)
    {
        return $this
            ->useUserSearchQuery()
            ->filterByGeoLocationFromUS($geoLocation, $comparison)
            ->endUse();
    }

    /**
     * Exclude object from result
     *
     * @param   ChildUserKeywordSet $userKeywordSet Object to remove from the list of results
     *
     * @return $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function prune($userKeywordSet = null)
    {
        if ($userKeywordSet) {
            $this->addCond('pruneCond0', $this->getAliasedColName(UserKeywordSetTableMap::COL_USER_ID), $userKeywordSet->getUserId(), Criteria::NOT_EQUAL);
            $this->addCond('pruneCond1', $this->getAliasedColName(UserKeywordSetTableMap::COL_USER_KEYWORD_SET_KEY), $userKeywordSet->getUserKeywordSetKey(), Criteria::NOT_EQUAL);
            $this->combine(array('pruneCond0', 'pruneCond1'), Criteria::LOGICAL_OR);
        }

        return $this;
    }

    /**
     * Deletes all rows from the user_keyword_set table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserKeywordSetTableMap::DATABASE_NAME);
        }

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con) {
            $affectedRows = 0; // initialize var to track total num of affected rows
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            UserKeywordSetTableMap::clearInstancePool();
            UserKeywordSetTableMap::clearRelatedInstancePool();

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
            $con = Propel::getServiceContainer()->getWriteConnection(UserKeywordSetTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(UserKeywordSetTableMap::DATABASE_NAME);

        // use transaction because $criteria could contain info
        // for more than one table or we could emulating ON DELETE CASCADE, etc.
        return $con->transaction(function () use ($con, $criteria) {
            $affectedRows = 0; // initialize var to track total num of affected rows

            UserKeywordSetTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            UserKeywordSetTableMap::clearRelatedInstancePool();

            return $affectedRows;
        });
    }

    // sluggable behavior

    /**
     * Filter the query on the slug column
     *
     * @param     string $slug The value to use as filter.
     *
     * @return    $this|ChildUserKeywordSetQuery The current query, for fluid interface
     */
    public function filterBySlug($slug)
    {
        return $this->addUsingAlias(UserKeywordSetTableMap::COL_USER_KEYWORD_SET_KEY, $slug, Criteria::EQUAL);
    }

    /**
     * Find one object based on its slug
     *
     * @param     string $slug The value to use as filter.
     * @param     ConnectionInterface $con The optional connection object
     *
     * @return    ChildUserKeywordSet the result, formatted by the current formatter
     */
    public function findOneBySlug($slug, $con = null)
    {
        return $this->filterBySlug($slug)->findOne($con);
    }

} // UserKeywordSetQuery