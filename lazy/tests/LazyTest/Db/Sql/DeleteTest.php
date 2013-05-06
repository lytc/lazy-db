<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Delete;
use Lazy\Db\Sql\Order;
use Lazy\Db\Sql\Limit;
use Lazy\Db\Sql\Where;
use LazyTest\Db\DbSample;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Delete::__construct
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testBasic()
    {
        $delete = new Delete(DbSample::getPdo(), 'foo');
        $expected = "DELETE FROM foo";
        $this->assertSame($expected, (String) $delete);
    }

    /**
     * @covers Lazy\Db\Sql\Delete::getPdo
     */
    public function testGetPdo()
    {
        $pdo = DbSample::getPdo();
        $delete = new Delete($pdo, 'foo');
        $this->assertSame($pdo, $delete->getPdo());
    }

    /**
     * @covers Lazy\Db\Sql\Delete::from
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testFrom()
    {
        $delete = new Delete(DbSample::getPdo());
        $delete->from('foo');
        $this->assertSame('foo', $delete->from());
        $expected = "DELETE FROM foo";
        $this->assertSame($expected, (String) $delete);
    }

    /**
     * @covers Lazy\Db\Sql\Delete::where
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testWhere()
    {
        $delete = new Delete(DbSample::getPdo(), 'foo');
        $delete->where('foo = ?', 'foo');
        $expected = "DELETE FROM foo WHERE (foo = 'foo')";
        $this->assertSame($expected, (String) $delete);

        $where = new Where(DbSample::getPdo());
        $where->where('bar = ?', 'bar');
        $delete->where($where);
        $expected = "DELETE FROM foo WHERE (bar = 'bar')";
        $this->assertSame($expected, (String) $delete);
    }

    /**
     * @covers Lazy\Db\Sql\Delete::order
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testOrder()
    {
        $delete = new Delete(DbSample::getPdo(), 'foo');
        $delete->order('bar');
        $expected = "DELETE FROM foo ORDER BY bar ASC";
        $this->assertSame($expected, (String) $delete);

        $order = new Order();
        $order->order('baz DESC');
        $delete->order($order);
        $expected = "DELETE FROM foo ORDER BY baz DESC";
        $this->assertSame($expected, (String) $delete);
        $this->assertSame($order, $delete->order());
    }

    /**
     * @covers Lazy\Db\Sql\Delete::limit
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testLimit()
    {
        $delete = new Delete(DbSample::getPdo(), 'foo');
        $delete->limit(10);
        $expected = "DELETE FROM foo LIMIT 10";
        $this->assertSame($expected, (String) $delete);

        $limit = new Limit();
        $limit->limit(20);
        $delete->limit($limit);
        $expected = "DELETE FROM foo LIMIT 20";
        $this->assertSame($expected, (String) $delete);
        $this->assertSame($limit, $delete->limit());
    }

    /**
     * @covers Lazy\Db\Sql\Delete::where
     * @covers Lazy\Db\Sql\Delete::order
     * @covers Lazy\Db\Sql\Delete::limit
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testComplex()
    {
        $delete = new Delete(DbSample::getPdo());
        $delete->from('foo')
            ->where('foo = ?', 1)
            ->orWhere('bar = ?', 2)
            ->order('bar DESC')
            ->limit(10);

        $expected = "DELETE FROM foo WHERE (foo = 1) OR (bar = 2) ORDER BY bar DESC LIMIT 10";
        $this->assertSame($expected, (String) $delete);
    }

    /**
     * @covers Lazy\Db\Sql\Delete::reset
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testReset()
    {
        $delete = new Delete(DbSample::getPdo());
        $delete->from('foo')
            ->where('foo = ?', 1)
            ->orWhere('bar = ?', 2)
            ->order('bar DESC')
            ->limit(10);

        $expected = "DELETE FROM foo WHERE (foo = 1) OR (bar = 2) ORDER BY bar DESC LIMIT 10";
        $this->assertSame($expected, (String) $delete);

        $delete->reset();
        $expected = "DELETE FROM foo";
        $this->assertSame($expected, $delete->__toString());
    }

    /**
     * @covers Lazy\Db\Sql\Delete::__call
     * @covers Lazy\Db\Sql\Delete::__toString
     */
    public function testResetPart()
    {
        $delete = new Delete(DbSample::getPdo());
        $delete->from('foo')
            ->where('foo = ?', 1)
            ->orWhere('bar = ?', 2)
            ->order('bar DESC')
            ->limit(10);

        $expected = "DELETE FROM foo WHERE (foo = 1) OR (bar = 2) ORDER BY bar DESC LIMIT 10";
        $this->assertSame($expected, (String) $delete);

        $delete->resetWhere()->resetOrder()->resetLimit();
        $expected = "DELETE FROM foo";
        $this->assertSame($expected, $delete->__toString());
    }

    /**
     * @covers Lazy\Db\Sql\Delete::__call
     * @expectedException Lazy\Db\Exception\Exception
     * @expectedExceptionMessage Call undefined method undefinedMethod
     */
    public function testCallUndefinedMethodShouldThrowAnException()
    {
        $delete = new Delete(DbSample::getPdo());
        $delete->undefinedMethod();
    }

    public function testExec()
    {
        $pdo = DbSample::getPdo();
        $delete = new Delete($pdo, 'users');

        $pdo->beginTransaction();
        $delete->limit(2);
        $this->assertSame(2, $delete->exec());
        $pdo->rollBack();
    }
}