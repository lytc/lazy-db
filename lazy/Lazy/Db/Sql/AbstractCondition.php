<?php

namespace Lazy\Db\Sql;

use Lazy\Db\Pdo;

/**
 * Class AbstractCondition
 * @package Lazy\Db\Sql
 */
abstract class AbstractCondition
{
    /**
     * @var \Lazy\Db\Pdo
     */
    protected $pdo;
    /**
     * @var String
     */
    protected $type;
    /**
     * @var array
     */
    protected $conditions = array();

    /**
     * @param Pdo $pdo
     */
    public function __construct(Pdo $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @param $type
     * @param string|array $conditions
     * @param array $bindParams
     * @return $this
     */
    protected function condition($type, $conditions = null, array $bindParams = null)
    {

        $this->conditions[] = array(
            'type'          => $type,
            'conditions'    => $conditions,
            'bindParams'    => $bindParams
        );
        return $this;
    }

    /**
     * @param array $option
     * @return array
     */
    protected function compile(array $option)
    {
        $type = $option['type'];
        $conditions = $option['conditions'];
        $bindParams = $option['bindParams'];

        if (is_string($conditions)) {
            if ($bindParams) {
                $conditions = $this->bind($conditions, $bindParams);
            }
            return array(
                'type' => $type,
                'conditions' => $conditions
            );
        }

        $parts = array();
        $first = true;
        foreach ($conditions as $condition => $bindParams) {
            if (is_numeric($condition)) {
                $condition = $bindParams;
                $bindParams = null;
            }

            if (null !== $bindParams) {
                $bindParams = (array) $bindParams;
            }

            if ($bindParams) {
                if (preg_match('/^\w+\.?\w+$/', $condition)) {
                    $condition = "$condition = ?";
                }
                $condition = $this->bind($condition, $bindParams);
            }

            if (!$first && !preg_match('/^(or|OR|and|AND)\s+/', $condition)) {
                $condition = 'AND ' . $condition;
            }
            $parts[] = $condition;
            $first = false;
        }

        return array(
            'type'  => $type,
            'conditions' => implode(' ', $parts)
        );
    }

    /**
     * @param string $conditions
     * @param array $bindParams
     * @return string
     */
    protected function bind($conditions, array $bindParams) {
        foreach ($bindParams as &$value) {
            $value = $this->pdo->quote($value);
        }
        $index = 0;
        return preg_replace_callback(array('/\(?(\?|\:(\w+))\)?([^\w]*)/', '/\(?(\?|\:(\w+))\)?$/'), function($matches) use(&$index, $bindParams) {
            if (preg_match('/^\((\?|\:(\w+))\)$/', $matches[0], $m)) {
                if($m[0] == '(?)') {
                    $value = $bindParams[$index++];
                    if (!is_array($value)) {
                        $value = $bindParams;
                    }
                } else {
                    $value = $bindParams[$m[2]];
                }

                if (is_array($value)) {
                    $value = implode(', ', $value);
                }

                return '(' . $value . ')';
            }

            if ($matches[1] == '?') {
                return $bindParams[$index++] . (isset($matches[3])? $matches[3] : '');
            }

            return $bindParams[$matches[2]] . (isset($matches[3])? $matches[3] : '');
        }, $conditions);
    }

    /**
     * @return $this
     */
    public function reset()
    {
        $this->conditions = array();
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if (!$this->conditions) {
            return '';
        }

        $parts = array();

        foreach ($this->conditions as $condition) {
            $parts[] = $this->compile($condition);
        }

        $part0 = array_shift($parts);
        $conditions = array('(' . $part0['conditions'] . ')');
        foreach ($parts as $part) {
            $conditions[] = sprintf('%s (%s)', $part['type'], $part['conditions']);
        }

        return sprintf('%s %s', $this->type, implode(' ', $conditions));
    }
}