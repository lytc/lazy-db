<?php

namespace Lazy\Db\Sql;

/**
 * Class Limit
 * @package Lazy\Db\Sql
 */
class Limit
{
    /**
     * @var int
     */
    protected $limit;
    /**
     * @var int
     */
    protected $offset;

    /**
     * ->limit(10) # => LIMIT 10
     *
     * @param int $limit
     * @return Limit
     */
    public function limit($limit = null)
    {
        if (!func_num_args()) {
            return $this->limit;
        }

        $this->limit = (int) $limit;
        return $this;
    }

    /**
     * ->limit(10)->offset(2) # => LIMIT 10 OFFSET 2
     *
     * @param int $offset
     * @return Limit
     */
    public function offset($offset = null)
    {
        if (!func_num_args()) {
            return $this->offset;
        }

        $this->offset = (int) $offset;
        return $this;
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->limit = null;
        $this->offset = null;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->limit) {
            return '';
        }

        $sql = 'LIMIT ' . $this->limit;
        if ($this->offset) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return $sql;
    }
}