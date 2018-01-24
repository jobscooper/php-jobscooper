<?php

namespace JobScooper\DataAccess\Map;

use JobScooper\DataAccess\UserSearchSiteRun;
use JobScooper\DataAccess\UserSearchSiteRunQuery;
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
 * This class defines the structure of the 'user_search_site_run' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class UserSearchSiteRunTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'JobScooper.DataAccess.Map.UserSearchSiteRunTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'user_search_site_run';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\JobScooper\\DataAccess\\UserSearchSiteRun';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'JobScooper.DataAccess.UserSearchSiteRun';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 11;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 11;

    /**
     * the column name for the user_search_site_run_id field
     */
    const COL_USER_SEARCH_SITE_RUN_ID = 'user_search_site_run.user_search_site_run_id';

    /**
     * the column name for the user_search_pair_id field
     */
    const COL_USER_SEARCH_PAIR_ID = 'user_search_site_run.user_search_pair_id';

    /**
     * the column name for the jobsite_key field
     */
    const COL_JOBSITE_KEY = 'user_search_site_run.jobsite_key';

    /**
     * the column name for the app_run_id field
     */
    const COL_APP_RUN_ID = 'user_search_site_run.app_run_id';

    /**
     * the column name for the user_search_site_run_key field
     */
    const COL_USER_SEARCH_SITE_RUN_KEY = 'user_search_site_run.user_search_site_run_key';

    /**
     * the column name for the date_started field
     */
    const COL_DATE_STARTED = 'user_search_site_run.date_started';

    /**
     * the column name for the date_ended field
     */
    const COL_DATE_ENDED = 'user_search_site_run.date_ended';

    /**
     * the column name for the search_start_url field
     */
    const COL_SEARCH_START_URL = 'user_search_site_run.search_start_url';

    /**
     * the column name for the run_result_code field
     */
    const COL_RUN_RESULT_CODE = 'user_search_site_run.run_result_code';

    /**
     * the column name for the run_error_details field
     */
    const COL_RUN_ERROR_DETAILS = 'user_search_site_run.run_error_details';

    /**
     * the column name for the run_error_page_html field
     */
    const COL_RUN_ERROR_PAGE_HTML = 'user_search_site_run.run_error_page_html';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /** The enumerated values for the run_result_code field */
    const COL_RUN_RESULT_CODE_NOT_RUN = 'not-run';
    const COL_RUN_RESULT_CODE_FAILED = 'failed';
    const COL_RUN_RESULT_CODE_EXCLUDED = 'excluded';
    const COL_RUN_RESULT_CODE_SKIPPED = 'skipped';
    const COL_RUN_RESULT_CODE_SUCCESSFUL = 'successful';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('UserSearchSiteRunId', 'UserSearchPairId', 'JobSiteKey', 'AppRunId', 'UserSearchSiteRunKey', 'StartedAt', 'EndedAt', 'SearchStartUrl', 'RunResultCode', 'RunErrorDetails', 'RunErrorPageHtml', ),
        self::TYPE_CAMELNAME     => array('userSearchSiteRunId', 'userSearchPairId', 'jobSiteKey', 'appRunId', 'userSearchSiteRunKey', 'startedAt', 'endedAt', 'searchStartUrl', 'runResultCode', 'runErrorDetails', 'runErrorPageHtml', ),
        self::TYPE_COLNAME       => array(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID, UserSearchSiteRunTableMap::COL_JOBSITE_KEY, UserSearchSiteRunTableMap::COL_APP_RUN_ID, UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_KEY, UserSearchSiteRunTableMap::COL_DATE_STARTED, UserSearchSiteRunTableMap::COL_DATE_ENDED, UserSearchSiteRunTableMap::COL_SEARCH_START_URL, UserSearchSiteRunTableMap::COL_RUN_RESULT_CODE, UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS, UserSearchSiteRunTableMap::COL_RUN_ERROR_PAGE_HTML, ),
        self::TYPE_FIELDNAME     => array('user_search_site_run_id', 'user_search_pair_id', 'jobsite_key', 'app_run_id', 'user_search_site_run_key', 'date_started', 'date_ended', 'search_start_url', 'run_result_code', 'run_error_details', 'run_error_page_html', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('UserSearchSiteRunId' => 0, 'UserSearchPairId' => 1, 'JobSiteKey' => 2, 'AppRunId' => 3, 'UserSearchSiteRunKey' => 4, 'StartedAt' => 5, 'EndedAt' => 6, 'SearchStartUrl' => 7, 'RunResultCode' => 8, 'RunErrorDetails' => 9, 'RunErrorPageHtml' => 10, ),
        self::TYPE_CAMELNAME     => array('userSearchSiteRunId' => 0, 'userSearchPairId' => 1, 'jobSiteKey' => 2, 'appRunId' => 3, 'userSearchSiteRunKey' => 4, 'startedAt' => 5, 'endedAt' => 6, 'searchStartUrl' => 7, 'runResultCode' => 8, 'runErrorDetails' => 9, 'runErrorPageHtml' => 10, ),
        self::TYPE_COLNAME       => array(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID => 0, UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID => 1, UserSearchSiteRunTableMap::COL_JOBSITE_KEY => 2, UserSearchSiteRunTableMap::COL_APP_RUN_ID => 3, UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_KEY => 4, UserSearchSiteRunTableMap::COL_DATE_STARTED => 5, UserSearchSiteRunTableMap::COL_DATE_ENDED => 6, UserSearchSiteRunTableMap::COL_SEARCH_START_URL => 7, UserSearchSiteRunTableMap::COL_RUN_RESULT_CODE => 8, UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS => 9, UserSearchSiteRunTableMap::COL_RUN_ERROR_PAGE_HTML => 10, ),
        self::TYPE_FIELDNAME     => array('user_search_site_run_id' => 0, 'user_search_pair_id' => 1, 'jobsite_key' => 2, 'app_run_id' => 3, 'user_search_site_run_key' => 4, 'date_started' => 5, 'date_ended' => 6, 'search_start_url' => 7, 'run_result_code' => 8, 'run_error_details' => 9, 'run_error_page_html' => 10, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
                UserSearchSiteRunTableMap::COL_RUN_RESULT_CODE => array(
                            self::COL_RUN_RESULT_CODE_NOT_RUN,
            self::COL_RUN_RESULT_CODE_FAILED,
            self::COL_RUN_RESULT_CODE_EXCLUDED,
            self::COL_RUN_RESULT_CODE_SKIPPED,
            self::COL_RUN_RESULT_CODE_SUCCESSFUL,
        ),
    );

    /**
     * Gets the list of values for all ENUM and SET columns
     * @return array
     */
    public static function getValueSets()
    {
      return static::$enumValueSets;
    }

    /**
     * Gets the list of values for an ENUM or SET column
     * @param string $colname
     * @return array list of possible values for the column
     */
    public static function getValueSet($colname)
    {
        $valueSets = self::getValueSets();

        return $valueSets[$colname];
    }

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
        $this->setName('user_search_site_run');
        $this->setPhpName('UserSearchSiteRun');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\JobScooper\\DataAccess\\UserSearchSiteRun');
        $this->setPackage('JobScooper.DataAccess');
        $this->setUseIdGenerator(true);
        $this->setIsCrossRef(true);
        // columns
        $this->addPrimaryKey('user_search_site_run_id', 'UserSearchSiteRunId', 'INTEGER', true, null, null);
        $this->addForeignKey('user_search_pair_id', 'UserSearchPairId', 'INTEGER', 'user_search_pair', 'user_search_pair_id', true, null, null);
        $this->addForeignKey('jobsite_key', 'JobSiteKey', 'VARCHAR', 'job_site', 'jobsite_key', true, 100, null);
        $this->addColumn('app_run_id', 'AppRunId', 'VARCHAR', true, 75, null);
        $this->addColumn('user_search_site_run_key', 'UserSearchSiteRunKey', 'VARCHAR', true, 100, null);
        $this->getColumn('user_search_site_run_key')->setPrimaryString(true);
        $this->addColumn('date_started', 'StartedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_ended', 'EndedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('search_start_url', 'SearchStartUrl', 'VARCHAR', false, 1024, null);
        $this->addColumn('run_result_code', 'RunResultCode', 'ENUM', false, null, 'not-run');
        $this->getColumn('run_result_code')->setValueSet(array (
  0 => 'not-run',
  1 => 'failed',
  2 => 'excluded',
  3 => 'skipped',
  4 => 'successful',
));
        $this->addColumn('run_error_details', 'RunErrorDetails', 'ARRAY', false, null, null);
        $this->addColumn('run_error_page_html', 'RunErrorPageHtml', 'LONGVARCHAR', false, null, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('JobSiteFromUSSR', '\\JobScooper\\DataAccess\\JobSiteRecord', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':jobsite_key',
    1 => ':jobsite_key',
  ),
), 'CASCADE', null, null, false);
        $this->addRelation('UserSearchPairFromUSSR', '\\JobScooper\\DataAccess\\UserSearchPair', RelationMap::MANY_TO_ONE, array (
  0 =>
  array (
    0 => ':user_search_pair_id',
    1 => ':user_search_pair_id',
  ),
), 'CASCADE', 'CASCADE', null, false);
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
            'sluggable' => array('slug_column' => 'user_search_site_run_key', 'slug_pattern' => '{UserSearchPairId}_{JobSiteKey}_{AppRunId}', 'replace_pattern' => '/[^\w\/]+/u', 'replacement' => '', 'separator' => '-', 'permanent' => 'true', 'scope_column' => '', 'unique_constraint' => 'true', ),
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
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)];
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
                : self::translateFieldName('UserSearchSiteRunId', TableMap::TYPE_PHPNAME, $indexType)
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
        return $withPrefix ? UserSearchSiteRunTableMap::CLASS_DEFAULT : UserSearchSiteRunTableMap::OM_CLASS;
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
     * @return array           (UserSearchSiteRun object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = UserSearchSiteRunTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = UserSearchSiteRunTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + UserSearchSiteRunTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = UserSearchSiteRunTableMap::OM_CLASS;
            /** @var UserSearchSiteRun $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            UserSearchSiteRunTableMap::addInstanceToPool($obj, $key);
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
            $key = UserSearchSiteRunTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = UserSearchSiteRunTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var UserSearchSiteRun $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                UserSearchSiteRunTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_USER_SEARCH_PAIR_ID);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_JOBSITE_KEY);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_APP_RUN_ID);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_KEY);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_DATE_STARTED);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_DATE_ENDED);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_SEARCH_START_URL);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_RUN_RESULT_CODE);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_RUN_ERROR_DETAILS);
            $criteria->addSelectColumn(UserSearchSiteRunTableMap::COL_RUN_ERROR_PAGE_HTML);
        } else {
            $criteria->addSelectColumn($alias . '.user_search_site_run_id');
            $criteria->addSelectColumn($alias . '.user_search_pair_id');
            $criteria->addSelectColumn($alias . '.jobsite_key');
            $criteria->addSelectColumn($alias . '.app_run_id');
            $criteria->addSelectColumn($alias . '.user_search_site_run_key');
            $criteria->addSelectColumn($alias . '.date_started');
            $criteria->addSelectColumn($alias . '.date_ended');
            $criteria->addSelectColumn($alias . '.search_start_url');
            $criteria->addSelectColumn($alias . '.run_result_code');
            $criteria->addSelectColumn($alias . '.run_error_details');
            $criteria->addSelectColumn($alias . '.run_error_page_html');
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
        return Propel::getServiceContainer()->getDatabaseMap(UserSearchSiteRunTableMap::DATABASE_NAME)->getTable(UserSearchSiteRunTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(UserSearchSiteRunTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(UserSearchSiteRunTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new UserSearchSiteRunTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a UserSearchSiteRun or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or UserSearchSiteRun object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(UserSearchSiteRunTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \JobScooper\DataAccess\UserSearchSiteRun) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(UserSearchSiteRunTableMap::DATABASE_NAME);
            $criteria->add(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID, (array) $values, Criteria::IN);
        }

        $query = UserSearchSiteRunQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            UserSearchSiteRunTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                UserSearchSiteRunTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the user_search_site_run table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return UserSearchSiteRunQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a UserSearchSiteRun or Criteria object.
     *
     * @param mixed               $criteria Criteria or UserSearchSiteRun object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserSearchSiteRunTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from UserSearchSiteRun object
        }

        if ($criteria->containsKey(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID) && $criteria->keyContainsValue(UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.UserSearchSiteRunTableMap::COL_USER_SEARCH_SITE_RUN_ID.')');
        }


        // Set the correct dbName
        $query = UserSearchSiteRunQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // UserSearchSiteRunTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
UserSearchSiteRunTableMap::buildTableMap();
