<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Insert;
use LazyTest\Db\DbSample;

class InsertTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Insert::__construct
     * @covers Lazy\Db\Sql\Insert::__toString
     */
    public function testWithSingleValue()
    {
        $insert = new Insert(DbSample::getPdo(), 'foo');
        $insert->value(['foo' => 'foo', 'bar' => 1]);
        $expected = "INSERT INTO foo (foo, bar) VALUES ('foo', 1)";
        $this->assertSame($expected, (String) $insert);
    }

    /**
     * @covers Lazy\Db\Sql\Insert::value
     * @covers Lazy\Db\Sql\Insert::__toString
     */
    public function testWithMultipleValue()
    {
        $insert = new Insert(DbSample::getPdo(), 'foo');
        $insert->value([
            ['foo' => 'foo', 'bar' => 1],
            ['foo' => 'foo2', 'bar' => 2],
        ]);
        $expected = "INSERT INTO foo (foo, bar) VALUES ('foo', 1), ('foo2', 2)";
        $this->assertSame($expected, (String) $insert);
    }

    /**
     * @covers Lazy\Db\Sql\Insert::column
     * @covers Lazy\Db\Sql\Insert::value
     * @covers Lazy\Db\Sql\Insert::__toString
     */
    public function testWithColumn()
    {
        $insert = new Insert(DbSample::getPdo(), 'foo');
        $insert->column('foo, bar')
            ->value(['foo', 1]);

        $expected = "INSERT INTO foo (foo, bar) VALUES ('foo', 1)";
        $this->assertSame($expected, (String) $insert);

        $insert = new Insert(DbSample::getPdo(), 'foo');
        $insert->column('foo, bar')
            ->value([['foo', 1], ['foo2', 2]]);

        $expected = "INSERT INTO foo (foo, bar) VALUES ('foo', 1), ('foo2', 2)";
        $this->assertSame($expected, (String) $insert);
    }

    /**
     * @covers Lazy\Db\Sql\Insert::getPdo
     */
    public function testGetPdo()
    {
        $pdo = DbSample::getPdo();
        $insert = new Insert($pdo);
        $this->assertSame($pdo, $insert->getPdo());
    }

    /**
     * @covers Lazy\Db\Sql\Insert::into
     */
    public function testInto()
    {
        $insert = new Insert(DbSample::getPdo());
        $insert->into('foo');
        $this->assertSame('foo', $insert->into());
    }

    /**
     * @covers Lazy\Db\Sql\Insert::column
     */
    public function testColumn()
    {
        $insert = new Insert(DbSample::getPdo());
        $insert->column('foo, bar');
        $this->assertSame(['foo', 'bar'], $insert->column());

        $insert->column(['baz', 'qux']);
        $this->assertSame(['baz', 'qux'], $insert->column());
    }

    /**
     * @covers Lazy\Db\Sql\Insert::value
     */
    public function testGetValue()
    {
        $insert = new Insert(DbSample::getPdo());
        $values = ['foo' => 'foo', 'bar' => 'bar'];
        $insert->value($values);
        $this->assertSame($values, $insert->value());
    }

    /**
     * @covers Lazy\Db\Sql\Insert::reset
     */
    public function testReset()
    {
        $insert = new Insert(DbSample::getPdo());
        $insert->column(['foo', 'bar'])->value(['foo', 'bar']);
        $insert->reset();
        $this->assertSame([], $insert->column());
        $this->assertSame([], $insert->value());
    }

    /**
     * @covers Lazy\Db\Sql\Insert::exec
     */
    public function testExec()
    {
        $pdo = DbSample::getPdo();
        $insert = new Insert($pdo, 'users');
        $insert->value([['name' => 'name888'], ['name' => 'name999']]);

        $pdo->beginTransaction();
        $this->assertSame(2, $insert->exec());
        $pdo->rollBack();
    }
}