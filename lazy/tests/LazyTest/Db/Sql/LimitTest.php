<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Limit;

class LimitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Limit::__toString
     */
    public function testNothingReturnsAnEmptyString()
    {
        $limit = new Limit();
        $this->assertEquals('', $limit);
    }

    /**
     * @covers Lazy\Db\Sql\Limit::limit
     * @covers Lazy\Db\Sql\Limit::__toString
     */
    public function testWithNoOffset()
    {
        $limit = new Limit();
        $limit->limit(10);
        $this->assertSame(10, $limit->limit());
        $this->assertEquals('LIMIT 10', $limit);
    }

    /**
     * @covers Lazy\Db\Sql\Limit::offset
     * @covers Lazy\Db\Sql\Limit::__toString
     */
    public function testWithOffset()
    {
        $limit = new Limit();
        $limit->limit(10)->offset(2);
        $this->assertSame(2, $limit->offset());
        $this->assertEquals('LIMIT 10 OFFSET 2', $limit);
    }

    /**
     * @covers Lazy\Db\Sql\Limit::reset
     */
    public function testReset()
    {
        $limit = new Limit();
        $limit->limit(10)->offset(2);
        $this->assertSame(10, $limit->limit());
        $this->assertSame(2, $limit->offset());
        $limit->reset();
        $this->assertNull($limit->limit());
        $this->assertNull($limit->offset());
    }
}