<?php

namespace LazyTest\Db\Model;

use Lazy\Db\Stmt;
use LazyTest\Db\DbSample;
use Model\User;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testAll()
    {
        $users = User::all();
        $this->assertInstanceOf('\Model\Collection\Users', $users);
        $this->assertSame(4, count($users));
    }

    public function testCollectionGetModelById()
    {
        $users = User::all();
        $this->assertInstanceOf('\Model\User', $users->get(1));
        $this->assertSame(1, $users->get(1)->id);
        $this->assertSame('name1', $users->get(1)->name);
        $this->assertNull($users->get(9999));
    }

    public function testColumn()
    {
        Stmt::startLogInstance();

        $users = User::all();
        $users->column('name');
        $user = $users[0];
        $this->assertSame(1, $user->id);
        $this->assertSame('name1', $user->name);

        $stmtInstances = Stmt::getLogInstances();
        $this->assertCount(1, $stmtInstances);
        $this->assertSame("SELECT id, name FROM users", $stmtInstances[0]->queryString);
//        $this->assertNull($user->username);
    }

    public function testWhereAndOrWhereCondition()
    {
        $users = User::all();
        $users->where(array('id IN(?)' => array(2, 3)))->orWhere(array('name' => 'name4'));
        $this->assertSame(3, count($users));
    }

    public function testOrderAndLimitAndOffset()
    {
        $users = User::all();
        $users->order('id DESC')->limit(2)->offset(1);
        $this->assertSame(2, count($users));
        $this->assertSame($users[0], $users->get(3));
    }

    public function testIterator()
    {
        $users = User::all();
        $count = 0;

        foreach ($users as $index => $user) {
            $this->assertInstanceOf('\Model\User', $user);
            $this->assertSame($index + 1, $user->id);
            $this->assertSame($user, $users[$index]);
            $count++;
        }

        $this->assertSame(4, $count);
    }

    public function testFetchColumn()
    {
        $users = User::all()->limit(2);
        $this->assertSame(array('1', '2'), $users->fetchColumn());
        $this->assertSame(array('name1', 'name2'), $users->fetchColumn(1));
        $this->assertSame(array('name1', 'name2'), $users->fetchColumn('name'));
    }

    public function testFetchPair()
    {
        $users = User::all()->limit(2);

        $this->assertSame(array(1 => 'name1', 2 => 'name2'), $users->fetchPair());
        $this->assertSame(array('name1' => '1', 'name2' => '2'), $users->fetchPair(1, 0));
        $this->assertSame(array('name1' => '1', 'name2' => '2'), $users->fetchPair('name', 'id'));
    }

    public function testCountAll()
    {
        $users = User::all()->limit(2);
        $this->assertEquals(4, $users->countAll());
    }

    public function testDelete()
    {
        $users = User::all();
        $this->assertCount(4, $users);

        $users->delete();
        $this->assertCount(0, $users);
        $this->assertSame(array(), $users->toArray());
        $this->assertNull($users->get(1));

        $users = User::all();
        $this->assertCount(0, $users);

        DbSample::reset();
    }
}