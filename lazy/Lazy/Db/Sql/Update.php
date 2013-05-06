<?php

namespace Lazy\Db\Sql;

use Lazy\Db\Db;
use Lazy\Db\Exception\Exception;
use Lazy\Db\Pdo;

/**
 * Class Update
 * @package Lazy\Db\Sql
 */
class Update
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
    protected $data = [];
    /**
     * @var Where
     */
    protected $where;
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
    protected static $aliasMethods = [
        'orWhere'   => 'where',
    ];

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
     * @return Pdo
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * @param string $method
     * @param array $args
     * @return $this
     * @throws \Lazy\Db\Exception\Exception
     */
    public function __call($method, $args)
    {
        if (preg_match('/^reset(Where|Order|Limit)$/', $method)) {
            $part = strtolower(substr($method, 5));
            !$this->{$part} || $this->{$part}->reset();
            return $this;
        }

        if (!isset(self::$aliasMethods[$method])) {
            throw new Exception(sprintf('Call undefined method %s', $method));
        }

        call_user_func_array([$this->{self::$aliasMethods[$method]}(), $method], $args);
        return $this;
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
     * @param array $data
     * @return $this|array
     */
    public function data(array $data = null)
    {
        if (!func_num_args()) {
            return $this->data;
        }

        $this->data = $data;
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

        call_user_func_array([$this->where, 'where'], func_get_args());
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

        call_user_func_array([$this->order, 'order'], func_get_args());
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

        call_user_func_array([$this->limit, 'limit'], func_get_args());
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->data = [];
        !$this->where || $this->where->reset();
        !$this->order || $this->order->reset();
        !$this->limit || $this->limit->reset();

        return $this;
    }

    /**
     * @return int
     */
    public function exec()
    {
        return $this->pdo->exec($this->__toString());
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $sql = ['UPDATE'];

        # from
        $sql[] = $this->table;

        # set
        $data = $this->data;
        $sets = [];
        foreach ($data as $column => $value) {
            $sets[] = sprintf('%s = %s', $column, $this->pdo->quote($value));
        }

        $sql[] = 'SET ' . implode(', ', $sets);

        # where
        if ($this->where && $where = (String) $this->where) {
            $sql[] = $where;
        }

        $parts = ['order', 'limit'];
        foreach ($parts as $part) {
            if ($this->{$part} && $part = (String) $this->{$part}) {
                $sql[] = $part;
            }
        }

        return implode(' ', $sql);
    }
}