<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Order;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Order::__toString
     */
    public function testNothingReturnsAnEmptyString()
    {
        $order = new Order();
        $this->assertEquals('', $order);
    }

    /**
     * @covers Lazy\Db\Sql\Order::order
     * @covers Lazy\Db\Sql\Order::__toString
     */
    public function testWithParamString()
    {
        $order = new Order();
        $order->order('foo');
        $this->assertSame(array('foo ASC'), $order->order());
        $this->assertEquals('ORDER BY foo ASC', $order);

        $order = new Order();
        $order->order('foo DESC');
        $this->assertSame(array('foo DESC'), $order->order());
        $this->assertEquals('ORDER BY foo DESC', $order);

        $order = new Order();
        $order->order('foo desc');
        $this->assertSame(array('foo DESC'), $order->order());
        $this->assertEquals('ORDER BY foo DESC', $order);

        $order = new Order();
        $order->order('foo DESC, bar');
        $this->assertSame(array('foo DESC', 'bar ASC'), $order->order());
        $this->assertEquals('ORDER BY foo DESC, bar ASC', $order);
    }

    /**
     * @covers Lazy\Db\Sql\Order::order
     * @covers Lazy\Db\Sql\Order::__toString
     */
    public function testWithParamArray()
    {
        $order = new Order();
        $order->order(array('foo', 'bar' => 'asc', 'baz' => 'DESC', 'qux'));
        $this->assertSame(array('foo ASC', 'bar ASC', 'baz DESC', 'qux ASC'), $order->order());
        $this->assertEquals('ORDER BY foo ASC, bar ASC, baz DESC, qux ASC', $order);
    }

    public function testReset()
    {
        $order = new Order();
        $order->order('foo');
        $this->assertSame(array('foo ASC'), $order->order());
        $order->reset();
        $this->assertSame(array(), $order->order());
    }
}