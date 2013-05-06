<?php

namespace LazyTest\Db;
use Lazy\Db\Expr;
use Lazy\Db\Pdo;

class PdoTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultErrorMode()
    {
        $pdo = DbSample::getPdo();
        $this->assertSame(\PDO::ERRMODE_EXCEPTION, $pdo->getAttribute(\PDO::ATTR_ERRMODE));
    }

    public function testStmtClass()
    {
        $pdo = DbSample::getPdo();
        $this->assertSame(['Lazy\Db\Stmt'], $pdo->getAttribute(PDO::ATTR_STATEMENT_CLASS));
    }

    public function testQuote()
    {
        $pdo = DbSample::getPdo();
        $expr = new Expr("foo'bar");
        $this->assertSame("foo'bar", $pdo->quote($expr));

        $this->assertSame([1, 2, 3], $pdo->quote([1, 2, 3]));
        $this->assertSame([1, "'foo\'bar'"], $pdo->quote([1, "foo'bar"]));
    }

    public function testEscape()
    {
        $pdo = DbSample::getPdo();
        $expr = new Expr("%foo'bar%");
        $this->assertSame("%foo'bar%", $pdo->escape($expr));

        $this->assertSame("foo\'bar", $pdo->escape("foo'bar"));
    }
}