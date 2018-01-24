<?php

namespace JobScooper\DataAccess\Base;

use \Exception;
use \PDO;
use JobScooper\DataAccess\GeoLocation as ChildGeoLocation;
use JobScooper\DataAccess\GeoLocationQuery as ChildGeoLocationQuery;
use JobScooper\DataAccess\User as ChildUser;
use JobScooper\DataAccess\UserQuery as ChildUserQuery;
use JobScooper\DataAccess\UserSearchPair as ChildUserSearchPair;
use JobScooper\DataAccess\UserSearchPairQuery as ChildUserSearchPairQuery;
use JobScooper\DataAccess\UserSearchSiteRun as ChildUserSearchSiteRun;
use JobScooper\DataAccess\UserSearchSiteRunQuery as ChildUserSearchSiteRunQuery;
use JobScooper\DataAccess\Map\UserSearchPairTableMap;
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
 * Base class that represents a row from the 'user_search_pair' table.
 *
 *
 *
 * @package    propel.generator.JobScooper.DataAccess.Base
 */
abstract class UserSearchPair implements ActiveRecordInterface
{
    /**
     * TableMap class name
     */
    const TABLE_MAP = '\\JobScooper\\DataAccess\\Map\\UserSearchPairTableMap';


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
     * The value for the user_search_pair_id field.
     *
     * @var        int
     */
    protected $user_search_pair_id;

    /**
     * The value for the user_id field.
     *
     * @var        int
     */
    protected $user_id;

    /**
     * The value for the user_keyword field.
     *
     * @var        string
     */
    protected $user_keyword;

    /**
     * The value for the geolocation_id field.
     *
     * @var        int
     */
    protected $geolocation_id;

    /**
     * The value for the is_active field.
     *
     * Note: this column has a database default value of: true
     * @var        boolean
     */
    protected $is_active;

    /**
     * @var        ChildUser
     */
    protected $aUserFromUS;

    /**
     * @var        ChildGeoLocation
     */
    protected $aGeoLocationFromUS;

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
        $this->is_active = true;
    }

    /**
     * Initializes internal state of JobScooper\DataAccess\Base\UserSearchPair object.
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
     * Compares this with another <code>UserSearchPair</code> instance.  If
     * <code>obj</code> is an instance of <code>UserSearchPair</code>, delegates to
     * <code>equals(UserSearchPair)</code>.  Otherwise, returns <code>false</code>.
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
     * @return $this|UserSearchPair The current object, for fluid interface
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
     * Get the [user_search_pair_id] column value.
     *
     * @return int
     */
    public function getUserSearchPairId()
    {
        return $this->user_search_pair_id;
    }

    /**
     * Get the [user_id] column value.
     *
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Get the [user_keyword] column value.
     *
     * @return string
     */
    public function getUserKeyword()
    {
        return $this->user_keyword;
    }

    /**
     * Get the [geolocation_id] column value.
     *
     * @return int
     */
    public function getGeoLocationId()
    {
        return $this->geolocation_id;
    }

    /**
     * Get the [is_active] column value.
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Get the [is_active] column value.
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * Set the value of [user_search_pair_id] column.
     *
     * @param int $v new value
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     */
    public function setUserSearchPairId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->user_search_pair_id !== $v) {
            $this->user_search_pair_id = $v;
            $this->modifiedColumns[UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID] = true;
        }

        return $this;
    } // setUserSearchPairId()

    /**
     * Set the value of [user_id] column.
     *
     * @param int $v new value
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     */
    public function setUserId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->user_id !== $v) {
            $this->user_id = $v;
            $this->modifiedColumns[UserSearchPairTableMap::COL_USER_ID] = true;
        }

        if ($this->aUserFromUS !== null && $this->aUserFromUS->getUserId() !== $v) {
            $this->aUserFromUS = null;
        }

        return $this;
    } // setUserId()

    /**
     * Set the value of [user_keyword] column.
     *
     * @param string $v new value
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     */
    public function setUserKeyword($v)
    {
        if ($v !== null) {
            $v = (string) $v;
        }

        if ($this->user_keyword !== $v) {
            $this->user_keyword = $v;
            $this->modifiedColumns[UserSearchPairTableMap::COL_USER_KEYWORD] = true;
        }

        return $this;
    } // setUserKeyword()

    /**
     * Set the value of [geolocation_id] column.
     *
     * @param int $v new value
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     */
    public function setGeoLocationId($v)
    {
        if ($v !== null) {
            $v = (int) $v;
        }

        if ($this->geolocation_id !== $v) {
            $this->geolocation_id = $v;
            $this->modifiedColumns[UserSearchPairTableMap::COL_GEOLOCATION_ID] = true;
        }

        if ($this->aGeoLocationFromUS !== null && $this->aGeoLocationFromUS->getGeoLocationId() !== $v) {
            $this->aGeoLocationFromUS = null;
        }

        return $this;
    } // setGeoLocationId()

    /**
     * Sets the value of the [is_active] column.
     * Non-boolean arguments are converted using the following rules:
     *   * 1, '1', 'true',  'on',  and 'yes' are converted to boolean true
     *   * 0, '0', 'false', 'off', and 'no'  are converted to boolean false
     * Check on string values is case insensitive (so 'FaLsE' is seen as 'false').
     *
     * @param  boolean|integer|string $v The new value
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     */
    public function setIsActive($v)
    {
        if ($v !== null) {
            if (is_string($v)) {
                $v = in_array(strtolower($v), array('false', 'off', '-', 'no', 'n', '0', '')) ? false : true;
            } else {
                $v = (boolean) $v;
            }
        }

        if ($this->is_active !== $v) {
            $this->is_active = $v;
            $this->modifiedColumns[UserSearchPairTableMap::COL_IS_ACTIVE] = true;
        }

        return $this;
    } // setIsActive()

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
            if ($this->is_active !== true) {
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

            $col = $row[TableMap::TYPE_NUM == $indexType ? 0 + $startcol : UserSearchPairTableMap::translateFieldName('UserSearchPairId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->user_search_pair_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 1 + $startcol : UserSearchPairTableMap::translateFieldName('UserId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->user_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 2 + $startcol : UserSearchPairTableMap::translateFieldName('UserKeyword', TableMap::TYPE_PHPNAME, $indexType)];
            $this->user_keyword = (null !== $col) ? (string) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 3 + $startcol : UserSearchPairTableMap::translateFieldName('GeoLocationId', TableMap::TYPE_PHPNAME, $indexType)];
            $this->geolocation_id = (null !== $col) ? (int) $col : null;

            $col = $row[TableMap::TYPE_NUM == $indexType ? 4 + $startcol : UserSearchPairTableMap::translateFieldName('IsActive', TableMap::TYPE_PHPNAME, $indexType)];
            $this->is_active = (null !== $col) ? (boolean) $col : null;
            $this->resetModified();

            $this->setNew(false);

            if ($rehydrate) {
                $this->ensureConsistency();
            }

            return $startcol + 5; // 5 = UserSearchPairTableMap::NUM_HYDRATE_COLUMNS.

        } catch (Exception $e) {
            throw new PropelException(sprintf('Error populating %s object', '\\JobScooper\\DataAccess\\UserSearchPair'), 0, $e);
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
        if ($this->aUserFromUS !== null && $this->user_id !== $this->aUserFromUS->getUserId()) {
            $this->aUserFromUS = null;
        }
        if ($this->aGeoLocationFromUS !== null && $this->geolocation_id !== $this->aGeoLocationFromUS->getGeoLocationId()) {
            $this->aGeoLocationFromUS = null;
        }
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
            $con = Propel::getServiceContainer()->getReadConnection(UserSearchPairTableMap::DATABASE_NAME);
        }

        // We don't need to alter the object instance pool; we're just modifying this instance
        // already in the pool.

        $dataFetcher = ChildUserSearchPairQuery::create(null, $this->buildPkeyCriteria())->setFormatter(ModelCriteria::FORMAT_STATEMENT)->find($con);
        $row = $dataFetcher->fetch();
        $dataFetcher->close();
        if (!$row) {
            throw new PropelException('Cannot find matching row in the database to reload object values.');
        }
        $this->hydrate($row, 0, true, $dataFetcher->getIndexType()); // rehydrate

        if ($deep) {  // also de-associate any related objects?

            $this->aUserFromUS = null;
            $this->aGeoLocationFromUS = null;
            $this->collUserSearchSiteRuns = null;

        } // if (deep)
    }

    /**
     * Removes this object from datastore and sets delete attribute.
     *
     * @param      ConnectionInterface $con
     * @return void
     * @throws PropelException
     * @see UserSearchPair::setDeleted()
     * @see UserSearchPair::isDeleted()
     */
    public function delete(ConnectionInterface $con = null)
    {
        if ($this->isDeleted()) {
            throw new PropelException("This object has already been deleted.");
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserSearchPairTableMap::DATABASE_NAME);
        }

        $con->transaction(function () use ($con) {
            $deleteQuery = ChildUserSearchPairQuery::create()
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
     * Since this table was configured to reload rows on update, the object will
     * be reloaded from the database if an UPDATE operation is performed (unless
     * the $skipReload parameter is TRUE).
     *
     * @param      ConnectionInterface $con
     * @param      boolean $skipReload Whether to skip the reload for this object from database.
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see doSave()
     */
    public function save(ConnectionInterface $con = null, $skipReload = false)
    {
        if ($this->isDeleted()) {
            throw new PropelException("You cannot save an object that has been deleted.");
        }

        if ($this->alreadyInSave) {
            return 0;
        }

        if ($con === null) {
            $con = Propel::getServiceContainer()->getWriteConnection(UserSearchPairTableMap::DATABASE_NAME);
        }

        return $con->transaction(function () use ($con, $skipReload) {
            $ret = $this->preSave($con);
            $isInsert = $this->isNew();
            if ($isInsert) {
                $ret = $ret && $this->preInsert($con);
            } else {
                $ret = $ret && $this->preUpdate($con);
            }
            if ($ret) {
                $affectedRows = $this->doSave($con, $skipReload);
                if ($isInsert) {
                    $this->postInsert($con);
                } else {
                    $this->postUpdate($con);
                }
                $this->postSave($con);
                UserSearchPairTableMap::addInstanceToPool($this);
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
     * @param      boolean $skipReload Whether to skip the reload for this object from database.
     * @return int             The number of rows affected by this insert/update and any referring fk objects' save() operations.
     * @throws PropelException
     * @see save()
     */
    protected function doSave(ConnectionInterface $con, $skipReload = false)
    {
        $affectedRows = 0; // initialize var to track total num of affected rows
        if (!$this->alreadyInSave) {
            $this->alreadyInSave = true;

            $reloadObject = false;

            // We call the save method on the following object(s) if they
            // were passed to this object by their corresponding set
            // method.  This object relates to these object(s) by a
            // foreign key reference.

            if ($this->aUserFromUS !== null) {
                if ($this->aUserFromUS->isModified() || $this->aUserFromUS->isNew()) {
                    $affectedRows += $this->aUserFromUS->save($con);
                }
                $this->setUserFromUS($this->aUserFromUS);
            }

            if ($this->aGeoLocationFromUS !== null) {
                if ($this->aGeoLocationFromUS->isModified() || $this->aGeoLocationFromUS->isNew()) {
                    $affectedRows += $this->aGeoLocationFromUS->save($con);
                }
                $this->setGeoLocationFromUS($this->aGeoLocationFromUS);
            }

            if ($this->isNew() || $this->isModified()) {
                // persist changes
                if ($this->isNew()) {
                    $this->doInsert($con);
                    $affectedRows += 1;
                } else {
                    $affectedRows += $this->doUpdate($con);
                    if (!$skipReload) {
                        $reloadObject = true;
                    }
                }
                $this->resetModified();
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

            if ($reloadObject) {
                $this->reload($con);
            }

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

        $this->modifiedColumns[UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID] = true;
        if (null !== $this->user_search_pair_id) {
            throw new PropelException('Cannot insert a value for auto-increment primary key (' . UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID . ')');
        }

         // check the columns in natural order for more readable SQL queries
        if ($this->isColumnModified(UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID)) {
            $modifiedColumns[':p' . $index++]  = 'user_search_pair_id';
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_USER_ID)) {
            $modifiedColumns[':p' . $index++]  = 'user_id';
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_USER_KEYWORD)) {
            $modifiedColumns[':p' . $index++]  = 'user_keyword';
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_GEOLOCATION_ID)) {
            $modifiedColumns[':p' . $index++]  = 'geolocation_id';
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_IS_ACTIVE)) {
            $modifiedColumns[':p' . $index++]  = 'is_active';
        }

        $sql = sprintf(
            'INSERT INTO user_search_pair (%s) VALUES (%s)',
            implode(', ', $modifiedColumns),
            implode(', ', array_keys($modifiedColumns))
        );

        try {
            $stmt = $con->prepare($sql);
            foreach ($modifiedColumns as $identifier => $columnName) {
                switch ($columnName) {
                    case 'user_search_pair_id':
                        $stmt->bindValue($identifier, $this->user_search_pair_id, PDO::PARAM_INT);
                        break;
                    case 'user_id':
                        $stmt->bindValue($identifier, $this->user_id, PDO::PARAM_INT);
                        break;
                    case 'user_keyword':
                        $stmt->bindValue($identifier, $this->user_keyword, PDO::PARAM_STR);
                        break;
                    case 'geolocation_id':
                        $stmt->bindValue($identifier, $this->geolocation_id, PDO::PARAM_INT);
                        break;
                    case 'is_active':
                        $stmt->bindValue($identifier, (int) $this->is_active, PDO::PARAM_INT);
                        break;
                }
            }
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute INSERT statement [%s]', $sql), 0, $e);
        }

        try {
            $pk = $con->lastInsertId();
        } catch (Exception $e) {
            throw new PropelException('Unable to get autoincrement id.', 0, $e);
        }
        $this->setUserSearchPairId($pk);

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
        $pos = UserSearchPairTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);
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
                return $this->getUserSearchPairId();
                break;
            case 1:
                return $this->getUserId();
                break;
            case 2:
                return $this->getUserKeyword();
                break;
            case 3:
                return $this->getGeoLocationId();
                break;
            case 4:
                return $this->getIsActive();
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

        if (isset($alreadyDumpedObjects['UserSearchPair'][$this->hashCode()])) {
            return '*RECURSION*';
        }
        $alreadyDumpedObjects['UserSearchPair'][$this->hashCode()] = true;
        $keys = UserSearchPairTableMap::getFieldNames($keyType);
        $result = array(
            $keys[0] => $this->getUserSearchPairId(),
            $keys[1] => $this->getUserId(),
            $keys[2] => $this->getUserKeyword(),
            $keys[3] => $this->getGeoLocationId(),
            $keys[4] => $this->getIsActive(),
        );
        $virtualColumns = $this->virtualColumns;
        foreach ($virtualColumns as $key => $virtualColumn) {
            $result[$key] = $virtualColumn;
        }

        if ($includeForeignObjects) {
            if (null !== $this->aUserFromUS) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'user';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'user';
                        break;
                    default:
                        $key = 'UserFromUS';
                }

                $result[$key] = $this->aUserFromUS->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
            }
            if (null !== $this->aGeoLocationFromUS) {

                switch ($keyType) {
                    case TableMap::TYPE_CAMELNAME:
                        $key = 'geoLocation';
                        break;
                    case TableMap::TYPE_FIELDNAME:
                        $key = 'geolocation';
                        break;
                    default:
                        $key = 'GeoLocationFromUS';
                }

                $result[$key] = $this->aGeoLocationFromUS->toArray($keyType, $includeLazyLoadColumns,  $alreadyDumpedObjects, true);
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
     * @return $this|\JobScooper\DataAccess\UserSearchPair
     */
    public function setByName($name, $value, $type = TableMap::TYPE_PHPNAME)
    {
        $pos = UserSearchPairTableMap::translateFieldName($name, $type, TableMap::TYPE_NUM);

        return $this->setByPosition($pos, $value);
    }

    /**
     * Sets a field from the object by Position as specified in the xml schema.
     * Zero-based.
     *
     * @param  int $pos position in xml schema
     * @param  mixed $value field value
     * @return $this|\JobScooper\DataAccess\UserSearchPair
     */
    public function setByPosition($pos, $value)
    {
        switch ($pos) {
            case 0:
                $this->setUserSearchPairId($value);
                break;
            case 1:
                $this->setUserId($value);
                break;
            case 2:
                $this->setUserKeyword($value);
                break;
            case 3:
                $this->setGeoLocationId($value);
                break;
            case 4:
                $this->setIsActive($value);
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
        $keys = UserSearchPairTableMap::getFieldNames($keyType);

        if (array_key_exists($keys[0], $arr)) {
            $this->setUserSearchPairId($arr[$keys[0]]);
        }
        if (array_key_exists($keys[1], $arr)) {
            $this->setUserId($arr[$keys[1]]);
        }
        if (array_key_exists($keys[2], $arr)) {
            $this->setUserKeyword($arr[$keys[2]]);
        }
        if (array_key_exists($keys[3], $arr)) {
            $this->setGeoLocationId($arr[$keys[3]]);
        }
        if (array_key_exists($keys[4], $arr)) {
            $this->setIsActive($arr[$keys[4]]);
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
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object, for fluid interface
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
        $criteria = new Criteria(UserSearchPairTableMap::DATABASE_NAME);

        if ($this->isColumnModified(UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID)) {
            $criteria->add(UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID, $this->user_search_pair_id);
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_USER_ID)) {
            $criteria->add(UserSearchPairTableMap::COL_USER_ID, $this->user_id);
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_USER_KEYWORD)) {
            $criteria->add(UserSearchPairTableMap::COL_USER_KEYWORD, $this->user_keyword);
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_GEOLOCATION_ID)) {
            $criteria->add(UserSearchPairTableMap::COL_GEOLOCATION_ID, $this->geolocation_id);
        }
        if ($this->isColumnModified(UserSearchPairTableMap::COL_IS_ACTIVE)) {
            $criteria->add(UserSearchPairTableMap::COL_IS_ACTIVE, $this->is_active);
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
        $criteria = ChildUserSearchPairQuery::create();
        $criteria->add(UserSearchPairTableMap::COL_USER_SEARCH_PAIR_ID, $this->user_search_pair_id);

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
        $validPk = null !== $this->getUserSearchPairId();

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
     * @return int
     */
    public function getPrimaryKey()
    {
        return $this->getUserSearchPairId();
    }

    /**
     * Generic method to set the primary key (user_search_pair_id column).
     *
     * @param       int $key Primary key.
     * @return void
     */
    public function setPrimaryKey($key)
    {
        $this->setUserSearchPairId($key);
    }

    /**
     * Returns true if the primary key for this object is null.
     * @return boolean
     */
    public function isPrimaryKeyNull()
    {
        return null === $this->getUserSearchPairId();
    }

    /**
     * Sets contents of passed object to values from current object.
     *
     * If desired, this method can also make copies of all associated (fkey referrers)
     * objects.
     *
     * @param      object $copyObj An object of \JobScooper\DataAccess\UserSearchPair (or compatible) type.
     * @param      boolean $deepCopy Whether to also copy all rows that refer (by fkey) to the current row.
     * @param      boolean $makeNew Whether to reset autoincrement PKs and make the object new.
     * @throws PropelException
     */
    public function copyInto($copyObj, $deepCopy = false, $makeNew = true)
    {
        $copyObj->setUserId($this->getUserId());
        $copyObj->setUserKeyword($this->getUserKeyword());
        $copyObj->setGeoLocationId($this->getGeoLocationId());
        $copyObj->setIsActive($this->getIsActive());

        if ($deepCopy) {
            // important: temporarily setNew(false) because this affects the behavior of
            // the getter/setter methods for fkey referrer objects.
            $copyObj->setNew(false);

            foreach ($this->getUserSearchSiteRuns() as $relObj) {
                if ($relObj !== $this) {  // ensure that we don't try to copy a reference to ourselves
                    $copyObj->addUserSearchSiteRun($relObj->copy($deepCopy));
                }
            }

        } // if ($deepCopy)

        if ($makeNew) {
            $copyObj->setNew(true);
            $copyObj->setUserSearchPairId(NULL); // this is a auto-increment column, so set to default value
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
     * @return \JobScooper\DataAccess\UserSearchPair Clone of current object.
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
     * Declares an association between this object and a ChildUser object.
     *
     * @param  ChildUser $v
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     * @throws PropelException
     */
    public function setUserFromUS(ChildUser $v = null)
    {
        if ($v === null) {
            $this->setUserId(NULL);
        } else {
            $this->setUserId($v->getUserId());
        }

        $this->aUserFromUS = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildUser object, it will not be re-added.
        if ($v !== null) {
            $v->addUserSearchPair($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildUser object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildUser The associated ChildUser object.
     * @throws PropelException
     */
    public function getUserFromUS(ConnectionInterface $con = null)
    {
        if ($this->aUserFromUS === null && ($this->user_id != 0)) {
            $this->aUserFromUS = ChildUserQuery::create()->findPk($this->user_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aUserFromUS->addUserSearchPairs($this);
             */
        }

        return $this->aUserFromUS;
    }

    /**
     * Declares an association between this object and a ChildGeoLocation object.
     *
     * @param  ChildGeoLocation $v
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
     * @throws PropelException
     */
    public function setGeoLocationFromUS(ChildGeoLocation $v = null)
    {
        if ($v === null) {
            $this->setGeoLocationId(NULL);
        } else {
            $this->setGeoLocationId($v->getGeoLocationId());
        }

        $this->aGeoLocationFromUS = $v;

        // Add binding for other direction of this n:n relationship.
        // If this object has already been added to the ChildGeoLocation object, it will not be re-added.
        if ($v !== null) {
            $v->addUserSearchPair($this);
        }


        return $this;
    }


    /**
     * Get the associated ChildGeoLocation object
     *
     * @param  ConnectionInterface $con Optional Connection object.
     * @return ChildGeoLocation The associated ChildGeoLocation object.
     * @throws PropelException
     */
    public function getGeoLocationFromUS(ConnectionInterface $con = null)
    {
        if ($this->aGeoLocationFromUS === null && ($this->geolocation_id != 0)) {
            $this->aGeoLocationFromUS = ChildGeoLocationQuery::create()->findPk($this->geolocation_id, $con);
            /* The following can be used additionally to
                guarantee the related object contains a reference
                to this object.  This level of coupling may, however, be
                undesirable since it could result in an only partially populated collection
                in the referenced object.
                $this->aGeoLocationFromUS->addUserSearchPairs($this);
             */
        }

        return $this->aGeoLocationFromUS;
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
        if ('UserSearchSiteRun' == $relationName) {
            $this->initUserSearchSiteRuns();
            return;
        }
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
     * If this ChildUserSearchPair is new, it will return
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
                    ->filterByUserSearchPairFromUSSR($this)
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
     * @return $this|ChildUserSearchPair The current object (for fluent API support)
     */
    public function setUserSearchSiteRuns(Collection $userSearchSiteRuns, ConnectionInterface $con = null)
    {
        /** @var ChildUserSearchSiteRun[] $userSearchSiteRunsToDelete */
        $userSearchSiteRunsToDelete = $this->getUserSearchSiteRuns(new Criteria(), $con)->diff($userSearchSiteRuns);


        $this->userSearchSiteRunsScheduledForDeletion = $userSearchSiteRunsToDelete;

        foreach ($userSearchSiteRunsToDelete as $userSearchSiteRunRemoved) {
            $userSearchSiteRunRemoved->setUserSearchPairFromUSSR(null);
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
                ->filterByUserSearchPairFromUSSR($this)
                ->count($con);
        }

        return count($this->collUserSearchSiteRuns);
    }

    /**
     * Method called to associate a ChildUserSearchSiteRun object to this object
     * through the ChildUserSearchSiteRun foreign key attribute.
     *
     * @param  ChildUserSearchSiteRun $l ChildUserSearchSiteRun
     * @return $this|\JobScooper\DataAccess\UserSearchPair The current object (for fluent API support)
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
        $userSearchSiteRun->setUserSearchPairFromUSSR($this);
    }

    /**
     * @param  ChildUserSearchSiteRun $userSearchSiteRun The ChildUserSearchSiteRun object to remove.
     * @return $this|ChildUserSearchPair The current object (for fluent API support)
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
            $userSearchSiteRun->setUserSearchPairFromUSSR(null);
        }

        return $this;
    }


    /**
     * If this collection has already been initialized with
     * an identical criteria, it returns the collection.
     * Otherwise if this UserSearchPair is new, it will return
     * an empty collection; or if this UserSearchPair has previously
     * been saved, it will retrieve related UserSearchSiteRuns from storage.
     *
     * This method is protected by default in order to keep the public
     * api reasonable.  You can provide public methods for those you
     * actually need in UserSearchPair.
     *
     * @param      Criteria $criteria optional Criteria object to narrow the query
     * @param      ConnectionInterface $con optional connection object
     * @param      string $joinBehavior optional join type to use (defaults to Criteria::LEFT_JOIN)
     * @return ObjectCollection|ChildUserSearchSiteRun[] List of ChildUserSearchSiteRun objects
     */
    public function getUserSearchSiteRunsJoinJobSiteFromUSSR(Criteria $criteria = null, ConnectionInterface $con = null, $joinBehavior = Criteria::LEFT_JOIN)
    {
        $query = ChildUserSearchSiteRunQuery::create(null, $criteria);
        $query->joinWith('JobSiteFromUSSR', $joinBehavior);

        return $this->getUserSearchSiteRuns($query, $con);
    }

    /**
     * Clears the current object, sets all attributes to their default values and removes
     * outgoing references as well as back-references (from other objects to this one. Results probably in a database
     * change of those foreign objects when you call `save` there).
     */
    public function clear()
    {
        if (null !== $this->aUserFromUS) {
            $this->aUserFromUS->removeUserSearchPair($this);
        }
        if (null !== $this->aGeoLocationFromUS) {
            $this->aGeoLocationFromUS->removeUserSearchPair($this);
        }
        $this->user_search_pair_id = null;
        $this->user_id = null;
        $this->user_keyword = null;
        $this->geolocation_id = null;
        $this->is_active = null;
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
            if ($this->collUserSearchSiteRuns) {
                foreach ($this->collUserSearchSiteRuns as $o) {
                    $o->clearAllReferences($deep);
                }
            }
        } // if ($deep)

        $this->collUserSearchSiteRuns = null;
        $this->aUserFromUS = null;
        $this->aGeoLocationFromUS = null;
    }

    /**
     * Return the string representation of this object
     *
     * @return string
     */
    public function __toString()
    {
        return (string) $this->exportTo(UserSearchPairTableMap::DEFAULT_STRING_FORMAT);
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
