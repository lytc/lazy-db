<?php

namespace Lazy\Db\Sql;

/**
 * Class Order
 * @package Lazy\Db\Sql
 */
class Order
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * ->order('foo') # => ORDER BY foo ASC
     * ->order('foo, bar DESC') # => ORDER BY foo ASC, bar DESC
     * ->order(['foo', 'bar' => 'DESC']) # => ORDER BY foo ASC, bar DESC
     *
     * @param string|array|Order $columns
     * @return array|Order
     */
    public function order($columns = null)
    {
        if (!func_num_args()) {
            return $this->data;
        }

        if (is_string($columns)) {
            $columns = preg_split('/\s*,\s+/', $columns);
            foreach ($columns as $column) {
                $parts = preg_split('/\s+/', $column);
                $direction = isset($parts[1])? $parts[1] : 'ASC';
                $direction = strtoupper($direction);
                $this->data[] = "{$parts[0]} $direction";
            }
        } else {
            foreach ($columns as $column => $direction) {
                if (is_numeric($column)) {
                    $column = $direction;
                    $direction = 'ASC';
                }
                $direction = strtoupper($direction);
                $this->data[] = "$column $direction";
            }
        }

        return $this;
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

        return 'ORDER BY '  . implode(', ', $this->data);
    }
}