<?php

namespace Lazy\Db\Sql;

/**
 * Class Group
 * @package Lazy\Db\Sql
 */
class Group
{
    /**
     * @var array
     */
    protected $columns = [];

    /**
     * ->group('foo') # => GROUP BY fooo
     * ->group('foo, bar') # => ORDER BY foo, bar
     *
     * @param string|array $columns
     * @return array|Order
     */
    public function group($columns = null)
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
     * @return $this
     */
    public function reset()
    {
        $this->columns = [];
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->columns) {
            return '';
        }

        $columns = [];
        foreach ($this->columns as $column) {
            $columns[] = is_array($column)? implode(', ', $column) : $column;
        }

        return 'GROUP BY '  . implode(', ', $columns);
    }
}