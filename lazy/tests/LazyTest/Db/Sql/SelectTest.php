<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Having;
use Lazy\Db\Sql\Join;
use Lazy\Db\Sql\Limit;
use Lazy\Db\Sql\Select;
use Lazy\Db\Sql\Where;
use Lazy\Db\Sql\Group;
use Lazy\Db\Sql\Order;
use LazyTest\Db\DbSample;

class SelectTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Select::__construct
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testBasic()
    {
        $select = new Select(DbSample::getPdo(), 'foo');
        $expected = "SELECT * FROM foo";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::from
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testFrom()
    {
        $select = new Select(DbSample::getPdo());
        $select->from('foo');
        $expected = "SELECT * FROM foo";
        $this->assertSame('foo', $select->from());
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::column
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testColumn()
    {
        $select = new Select(DbSample::getPdo(), 'foo');
        $select->column('bar, baz b')
            ->column(['qux']);

        $this->assertSame(['bar', 'baz b', 'qux'], $select->column());
        $expected = "SELECT bar, baz b, qux FROM foo";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::join
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testJoin()
    {
        $select = new Select(DbSample::getPdo(), 'foo');
        $select->join('bar', 'bar.foo_id = foo.id');
        $expected = "SELECT * FROM foo INNER JOIN bar ON bar.foo_id = foo.id";

        $join = new Join();
        $join->join('baz', 'baz.foo_id = foo.id');
        $select->join($join);

        $this->assertSame($join, $select->join());
        $expected = "SELECT * FROM foo INNER JOIN baz ON baz.foo_id = foo.id";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::where
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testWhere()
    {
        $pdo = DbSample::getPdo();
        $select = new Select($pdo, 'foo');
        $select->where('bar = ?', 'bar');

        $expected = "SELECT * FROM foo WHERE (bar = 'bar')";
        $this->assertSame($expected, (String) $select);

        $select = new Select($pdo, 'foo');
        $where = new Where($pdo);
        $where->where('baz = ?', 'baz');
        $select->where($where);
        $this->assertSame($where, $select->where());
        $expected = "SELECT * FROM foo WHERE (baz = 'baz')";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::group
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testGroupBy()
    {
        $select = new Select(DbSample::getPdo(), 'foo');
        $select->group('bar, baz');

        $expected = "SELECT * FROM foo GROUP BY bar, baz";
        $this->assertSame($expected, (String) $select);

        $select = new Select(DbSample::getPdo(), 'foo');
        $group = new Group();
        $group->group('baz, qux');
        $select->group($group);
        $this->assertSame($group, $select->group());
        $expected = "SELECT * FROM foo GROUP BY baz, qux";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::having
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testHaving()
    {
        $pdo = DbSample::getPdo();
        $select = new Select($pdo, 'foo');
        $select->having('bar = :bar', ['bar' => 'bar']);

        $expected = "SELECT * FROM foo HAVING (bar = 'bar')";
        $this->assertSame($expected, (String) $select);

        $select = new Select($pdo, 'foo');
        $having = new Having($pdo);
        $having->having('baz = :baz', ['baz' => 'baz']);
        $select->having($having);
        $this->assertSame($having, $select->having());
        $expected = "SELECT * FROM foo HAVING (baz = 'baz')";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::order
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testOrderBy()
    {
        $select = new Select(DbSample::getPdo(), 'foo');
        $select->order('foo, bar DESC');

        $expected = "SELECT * FROM foo ORDER BY foo ASC, bar DESC";
        $this->assertSame($expected, (String) $select);

        $select = new Select(DbSample::getPdo(), 'foo');
        $order = new Order();
        $order->order('bar, baz DESC');
        $select->order($order);
        $this->assertSame($order, $select->order());
        $expected = "SELECT * FROM foo ORDER BY bar ASC, baz DESC";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::limit
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testLimit()
    {
        $select = new Select(DbSample::getPdo(), 'foo');
        $select->limit(10);

        $expected = "SELECT * FROM foo LIMIT 10";
        $this->assertSame($expected, (String) $select);

        $select = new Select(DbSample::getPdo(), 'foo');
        $limit = new Limit();
        $limit->limit(20);
        $select->limit($limit);
        $this->assertSame($limit, $select->limit());
        $expected = "SELECT * FROM foo LIMIT 20";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::__call
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testAliasMethod()
    {
        $select = new Select(DbSample::getPdo());
        $select->from('foo')
            ->column('bar, baz')
            ->join('bar', 'bar.foo_id = foo.id')
            ->leftJoin('baz', 'baz.foo_id = foo.id')
            ->rightJoin('qux', 'qux.foo_id = foo.id')
            ->where('foo = 1')
            ->orWhere('bar = 2')
            ->group('foo')
            ->group('bar')
            ->having('baz = 3')
            ->orHaving('qux = 4')
            ->order('foo')
            ->order('bar DESC')
            ->limit(10)
            ->offset(2)
        ;

        $expected = "SELECT bar, baz FROM foo ";
        $expected .= "INNER JOIN bar ON bar.foo_id = foo.id LEFT JOIN baz ON baz.foo_id = foo.id RIGHT JOIN qux ON qux.foo_id = foo.id ";
        $expected .= "WHERE (foo = 1) OR (bar = 2) GROUP BY foo, bar HAVING (baz = 3) OR (qux = 4) ORDER BY foo ASC, bar DESC LIMIT 10 OFFSET 2";
        $this->assertSame($expected, (String) $select);
    }

    /**
     * @covers Lazy\Db\Sql\Select::getPdo
     */
    public function testGetPdo()
    {
        $pdo = DbSample::getPdo();
        $select = new Select($pdo);
        $this->assertSame($pdo, $select->getPdo());
    }

    /**
     * @covers Lazy\Db\Sql\Select::resetColumn
     */
    public function testResetColumn()
    {
        $select = new Select(DbSample::getPdo());
        $select->column('foo, bar');
        $this->assertSame(['foo', 'bar'], $select->column());
        $select->resetColumn();
        $this->assertSame([], $select->column());
    }

    /**
     * @covers Lazy\Db\Sql\Select::reset
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testReset()
    {
        $select = new Select(DbSample::getPdo());
        $select->from('foo')
            ->column('bar, baz')
            ->join('bar', 'bar.foo_id = foo.id')
            ->leftJoin('baz', 'baz.foo_id = foo.id')
            ->rightJoin('qux', 'qux.foo_id = foo.id')
            ->where('foo = 1')
            ->orWhere('bar = 2')
            ->group('foo')
            ->group('bar')
            ->having('baz = 3')
            ->orHaving('qux = 4')
            ->order('foo')
            ->order('bar DESC')
            ->limit(10)
            ->offset(2)
        ;

        $expected = "SELECT bar, baz FROM foo ";
        $expected .= "INNER JOIN bar ON bar.foo_id = foo.id LEFT JOIN baz ON baz.foo_id = foo.id RIGHT JOIN qux ON qux.foo_id = foo.id ";
        $expected .= "WHERE (foo = 1) OR (bar = 2) GROUP BY foo, bar HAVING (baz = 3) OR (qux = 4) ORDER BY foo ASC, bar DESC LIMIT 10 OFFSET 2";
        $this->assertSame($expected, (String) $select);

        $select->reset();
        $this->assertSame("SELECT * FROM foo", $select->__toString());
    }

    /**
     * @covers Lazy\Db\Sql\Select::__call
     * @covers Lazy\Db\Sql\Select::__toString
     */
    public function testResetPart()
    {
        $select = new Select(DbSample::getPdo());
        $select->from('foo')
            ->column('bar, baz')
            ->join('bar', 'bar.foo_id = foo.id')
            ->leftJoin('baz', 'baz.foo_id = foo.id')
            ->rightJoin('qux', 'qux.foo_id = foo.id')
            ->where('foo = 1')
            ->orWhere('bar = 2')
            ->group('foo')
            ->group('bar')
            ->having('baz = 3')
            ->orHaving('qux = 4')
            ->order('foo')
            ->order('bar DESC')
            ->limit(10)
            ->offset(2)
        ;

        $expected = "SELECT bar, baz FROM foo ";
        $expected .= "INNER JOIN bar ON bar.foo_id = foo.id LEFT JOIN baz ON baz.foo_id = foo.id RIGHT JOIN qux ON qux.foo_id = foo.id ";
        $expected .= "WHERE (foo = 1) OR (bar = 2) GROUP BY foo, bar HAVING (baz = 3) OR (qux = 4) ORDER BY foo ASC, bar DESC LIMIT 10 OFFSET 2";
        $this->assertSame($expected, (String) $select);

        $select->resetJoin()->resetWhere()->resetHaving()->resetOrder()->resetGroup()->resetLimit();
        $this->assertSame("SELECT bar, baz FROM foo", $select->__toString());
    }

    /**
     * @covers Lazy\Db\Sql\Select::__call
     * @expectedException Lazy\Db\Exception\Exception
     * @expectedExceptionMessage Call undefined method undefinedMethod
     */
    public function testCallUndefinedMethodShouldThrowAnException()
    {
        $select = new Select(DbSample::getPdo());
        $select->undefinedMethod();
    }

    /**
     * @covers Lazy\Db\Sql\Select::query
     */
    public function testQuery()
    {
        $select = new Select(DbSample::getPdo(), 'users');
        $this->assertInstanceOf('\Lazy\Db\Stmt', $select->query());
    }

    /**
     * @covers Lazy\Db\Sql\Select::fetch
     */
    public function testFetch()
    {
        $pdo = DbSample::getPdo();
        $select = new Select($pdo, 'users');
        $stmt = $pdo->query($select);
        $this->assertSame($select->fetch(), $stmt->fetch());
    }

    /**
     * @covers Lazy\Db\Sql\Select::fetchAll
     */
    public function testFetchAll()
    {
        $pdo = DbSample::getPdo();
        $select = new Select($pdo, 'users');
        $stmt = $pdo->query($select);
        $this->assertSame($select->fetchAll(), $stmt->fetchAll());
    }

    /**
     * @covers Lazy\Db\Sql\Select::fetchColumn
     */
    public function testFetchColumn()
    {
        $pdo = DbSample::getPdo();
        $select = new Select($pdo, 'users');
        $stmt = $pdo->query($select);
        $this->assertSame($select->fetchColumn(), $stmt->fetchColumn());
    }
}