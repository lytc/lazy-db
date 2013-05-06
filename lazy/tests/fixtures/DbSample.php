<?php

namespace LazyTest\Db;

use Lazy\Db\Pdo;

class DbSample
{
    public static $host = '127.0.0.1';
    public static $username = 'root';
    public static $password = '';
    public static $dbName = 'lazy_db_test';
    protected static $pdo;

    public static function getPdo($new = false)
    {
        if (!$new && self::$pdo) {
            return self::$pdo;
        }

        $host = self::$host;
        $username = self::$username;
        $password = self::$password;
        $dbName = self::$dbName;

        if (getenv('LAZY_TEST_ENV') != 'travis') {
            exec("mysql -h{$host} -u{$username} --password={$password} -e 'DROP DATABASE IF EXISTS `{$dbName}`'");
            exec("mysql -h{$host} -u{$username} --password={$password} -e 'CREATE DATABASE `{$dbName}`'");
        }
        $pdo = new Pdo("mysql:host={$host};dbname={$dbName}", $username, $password);
        $pdo->exec(file_get_contents(__DIR__ . '/sample-database.sql'));

        self::$pdo = $pdo;

        return self::$pdo;
    }
}