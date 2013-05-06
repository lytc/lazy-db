<?php

namespace Lazy\Db\Sql;

use Lazy\Db\Exception\Exception;
use Lazy\Db\Pdo;

/**
 * Class Select
 * @package Lazy\Db\Sql
 */
class Select
{
    /**
     * @var \Lazy\Db\Pdo
     */
    protected $pdo;
    /**
     * @var string
     */
    protected $table;
    /**
     * @var array
     */
    protected $columns = array();
    /**
     * @var Join
     */
    protected $join;
    /**
     * @var Where
     */
    protected $where;
    /**
     * @var Group
     */
    protected $group;
    /**
     * @var Having
     */
    protected $having;
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var Limit
     */
    protected $limit;

    /**
     * @var array
     */
    protected static $aliasMethods = array(
        'innerJoin' => 'join',
        'leftJoin'  => 'join',
        'rightJoin' => 'join',
        'orWhere'   => 'where',
        'orHaving'  => 'having',
        'offset'    => 'limit'
    );

    /**
     * @param Pdo $pdo
     * @param string $table
     */
    public function __construct(Pdo $pdo, $table = null)
    {
        $this->pdo = $pdo;
        $this->from($table);
    }

    /**
     * @param string $method
     * @param array $args
     * @return $this|Select|mixed
     * @throws \Lazy\Db\Exception\Exception
     */
    public function __call($method, $args)
    {
        if (preg_match('/^reset(Join|Where|Group|Having|Order|Limit)$/', $method)) {
            $part = strtolower(substr($method, 5));
            !$this->{$part} || $this->{$part}->reset();
            return $this;
        }

        if (!array_key_exists($method, self::$aliasMethods)) {
            throw new Exception(sprintf('Call undefined method %s', $method));
        }

        $result = call_user_func_array(array($this->{self::$aliasMethods[$method]}(), $method), $args);
        return $args? $this : $result;
    }

    /**
     * @return Pdo
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param string $table
     * @return $this
     */
    public function from($table = null)
    {
        if (!func_num_args()) {
            return $this->table;
        }

        $this->table = $table;
        return $this;
    }

    /**
     * @param string|array $columns
     * @return $this|array
     */
    public function column($columns = null)
    {
        if (!func_num_args()) {
            return $this->columns;
        }

        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s+/', $columns);
        }

        $this->columns = array_merge($this->columns, $columns);

        return $this;
    }

    /**
     * @param string|Join $join
     * @return $this|Join
     */
    public function join($join = null)
    {
        if ($join instanceof Join) {
            $this->join = $join;
            return $this;
        }

        if (!$this->join) {
            $this->join = new Join();
        }

        if (!func_num_args()) {
            return $this->join;
        }

        call_user_func_array(array($this->join, 'join'), func_get_args());
        return $this;
    }

    /**
     * @param string|array|Where $where
     * @return $this|Where
     */
    public function where($where = null)
    {
        if ($where instanceof Where) {
            $this->where = $where;
            return $this;
        }

        if (!$this->where) {
            $this->where = new Where($this->pdo);
        }

        if (!func_num_args()) {
            return $this->where;
        }

        call_user_func_array(array($this->where, 'where'), func_get_args());
        return $this;
    }

    /**
     * @param string|array|Group $group
     * @return $this|Group
     */
    public function group($group = null)
    {
        if ($group instanceof Group) {
            $this->group = $group;
            return $this;
        }

        if (!$this->group) {
            $this->group = new Group();
        }

        if (!func_num_args()) {
            return $this->group;
        }

        call_user_func_array(array($this->group, 'group'), func_get_args());
        return $this;
    }

    /**
     * @param string|array|Having $having
     * @return $this|Having
     */
    public function having($having = null)
    {
        if ($having instanceof Having) {
            $this->having = $having;
            return $this;
        }

        if (!$this->having) {
            $this->having = new Having($this->pdo);
        }

        if (!func_num_args()) {
            return $this->having;
        }

        call_user_func_array(array($this->having, 'having'), func_get_args());
        return $this;
    }

    /**
     * @param string|array|Order $order
     * @return $this|Order
     */
    public function order($order = null)
    {
        if ($order instanceof Order) {
            $this->order = $order;
            return $this;
        }

        if (!$this->order) {
            $this->order = new Order();
        }

        if (!func_num_args()) {
            return $this->order;
        }

        call_user_func_array(array($this->order, 'order'), func_get_args());
        return $this;
    }

    /**
     * @param int|Limit $limit
     * @return $this|Limit
     */
    public function limit($limit = null)
    {
        if ($limit instanceof Limit) {
            $this->limit = $limit;
            return $this;
        }

        if (!$this->limit) {
            $this->limit = new Limit();
        }

        if (!func_num_args()) {
            return $this->limit;
        }

        call_user_func_array(array($this->limit, 'limit'), func_get_args());
        return $this;
    }

    /**
     * @return $this
     */
    public function resetColumn()
    {
        $this->columns = array();
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->resetColumn();
        !$this->join || $this->join->reset();
        !$this->where || $this->where->reset();
        !$this->group || $this->group->reset();
        !$this->having || $this->having->reset();
        !$this->order || $this->order->reset();
        !$this->limit || $this->limit->reset();

        return $this;
    }

    /**
     * @return \PDOStatement
     */
    public function query()
    {
        return $this->getPdo()->query($this->__toString());
    }

    /**
     * @return array
     */
    public function fetch()
    {
        return call_user_func_array(array($this->query(), 'fetch'), func_get_args());
    }

    /**
     * @return array
     */
    public function fetchAll()
    {
        return call_user_func_array(array($this->query(), 'fetchAll'), func_get_args());
    }

    /**
     * @return array
     */
    public function fetchColumn()
    {
        return call_user_func_array(array($this->query(), 'fetchColumn'), func_get_args());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $sql = array('SELECT');

        # columns
        if (!$this->columns) {
            $sql[] = '*';
        } else {
            $sql[] = implode(', ', $this->columns);
        }

        # from
        $sql[] = 'FROM ' . $this->table;

        $parts = array('join', 'where', 'group', 'having', 'order', 'limit');
        foreach ($parts as $part) {
            if ($this->{$part} && $part = (String) $this->{$part}) {
                $sql[] = $part;
            }
        }

        return implode(' ', $sql);
    }
}