<?php

namespace Lazy\Db\Model;

use Lazy\Db\Exception\Exception;
use Lazy\Db\Sql\Select;
use Lazy\Db\Sql\Insert;
use Lazy\Db\Sql\Update;
use Lazy\Db\Sql\Delete;

/**
 * Class AbstractModel
 * @package Lazy\Db\Model
 */
abstract class AbstractModel
{
    /**
     * @var String
     */
    protected static $primaryKey;

    /**
     * @var String
     */
    protected static $tableName;

    /**
     * @var AbstractCollection
     */
    protected static $collectionClass;

    /**
     * @var array
     */
    protected static $columnsSchema;

    /**
     * @var array
     */
    protected static $immediatelySelectColumns;

    /**
     * @var array
     */
    protected static $oneToMany = [];

    /**
     * @var array
     */
    protected static $manyToOne = [];

    /**
     * @var array
     */
    protected static $manyToMany = [];

    /**
     * @var bool
     */
    protected $isExistingRow = false;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @var array
     */
    protected $changedData = [];

    /**
     * @var array
     */
    protected $associationsData = [];

    /**
     * @var \Lazy\Db\Sql\Select
     */
    protected $sqlSelect;

    /**
     * @var \Lazy\Db\Sql\Insert
     */
    protected $sqlInsert;

    /**
     * @var \Lazy\Db\Sql\Update
     */
    protected $sqlUpdate;

    /**
     * @var \Lazy\Db\Sql\Delete
     */
    protected $sqlDelete;

    /**
     * @var AbstractCollection
     */
    protected $collection;

    /**
     * @param array $data
     * @param AbstractCollection $collection
     */
    public function __construct(array $data = [], AbstractCollection $collection = null)
    {
        if (array_key_exists(static::primaryKey(), $data)) {
            $this->isExistingRow = true;
        }
        $this->fromArray($data);
        $this->collection = $collection;
    }

    /**
     * @codeCoverageIgnore
     * return String
     */
    public static function primaryKey()
    {
        return static::$primaryKey;
    }

    /**
     * @codeCoverageIgnore
     * @return String
     */
    public static function tableName()
    {
        return static::$tableName;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function columnSchema()
    {
        return static::$columnsSchema;
    }

    /**
     * @codeCoverageIgnore
     * @return AbstractCollection
     */
    public static function collectionClass()
    {
        return static::$collectionClass;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function oneToMany()
    {
        return static::$oneToMany;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function manyToOne()
    {
        return static::$manyToOne;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function manyToMany()
    {
        return static::$manyToMany;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function immediatelySelectColumns()
    {
        return static::$immediatelySelectColumns;
    }


    /**
     * @return \Lazy\Db\Pdo
     */
    abstract public static function getPdo();

    /**
     * @return Select
     */
    public static function createSqlSelect()
    {
        $select = new Select(static::getPdo(), static::tableName());
        $select->column(static::immediatelySelectColumns());
        return $select;
    }

    /**
     * @param String|array $where
     * @return AbstractCollection
     */
    public static function all($where = null)
    {
        $select = static::createSqlSelect();

        if ($where) {
            $select->where($where);
        }

        $collectionClass = static::collectionClass();
        return new $collectionClass($select);
    }

    /**
     * @param String|int|array $where
     * @return AbstractModel
     */
    public static function first($where = null)
    {
        if (is_numeric($where)) {
            $where = [static::$tableName . '.' . static::$primaryKey => $where];
        }

        $select = static::createSqlSelect();
        if ($where) {
            $select->where($where);
        }

        $select->limit(1);

        $row = $select->fetch(\PDO::FETCH_ASSOC);
        if (!$row) {
            return;
        }

        $class = get_called_class();

        return new $class($row);
    }

    /**
     * @return bool
     */
    public function isNew()
    {
        return !$this->isExistingRow;
    }

    /**
     * @return mixed
     */
    public function id()
    {
        return $this->{static::primaryKey()};
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array_merge($this->data, $this->changedData);
    }

    /**
     * @param array $data
     * @return $this
     */
    public function fromArray(array $data)
    {
        foreach ($data as $columnName => $value) {
            if (array_key_exists($columnName, static::columnSchema())) {
                $this->set($columnName, $value);
            } else {
                if (array_key_exists($columnName, static::oneToMany())) {
                    $refModel = static::oneToMany()[$columnName]['model'];
                }elseif (array_key_exists($columnName, static::manyToMany())) {
                    $refModel = static::manyToMany()[$columnName]['model'];
                }

                if (isset($refModel)) {
                    $collectionClass = $refModel::collectionClass();
                    $this->associationsData[$columnName] = new $collectionClass($value);
                }
            }
        }
        return $this;
    }

    /**
     * @param String $name
     * @param mixed $value
     * @return $this
     * @throws \Lazy\Db\Exception\Exception
     */
    public function set($name, $value)
    {
        $nameUnderscore = $this->underscore($name);

        if (array_key_exists($nameUnderscore, static::columnSchema())) {
            switch (static::columnSchema()[$nameUnderscore]['type']) {
                case 'int':
                case 'tinyint':
                    $value = (int) $value;
                    break;
            }

            if ($this->isExistingRow) {
                $this->changedData[$nameUnderscore] = $value;
            } else {
                $this->data[$nameUnderscore] = $value;
            }
            return $this;
        }

        if(array_key_exists($name, array_merge(static::oneToMany(), static::manyToOne(), static::manyToMany()))) {
            $this->associationsData[$name] = $value;
            return $this;
        }

        throw new Exception("Call undefined property $name");

    }

    /**
     * @param String $name
     * @param mixed $value
     * @return $this
     */
    public function __set($name, $value)
    {
        return $this->set($name, $value);
    }

    /**
     * @param String $name
     * @return mixed
     * @throws \Lazy\Db\Exception\Exception
     */
    public function __get($name)
    {
        $nameUnderscore = $this->underscore($name);

        if (array_key_exists($nameUnderscore, $this->changedData)) {
            return $this->changedData[$nameUnderscore];
        }

        if (array_key_exists($nameUnderscore, $this->data)) {
            return $this->data[$nameUnderscore];
        }

        if (array_key_exists($name, $this->associationsData)) {
            return $this->associationsData[$name];
        }

        # check lazy load
        if (array_key_exists($nameUnderscore, static::$columnsSchema)) {
            $select = static::createSqlSelect()->resetColumn();

            if ($this->collection) {
                $primaryKey = static::$primaryKey;
                $collectionIds = $this->collection->fetchColumn(static::$primaryKey);
                $select->column([static::$primaryKey, $nameUnderscore])->where(["$primaryKey IN(?)" => $collectionIds]);
                $pair = $select->fetchAll(\PDO::FETCH_KEY_PAIR);
                foreach ($pair as $id => $value) {
                    $this->collection->get($id)->set($nameUnderscore, $value);
                }
                return $pair[$this->id()];
            } else {
                $select->column($nameUnderscore)->where([static::$primaryKey => $this->id()])->limit(1);
                $value = $select->fetchColumn();
                $this->set($nameUnderscore, $value);
                return $value;
            }

        }

        # get from associations
        # one to many
        if (array_key_exists($name, static::$oneToMany)) {
            $refModel = static::$oneToMany[$name]['model'];
            $foreignKey = static::$oneToMany[$name]['key'];

            if ($this->collection) {
                $collectionIds = $this->collection->fetchColumn(static::$primaryKey);
                $rows = $refModel::createSqlSelect()->where(["$foreignKey IN(?)" => $collectionIds])->fetchAll(\PDO::FETCH_BOTH);

                $associationData = [];
                foreach ($rows as $row) {
                    isset($associationData[$row[$foreignKey]]) || $associationData[$row[$foreignKey]] = [];
                    $associationData[$row[$foreignKey]][] = $row;
                }

                foreach ($associationData as $id => $rows) {
                    $collectionClass = $refModel::collectionClass();
                    $this->collection->get($id)->set($name, new $collectionClass($rows));
                }

                return $this->associationsData[$name];
            } else {
                $collections = $refModel::all([$foreignKey => $this->id()]);
                $this->set($name, $collections);
                return $collections;
            }
        }

        # many to one
        if (array_key_exists($name, static::$manyToOne)) {
            $refModel = static::$manyToOne[$name]['model'];
            $foreignKey = static::$manyToOne[$name]['key'];
            $model = $refModel::first($this->$foreignKey);
            $this->associationsData[$name] = $model;
            return $model;
        }

        # many to many
        if (array_key_exists($name, static::$manyToMany)) {
            $refModel = static::$manyToMany[$name]['model'];
            $refModelPrimaryKey = $refModel::primaryKey();
            $refModelTableName = $refModel::tableName();
            $throughModel = static::$manyToMany[$name]['throughModel'];
            $leftKey = static::$manyToMany[$name]['leftKey'];
            $rightKey = static::$manyToMany[$name]['rightKey'];

            $throughTableName = $throughModel::tableName();

            if ($this->collection) {
                $collectionIds = $this->collection->fetchColumn(static::$primaryKey);
                $rows = $refModel::createSqlSelect()->column("$throughTableName.$leftKey")
                    ->join($throughTableName, "$throughTableName.$rightKey = $refModelTableName.$refModelPrimaryKey")
                    ->where(["$throughTableName.$leftKey IN(?)" => $collectionIds])
                    ->fetchAll(\PDO::FETCH_BOTH);

                $associationData = [];
                foreach ($rows as $row) {
                    isset($associationData[$row[$leftKey]]) || $associationData[$row[$leftKey]] = [];
                    $associationData[$row[$leftKey]][] = $row;
                }

                foreach ($associationData as $id => $rows) {
                    $collectionClass = $refModel::collectionClass();
                    $this->collection->get($id)->set($name, new $collectionClass($rows));
                }

                return $this->associationsData[$name];
            } else {
                $collections = $refModel::all()->join($throughTableName, "$throughTableName.$rightKey = $refModelTableName.$refModelPrimaryKey")
                    ->where(["$throughTableName.$leftKey" => $this->id()]);
                $this->set($name, $collections);
                return $collections;
            }
        }

        if (!array_key_exists($nameUnderscore, static::$columnsSchema)) {
            throw new Exception("Call undefined property $name");
        }

    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->changedData = [];
        return $this;
    }

    /**
     * @return $this
     */
    public function save()
    {
        $primaryKey = static::$primaryKey;

        if ($this->isExistingRow) {
            if (!$this->changedData) {
                return $this;
            }
            $this->sqlUpdate()->data($this->changedData)->exec();
            $this->data = array_merge($this->data, $this->changedData);
            $this->reset();
        } else {
            $this->sqlInsert()->value($this->data)->exec();
            $this->set($primaryKey, $this->getPdo()->lastInsertId());
            $this->isExistingRow = true;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        if ($this->id()) {
            static::getPdo()->exec($this->sqlDelete());
        }
        return $this;
    }

    /**
     * @return Select
     */
    protected function sqlSelect()
    {
        if (!$this->sqlSelect) {
            $this->sqlSelect = static::createSqlSelect();
            $this->sqlSelect->where(['id' => $this->id()]);
        }

        return $this->sqlSelect;
    }

    /**
     * @return Insert
     */
    protected function sqlInsert()
    {
        if (!$this->sqlInsert) {
            $this->sqlInsert = new Insert(static::getPdo(), static::$tableName);
        }
        return $this->sqlInsert;
    }

    /**
     * @return Update
     */
    protected function sqlUpdate()
    {
        if (!$this->sqlUpdate) {
            $this->sqlUpdate = new Update(static::getPdo(), static::$tableName);
            $this->sqlUpdate->where([static::$primaryKey => $this->id()]);
        }
        return $this->sqlUpdate;
    }

    /**
     * @return Delete
     */
    protected function sqlDelete()
    {
        if (!$this->sqlDelete) {
            $this->sqlDelete = new Delete(static::getPdo(), static::$tableName);
            $this->sqlDelete->where([static::$primaryKey => $this->id()]);
        }
        return $this->sqlDelete;
    }

    /**
     * @return $this
     */
    public function refresh()
    {
        if ($this->isExistingRow) {
            $row = $this->sqlSelect()->fetch(\PDO::FETCH_ASSOC);
            $this->fromArray($row);
        }

        return $this;
    }

    /**
     * @param String $str
     * @return string
     */
    protected function underscore($str)
    {
        return strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $str));
    }
}