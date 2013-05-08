<?php

namespace LazyTest\Db\Model;
use Model\User;

class ManyToMany extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $user = User::first();
        $permissions = $user->Permissions;

        $this->assertInstanceOf('\Model\Collection\Permissions', $permissions);
        $this->assertSame(2, count($permissions));
        $this->assertSame($permissions, $user->Permissions);
    }
}