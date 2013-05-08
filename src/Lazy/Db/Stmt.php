<?php

namespace Lazy\Db;

/**
 * Class Stmt
 * @package Lazy\Db
 */
class Stmt extends \PDOStatement
{
    /**
     * @var array
     */
    protected static $instances = array();

    /**
     *
     */
    protected function __construct()
    {
        self::$instances[] = $this;
    }

    /**
     *
     */
    public static function startLogInstance()
    {
        self::$instances = array();
    }

    /**
     * @return array
     */
    public static function getLogInstances()
    {
        return self::$instances;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function bindParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->bindParam($name, $value);
        }

        return $this;
    }

    /**
     * @param array $params
     * @return $this
     */
    public function bindValues(array $params)
    {
        foreach ($params as $name => $value) {
            $this->bindValue($name, $value);
        }

        return $this;
    }
}