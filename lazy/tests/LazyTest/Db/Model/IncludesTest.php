<?php

namespace LazyTest\Db\Model;

use Lazy\Db\Stmt;
use Model\Permission;
use Model\User;
use Model\Order;
use Model\Post;

class IncludesTest extends \PHPUnit_Framework_TestCase
{
    public function testIncludesOneToMany()
    {
        Stmt::startLogInstance();
        $users = User::all()->limit(2);//->includes('Orders', 'Posts');

        $this->assertInstanceOf('\Model\Collection\Users', $users);
        $this->assertCount(2, $users);

        $count = 0;
        foreach ($users as $index => $user) {
            $this->assertCount(4, $user->Orders);
            $this->assertSame($users[$index]->Orders, $user->Orders);

            $this->assertCount(4, $user->Posts);
            $this->assertSame($users[$index]->Posts, $user->Posts);

            $count++;
        }

        $this->assertSame(2, $count);

        $this->assertSame(1, $users[0]->Orders[0]->id);
        $this->assertSame('name1', $users[0]->Posts[0]->name);

        $stmtInstances = Stmt::getLogInstances();
        $this->assertCount(3, $stmtInstances);

        $this->assertEquals(User::createSqlSelect()->limit(2), $stmtInstances[0]->queryString);
        $this->assertEquals(Order::createSqlSelect()->where(['user_id IN(?)' => ['1', '2']]), $stmtInstances[1]->queryString);
        $this->assertEquals(Post::createSqlSelect()->where(['user_id IN(?)' => ['1', '2']]), $stmtInstances[2]->queryString);
    }

    public function testIncludesManyToMany()
    {
        Stmt::startLogInstance();

        $users = User::all()->limit(2);//->includes('Permissions');
        $this->assertInstanceOf('\Model\Collection\Users', $users);
        $this->assertCount(2, $users);

        foreach ($users as $index => $user) {
            $this->assertCount(2, $user->Permissions);
            $this->assertSame($users[$index]->Permissions, $user->Permissions);
        }

        $stmtInstances = Stmt::getLogInstances();
        $this->assertCount(2, $stmtInstances);

        $expected = "SELECT " . implode(', ', User::immediatelySelectColumns()) . " FROM users LIMIT 2";
        $this->assertSame($expected, $stmtInstances[0]->queryString);

        $expected = "SELECT " . implode(', ', Permission::immediatelySelectColumns()) . ", user_permissions.user_id FROM permissions";
        $expected .= " INNER JOIN user_permissions ON user_permissions.permission_id = permissions.id WHERE (user_permissions.user_id IN('1', '2'))";
        $this->assertSame($expected, $stmtInstances[1]->queryString);
    }
}