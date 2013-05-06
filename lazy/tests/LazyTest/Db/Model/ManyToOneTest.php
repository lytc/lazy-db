<?php

namespace LazyTest\Db\Model;
use Model\Order;

class ManyToOne extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $order = Order::first();
        $user = $order->User;

        $this->assertInstanceOf('\Model\User', $user);
        $this->assertSame(1, $user->id);
        $this->assertSame($user, $order->User);
    }
}