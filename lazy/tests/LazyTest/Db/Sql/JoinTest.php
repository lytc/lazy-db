<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Join;

class JoinTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testNothingShouldReturnsAnEmptyString()
    {
        $join = new Join();
        $this->assertSame('', (String) $join);
    }

    /**
     * @covers Lazy\Db\Sql\Join::innerJoin
     * @covers Lazy\Db\Sql\Join::_join
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testInnerJoin()
    {
        $join = new Join();
        $join->innerJoin('bar', 'bar.foo_id = foo.id');
        $this->assertSame('INNER JOIN bar ON bar.foo_id = foo.id', (String) $join);
    }

    /**
     * @covers Lazy\Db\Sql\Join::leftJoin
     * @covers Lazy\Db\Sql\Join::_join
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testLeftJoin()
    {
        $join = new Join();
        $join->leftJoin('bar', 'bar.foo_id = foo.id');
        $this->assertSame('LEFT JOIN bar ON bar.foo_id = foo.id', (String) $join);
    }

    /**
     * @covers Lazy\Db\Sql\Join::rightJoin
     * @covers Lazy\Db\Sql\Join::_join
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testRightJoin()
    {
        $join = new Join();
        $join->rightJoin('bar', 'bar.foo_id = foo.id');
        $this->assertSame('RIGHT JOIN bar ON bar.foo_id = foo.id', (String) $join);
    }


    /**
     * @covers Lazy\Db\Sql\Join::join
     * @covers Lazy\Db\Sql\Join::_join
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testJoinShouldFallbackToInnerJoin()
    {
        $join = new Join();
        $join->join('bar', 'bar.foo_id = foo.id');

        $join2 = new Join();
        $join2->innerJoin('bar', 'bar.foo_id = foo.id');

        $this->assertSame((String) $join, (String) $join2);
    }
    /**
     * @covers Lazy\Db\Sql\Join::innerJoin
     * @covers Lazy\Db\Sql\Join::leftJoin
     * @covers Lazy\Db\Sql\Join::rightJoin
     * @covers Lazy\Db\Sql\Join::_join
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testMultipleJoin()
    {
        $join = new Join();
        $join->innerJoin('bar', 'bar.foo_id = foo.id')
            ->leftJoin('baz b', 'b.bar_id = bar.id')
            ->rightJoin('qux q', 'q.bar_id = bar.id')
        ;

        $expected = 'INNER JOIN bar ON bar.foo_id = foo.id LEFT JOIN baz b ON b.bar_id = bar.id RIGHT JOIN qux q ON q.bar_id = bar.id';
        $this->assertSame($expected, (String) $join);
    }

    /**
     * @covers Lazy\Db\Sql\Join::reset
     * @covers Lazy\Db\Sql\Join::__toString
     */
    public function testReset()
    {
        $join = new Join();
        $join->innerJoin('bar', 'bar.foo_id = foo.id')
            ->leftJoin('baz b', 'b.bar_id = bar.id')
            ->rightJoin('qux q', 'q.bar_id = bar.id');

        $expected = 'INNER JOIN bar ON bar.foo_id = foo.id LEFT JOIN baz b ON b.bar_id = bar.id RIGHT JOIN qux q ON q.bar_id = bar.id';
        $this->assertSame($expected, (String) $join);
        $join->reset();
        $this->assertSame('', $join->__toString());
    }
}