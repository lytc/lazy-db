<?php

namespace LazyTest\Db\Sql;

use LazyTest\Db\DbSample;
use Lazy\Db\Expr;
use Lazy\Db\Sql\Where;

class WhereTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testNothingReturnsAnEmptyString()
    {
        $where = new Where(DbSample::getPdo());
        $this->assertEquals('', $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testWithParamString()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo = 'bar' AND bar = 'baz'");
        $expected = "WHERE (foo = 'bar' AND bar = 'baz')";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testWithParamStringAndBindQuestionMarkParams()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo = ? AND bar = ?", array('foo', 'bar'));
        $expected = "WHERE (foo = 'foo' AND bar = 'bar')";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testWithParamStringAndBindNamedParams()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo = :foo AND bar = :bar", array('bar' => 'bar', 'foo' => 'foo'));
        $expected = "WHERE (foo = 'foo' AND bar = 'bar')";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testWithParamStringAndBindBothQuestionMarkAndNamedParams()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo = :foo AND bar = ? AND baz = :baz", array('baz' => 'baz', 'bar', 'foo' => 'foo'));
        $expected = "WHERE (foo = 'foo' AND bar = 'bar' AND baz = 'baz')";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testMultipleWhereWithStringParam()
    {
        $where = new Where(DbSample::getPdo());
        $where->where('foo = ? AND bar = :bar', array('foo', 'bar' => 'bar'));
        $where->where('baz = :baz AND qux = ?', array('baz' => 'baz', 'qux'));
        $expected = "WHERE (foo = 'foo' AND bar = 'bar') AND (baz = 'baz' AND qux = 'qux')";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testWithParamArray()
    {
        $where = new Where(DbSample::getPdo());
        $where->where(array(
            'foo' => 'foo',
            "bar = 'bar' OR baz = ?" => 'baz',
            'qux = 1'
        ));

        $expected = "WHERE (foo = 'foo' AND bar = 'bar' OR baz = 'baz' AND qux = 1)";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testParamArrayAndHasConditionType()
    {
        $where = new Where(DbSample::getPdo());
        $where->where(array(
            'foo' => 'foo',
            "OR bar = 'bar' OR baz = ?" => 'baz',
            'or qux = 1'
        ));

        $expected = "WHERE (foo = 'foo' OR bar = 'bar' OR baz = 'baz' or qux = 1)";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testOrWhere()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo = ?", array('foo'));
        $where->orWhere('bar = :bar', array('bar' => 'bar'));
        $where->where('baz = 1');
        $expected = "WHERE (foo = 'foo') OR (bar = 'bar') AND (baz = 1)";
        $this->assertEquals($expected, (String) $where);

        $where = new Where(DbSample::getPdo());
        $where->where('foo = ?', array('foo'));
        $where->orWhere("bar = ? AND baz = ?", 'bar', 'baz');
        $expected = "WHERE (foo = 'foo') OR (bar = 'bar' AND baz = 'baz')";
        $this->assertEquals($expected, (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testAutoConvertBindParamToArrayIfItIsNotAnArray()
    {
        $where = new Where(DbSample::getPdo());
        $where->where('foo = ?', 'foo');
        $this->assertEquals("WHERE (foo = 'foo')", (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testMultipleBindParams()
    {
        $where = new Where(DbSample::getPdo());
        $where->where('foo = ? AND bar = ?', 'foo', 'bar');
        $this->assertEquals("WHERE (foo = 'foo' AND bar = 'bar')", (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testBindParamShouldNotEscapeIfValueIsExpr()
    {
        $where = new Where(DbSample::getPdo());
        $where->where('foo = ?', new Expr("'foo'"));
        $this->assertEquals("WHERE (foo = 'foo')", (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testBindInWithQuestionMark()
    {
        $where = new Where(DbSample::getPdo());
        $where->where('foo IN(?)', array(1, 2));
        $this->assertEquals("WHERE (foo IN(1, 2))", (String) $where);

        $where = new Where(DbSample::getPdo());
        $where->where('foo IN(?)', array(array(1, 2)));
        $this->assertEquals("WHERE (foo IN(1, 2))", (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testBindInWithNamed()
    {
        $where = new Where(DbSample::getPdo());
        $where->where('foo IN(:foo)', array('foo' => array(1, 2)));
        $this->assertEquals("WHERE (foo IN(1, 2))", (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testLike()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo LIKE '%?%'", new Expr(DbSample::getPdo()->escape("foo'bar")));
        $this->assertEquals("WHERE (foo LIKE '%foo\'bar%')", (String) $where);
    }

    /**
     * @covers Lazy\Db\Sql\Where<extended>
     */
    public function testReset()
    {
        $where = new Where(DbSample::getPdo());
        $where->where("foo = 'bar' AND bar = 'baz'");
        $expected = "WHERE (foo = 'bar' AND bar = 'baz')";
        $this->assertEquals($expected, (String) $where);

        $where->reset();
        $this->assertSame('', $where->__toString());
    }
}