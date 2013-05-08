<?php

namespace Lazy\Db\Model;

use Lazy\Db\Exception\Exception;
use Lazy\Db\Inflector;
use Lazy\Db\Sql\Select;

/**
 * Class AbstractCollection
 * @package Lazy\Db\Model
 */
abstract class AbstractCollection implements \Countable, \ArrayAccess, \Iterator
{
    /**
     * @var String
     */
    protected $primaryKey;

    /**
     * @var String
     */
    protected $tableName;

    /**
     * @var Select
     */
    protected $select;

    /**
     * @var AbstractModel
     */
    protected static $modelClass;

    /**
     * @var array
     */
    protected $data;

    /**
     * @var array
     */
    protected $models = array();

    /**
     * @var int
     */
    protected $count;

    /**
     * @var int
     */
    protected $countAll;

    /**
     * @var int
     */
    protected $position = 0;

    /**
     * @var array
     */
    protected static $fallbackMethods = array(
        'where', 'orWhere', 'having', 'orHaving', 'group', 'order', 'limit', 'offset', 'join'
    );

    /**
     * @param $select
     */
    public function __construct($select)
    {
        if (is_array($select)) {
            $this->data = $select;
        } else {
            $this->select = $select;
        }
        $modelClass = static::modelClass();
        $this->primaryKey = $modelClass::primaryKey();
        $this->tableName = $modelClass::tableName();
    }

    /**
     * @return AbstractModel
     */
    public static function modelClass()
    {
        if (!isset(static::$modelClass)) {
            static $modelClass;

            if (!$modelClass) {
                $className = get_called_class();
                $parts = explode('\\', $className);
                $classNameWithoutNamespace = array_pop($parts);
                $parts[] = Inflector::singularize($classNameWithoutNamespace);
                $modelClass = '\\' . implode('\\', $parts);
            }

            return $modelClass;
        }

        return static::$modelClass;
    }

    /**
     * @return Select
     */
    public function sqlSelect()
    {
        return $this->select;
    }

    /**
     * @param $method
     * @param $args
     * @return $this
     * @throws \Lazy\Db\Exception\Exception
     */
    public function __call($method, $args)
    {
        if (!in_array($method, self::$fallbackMethods)) {
            throw new Exception(sprintf('Call undefined magic method %s', $method));
        }

        call_user_func_array(array($this->select, $method), $args);
        return $this;
    }

    /**
     * @param String|array $columns
     * @return $this
     */
    public function column($columns)
    {
        if (!is_array($columns)) {
            $columns = preg_split('/\s+/', trim($columns));
        }

        array_unshift($columns, $this->primaryKey);
        $columns = array_unique($columns);

        $this->select->resetColumn()->column($columns);
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        if (null === $this->data) {
            $this->data = $this->select->fetchAll(\PDO::FETCH_BOTH);
        }

        return $this->data;
    }

    /**
     * @param int $id
     * @return AbstractModel
     */
    public function get($id)
    {
        if (array_key_exists($id, $this->models)) {
            return $this->models[$id];
        }

        foreach ($this->toArray() as $row) {
            if ($row[$this->primaryKey] == $id) {
                $modelClass = static::modelClass();
                $this->models[$id] = new $modelClass($row, $this);
                return $this->models[$id];
            }
        }
    }

    /**
     * @param int|String $column
     * @return array
     */
    public function fetchColumn($column = 0)
    {
        $result = array();
        foreach ($this->toArray() as $row) {
            $result[] = $row[$column];
        }
        return $result;
    }

    /**
     * @param int|string $columnKey
     * @param int|string $columnValue
     * @return array
     */
    public function fetchPair($columnKey = 0, $columnValue = 1)
    {
        return array_combine($this->fetchColumn($columnKey), $this->fetchColumn($columnValue));
    }

    public function countAll()
    {
        if (null === $this->countAll) {
            $select = clone $this->select;
            $select->resetColumn()->resetOrder()->resetLimit()->column('COUNT(*)');
            $this->countAll = $select->fetchColumn();
        }

        return $this->countAll;
    }

    /**
     * @return int
     */
    public function count()
    {
        if (null === $this->count) {
            $this->count = count($this->toArray());
        }

        return $this->count;
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->toArray());
    }

    /**
     * @param int $offset
     * @return AbstractModel
     */
    public function offsetGet($offset)
    {
        $data = $this->toArray();
        return $this->get($data[$offset][$this->primaryKey]);
    }

    /**
     * @param int $offset
     * @param mixed $value
     * @return $this
     */
    public function offsetSet($offset, $value)
    {
        $this->offsetGet($offset)->fromArray($value);
        return $this;
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset)
    {
        $this->offsetGet($offset)->delete();
    }

    /**
     * @return AbstractModel
     */
    public function current()
    {
        return $this->offsetGet($this->position);
    }

    /**
     *
     */
    public function next()
    {
        $this->position++;
    }

    /**
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * @return bool
     */
    public function valid()
    {
        return array_key_exists($this->position, $this->toArray());
    }

    /**
     *
     */
    public function rewind()
    {
        $this->position = 0;
    }
}