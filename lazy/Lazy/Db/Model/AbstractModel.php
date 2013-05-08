<?php

namespace Lazy\Db\Model;

use Lazy\Db\Exception\Exception;
use Lazy\Db\Pdo;
use Lazy\Db\Sql\Select;
use Lazy\Db\Sql\Insert;
use Lazy\Db\Sql\Update;
use Lazy\Db\Sql\Delete;
use Lazy\Db\Expr;
use Lazy\Db\Inflector;

/**
 * Class AbstractModel
 * @package Lazy\Db\Model
 */
abstract class AbstractModel
{
    /**
     * @var String
     */
    protected static $primaryKey = 'id';

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
    protected static $defaultColumnsSchema = array(
        'int'           => array('length' => 11, 'nullable' => false, 'unsigned' => true),
        'tinyint'       => array('length' => 1, 'nullable' => false, 'default' => 0),
        'varchar'       => array('length' => 255, 'nullable' => false),
        'text'          => array('nullable' => false),
        'mediumtext'    => array('nullable' => false),
        'longtext'      => array('nullable' => false),
        'date'          => array('nullable' => false),
        'time'          => array('nullable' => false),
        'datetime'      => array('nullable' => false),
        'timestamp'     => array('nullable' => false, 'onUpdateCurrentTimeStamp' => true),
    );

    /**
     * @var Expr
     */
    protected static $exprNow;

    /**
     * @var array
     */
    protected static $columnsSchema;

    /**
     * @var array
     */
    protected static $defaultImmediatelySelectColumnTypes = array(
        'int', 'tinyint', 'varchar', 'float', 'date', 'time', 'datetime', 'timestamp');

    /**
     * @var array
     */
    protected static $immediatelySelectColumns;

    /**
     * @var array
     */
    protected static $oneToMany = array();

    /**
     * @var array
     */
    protected static $manyToOne = array();

    /**
     * @var array
     */
    protected static $manyToMany = array();

    /**
     * @var bool
     */
    protected $isExistingRow = false;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var array
     */
    protected $changedData = array();

    /**
     * @var array
     */
    protected $associationsData = array();

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
     * @var Pdo
     */
    protected static $pdo;

    /**
     * @var bool
     */
    protected $initialize;

    /**
     * @param array $data
     * @param AbstractCollection $collection
     */
    public function __construct(array $data = array(), AbstractCollection $collection = null)
    {
        if (array_key_exists(static::primaryKey(), $data)) {
            $this->isExistingRow = true;
            $this->initialize = true;
        }
        $this->fromArray($data);
        $this->initialize = false;
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
        if (!isset(static::$tableName)) {
            static $tableName;

            if (!$tableName) {
                $className = get_called_class();
                $classNameWithoutNamespace = array_pop(explode('\\', $className));
                $tableName = Inflector::tableize($classNameWithoutNamespace);
            }

            return $tableName;
        }

        return static::$tableName;
    }

    /**
     * @codeCoverageIgnore
     * @return AbstractCollection
     */
    public static function collectionClass()
    {
        if (!isset(static::$collectionClass)) {
            static $collectionClass;

            if (!$collectionClass) {
                $className = get_called_class();
                $parts = explode('\\', $className);
                $classNameWithoutNamespace = array_pop($parts);
                $parts[] = Inflector::pluralize($classNameWithoutNamespace);
                $collectionClass = '\\' . implode('\\', $parts);
            }

            return $collectionClass;
        }

        return static::$collectionClass;
    }

    public static function exprNow()
    {
        if (!self::$exprNow) {
            self::$exprNow = new Expr('NOW()');
        }
        return self::$exprNow;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function immediatelySelectColumns()
    {
        if (!isset(static::$immediatelySelectColumns)) {
            static $immediatelySelectColumns;

            if (!$immediatelySelectColumns) {
                $immediatelySelectColumns = array();
                foreach (static::columnSchema() as $columnName => $columnSchema) {
                    if (in_array($columnSchema['type'], self::$defaultImmediatelySelectColumnTypes)) {
                        $immediatelySelectColumns[] = $columnName;
                    }
                }
            }

            return $immediatelySelectColumns;
        }

        return static::$immediatelySelectColumns;
    }

    protected static function processColumnsSchema()
    {
        static $processedColumnsSchema;

        if (!$processedColumnsSchema) {
            foreach (static::$columnsSchema as  &$columnSchema) {
                if (is_string($columnSchema)) {
                    $columnSchema = array('type' => $columnSchema);
                }

                $type = $columnSchema['type'];
                $columnSchema = $columnSchema + self::$defaultColumnsSchema[$type];

                switch ($type) {
                    case 'date':
                    case 'time':
                    case 'datetime':
                    case 'timestamp':
                        if (!array_key_exists('default', $columnSchema)) {
                            $columnSchema['default'] = self::exprNow();
                        }
                        break;
                }
            }

            # process specific for primary column
            static::$columnsSchema[static::primaryKey()] += array('primaryKey' => true, 'autoIncrement' => true);
            $processedColumnsSchema = true;
        }
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function columnSchema()
    {
        static::processColumnsSchema();
        return static::$columnsSchema;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function oneToMany()
    {
        static $oneToManyProcessed;
        if (!$oneToManyProcessed) {
            $className = get_called_class();
            $namespace = explode('\\', $className);
            $classNameWithoutNamespace = array_pop($namespace);

            static::$oneToMany = (array) static::$oneToMany;
            $oneToMany = array();
            foreach (static::$oneToMany as $refName => $refSchema) {
                if (is_string($refSchema)) {
                    $refName = $refSchema;
                    $oneToMany[$refName] = array();
                } else {
                    $oneToMany[$refName] = $refSchema;
                }

                if (!isset($oneToMany[$refName]['model'])) {
                    $modelClass = $namespace;
                    $modelClass[] = Inflector::singularize($refName);
                    $oneToMany[$refName]['model'] = '\\' . implode('\\', $modelClass);
                }

                if (!isset($oneToMany[$refName]['key'])) {
                    $oneToMany[$refName]['key'] = strtolower($classNameWithoutNamespace) . '_id';
                }
            }

            static::$oneToMany = $oneToMany;
        }
        return static::$oneToMany;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function manyToOne()
    {
        static $manyToOneProcessed;
        if (!$manyToOneProcessed) {
            $className = get_called_class();
            $namespace = explode('\\', $className);
            array_pop($namespace);

            static::$manyToOne = (array) static::$manyToOne;
            $manyToOne = array();
            foreach (static::$manyToOne as $refName => $refSchema) {
                if (is_string($refSchema)) {
                    $refName = $refSchema;
                    $manyToOne[$refName] = array();
                } else {
                    $manyToOne[$refName] = $refSchema;
                }

                if (!isset($manyToOne[$refName]['model'])) {
                    $modelClass = $namespace;
                    $modelClass[] = Inflector::singularize($refName);
                    $manyToOne[$refName]['model'] = '\\' . implode('\\', $modelClass);
                }

                if (!isset($manyToOne[$refName]['key'])) {
                    $manyToOne[$refName]['key'] = strtolower($refName) . '_id';
                }
            }

            static::$manyToOne = $manyToOne;
        }
        return static::$manyToOne;
    }

    /**
     * @codeCoverageIgnore
     * @return array
     */
    public static function manyToMany()
    {
        static $manyToManyProcessed;
        if (!$manyToManyProcessed) {
            $className = get_called_class();
            $namespace = explode('\\', $className);
            $classNameWithoutNamespace = array_pop($namespace);

            static::$manyToMany = (array) static::$manyToMany;
            $manyToMany = array();
            foreach (static::$manyToMany as $refName => $refSchema) {
                if (is_string($refSchema)) {
                    $refName = $refSchema;
                    $manyToMany[$refName] = array();
                } else {
                    $manyToMany[$refName] = $refSchema;
                }

                if (!isset($manyToMany[$refName]['model'])) {
                    $modelClass = $namespace;
                    $modelClass[] = Inflector::singularize($refName);
                    $manyToMany[$refName]['model'] = '\\' . implode('\\', $modelClass);
                }

                if (!isset($manyToMany[$refName]['throughModel'])) {
                    $modelClass = $namespace;
                    $modelClass[] = $classNameWithoutNamespace . Inflector::singularize($refName);
                    $manyToMany[$refName]['throughModel'] = '\\' . implode('\\', $modelClass);
                }

                if (!isset($manyToMany[$refName]['leftKey'])) {
                    $manyToMany[$refName]['leftKey'] = strtolower($classNameWithoutNamespace) . '_id';
                }

                if (!isset($manyToMany[$refName]['rightKey'])) {
                    $manyToMany[$refName]['rightKey'] = strtolower(Inflector::singularize($refName)) . '_id';
                }


            }

            static::$manyToMany = $manyToMany;
        }
        return static::$manyToMany;
    }

    /**
     * @param Pdo $pdo
     */
    public static function setPdo(Pdo $pdo)
    {
        static::$pdo = $pdo;
    }

    /**
     * @return \Lazy\Db\Pdo
     */
    public static function getPdo(){
        return static::$pdo?: Pdo::getDefaultInstance();
    }

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
            $where = array(static::$tableName . '.' . static::$primaryKey => $where);
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
        $primaryKey = static::primaryKey();
        if (isset($this->data[$primaryKey])) {
            return $this->data[$primaryKey];
        }
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
                $oneToMany = static::oneToMany();
                $manyToMany = static::manyToMany();

                if (array_key_exists($columnName, $oneToMany)) {
                    $refModel = $oneToMany[$columnName]['model'];
                }elseif (array_key_exists($columnName, $manyToMany)) {
                    $refModel = $manyToMany[$columnName]['model'];
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

        $columnsSchema = static::columnSchema();
        if (array_key_exists($nameUnderscore, $columnsSchema)) {
            switch ($columnsSchema[$nameUnderscore]['type']) {
                case 'int':
                case 'tinyint':
                    $value = (int) $value;
                    break;
            }

            if ($this->isExistingRow) {
                if ($this->initialize) {
                    $this->data[$nameUnderscore] = $value;
                } else {
                    $this->changedData[$nameUnderscore] = $value;
                }
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
                $select->column(array(static::$primaryKey, $nameUnderscore))->where(array("$primaryKey IN(?)" => $collectionIds));
                $pair = $select->fetchAll(\PDO::FETCH_KEY_PAIR);
                foreach ($pair as $id => $value) {
                    $this->collection->get($id)->set($nameUnderscore, $value);
                }
                return $pair[$this->id()];
            } else {
                $select->column($nameUnderscore)->where(array(static::primaryKey() => $this->id()))->limit(1);
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
                $rows = $refModel::createSqlSelect()->where(array("$foreignKey IN(?)" => $collectionIds))->fetchAll(\PDO::FETCH_BOTH);

                $associationData = array();
                foreach ($rows as $row) {
                    isset($associationData[$row[$foreignKey]]) || $associationData[$row[$foreignKey]] = array();
                    $associationData[$row[$foreignKey]][] = $row;
                }

                foreach ($associationData as $id => $rows) {
                    $collectionClass = $refModel::collectionClass();
                    $this->collection->get($id)->set($name, new $collectionClass($rows));
                }

                return $this->associationsData[$name];
            } else {
                $collections = $refModel::all(array($foreignKey => $this->id()));
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
                    ->where(array("$throughTableName.$leftKey IN(?)" => $collectionIds))
                    ->fetchAll(\PDO::FETCH_BOTH);

                $associationData = array();
                foreach ($rows as $row) {
                    isset($associationData[$row[$leftKey]]) || $associationData[$row[$leftKey]] = array();
                    $associationData[$row[$leftKey]][] = $row;
                }

                foreach ($associationData as $id => $rows) {
                    $collectionClass = $refModel::collectionClass();
                    $this->collection->get($id)->set($name, new $collectionClass($rows));
                }

                return $this->associationsData[$name];
            } else {
                $collections = $refModel::all()->join($throughTableName, "$throughTableName.$rightKey = $refModelTableName.$refModelPrimaryKey")
                    ->where(array("$throughTableName.$leftKey" => $this->id()));
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
        $this->changedData = array();
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
            $this->sqlSelect->where(array(static::primaryKey() => $this->id()));
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
            $this->sqlUpdate->where(array(static::primaryKey() => $this->id()));
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
            $this->sqlDelete->where(array(static::primaryKey() => $this->id()));
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