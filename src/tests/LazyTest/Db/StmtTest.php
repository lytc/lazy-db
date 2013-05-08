<?php

namespace LazyTest\Db;

use Lazy\Db\Pdo;

class StmtTest extends \PHPUnit_Framework_TestCase
{
    public function testBindParams()
    {
        $pdo = DbSample::getPdo();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $sql = "SELECT * FROM users WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $name = 'name1';
        $stmt->bindParam('name', $name);
        $stmt->execute();
        $expected = $stmt->fetchAll();

        $stmt = $pdo->prepare($sql);
        $stmt->bindParams(array('name' => $name));
        $stmt->execute();
        $actual = $stmt->fetchAll();

        $this->assertSame($expected, $actual);
    }

    public function testBindValues()
    {
        $pdo = DbSample::getPdo();
        $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $sql = "SELECT * FROM users WHERE name = :name";
        $stmt = $pdo->prepare($sql);
        $name = 'name1';
        $stmt->bindValue('name', $name);
        $stmt->execute();
        $expected = $stmt->fetchAll();

        $stmt = $pdo->prepare($sql);
        $stmt->bindValues(array('name' => $name));
        $stmt->execute();
        $actual = $stmt->fetchAll();

        $this->assertSame($expected, $actual);
    }
}