<?php

namespace Lazy\Db;

/**
 * Class Pdo
 * @package Lazy\Db
 */
class Pdo extends \PDO
{
    /**
     * @param $dsn
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     */
    public function __construct($dsn, $username = null, $password = null, array $driverOptions = [])
    {
        parent::__construct($dsn, $username, $password, $driverOptions);

        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(self::ATTR_STATEMENT_CLASS, [__NAMESPACE__ . '\\Stmt']);
    }

    /**
     * @param string $value
     * @param string $type
     * @return array|int|string
     */
    public function quote($value, $type = null)
    {
        if ($value instanceof Expr || $value instanceof Select) {
            return (String) $value;
        }

        $type || $type = gettype($value);

        switch ($type) {
            case 'boolean': return (int) !!$value;
            case 'integer': return (int) $value;
            case 'NULL': return 'NULL';
            case 'array':
                $value = (array) $value;
                foreach ($value as &$v) {
                    $v = $this->quote($v);
                }
                return $value;

            default: return parent::quote($value);
        }
    }

    /**
     * @param $str
     * @return string
     */
    public function escape($str)
    {
        if ($str instanceof Expr || $str instanceof Select) {
            return (String) $str;
        }

        return substr(parent::quote($str), 1, -1);
    }
}