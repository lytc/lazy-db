<?php

namespace LazyTest\Db;

use Lazy\Db\Expr;

class ExprTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $expr = new Expr('COUNT(*)');
        $this->assertSame('COUNT(*)', $expr->__toString());
    }
}