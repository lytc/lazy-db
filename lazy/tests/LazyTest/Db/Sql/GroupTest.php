<?php

namespace LazyTest\Db\Sql;

use Lazy\Db\Sql\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Group::__toString
     */
    public function testNothingShouldReturnsAnEmptyString()
    {
        $group = new Group();
        $this->assertSame('', (String) $group);
    }

    /**
     * @covers Lazy\Db\Sql\Group::group
     * @covers Lazy\Db\Sql\Group::__toString
     */
    public function testGroup()
    {
        $group = new Group();
        $group->group('foo')
            ->group('bar, baz')
            ->group(['qux', 'oop']);

        $this->assertSame(['foo', 'bar', 'baz', 'qux', 'oop'], $group->group());
        $this->assertSame('GROUP BY foo, bar, baz, qux, oop', (String) $group);
    }

    /**
     * @covers Lazy\Db\Sql\Group::reset
     */
    public function testReset()
    {
        $group = new Group();
        $group->group('foo');
        $this->assertSame(['foo'], $group->group());
        $group->reset();
        $this->assertSame([], $group->group());
    }
}