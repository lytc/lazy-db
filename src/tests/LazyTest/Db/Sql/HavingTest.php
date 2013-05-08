<?php

namespace LazyTest\Db\Sql;

use LazyTest\Db\DbSample;
use Lazy\Db\Expr;
use Lazy\Db\Sql\Having;

class HavingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testNothingReturnsAnEmptyString()
    {
        $Having = new Having(DbSample::getPdo());
        $this->assertEquals('', $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testWithParamString()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo = 'bar' AND bar = 'baz'");
        $expected = "HAVING (foo = 'bar' AND bar = 'baz')";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testWithParamStringAndBindQuestionMarkParams()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo = ? AND bar = ?", array('foo', 'bar'));
        $expected = "HAVING (foo = 'foo' AND bar = 'bar')";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testWithParamStringAndBindNamedParams()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo = :foo AND bar = :bar", array('bar' => 'bar', 'foo' => 'foo'));
        $expected = "HAVING (foo = 'foo' AND bar = 'bar')";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testWithParamStringAndBindBothQuestionMarkAndNamedParams()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo = :foo AND bar = ? AND baz = :baz", array('baz' => 'baz', 'bar', 'foo' => 'foo'));
        $expected = "HAVING (foo = 'foo' AND bar = 'bar' AND baz = 'baz')";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testMultipleHavingWithStringParam()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo = ? AND bar = :bar', array('foo', 'bar' => 'bar'));
        $Having->Having('baz = :baz AND qux = ?', array('baz' => 'baz', 'qux'));
        $expected = "HAVING (foo = 'foo' AND bar = 'bar') AND (baz = 'baz' AND qux = 'qux')";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testWithParamArray()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having(array(
            'foo' => 'foo',
            "bar = 'bar' OR baz = ?" => 'baz',
            'qux = 1'
        ));

        $expected = "HAVING (foo = 'foo' AND bar = 'bar' OR baz = 'baz' AND qux = 1)";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testParamArrayAndHasConditionType()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having(array(
            'foo' => 'foo',
            "OR bar = 'bar' OR baz = ?" => 'baz',
            'or qux = 1'
        ));

        $expected = "HAVING (foo = 'foo' OR bar = 'bar' OR baz = 'baz' or qux = 1)";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testOrHaving()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo = ?", array('foo'));
        $Having->orHaving('bar = :bar', array('bar' => 'bar'));
        $Having->Having('baz = 1');
        $expected = "HAVING (foo = 'foo') OR (bar = 'bar') AND (baz = 1)";
        $this->assertEquals($expected, (String) $Having);

        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo = ?', array('foo'));
        $Having->orHaving("bar = ? AND baz = ?", 'bar', 'baz');
        $expected = "HAVING (foo = 'foo') OR (bar = 'bar' AND baz = 'baz')";
        $this->assertEquals($expected, (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testAutoConvertBindParamToArrayIfItIsNotAnArray()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo = ?', 'foo');
        $this->assertEquals("HAVING (foo = 'foo')", (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testMultipleBindParams()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo = ? AND bar = ?', 'foo', 'bar');
        $this->assertEquals("HAVING (foo = 'foo' AND bar = 'bar')", (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testBindParamShouldNotEscapeIfValueIsExpr()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo = ?', new Expr("'foo'"));
        $this->assertEquals("HAVING (foo = 'foo')", (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testBindInWithQuestionMark()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo IN(?)', array(1, 2));
        $this->assertEquals("HAVING (foo IN(1, 2))", (String) $Having);

        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo IN(?)', array(array(1, 2)));
        $this->assertEquals("HAVING (foo IN(1, 2))", (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testBindInWithNamed()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having('foo IN(:foo)', array('foo' => array(1, 2)));
        $this->assertEquals("HAVING (foo IN(1, 2))", (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testLike()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo LIKE '%?%'", new Expr(DbSample::getPdo()->escape("foo'bar")));
        $this->assertEquals("HAVING (foo LIKE '%foo\'bar%')", (String) $Having);
    }

    /**
     * @covers Lazy\Db\Sql\Having<extended>
     */
    public function testReset()
    {
        $Having = new Having(DbSample::getPdo());
        $Having->Having("foo = 'bar' AND bar = 'baz'");
        $expected = "HAVING (foo = 'bar' AND bar = 'baz')";
        $this->assertEquals($expected, (String) $Having);

        $Having->reset();
        $this->assertSame('', $Having->__toString());
    }
}