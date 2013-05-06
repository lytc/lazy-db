<?php

namespace Lazy\Db\Sql;

/**
 * Class Join
 * @package Lazy\Db\Sql
 */
class Join
{
    /**
     *
     */
    const INNER_JOIN    = 'INNER JOIN';
    /**
     *
     */
    const LEFT_JOIN     = 'LEFT JOIN';
    /**
     *
     */
    const RIGHT_JOIN    = 'RIGHT JOIN';
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param string $type
     * @param string $table
     * @param string $conditions
     * @return $this
     */
    protected function _join($type, $table, $conditions)
    {
        $this->data[] = [
            'type' => $type,
            'table' => $table,
            'conditions' => $conditions
        ];

        return $this;
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return $this
     */
    public function innerJoin($table, $conditions)
    {
        return $this->_join(self::INNER_JOIN, $table, $conditions);
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return $this
     */
    public function leftJoin($table, $conditions)
    {
        return $this->_join(self::LEFT_JOIN, $table, $conditions);
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return $this
     */
    public function rightJoin($table, $conditions)
    {
        return $this->_join(self::RIGHT_JOIN, $table, $conditions);
    }

    /**
     * @param string $table
     * @param string $conditions
     * @return $this
     */
    public function join($table, $conditions)
    {
        return $this->innerJoin($table, $conditions);
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->data = [];
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->data) {
            return '';
        }

        $joins = [];
        foreach ($this->data as $option) {
            $type = $option['type'];
            $table = $option['table'];
            $conditions = $option['conditions'];

            $joins[] = sprintf('%s %s ON %s', $type, $table, $conditions);
        }

        return implode(' ', $joins);
    }
}