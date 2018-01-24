<?php

namespace JobScooper\DataAccess\Map;

use JobScooper\DataAccess\JobSiteRecord;
use JobScooper\DataAccess\JobSiteRecordQuery;
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
 * This class defines the structure of the 'job_site' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class JobSiteRecordTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'JobScooper.DataAccess.Map.JobSiteRecordTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'default';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'job_site';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\JobScooper\\DataAccess\\JobSiteRecord';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'JobScooper.DataAccess.JobSiteRecord';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 5;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 5;

    /**
     * the column name for the jobsite_key field
     */
    const COL_JOBSITE_KEY = 'job_site.jobsite_key';

    /**
     * the column name for the plugin_class_name field
     */
    const COL_PLUGIN_CLASS_NAME = 'job_site.plugin_class_name';

    /**
     * the column name for the display_name field
     */
    const COL_DISPLAY_NAME = 'job_site.display_name';

    /**
     * the column name for the is_disabled field
     */
    const COL_IS_DISABLED = 'job_site.is_disabled';

    /**
     * the column name for the results_filter_type field
     */
    const COL_RESULTS_FILTER_TYPE = 'job_site.results_filter_type';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /** The enumerated values for the results_filter_type field */
    const COL_RESULTS_FILTER_TYPE_UNKNOWN = 'unknown';
    const COL_RESULTS_FILTER_TYPE_ALL_ONLY = 'all-only';
    const COL_RESULTS_FILTER_TYPE_ALL_BY_LOCATION = 'all-by-location';
    const COL_RESULTS_FILTER_TYPE_USER_FILTERED = 'user-filtered';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('JobSiteKey', 'PluginClassName', 'DisplayName', 'isDisabled', 'ResultsFilterType', ),
        self::TYPE_CAMELNAME     => array('jobSiteKey', 'pluginClassName', 'displayName', 'isDisabled', 'resultsFilterType', ),
        self::TYPE_COLNAME       => array(JobSiteRecordTableMap::COL_JOBSITE_KEY, JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME, JobSiteRecordTableMap::COL_DISPLAY_NAME, JobSiteRecordTableMap::COL_IS_DISABLED, JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE, ),
        self::TYPE_FIELDNAME     => array('jobsite_key', 'plugin_class_name', 'display_name', 'is_disabled', 'results_filter_type', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('JobSiteKey' => 0, 'PluginClassName' => 1, 'DisplayName' => 2, 'isDisabled' => 3, 'ResultsFilterType' => 4, ),
        self::TYPE_CAMELNAME     => array('jobSiteKey' => 0, 'pluginClassName' => 1, 'displayName' => 2, 'isDisabled' => 3, 'resultsFilterType' => 4, ),
        self::TYPE_COLNAME       => array(JobSiteRecordTableMap::COL_JOBSITE_KEY => 0, JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME => 1, JobSiteRecordTableMap::COL_DISPLAY_NAME => 2, JobSiteRecordTableMap::COL_IS_DISABLED => 3, JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE => 4, ),
        self::TYPE_FIELDNAME     => array('jobsite_key' => 0, 'plugin_class_name' => 1, 'display_name' => 2, 'is_disabled' => 3, 'results_filter_type' => 4, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, )
    );

    /** The enumerated values for this table */
    protected static $enumValueSets = array(
                JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE => array(
                            self::COL_RESULTS_FILTER_TYPE_UNKNOWN,
            self::COL_RESULTS_FILTER_TYPE_ALL_ONLY,
            self::COL_RESULTS_FILTER_TYPE_ALL_BY_LOCATION,
            self::COL_RESULTS_FILTER_TYPE_USER_FILTERED,
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
        $this->setName('job_site');
        $this->setPhpName('JobSiteRecord');
        $this->setIdentifierQuoting(false);
        $this->setClassName('\\JobScooper\\DataAccess\\JobSiteRecord');
        $this->setPackage('JobScooper.DataAccess');
        $this->setUseIdGenerator(false);
        // columns
        $this->addPrimaryKey('jobsite_key', 'JobSiteKey', 'VARCHAR', true, 100, null);
        $this->getColumn('jobsite_key')->setPrimaryString(true);
        $this->addColumn('plugin_class_name', 'PluginClassName', 'VARCHAR', false, 100, null);
        $this->addColumn('display_name', 'DisplayName', 'VARCHAR', false, 255, null);
        $this->addColumn('is_disabled', 'isDisabled', 'BOOLEAN', false, 1, false);
        $this->addColumn('results_filter_type', 'ResultsFilterType', 'ENUM', false, null, 'unknown');
        $this->getColumn('results_filter_type')->setValueSet(array (
  0 => 'unknown',
  1 => 'all-only',
  2 => 'all-by-location',
  3 => 'user-filtered',
));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('JobPosting', '\\JobScooper\\DataAccess\\JobPosting', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':jobsite_key',
    1 => ':jobsite_key',
  ),
), null, null, 'JobPostings', false);
        $this->addRelation('UserSearchSiteRun', '\\JobScooper\\DataAccess\\UserSearchSiteRun', RelationMap::ONE_TO_MANY, array (
  0 =>
  array (
    0 => ':jobsite_key',
    1 => ':jobsite_key',
  ),
), 'CASCADE', null, 'UserSearchSiteRuns', false);
    } // buildRelations()
    /**
     * Method to invalidate the instance pool of all tables related to job_site     * by a foreign key with ON DELETE CASCADE
     */
    public static function clearRelatedInstancePool()
    {
        // Invalidate objects in related instance pools,
        // since one or more of them may be deleted by ON DELETE CASCADE/SETNULL rule.
        UserSearchSiteRunTableMap::clearInstancePool();
    }

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
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return null === $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)] || is_scalar($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)]) || is_callable([$row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)], '__toString']) ? (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)] : $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)];
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
        return (string) $row[
            $indexType == TableMap::TYPE_NUM
                ? 0 + $offset
                : self::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)
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
        return $withPrefix ? JobSiteRecordTableMap::CLASS_DEFAULT : JobSiteRecordTableMap::OM_CLASS;
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
     * @return array           (JobSiteRecord object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = JobSiteRecordTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = JobSiteRecordTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + JobSiteRecordTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = JobSiteRecordTableMap::OM_CLASS;
            /** @var JobSiteRecord $obj */
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            JobSiteRecordTableMap::addInstanceToPool($obj, $key);
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
            $key = JobSiteRecordTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = JobSiteRecordTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                /** @var JobSiteRecord $obj */
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                JobSiteRecordTableMap::addInstanceToPool($obj, $key);
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
            $criteria->addSelectColumn(JobSiteRecordTableMap::COL_JOBSITE_KEY);
            $criteria->addSelectColumn(JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME);
            $criteria->addSelectColumn(JobSiteRecordTableMap::COL_DISPLAY_NAME);
            $criteria->addSelectColumn(JobSiteRecordTableMap::COL_IS_DISABLED);
            $criteria->addSelectColumn(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE);
        } else {
            $criteria->addSelectColumn($alias . '.jobsite_key');
            $criteria->addSelectColumn($alias . '.plugin_class_name');
            $criteria->addSelectColumn($alias . '.display_name');
            $criteria->addSelectColumn($alias . '.is_disabled');
            $criteria->addSelectColumn($alias . '.results_filter_type');
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
        return Propel::getServiceContainer()->getDatabaseMap(JobSiteRecordTableMap::DATABASE_NAME)->getTable(JobSiteRecordTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
        $dbMap = Propel::getServiceContainer()->getDatabaseMap(JobSiteRecordTableMap::DATABASE_NAME);
        if (!$dbMap->hasTable(JobSiteRecordTableMap::TABLE_NAME)) {
            $dbMap->addTableObject(new JobSiteRecordTableMap());
        }
    }

    /**
     * Performs a DELETE on the database, given a JobSiteRecord or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or JobSiteRecord object or primary key or array of primary keys
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
            $con = Propel::getServiceContainer()->getWriteConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \JobScooper\DataAccess\JobSiteRecord) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(JobSiteRecordTableMap::DATABASE_NAME);
            $criteria->add(JobSiteRecordTableMap::COL_JOBSITE_KEY, (array) $values, Criteria::IN);
        }

        $query = JobSiteRecordQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) {
            JobSiteRecordTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) {
                JobSiteRecordTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the job_site table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return JobSiteRecordQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a JobSiteRecord or Criteria object.
     *
     * @param mixed               $criteria Criteria or JobSiteRecord object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *                         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from JobSiteRecord object
        }


        // Set the correct dbName
        $query = JobSiteRecordQuery::create()->mergeWith($criteria);

        // use transaction because $criteria could contain info
        // for more than one table (I guess, conceivably)
        return $con->transaction(function () use ($con, $query) {
            return $query->doInsert($con);
        });
    }

} // JobSiteRecordTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
JobSiteRecordTableMap::buildTableMap();
