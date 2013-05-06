<?php

namespace LazyTest\Db\Model;
use Model\User;

class OneToManyTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $user = User::first();
        $orders = $user->Orders;

        $this->assertInstanceOf('\Model\Collection\Orders', $orders);
        $this->assertSame(4, count($orders));
        $this->assertSame($orders, $user->Orders);
    }
}