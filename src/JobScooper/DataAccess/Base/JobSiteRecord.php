<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\JobPosting as ChildJobPosting;
use JobScooper\DataAccess\JobPostingQuery as ChildJobPostingQuery;
use JobScooper\DataAccess\JobSiteRecord as ChildJobSiteRecord;
use JobScooper\DataAccess\JobSiteRecordQuery as ChildJobSiteRecordQuery;
use JobScooper\DataAccess\UserSearchSiteRun as ChildUserSearchSiteRun;
use JobScooper\DataAccess\UserSearchSiteRunQuery as ChildUserSearchSiteRunQuery;
use JobScooper\DataAccess\Map\JobPostingTableMap;
use JobScooper\DataAccess\Map\JobSiteRecordTableMap;
use JobScooper\DataAccess\Map\UserSearchSiteRunTableMap;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\BadMethodCallException;
use Propel\Runtime\Exception\LogicException;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Parser\AbstractParser;

/**
 * Base class that represents a row from the 'job_site' table.
 *
 *
 *
 * @package    propel.generator.JobScooper.DataAccess.Base
 */
abstract class JobSiteRecord implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\JobScooper\\DataAccess\\Map\\JobSiteRecordTableMap';


    /**
     * attribute to determine if this object has previously been saved.
     * @var boolean
     */
    protected $new = true;

    /**
     * attribute to determine whether this object has been deleted.
     * @var boolean
     */
    protected $deleted = false;

    /**
     * The columns that have been modified in current object.
     * Tracking modified columns allows us to only update modified columns.
     * @var array
     */
    protected $modifiedColumns = array();

    /**
     * The (virtual) columns that are added at runtime
     * The formatters can add supplementary columns based on a resultset
     * @var array
     */
    protected $virtualColumns = array();

    /**
     * The value for the jobsite_key field.
     *
     * @var        string
     */
    protected $jobsite_key;

    /**
     * The value for the plugin_class_name field.
     *
     * @var        string
     */
    protected $plugin_class_name;

    /**
     * The value for the display_name field.
     *
     * @var        string
     */
    protected $display_name;

    /**
     * The value for the is_disabled field.
     *
     * Note: this column has a database default value of: false
     * @var        boolean
     */
    protected $is_disabled;

    /**
     * The value for the results_filter_type field.
     *
     * Note: this column has a database default value of: 0
     * @var        int
     */
    protected $results_filter_type;

    /**
     * @var        ObjectCollection|ChildJobPosting[] Collection to store aggregation of ChildJobPosting objects.
     */
    protected $collJobPostings;
    protected $collJobPostingsPartial;

    /**
     * @var        ObjectCollection|ChildUserSearchSiteRun[] Collection to store aggregation of ChildUserSearchSiteRun objects.
     */
    protected $collUserSearchSiteRuns;
    protected $collUserSearchSiteRunsPartial;

    /**
     * Flag to prevent endless save loop, if this object is referenced
     * by another object which falls in this transaction.
     *
     * @var boolean
     */
    protected $alreadyInSave = false;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildJobPosting[]
     */
    protected $jobPostingsScheduledForDeletion = null;

    /**
     * An array of objects scheduled for deletion.
     * @var ObjectCollection|ChildUserSearchSiteRun[]
     */
    protected $userSearchSiteRunsScheduledForDeletion = null;

    /**
     * Applies default values to this object.
     * This method should be called from the object's constructor (or
     * equivalent initialization method).
     * @see __construct()
     */
    public function applyDefaultValues()
    {
        $this->is_disabled = false;
        $this->results_filter_type = 0;
    }

    /**
     * Initializes internal state of JobScooper\DataAccess\Base\JobSiteRecord object.
     * @see applyDefaults()
     */
    public function __construct()
    {
        $this->applyDefaultValues();
    }

    /**
     * Returns whether the object has been modified.
     *
     * @return boolean True if the object has been modified.
     */
    public function isModified()
    {
        return !!$this->modifiedColumns;
    }

    /**
     * Has specified column been modified?
     *
     * @param  string  $col column fully qualified name (TableMap::TYPE_COLNAME), e.g. Book::AUTHOR_ID
     * @return boolean True if $col has been modified.
     */
    public function isColumnModified($col)
    {
        return $this->modifiedColumns && isset($this->modifiedColumns[$col]);
    }

    /**
     * Get the columns that have been modified in this object.
     * @return array A unique list of the modified column names for this object.
     */
    public function getModifiedColumns()
    {
        return $this->modifiedColumns ? array_keys($this->modifiedColumns) : [];
    }

    /**
     * Returns whether the object has ever been saved.  This will
     * be false, if the object was retrieved from storage or was created
     * and then saved.
     *
     * @return boolean true, if the object has never been persisted.
     */
    public function isNew()
    {
        return $this->new;
    }

    /**
     * Setter for the isNew attribute.  This method will be called
     * by Propel-generated children and objects.
     *
     * @param boolean $b the state of the object.
     */
    public function setNew($b)
    {
        $this->new = (boolean) $b;
    }

    /**
     * Whether this object has been deleted.
     * @return boolean The deleted state of this object.
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * Specify whether this object has been deleted.
     * @param  boolean $b The deleted state of this object.
     * @return void
     */
    public function setDeleted($b)
    {
        $this->deleted = (boolean) $b;
    }

    /**
     * Sets the modified state for the object to be false.
     * @param  string $col If supplied, only the specified column is reset.
     * @return void
     */
    public function resetModified($col = null)
    {
        if (null !== $col) {
            if (isset($this->modifiedColumns[$col])) {
                unset($this->modifiedColumns[$col]);
            }
        } else {
            $this->modifiedColumns = array();
        }
    }

    /**
     * Compares this with another <code>JobSiteRecord</code> instance.  If
     * <code>obj</code> is an instance of <code>JobSiteRecord</code>, delegates to
     * <code>equals(JobSiteRecord)</code>.  Otherwise, returns <code>false</code>.
     *
     * @param  mixed   $obj The object to compare to.
     * @return boolean Whether equal to the object specified.
     */
    public function equals($obj)
    {
        if (!$obj instanceof static) {
            return false;
        }

        if ($this === $obj) {
            return true;
        }

        if (null === $this->getPrimaryKey() || null === $obj->getPrimaryKey()) {
            return false;
        }

        return $this->getPrimaryKey() === $obj->getPrimaryKey();
    }

    /**
     * Get the associative array of the virtual columns in this object
     *
     * @return array
     */
    public function getVirtualColumns()
    {
        return $this->virtualColumns;
    }

    /**
     * Checks the existence of a virtual column in this object
     *
     * @param  string  $name The virtual column name
     * @return boolean
     */
    public function hasVirtualColumn($name)
    {
        return array_key_exists($name, $this->virtualColumns);
    }

    /**
     * Get the value of a virtual column in this object
     *
     * @param  string $name The virtual column name
     * @return mixed
     *
     * @throws PropelException
     */
    public function getVirtualColumn($name)
    {
        if (!$this->hasVirtualColumn($name)) {
            throw new PropelException(sprintf('Cannot get value of inexistent virtual column %s.', $name));
        }

        return $this->virtualColumns[$name];
    }

    /**
     * Set the value of a virtual column in this object
     *
     * @param string $name  The virtual column name
     * @param mixed  $value The value to give to the virtual column
     *
     * @return $this|JobSiteRecord The current object, for fluid interface
     */
    public function setVirtualColumn($name, $value)
    {
        $this->virtualColumns[$name] = $value;

        return $this;
    }

    /**
     * Logs a message using Propel::log().
     *
     * @param  string  $msg
     * @param  int     $priority One of the Propel::LOG_* logging levels
     * @return boolean
     */
    protected function log($msg, $priority = Propel::LOG_INFO)
    {
        return Propel::log(get_class($this) . ': ' . $msg, $priority);
    }

    /**
     * Export the current object properties to a string, using a given parser format
     * <code>
     * $book = BookQuery::create()->findPk(9012);
     * echo $book->exportTo('JSON');
     *  => {"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * @param  mixed   $parser                 A AbstractParser instance, or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param  boolean $includeLazyLoadColumns (optional) Whether to include lazy load(ed) columns. Defaults to TRUE.
     * @return string  The exported data
     */
    public function exportTo($parser, $includeLazyLoadColumns = true)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        return $parser->fromArray($this->toArray(TableMap::TYPE_PHPNAME, $includeLazyLoadColumns, array(), true));
    }

    /**
     * Clean up internal collections prior to serializing
     * Avoids recursive loops that turn into segmentation faults when serializing
     */
    public function __sleep()
    {
        $this->clearAllReferences();

        $cls = new \ReflectionClass($this);
        $propertyNames = [];
        $serializableProperties = array_diff($cls->getProperties(), $cls->getProperties(\ReflectionProperty::IS_STATIC));

        foreach($serializableProperties as $property) {
            $propertyNames[] = $property->getName();
        }

        return $propertyNames;
    }

    /**
     * Get the [jobsite_key] column value.
     *
     * @return string
     */
    public function getJobSiteKey()
    {
        return $this->jobsite_key;
    }

    /**
     * Get the [plugin_class_name] column value.
     *
     * @return string
     */
    public function getPluginClassName()
    {
        return $this->plugin_class_name;
    }

    /**
     * Get the [display_name] column value.
     *
     * @return string
     */
    public function getDisplayName()
    {
        return $this->display_name;
    }

    /**
     * Get the [is_disabled] column value.
     *
     * @return boolean
     */
    public function getisDisabled()
    {
        return $this->is_disabled;
    }

    /**
     * Get the [is_disabled] column value.
     *
     * @return boolean
     */
    public function isDisabled()
    {
        return $this->getisDisabled();
    }

    /**
     * Get the [results_filter_type] column value.
     *
     * @return string
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function getResultsFilterType()
    {
        if (null === $this->results_filter_type) {
            return null;
        }
        $valueSet = JobSiteRecordTableMap::getValueSet(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE);
        if (!isset($valueSet[$this->results_filter_type])) {
            throw new PropelException('Unknown stored enum key: ' . $this->results_filter_type);
        }

        return $valueSet[$this->results_filter_type];
    }

    /**
     * Set the value of [jobsite_key] column.
     *
     * @param string $v new value
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     */
    public function setJobSiteKey($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->jobsite_key !== $v) {
            $this->jobsite_key = $v;
            $this->modifiedColumns[JobSiteRecordTableMap::COL_JOBSITE_KEY] = true;
        }

        return $this;
    } // setJobSiteKey()

    /**
     * Set the value of [plugin_class_name] column.
     *
     * @param string $v new value
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     */
    public function setPluginClassName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->plugin_class_name !== $v) {
            $this->plugin_class_name = $v;
            $this->modifiedColumns[JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME] = true;
        }

        return $this;
    } // setPluginClassName()

    /**
     * Set the value of [display_name] column.
     *
     * @param string $v new value
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     */
    public function setDisplayName($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->display_name !== $v) {
            $this->display_name = $v;
            $this->modifiedColumns[JobSiteRecordTableMap::COL_DISPLAY_NAME] = true;
        }

        return $this;
    } // setDisplayName()

    /**
     * Sets the value of the [is_disabled] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param  boolean|integer|string $v The new value
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     */
    public function setisDisabled($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_disabled !== $v) {
            $this->is_disabled = $v;
            $this->modifiedColumns[JobSiteRecordTableMap::COL_IS_DISABLED] = true;
        }

        return $this;
    } // setisDisabled()

    /**
     * Set the value of [results_filter_type] column.
     *
     * @param  string $v new value
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     * @throws \Propel\Runtime\Exception\PropelException
     */
    public function setResultsFilterType($v)
    {
        if ($v !== null) {
            $valueSet = JobSiteRecordTableMap::getValueSet(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE);
            if (!in_array($v, $valueSet)) {
                throw new PropelException(sprintf('Value "%s" is not accepted in this enumerated column', $v));
            }
            $v = array_search($v, $valueSet);
        }

        if ($this->results_filter_type !== $v) {
            $this->results_filter_type = $v;
            $this->modifiedColumns[JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE] = true;
        }

        return $this;
    } // setResultsFilterType()

    /**
     * Indicates whether the columns in this object are only set to default values.
     *
     * This method can be used in conjunction with isModified() to indicate whether an object is both
     * modified _and_ has some values set which are non-default.
     *
     * @return boolean Whether the columns in this object are only been set with default values.
     */
    public function hasOnlyDefaultValues()
    {
            if ($this->is_disabled !== false) {
                return false;
            }

            if ($this->results_filter_type !== 0) {
                return false;
            }

        // otherwise, everything was equal, so return TRUE
        return true;
    } // hasOnlyDefaultValues()

    /**
     * Hydrates (populates) the object variables with values from the database resultset.
     *
     * An offset (0-based "start column") is specified so that objects can be hydrated
     * with a subset of the columns in the resultset rows.  This is needed, for example,
     * for results of JOIN queries where the resultset row includes columns from two or
     * more tables.
     *
     * @param array   $row       The row returned by DataFetcher->fetch().
     * @param int     $startcol  0-based offset column which indicates which restultset column to start with.
     * @param boolean $rehydrate Whether this object is being re-hydrated from the database.
     * @param string  $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                  One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                            TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @return int             next starting column
     * @throws PropelException - Any caught Exception will be rewrapped as a PropelException.
     */
    public function hydrate($row, $startcol = 0, $rehydrate = false, $indexType = TableMap::TYPE_NUM)
    {
        try {

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : JobSiteRecordTableMap::translateFieldName('JobSiteKey', TableMap::TYPE_PHPNAME, $indexType)];
            $this->jobsite_key = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : JobSiteRecordTableMap::translateFieldName('PluginClassName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->plugin_class_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : JobSiteRecordTableMap::translateFieldName('DisplayName', TableMap::TYPE_PHPNAME, $indexType)];
            $this->display_name = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : JobSiteRecordTableMap::translateFieldName('isDisabled', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_disabled = (null !== $col) ? (boolean) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : JobSiteRecordTableMap::translateFieldName('ResultsFilterType', TableMap::TYPE_PHPNAME, $indexType)];
            $this->results_filter_type = (null !== $col) ? (int) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 5; // 5 = JobSiteRecordTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\JobScooper\\DataAccess\\JobSiteRecord'), 0, $e);
        }
    }

    /**
     * Checks and repairs the internal consistency of the object.
     *
     * This method is executed after an already-instantiated object is re-hydrated
     * from the database.  It exists to check any foreign keys to make sure that
     * the objects related to the current object are correct based on foreign key.
     *
     * You can override this method in the stub class, but you should always invoke
     * the base method from the overridden method (i.e. parent::ensureConsistency()),
     * in case your model changes.
     *
     * @throws PropelException
     */
    public function ensureConsistency()
    {
    } // ensureConsistency

    /**
     * Reloads this object from datastore based on primary key and (optionally) resets all associated objects.
     *
     * This will only work if the object has been saved and has a valid primary key set.
     *
     * @param      boolean $deep (optional) Whether to also de-associated any related objects.
     * @param      ConnectionInterface $con (optional) The ConnectionInterface connection to use.
     * @return void
     * @throws PropelException - if this object is deleted, unsaved or doesn't have pk match in db
     */
    public function reload($deep = false, ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("Cannot reload a deleted object.");
        }

        if ($this->isNew()) {
            throw new PropelException("Cannot reload an unsaved object.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildJobSiteRecordQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->collJobPostings = null;

            $this->collUserSearchSiteRuns = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see JobSiteRecord::setDeleted()
     * @see JobSiteRecord::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildJobSiteRecordQuery::create()
                ->filterByPrimaryKey($this->getPrimaryKey());
            $ret = $this->preDelete($con);
            if ($ret) {
                $deleteQuery->delete($con);
                $this->postDelete($con);
                $this->setDeleted(true);
            }
        });
    }

    /**
     * Persists this object to the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All modified related objects will also be persisted in the doSave()
     * method.  This method wraps all precipitate database operations in a
     * single transaction.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(JobSiteRecordTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                JobSiteRecordTableMap::addInstanceToPool($this);
            } else {
                $affectedRows = 0;
            }

            return $affectedRows;
        });
    }

    /**
     * Performs the work of inserting or updating the row in the database.
     *
     * If the object is new, it inserts it; otherwise an update is performed.
     * All related objects are also updated in this method.
     *
     * @param      ConnectionInterface $con
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                }
                $this->resetModified();
            }

            if ($this->jobPostingsScheduledForDeletion !== null) {
                if (!$this->jobPostingsScheduledForDeletion->isEmpty()) {
                    \JobScooper\DataAccess\JobPostingQuery::create()
                        ->filterByPrimaryKeys($this->jobPostingsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->jobPostingsScheduledForDeletion = null;
                }
            }

            if ($this->collJobPostings !== null) {
                foreach ($this->collJobPostings as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            if ($this->userSearchSiteRunsScheduledForDeletion !== null) {
                if (!$this->userSearchSiteRunsScheduledForDeletion->isEmpty()) {
                    \JobScooper\DataAccess\UserSearchSiteRunQuery::create()
                        ->filterByPrimaryKeys($this->userSearchSiteRunsScheduledForDeletion->getPrimaryKeys(false))
                        ->delete($con);
                    $this->userSearchSiteRunsScheduledForDeletion = null;
                }
            }

            if ($this->collUserSearchSiteRuns !== null) {
                foreach ($this->collUserSearchSiteRuns as $referrerFK) {
                    if (!$referrerFK->isDeleted() && ($referrerFK->isNew() || $referrerFK->isModified())) {
                        $affectedRows += $referrerFK->save($con);
                    }
                }
            }

            $this->alreadyInSave = false;

        }

        return $affectedRows;
    } // doSave()

    /**
     * Insert the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @throws PropelException
     * @see doSave()
     */
    protected function doInsert(ConnectionInterface $con)
    {
        $modifiedColumns = array();
        $index = 0;


         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_JOBSITE_KEY)) {
            $modifiedColumns[':p' . $index++]  = 'jobsite_key';
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'plugin_class_name';
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_DISPLAY_NAME)) {
            $modifiedColumns[':p' . $index++]  = 'display_name';
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_IS_DISABLED)) {
            $modifiedColumns[':p' . $index++]  = 'is_disabled';
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE)) {
            $modifiedColumns[':p' . $index++]  = 'results_filter_type';
        }

        $sql = sprintf(
            'INSERT INTO job_site (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'jobsite_key':
                        $stmt->bindValue($identifier, $this->jobsite_key, PDO::PARAM_STR);
                        break;
                    case 'plugin_class_name':
                        $stmt->bindValue($identifier, $this->plugin_class_name, PDO::PARAM_STR);
                        break;
                    case 'display_name':
                        $stmt->bindValue($identifier, $this->display_name, PDO::PARAM_STR);
                        break;
                    case 'is_disabled':
                        $stmt->bindValue($identifier, (int) $this->is_disabled, PDO::PARAM_INT);
                        break;
                    case 'results_filter_type':
                        $stmt->bindValue($identifier, $this->results_filter_type, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        $this->setNew(false);
    }

    /**
     * Update the row in the database.
     *
     * @param      ConnectionInterface $con
     *
     * @return Integer Number of updated rows
     * @see doSave()
     */
    protected function doUpdate(ConnectionInterface $con)
    {
        $selectCriteria = $this->buildPkeyCriteria();
        $valuesCriteria = $this->buildCriteria();

        return $selectCriteria->doUpdate($valuesCriteria, $con);
    }

    /**
     * Retrieves a field from the object by name passed in as a string.
     *
     * @param      string $name name
     * @param      string $type The type of fieldname the $name is of:
     *                     one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                     TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                     Defaults to TableMap::TYPE_PHPNAME.
     * @return mixed Value of field.
     */
    public function getByName($name, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = JobSiteRecordTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
        $field = $this->getByPosition($pos);

        return $field;
    }

    /**
     * Retrieves a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param      int $pos position in xml schema
     * @return mixed Value of field at $pos
     */
    public function getByPosition($pos)
    {
        switch ($pos) {
            case 0:
                return $this->getJobSiteKey();
                break;
            case 1:
                return $this->getPluginClassName();
                break;
            case 2:
                return $this->getDisplayName();
                break;
            case 3:
                return $this->getisDisabled();
                break;
            case 4:
                return $this->getResultsFilterType();
                break;
            default:
                return null;
                break;
        } // switch()
    }

    /**
     * Exports the object as an array.
     *
     * You can specify the key type of the array by passing one of the class
     * type constants.
     *
     * @param     string  $keyType (optional) One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     *                    TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                    Defaults to TableMap::TYPE_PHPNAME.
     * @param     boolean $includeLazyLoadColumns (optional) Whether to include lazy loaded columns. Defaults to TRUE.
     * @param     array $alreadyDumpedObjects List of objects to skip to avoid recursion
     * @param     boolean $includeForeignObjects (optional) Whether to include hydrated related objects. Default to FALSE.
     *
     * @return array an associative array containing the field names (as keys) and field values
     */
    public function toArray($keyType = TableMap::TYPE_PHPNAME, $includeLazyLoadColumns = true, $alreadyDumpedObjects = array(), $includeForeignObjects = false)
    {

        if (isset($alreadyDumpedObjects['JobSiteRecord'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['JobSiteRecord'][$this->hashCode()] = true;
        $keys = JobSiteRecordTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getJobSiteKey(),
            $keys[1] => $this->getPluginClassName(),
            $keys[2] => $this->getDisplayName(),
            $keys[3] => $this->getisDisabled(),
            $keys[4] => $this->getResultsFilterType(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->collJobPostings) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'jobPostings';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'jobpostings';
                        break;
                    default:
                        $key = 'JobPostings';
                }

                $result[$key] = $this->collJobPostings->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
            if (null !== $this->collUserSearchSiteRuns) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'userSearchSiteRuns';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'user_search_site_runs';
                        break;
                    default:
                        $key = 'UserSearchSiteRuns';
                }

                $result[$key] = $this->collUserSearchSiteRuns->toArray(null, false, $keyType, $includeLazyLoadColumns, $alreadyDumpedObjects);
            }
        }

        return $result;
    }

    /**
     * Sets a field from the object by name passed in as a string.
     *
     * @param  string $name
     * @param  mixed  $value field value
     * @param  string $type The type of fieldname the $name is of:
     *                one of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME
     *                TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *                Defaults to TableMap::TYPE_PHPNAME.
     * @return $this|\JobScooper\DataAccess\JobSiteRecord
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = JobSiteRecordTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\JobScooper\DataAccess\JobSiteRecord
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setJobSiteKey($value);
                break;
            case 1:
                $this->setPluginClassName($value);
                break;
            case 2:
                $this->setDisplayName($value);
                break;
            case 3:
                $this->setisDisabled($value);
                break;
            case 4:
                $valueSet = JobSiteRecordTableMap::getValueSet(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE);
                if (isset($valueSet[$value])) {
                    $value = $valueSet[$value];
                }
                $this->setResultsFilterType($value);
                break;
        } // switch()

        return $this;
    }

    /**
     * Populates the object using an array.
     *
     * This is particularly useful when populating an object from one of the
     * request arrays (e.g. $_POST).  This method goes through the column
     * names, checking to see whether a matching key exists in populated
     * array. If so the setByName() method is called for that column.
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param      array  $arr     An array to populate the object from.
     * @param      string $keyType The type of keys the array uses.
     * @return void
     */
    public function fromArray($arr, $keyType = TableMap::TYPE_PHPNAME)
    {
        $keys = JobSiteRecordTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setJobSiteKey($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setPluginClassName($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setDisplayName($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setisDisabled($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setResultsFilterType($arr[$keys[4]]);
        }
    }

     /**
     * Populate the current object from a string, using a given parser format
     * <code>
     * $book = new Book();
     * $book->importFrom('JSON', '{"Id":9012,"Title":"Don Juan","ISBN":"0140422161","Price":12.99,"PublisherId":1234,"AuthorId":5678}');
     * </code>
     *
     * You can specify the key type of the array by additionally passing one
     * of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_CAMELNAME,
     * TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     * The default key type is the column's TableMap::TYPE_PHPNAME.
     *
     * @param mixed $parser A AbstractParser instance,
     *                       or a format name ('XML', 'YAML', 'JSON', 'CSV')
     * @param string $data The source data to import from
     * @param string $keyType The type of keys the array uses.
     *
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object, for fluid interface
     */
    public function importFrom($parser, $data, $keyType = TableMap::TYPE_PHPNAME)
    {
        if (!$parser instanceof AbstractParser) {
            $parser = AbstractParser::getParser($parser);
        }

        $this->fromArray($parser->toArray($data), $keyType);

        return $this;
    }

    /**
     * Build a Criteria object containing the values of all modified columns in this object.
     *
     * @return Criteria The Criteria object containing all modified values.
     */
    public function buildCriteria()
    {
        $criteria = new Criteria(JobSiteRecordTableMap::DATABASE_NAME);

        if ($this->isColumnModified(JobSiteRecordTableMap::COL_JOBSITE_KEY)) {
            $criteria->add(JobSiteRecordTableMap::COL_JOBSITE_KEY, $this->jobsite_key);
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME)) {
            $criteria->add(JobSiteRecordTableMap::COL_PLUGIN_CLASS_NAME, $this->plugin_class_name);
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_DISPLAY_NAME)) {
            $criteria->add(JobSiteRecordTableMap::COL_DISPLAY_NAME, $this->display_name);
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_IS_DISABLED)) {
            $criteria->add(JobSiteRecordTableMap::COL_IS_DISABLED, $this->is_disabled);
        }
        if ($this->isColumnModified(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE)) {
            $criteria->add(JobSiteRecordTableMap::COL_RESULTS_FILTER_TYPE, $this->results_filter_type);
        }

        return $criteria;
    }

    /**
     * Builds a Criteria object containing the primary key for this object.
     *
     * Unlike buildCriteria() this method includes the primary key values regardless
     * of whether or not they have been modified.
     *
     * @throws LogicException if no primary key is defined
     *
     * @return Criteria The Criteria object containing value(s) for primary key(s).
     */
    public function buildPkeyCriteria()
    {
        $criteria = ChildJobSiteRecordQuery::create();
        $criteria->add(JobSiteRecordTableMap::COL_JOBSITE_KEY, $this->jobsite_key);

        return $criteria;
    }

    /**
     * If the primary key is not null, return the hashcode of the
     * primary key. Otherwise, return the hash code of the object.
     *
     * @return int Hashcode
     */
    public function hashCode()
    {
        $validPk = null !== $this->getJobSiteKey();

        $validPrimaryKeyFKs = 0;
        $primaryKeyFKs = [];

        if ($validPk) {
            return crc32(json_encode($this->getPrimaryKey(), JSON_UNESCAPED_UNICODE));
        } elseif ($validPrimaryKeyFKs) {
            return crc32(json_encode($primaryKeyFKs, JSON_UNESCAPED_UNICODE));
        }

        return spl_object_hash($this);
    }

    /**
     * Returns the primary key for this object (row).
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->getJobSiteKey();
    }

    /**
     * Generic method to set the primary key (jobsite_key column).
     *
     * @param       string $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setJobSiteKey($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getJobSiteKey();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \JobScooper\DataAccess\JobSiteRecord (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setJobSiteKey($this->getJobSiteKey());
        $copyObj->setPluginClassName($this->getPluginClassName());
        $copyObj->setDisplayName($this->getDisplayName());
        $copyObj->setisDisabled($this->getisDisabled());
        $copyObj->setResultsFilterType($this->getResultsFilterType());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getJobPostings() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addJobPosting($relObj->copy($deepCopy));
                }
            }

            foreach ($this->getUserSearchSiteRuns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUserSearchSiteRun($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
        }
    }

    /**
     * Makes a copy of this object that will be inserted as a new row in table when saved.
     * It creates a new object filling in the simple attributes, but skipping any primary
     * keys that are defined for the table.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param  boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @return \JobScooper\DataAccess\JobSiteRecord Clone of current object.
     * @throws PropelException
     */
    public function copy($deepCopy = false)
    {
        // we use get_class(), because this might be a subclass
        $clazz = get_class($this);
        $copyObj = new $clazz();
        $this->copyInto($copyObj, $deepCopy);

        return $copyObj;
    }


    /**
     * Initializes a collection based on the name of a relation.
     * Avoids crafting an 'init[$relationName]s' method name
     * that wouldn't work when StandardEnglishPluralizer is used.
     *
     * @param      string $relationName The name of the relation to initialize
     * @return void
     */
    public function initRelation($relationName)
    {
        if ('JobPosting' == $relationName) {
            $this->initJobPostings();
            return;
        }
        if ('UserSearchSiteRun' == $relationName) {
            $this->initUserSearchSiteRuns();
            return;
        }
    }

    /**
     * Clears out the collJobPostings collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addJobPostings()
     */
    public function clearJobPostings()
    {
        $this->collJobPostings = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collJobPostings collection loaded partially.
     */
    public function resetPartialJobPostings($v = true)
    {
        $this->collJobPostingsPartial = $v;
    }

    /**
     * Initializes the collJobPostings collection.
     *
     * By default this just sets the collJobPostings collection to an empty array (like clearcollJobPostings());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initJobPostings($overrideExisting = true)
    {
        if (null !== $this->collJobPostings && !$overrideExisting) {
            return;
        }

        $collectionClassName = JobPostingTableMap::getTableMap()->getCollectionClassName();

        $this->collJobPostings = new $collectionClassName;
        $this->collJobPostings->setModel('\JobScooper\DataAccess\JobPosting');
    }

    /**
     * Gets an array of ChildJobPosting objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildJobSiteRecord is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildJobPosting[] List of ChildJobPosting objects
     * @throws PropelException
     */
    public function getJobPostings(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collJobPostingsPartial && !$this->isNew();
        if (null === $this->collJobPostings || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collJobPostings) {
                // return empty collection
                $this->initJobPostings();
            } else {
                $collJobPostings = ChildJobPostingQuery::create(null, $criteria)
                    ->filterByJobSiteFromJP($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collJobPostingsPartial && count($collJobPostings)) {
                        $this->initJobPostings(false);

                        foreach ($collJobPostings as $obj) {
                            if (false == $this->collJobPostings->contains($obj)) {
                                $this->collJobPostings->append($obj);
                            }
                        }

                        $this->collJobPostingsPartial = true;
                    }

                    return $collJobPostings;
                }

                if ($partial && $this->collJobPostings) {
                    foreach ($this->collJobPostings as $obj) {
                        if ($obj->isNew()) {
                            $collJobPostings[] = $obj;
                        }
                    }
                }

                $this->collJobPostings = $collJobPostings;
                $this->collJobPostingsPartial = false;
            }
        }

        return $this->collJobPostings;
    }

    /**
     * Sets a collection of ChildJobPosting objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $jobPostings A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildJobSiteRecord The current object (for fluent API support)
     */
    public function setJobPostings(Collection $jobPostings, ConnectionInterface $con = null)
    {
        /** @var ChildJobPosting[] $jobPostingsToDelete */
        $jobPostingsToDelete = $this->getJobPostings(new Criteria(), $con)->diff($jobPostings);


        $this->jobPostingsScheduledForDeletion = $jobPostingsToDelete;

        foreach ($jobPostingsToDelete as $jobPostingRemoved) {
            $jobPostingRemoved->setJobSiteFromJP(null);
        }

        $this->collJobPostings = null;
        foreach ($jobPostings as $jobPosting) {
            $this->addJobPosting($jobPosting);
        }

        $this->collJobPostings = $jobPostings;
        $this->collJobPostingsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related JobPosting objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related JobPosting objects.
     * @throws PropelException
     */
    public function countJobPostings(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collJobPostingsPartial && !$this->isNew();
        if (null === $this->collJobPostings || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collJobPostings) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getJobPostings());
            }

            $query = ChildJobPostingQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByJobSiteFromJP($this)
                ->count($con);
        }

        return count($this->collJobPostings);
    }

    /**
     * Method called to associate a ChildJobPosting object to this object
     * through the ChildJobPosting foreign key attribute.
     *
     * @param  ChildJobPosting $l ChildJobPosting
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     */
    public function addJobPosting(ChildJobPosting $l)
    {
        if ($this->collJobPostings === null) {
            $this->initJobPostings();
            $this->collJobPostingsPartial = true;
        }

        if (!$this->collJobPostings->contains($l)) {
            $this->doAddJobPosting($l);

            if ($this->jobPostingsScheduledForDeletion and $this->jobPostingsScheduledForDeletion->contains($l)) {
                $this->jobPostingsScheduledForDeletion->remove($this->jobPostingsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildJobPosting $jobPosting The ChildJobPosting object to add.
     */
    protected function doAddJobPosting(ChildJobPosting $jobPosting)
    {
        $this->collJobPostings[]= $jobPosting;
        $jobPosting->setJobSiteFromJP($this);
    }

    /**
     * @param  ChildJobPosting $jobPosting The ChildJobPosting object to remove.
     * @return $this|ChildJobSiteRecord The current object (for fluent API support)
     */
    public function removeJobPosting(ChildJobPosting $jobPosting)
    {
        if ($this->getJobPostings()->contains($jobPosting)) {
            $pos = $this->collJobPostings->search($jobPosting);
            $this->collJobPostings->remove($pos);
            if (null === $this->jobPostingsScheduledForDeletion) {
                $this->jobPostingsScheduledForDeletion = clone $this->collJobPostings;
                $this->jobPostingsScheduledForDeletion->clear();
            }
            $this->jobPostingsScheduledForDeletion[]= clone $jobPosting;
            $jobPosting->setJobSiteFromJP(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this JobSiteRecord is new, it will return
     * an empty collection; or if this JobSiteRecord has previously
     * been saved, it will retrieve related JobPostings from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in JobSiteRecord.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildJobPosting[] List of ChildJobPosting objects
     */
    public function getJobPostingsJoinGeoLocationFromJP(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildJobPostingQuery::create(null, $criteria);
        $query->joinWith('GeoLocationFromJP', $joinBehavior);

        return $this->getJobPostings($query, $con);
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this JobSiteRecord is new, it will return
     * an empty collection; or if this JobSiteRecord has previously
     * been saved, it will retrieve related JobPostings from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in JobSiteRecord.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildJobPosting[] List of ChildJobPosting objects
     */
    public function getJobPostingsJoinDuplicateJobPosting(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildJobPostingQuery::create(null, $criteria);
        $query->joinWith('DuplicateJobPosting', $joinBehavior);

        return $this->getJobPostings($query, $con);
    }

    /**
     * Clears out the collUserSearchSiteRuns collection
     *
     * This does not modify the database; however, it will remove any associated objects, causing
     * them to be refetched by subsequent calls to accessor method.
     *
     * @return void
     * @see        addUserSearchSiteRuns()
     */
    public function clearUserSearchSiteRuns()
    {
        $this->collUserSearchSiteRuns = null; // important to set this to NULL since that means it is uninitialized
    }

    /**
     * Reset is the collUserSearchSiteRuns collection loaded partially.
     */
    public function resetPartialUserSearchSiteRuns($v = true)
    {
        $this->collUserSearchSiteRunsPartial = $v;
    }

    /**
     * Initializes the collUserSearchSiteRuns collection.
     *
     * By default this just sets the collUserSearchSiteRuns collection to an empty array (like clearcollUserSearchSiteRuns());
     * however, you may wish to override this method in your stub class to provide setting appropriate
     * to your application -- for example, setting the initial array to the values stored in database.
     *
     * @param      boolean $overrideExisting If set to true, the method call initializes
     *                                        the collection even if it is not empty
     *
     * @return void
     */
    public function initUserSearchSiteRuns($overrideExisting = true)
    {
        if (null !== $this->collUserSearchSiteRuns && !$overrideExisting) {
            return;
        }

        $collectionClassName = UserSearchSiteRunTableMap::getTableMap()->getCollectionClassName();

        $this->collUserSearchSiteRuns = new $collectionClassName;
        $this->collUserSearchSiteRuns->setModel('\JobScooper\DataAccess\UserSearchSiteRun');
    }

    /**
     * Gets an array of ChildUserSearchSiteRun objects which contain a foreign key that references this object.
     *
     * If the $criteria is not null, it is used to always fetch the results from the database.
     * Otherwise the results are fetched from the database the first time, then cached.
     * Next time the same method is called without $criteria, the cached collection is returned.
     * If this ChildJobSiteRecord is new, it will return
     * an empty collection or the current collection; the criteria is ignored on a new object.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @return ObjectCollection|ChildUserSearchSiteRun[] List of ChildUserSearchSiteRun objects
     * @throws PropelException
     */
    public function getUserSearchSiteRuns(Criteria $criteria = null, ConnectionInterface $con = null)
    {
        $partial = $this->collUserSearchSiteRunsPartial && !$this->isNew();
        if (null === $this->collUserSearchSiteRuns || null !== $criteria  || $partial) {
            if ($this->isNew() && null === $this->collUserSearchSiteRuns) {
                // return empty collection
                $this->initUserSearchSiteRuns();
            } else {
                $collUserSearchSiteRuns = ChildUserSearchSiteRunQuery::create(null, $criteria)
                    ->filterByJobSiteFromUSSR($this)
                    ->find($con);

                if (null !== $criteria) {
                    if (false !== $this->collUserSearchSiteRunsPartial && count($collUserSearchSiteRuns)) {
                        $this->initUserSearchSiteRuns(false);

                        foreach ($collUserSearchSiteRuns as $obj) {
                            if (false == $this->collUserSearchSiteRuns->contains($obj)) {
                                $this->collUserSearchSiteRuns->append($obj);
                            }
                        }

                        $this->collUserSearchSiteRunsPartial = true;
                    }

                    return $collUserSearchSiteRuns;
                }

                if ($partial && $this->collUserSearchSiteRuns) {
                    foreach ($this->collUserSearchSiteRuns as $obj) {
                        if ($obj->isNew()) {
                            $collUserSearchSiteRuns[] = $obj;
                        }
                    }
                }

                $this->collUserSearchSiteRuns = $collUserSearchSiteRuns;
                $this->collUserSearchSiteRunsPartial = false;
            }
        }

        return $this->collUserSearchSiteRuns;
    }

    /**
     * Sets a collection of ChildUserSearchSiteRun objects related by a one-to-many relationship
     * to the current object.
     * It will also schedule objects for deletion based on a diff between old objects (aka persisted)
     * and new objects from the given Propel collection.
     *
     * @param      Collection $userSearchSiteRuns A Propel collection.
     * @param      ConnectionInterface $con Optional connection object
     * @return $this|ChildJobSiteRecord The current object (for fluent API support)
     */
    public function setUserSearchSiteRuns(Collection $userSearchSiteRuns, ConnectionInterface $con = null)
    {
        /** @var ChildUserSearchSiteRun[] $userSearchSiteRunsToDelete */
        $userSearchSiteRunsToDelete = $this->getUserSearchSiteRuns(new Criteria(), $con)->diff($userSearchSiteRuns);


        $this->userSearchSiteRunsScheduledForDeletion = $userSearchSiteRunsToDelete;

        foreach ($userSearchSiteRunsToDelete as $userSearchSiteRunRemoved) {
            $userSearchSiteRunRemoved->setJobSiteFromUSSR(null);
        }

        $this->collUserSearchSiteRuns = null;
        foreach ($userSearchSiteRuns as $userSearchSiteRun) {
            $this->addUserSearchSiteRun($userSearchSiteRun);
        }

        $this->collUserSearchSiteRuns = $userSearchSiteRuns;
        $this->collUserSearchSiteRunsPartial = false;

        return $this;
    }

    /**
     * Returns the number of related UserSearchSiteRun objects.
     *
     * @param      Criteria $criteria
     * @param      boolean $distinct
     * @param      ConnectionInterface $con
     * @return int             Count of related UserSearchSiteRun objects.
     * @throws PropelException
     */
    public function countUserSearchSiteRuns(Criteria $criteria = null, $distinct = false, ConnectionInterface $con = null)
    {
        $partial = $this->collUserSearchSiteRunsPartial && !$this->isNew();
        if (null === $this->collUserSearchSiteRuns || null !== $criteria || $partial) {
            if ($this->isNew() && null === $this->collUserSearchSiteRuns) {
                return 0;
            }

            if ($partial && !$criteria) {
                return count($this->getUserSearchSiteRuns());
            }

            $query = ChildUserSearchSiteRunQuery::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->filterByJobSiteFromUSSR($this)
                ->count($con);
        }

        return count($this->collUserSearchSiteRuns);
    }

    /**
     * Method called to associate a ChildUserSearchSiteRun object to this object
     * through the ChildUserSearchSiteRun foreign key attribute.
     *
     * @param  ChildUserSearchSiteRun $l ChildUserSearchSiteRun
     * @return $this|\JobScooper\DataAccess\JobSiteRecord The current object (for fluent API support)
     */
    public function addUserSearchSiteRun(ChildUserSearchSiteRun $l)
    {
        if ($this->collUserSearchSiteRuns === null) {
            $this->initUserSearchSiteRuns();
            $this->collUserSearchSiteRunsPartial = true;
        }

        if (!$this->collUserSearchSiteRuns->contains($l)) {
            $this->doAddUserSearchSiteRun($l);

            if ($this->userSearchSiteRunsScheduledForDeletion and $this->userSearchSiteRunsScheduledForDeletion->contains($l)) {
                $this->userSearchSiteRunsScheduledForDeletion->remove($this->userSearchSiteRunsScheduledForDeletion->search($l));
            }
        }

        return $this;
    }

    /**
     * @param ChildUserSearchSiteRun $userSearchSiteRun The ChildUserSearchSiteRun object to add.
     */
    protected function doAddUserSearchSiteRun(ChildUserSearchSiteRun $userSearchSiteRun)
    {
        $this->collUserSearchSiteRuns[]= $userSearchSiteRun;
        $userSearchSiteRun->setJobSiteFromUSSR($this);
    }

    /**
     * @param  ChildUserSearchSiteRun $userSearchSiteRun The ChildUserSearchSiteRun object to remove.
     * @return $this|ChildJobSiteRecord The current object (for fluent API support)
     */
    public function removeUserSearchSiteRun(ChildUserSearchSiteRun $userSearchSiteRun)
    {
        if ($this->getUserSearchSiteRuns()->contains($userSearchSiteRun)) {
            $pos = $this->collUserSearchSiteRuns->search($userSearchSiteRun);
            $this->collUserSearchSiteRuns->remove($pos);
            if (null === $this->userSearchSiteRunsScheduledForDeletion) {
                $this->userSearchSiteRunsScheduledForDeletion = clone $this->collUserSearchSiteRuns;
                $this->userSearchSiteRunsScheduledForDeletion->clear();
            }
            $this->userSearchSiteRunsScheduledForDeletion[]= clone $userSearchSiteRun;
            $userSearchSiteRun->setJobSiteFromUSSR(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this JobSiteRecord is new, it will return
     * an empty collection; or if this JobSiteRecord has previously
     * been saved, it will retrieve related UserSearchSiteRuns from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in JobSiteRecord.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserSearchSiteRun[] List of ChildUserSearchSiteRun objects
     */
    public function getUserSearchSiteRunsJoinUserSearchPairFromUSSR(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserSearchSiteRunQuery::create(null, $criteria);
        $query->joinWith('UserSearchPairFromUSSR', $joinBehavior);

        return $this->getUserSearchSiteRuns($query, $con);
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        $this->jobsite_key = null;
        $this->plugin_class_name = null;
        $this->display_name = null;
        $this->is_disabled = null;
        $this->results_filter_type = null;
        $this->alreadyInSave = false;
        $this->clearAllReferences();
        $this->applyDefaultValues();
        $this->resetModified();
        $this->setNew(true);
        $this->setDeleted(false);
    }

    /**
     * Resets all references and back-references to other model objects or collections of model objects.
     *
     * This method is used to reset all php object references (not the actual reference in the database).
     * Necessary for object serialisation.
     *
     * @param      boolean $deep Whether to also clear the references on all referrer objects.
     */
    public function clearAllReferences($deep = false)
    {
        if ($deep) {
            if ($this->collJobPostings) {
                foreach ($this->collJobPostings as $o) {
                    $o->clearAllReferences($deep);
                }
            }
            if ($this->collUserSearchSiteRuns) {
                foreach ($this->collUserSearchSiteRuns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collJobPostings = null;
        $this->collUserSearchSiteRuns = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string The value of the 'jobsite_key' column
     */
    public function __toString()
    {
        return (string) $this->getJobSiteKey();
    }

    /**
     * Code to be run before persisting the object
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preSave')) {
            return parent::preSave($con);
        }
        return true;
    }

    /**
     * Code to be run after persisting the object
     * @param ConnectionInterface $con
     */
    public function postSave(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postSave')) {
            parent::postSave($con);
        }
    }

    /**
     * Code to be run before inserting to database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preInsert')) {
            return parent::preInsert($con);
        }
        return true;
    }

    /**
     * Code to be run after inserting to database
     * @param ConnectionInterface $con
     */
    public function postInsert(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postInsert')) {
            parent::postInsert($con);
        }
    }

    /**
     * Code to be run before updating the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preUpdate')) {
            return parent::preUpdate($con);
        }
        return true;
    }

    /**
     * Code to be run after updating the object in database
     * @param ConnectionInterface $con
     */
    public function postUpdate(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postUpdate')) {
            parent::postUpdate($con);
        }
    }

    /**
     * Code to be run before deleting the object in database
     * @param  ConnectionInterface $con
     * @return boolean
     */
    public function preDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::preDelete')) {
            return parent::preDelete($con);
        }
        return true;
    }

    /**
     * Code to be run after deleting the object in database
     * @param ConnectionInterface $con
     */
    public function postDelete(ConnectionInterface $con = null)
    {
        if (is_callable('parent::postDelete')) {
            parent::postDelete($con);
        }
    }


    /**
     * Derived method to catches calls to undefined methods.
     *
     * Provides magic import/export method support (fromXML()/toXML(), fromYAML()/toYAML(), etc.).
     * Allows to define default __call() behavior if you overwrite __call()
     *
     * @param string $name
     * @param mixed  $params
     *
     * @return array|string
     */
    public function __call($name, $params)
    {
        if (0 === strpos($name, 'get')) {
            $virtualColumn = substr($name, 3);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }

            $virtualColumn = lcfirst($virtualColumn);
            if ($this->hasVirtualColumn($virtualColumn)) {
                return $this->getVirtualColumn($virtualColumn);
            }
        }

        if (0 === strpos($name, 'from')) {
            $format = substr($name, 4);

            return $this->importFrom($format, reset($params));
        }

        if (0 === strpos($name, 'to')) {
            $format = substr($name, 2);
            $includeLazyLoadColumns = isset($params[0]) ? $params[0] : true;

            return $this->exportTo($format, $includeLazyLoadColumns);
        }

        throw new BadMethodCallException(sprintf('Call to undefined method: %s.', $name));
    }

}
