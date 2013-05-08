<?php

namespace Lazy\Db;

use Lazy\Db\Model\Generator;

/**
 * Class Pdo
 * @package Lazy\Db
 */
class Pdo extends \PDO
{
    /**
     * @var
     */
    protected static $defaultInstance;

    /**
     * @var bool
     */
    protected static $dbFirst = false;

    /**
     * @var bool
     */
    protected static $generated = false;

    /**
     * @param $dsn
     * @param string $username
     * @param string $password
     * @param array $driverOptions
     */
    public function __construct($dsn, $username = null, $password = null, array $driverOptions = array())
    {
        parent::__construct($dsn, $username, $password, $driverOptions);

        $this->setAttribute(self::ATTR_ERRMODE, self::ERRMODE_EXCEPTION);
        $this->setAttribute(self::ATTR_STATEMENT_CLASS, array(__NAMESPACE__ . '\\Stmt'));

        if (!self::$defaultInstance) {
            self::$defaultInstance = $this;
        }

        if (self::$dbFirst && !self::$generated) {
            $generator = new Generator($this, self::$dbFirst['directory'], self::$dbFirst['namespace']);
            $generator->generate();
            self::$generated = true;
        }
    }

    /**
     * @param Pdo $pdo
     */
    public static function setDefaultInstance(Pdo $pdo)
    {
        self::$defaultInstance = $pdo;
    }

    /**
     * @return Pdo
     */
    public static function getDefaultInstance()
    {
        return self::$defaultInstance;
    }

    /**
     * @param int $style
     */
    public static function dbFirst($directory, $namespace = null)
    {
        self::$dbFirst = array(
            'directory' => $directory,
            'namespace' => $namespace
        );
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