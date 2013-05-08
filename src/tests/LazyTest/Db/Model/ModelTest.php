<?php

namespace LazyTest\Db\Model;

use LazyTest\Db\DbSample;
use Model\User;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    protected $mockPdo;
    protected $mockModel;
    protected $mockCollection;

    public function setUp()
    {
        $mockPdo = $this->getMock('\PdoMock', array('quote'));
        $this->mockPdo = $mockPdo;

        $mockModel = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            array('getPdo', 'primaryKey', 'tableName', 'immediatelySelectColumns', 'collectionClass'));
        $mockModel::staticExpects($this->any())
            ->method('getPdo')
            ->will($this->returnValue($mockPdo));

        $mockModel::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('id'));

        $mockModel::staticExpects($this->any())
            ->method('tableName')
            ->will($this->returnValue('foo'));

        $mockModel::staticExpects($this->any())
            ->method('immediatelySelectColumns')
            ->will($this->returnValue(array('bar', 'baz')));

        $this->mockModel = $mockModel;

        $mockCollection = $this->getMockClass('\Lazy\Db\Model\AbstractCollection', array('modelClass'));
        $mockCollection::staticExpects($this->any())
            ->method('modelClass')
            ->will($this->returnValue($mockModel));

        $this->mockCollection = $mockCollection;

        $mockModel::staticExpects($this->any())
            ->method('collectionClass')
            ->will($this->returnValue($this->mockCollection));
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::createSqlSelect
     */
    public function testCreateSqlSelect()
    {
        $model = $this->mockModel;
        $select = $model::createSqlSelect();
        $this->assertInstanceOf('\Lazy\Db\Sql\Select', $select);

        $expected = "SELECT bar, baz FROM foo";
        $this->assertEquals($expected, $select->__toString());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::all
     */
    public function testMethodAll()
    {
        $model = $this->mockModel;

        $collection = $model::all();
        $this->assertInstanceOf('\Lazy\Db\Model\AbstractCollection', $collection);
        $expected = "SELECT bar, baz FROM foo";
        $this->assertSame($expected, $collection->sqlSelect()->__toString());

        $collection = $model::all(array('id IN(1, 2, 3)'));
        $this->assertInstanceOf('\Lazy\Db\Model\AbstractCollection', $collection);
        $expected = "SELECT bar, baz FROM foo WHERE (id IN(1, 2, 3))";
        $this->assertSame($expected, $collection->sqlSelect()->__toString());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testMethodFirstReturnNull()
    {
        $mockSelect = $this->getMockBuilder('\Lazy\Db\Sql\Select')->disableOriginalConstructor()->getMock();
        $mockSelect->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(false));

        $mockModel = $this->getMockForAbstractClass('\Lazy\Db\Model\AbstractModel',
            array(), '', false, true, true, array('createSqlSelect'));
        $mockModel::staticExpects($this->any())
            ->method('createSqlSelect')
            ->will($this->returnValue($mockSelect));

        $first = $mockModel::first();
        $this->assertNull($first);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testMethodFirstReturnInstanceOfModel()
    {
        $mockSelect = $this->getMockBuilder('\Lazy\Db\Sql\Select')->disableOriginalConstructor()->getMock();
        $mockSelect->expects($this->any())
            ->method('fetch')
            ->will($this->returnValue(array('foo' => 'foo')));

        $mockModel = $this->getMockForAbstractClass('\Lazy\Db\Model\AbstractModel',
            array(), '', false, true, true, array('columnSchema', 'createSqlSelect'));

        $mockModel::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(array()));

        $mockModel::staticExpects($this->any())
            ->method('createSqlSelect')
            ->will($this->returnValue($mockSelect));


        $first = $mockModel::first();
        $this->assertInstanceOf(get_class($mockModel), $first);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::__construct
     * @covers \Lazy\Db\Model\AbstractModel::isNew
     */
    public function test__construct()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            array('primaryKey', 'columnSchema', 'getPdo'));

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(array('foo' => array('type' => 'int'), 'bar' => array('type' => 'varchar'))));

        $model = new $mockModelClass(array('foo' => 1, 'bar' => 'bar'));
        $this->assertFalse($model->isNew());

        $model = new $mockModelClass(array('bar' => 'bar'));
        $this->assertTrue($model->isNew());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::id
     */
    public function testMethodId()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            array('primaryKey', 'columnSchema', 'getPdo'));

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(array('foo' => array('type' => 'int'), 'bar' => array('type' => 'varchar'))));

        $model = new $mockModelClass(array('foo' => 10));
        $this->assertSame(10, $model->id());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::toArray
     */
    public function testMethodToArray()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            array('primaryKey', 'columnSchema', 'getPdo'));

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(array('foo' => array('type' => 'int'), 'bar' => array('type' => 'varchar'))));

        $model = new $mockModelClass(array('foo' => 1));
        $this->assertInternalType('array', $model->toArray());
        $this->assertSame(array('foo' => 1), $model->toArray());

        $model->bar = 'bar';
        $this->assertSame(array('foo' => 1, 'bar' => 'bar'), $model->toArray());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::fromArray
     */
    public function testFromArrayWithExistingRow()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            array('primaryKey', 'columnSchema', 'getPdo'));

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(array('foo' => array('type' => 'int'), 'bar' => array('type' => 'varchar'))));

        $model = new $mockModelClass(array('foo' => 1, 'bar' => 'bar'));
        $this->assertSame(array('foo' => 1, 'bar' => 'bar'), $model->toArray());
        $model->fromArray(array('bar' => 'baz'));
        $this->assertSame(array('foo' => 1, 'bar' => 'baz'), $model->toArray());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testFirstWithNoArgs()
    {
        $user = User::first();
        $this->assertInstanceOf('\Model\User', $user);
        $this->assertSame(1, $user->id);
        $this->assertSame('name1', $user->name);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testFirstWithPrimaryKey()
    {
        $user = User::first(2);
        $this->assertSame(2, $user->id);
        $this->assertSame('name2', $user->name);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testFirstWithWhereStringCondition()
    {
        $user = User::first("name = 'name3'");
        $this->assertSame(3, $user->id);
        $this->assertSame('name3', $user->name);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testFirstWithWhereArrayCondition()
    {
        $user = User::first(array('name' => 'name4'));
        $this->assertSame(4, $user->id);
        $this->assertSame('name4', $user->name);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::first
     */
    public function testFirstShouldReturnNullWithNonExistingRow()
    {
        $user = User::first(9999);
        $this->assertNull($user);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::__get
     * @expectedException \Lazy\Db\Exception\Exception
     * @expectedExceptionMessage Call undefined property non_existing_column
     */
    public function testGetNonExistingColumnShouldThrowAnException()
    {
        $user = User::first();
        $user->non_existing_column;
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::__get
     */
    public function testGetPropertyShouldSupportCamelize()
    {
        $user = User::first();
        $this->assertSame($user->createdTime, $user->created_time);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::__get
     * @covers \Lazy\Db\Model\AbstractModel::__set
     */
    public function test__setAndReset()
    {
        $user = User::first();
        $name = uniqid();
        $user->name = $name;

        $this->assertSame($name, $user->name);
        $user->reset();
        $this->assertSame('name1', $user->name);
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::save
     * @covers \Lazy\Db\Model\AbstractModel::delete
     */
    public function testInsertAndDelete()
    {
        $name = uniqid();
        $user = new User(array('name' => $name));
        $user->save();
        $this->assertSame(5, $user->id);
        $this->assertSame($name, $user->name);

        $user->delete();
        $this->assertNull(User::first(5));
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::save
     * @covers \Lazy\Db\Model\AbstractModel::delete
     */
    public function testUpdate()
    {
        $name = uniqid();
        $user = new User(array('name' => $name));
        $user->save();

        $this->assertSame($name, $user->name);
        $newName = uniqid('update');
        $user->name  = $newName;
        $user->save();
        $this->assertSame($newName, $user->name);
        $this->assertSame($newName, User::first($user->id)->name);
        $user->delete();
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::create
     */
    public function testCreate()
    {
        $user = User::create(array('name' => 'namexxx'));
        $this->assertInstanceOf('\Model\User', $user);
        $this->assertSame('namexxx', $user->name);
    }

    public function testStaticInsert()
    {
        $data = array(
            array('name' => 'name_1'),
            array('name' => 'name_2'),
            array('name' => 'name_3'),
        );

        $count = User::all()->countAll();

        $result = User::insert($data);
        $this->assertSame(3, $result);
        $this->assertSame($count + 3, User::all()->countAll());

        DbSample::reset();
    }

    public function testStaticUpdate()
    {
        $data = array(
            'name' => 'name_xxx'
        );

        $result = User::update($data, array('id > ?' => 1));
        $this->assertSame(3, $result);

        $user1 = User::first();
        $this->assertSame('name1', $user1->name);

        $user2 = User::first(2);
        $this->assertSame('name_xxx', $user2->name);

        $user2 = User::first(3);
        $this->assertSame('name_xxx', $user2->name);

        DbSample::reset();
    }

    public function testStaticUpdateWithPrimaryKeyValue()
    {
        $user = User::first();
        $this->assertSame('name1', $user->name);

        $data = array(
            'name' => 'name_xxx'
        );

        User::update($data, $user->id);
        $this->assertSame('name_xxx', User::first()->name);
        $this->assertSame('name2', User::first(2)->name);

        DbSample::reset();

    }

    public function testStaticRemove()
    {
        $count = User::all()->countAll();
        $result = User::remove(array('id IN(?)' => array(1, 3)));

        $this->assertSame(2, $result);
        $this->assertSame($count - 2, User::all()->countAll());
        $this->assertNull(User::first(1));
        $this->assertNull(User::first(3));

        DbSample::reset();
    }

    public function testStaticRemoveWithPrimaryKeyValue()
    {
        $count = User::all()->countAll();
        User::remove(1);
        $this->assertSame($count - 1, User::all()->countAll());

        DbSample::reset();
    }
}