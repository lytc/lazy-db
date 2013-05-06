<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Limit;
use Lazy\Db\Sql\Order;
use Lazy\Db\Sql\Update;
use Lazy\Db\Sql\Where;
use LazyTest\Db\DbSample;

class UpdateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Update::__construct
     * @covers Lazy\Db\Sql\Update::data
     * @covers Lazy\Db\Sql\Update::__toString
     */
    public function testBasic()
    {
        $update = new Update(DbSample::getPdo(), 'foo');
        $update->data(['foo' => 'foo', 'bar' => 1]);

        $expected = "UPDATE foo SET foo = 'foo', bar = 1";
        $this->assertSame($expected, (String) $update);
    }

    /**
     * @covers Lazy\Db\Sql\Update::getPdo
     */
    public function testGetPdo()
    {
        $pdo = DbSample::getPdo();
        $update = new Update($pdo);
        $this->assertSame($pdo, $update->getPdo());
    }

    /**
     * @covers Lazy\Db\Sql\Update::from
     */
    public function testFrom()
    {
        $update = new Update(DbSample::getPdo(), 'foo');
        $this->assertSame('foo', $update->from());
        $update->from('bar');
        $this->assertSame('bar', $update->from());
    }

    /**
     * @covers Lazy\Db\Sql\Update::data
     */
    public function testData()
    {
        $update = new Update(DbSample::getPdo(), 'foo');
        $data = ['foo' => 'foo', 'bar' => 'bar'];
        $update->data($data);
        $this->assertSame($data, $update->data());
    }

    /**
     * @covers Lazy\Db\Sql\Update::where
     */
    public function testWhere()
    {
        $pdo = DbSample::getPdo();
        $update = new Update($pdo, 'foo');
        $update->data(['bar' => 'bar', 'baz' => 'baz'])->where(['id' => 1]);

        $expected = "UPDATE foo SET bar = 'bar', baz = 'baz' WHERE (id = 1)";
        $this->assertSame($expected, $update->__toString());

        $where = new Where($pdo);
        $where->where(['id' => 2]);
        $update->where($where);
        $expected = "UPDATE foo SET bar = 'bar', baz = 'baz' WHERE (id = 2)";
        $this->assertSame($expected, $update->__toString());
        $this->assertSame($where, $update->where());
    }

    /**
     * @covers Lazy\Db\Sql\Update::order
     */
    public function testOrder()
    {
        $pdo = DbSample::getPdo();
        $update = new Update($pdo, 'foo');
        $update->data(['bar' => 'bar', 'baz' => 'baz'])->order('id DESC');

        $expected = "UPDATE foo SET bar = 'bar', baz = 'baz' ORDER BY id DESC";
        $this->assertSame($expected, $update->__toString());

        $order = new Order($pdo);
        $order->order('id');
        $update->order($order);
        $expected = "UPDATE foo SET bar = 'bar', baz = 'baz' ORDER BY id ASC";
        $this->assertSame($expected, $update->__toString());
        $this->assertSame($order, $update->order());
    }

    /**
     * @covers Lazy\Db\Sql\Update::limit
     */
    public function testLimit()
    {
        $pdo = DbSample::getPdo();
        $update = new Update($pdo, 'foo');
        $update->data(['bar' => 'bar', 'baz' => 'baz'])->limit(10);

        $expected = "UPDATE foo SET bar = 'bar', baz = 'baz' LIMIT 10";
        $this->assertSame($expected, $update->__toString());

        $limit = new Limit($pdo);
        $limit->limit(20);
        $update->limit($limit);
        $expected = "UPDATE foo SET bar = 'bar', baz = 'baz' LIMIT 20";
        $this->assertSame($expected, $update->__toString());
        $this->assertSame($limit, $update->limit());
    }

    /**
     * @covers Lazy\Db\Sql\Update::__construct
     * @covers Lazy\Db\Sql\Update::data
     * @covers Lazy\Db\Sql\Update::where
     * @covers Lazy\Db\Sql\Update::order
     * @covers Lazy\Db\Sql\Update::limit
     * @covers Lazy\Db\Sql\Update::__call
     * @covers Lazy\Db\Sql\Update::__toString
     */
    public function testComplex()
    {
        $update = new Update(DbSample::getPdo(), 'foo');
        $update->data(['foo' => 'foo', 'bar' => 1])
            ->where('foo IN(?)', [[1, 2, 3]])
            ->orWhere('bar = ?', 'bar')
            ->order('foo')
            ->limit(10);

        $expected = "UPDATE foo SET foo = 'foo', bar = 1 WHERE (foo IN(1, 2, 3)) OR (bar = 'bar') ORDER BY foo ASC LIMIT 10";
        $this->assertSame($expected, (String) $update);
    }

    /**
     * @covers Lazy\Db\Sql\Update::reset
     */
    public function testReset()
    {
        $update = new Update(DbSample::getPdo(), 'foo');
        $update->data(['foo' => 'foo', 'bar' => 1])
            ->where('foo IN(?)', [[1, 2, 3]])
            ->order('foo')
            ->limit(10);

        $expected = "UPDATE foo SET foo = 'foo', bar = 1 WHERE (foo IN(1, 2, 3)) ORDER BY foo ASC LIMIT 10";
        $this->assertSame($expected, (String) $update);

        $update->reset();
        $expected = "UPDATE foo SET ";
        $this->assertSame($expected, (String) $update);
    }

    /**
     * @covers Lazy\Db\Sql\Update::__call
     * @covers Lazy\Db\Sql\Update::__toString
     */
    public function testResetPart()
    {
        $update = new Update(DbSample::getPdo(), 'foo');
        $update->data(['foo' => 'foo', 'bar' => 1])
            ->where('foo IN(?)', [[1, 2, 3]])
            ->order('foo')
            ->limit(10);

        $expected = "UPDATE foo SET foo = 'foo', bar = 1 WHERE (foo IN(1, 2, 3)) ORDER BY foo ASC LIMIT 10";
        $this->assertSame($expected, (String) $update);

        $update->resetWhere()->resetOrder()->resetLimit();
        $expected = "UPDATE foo SET foo = 'foo', bar = 1";
        $this->assertSame($expected, (String) $update);
    }

    /**
     * @covers Lazy\Db\Sql\Update::exec
     */
    public function testExec()
    {
        $pdo = DbSample::getPdo();
        $update = new Update($pdo, 'users');
        $update->data(['name' => 'name888'])->limit(1);

        $pdo->beginTransaction();
        $this->assertSame(1, $update->exec());
        $pdo->rollBack();
    }

    /**
     * @covers Lazy\Db\Sql\Update::__call
     * @expectedException Lazy\Db\Exception\Exception
     * @expectedExceptionMessage Call undefined method undefinedMethod
     */
    public function testCallUndefinedMethodShouldThrowAnException()
    {
        $delete = new Update(DbSample::getPdo());
        $delete->undefinedMethod();
    }
}