<?php

namespace LazyTest\Db\Model;

use Model\User;

class ModelTest extends \PHPUnit_Framework_TestCase
{
    protected $mockPdo;
    protected $mockModel;
    protected $mockCollection;

    public function setUp()
    {
        $mockPdo = $this->getMock('\PdoMock', ['quote']);
        $this->mockPdo = $mockPdo;

        $mockModel = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            ['getPdo', 'primaryKey', 'tableName', 'immediatelySelectColumns', 'collectionClass']);
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
            ->will($this->returnValue(['bar', 'baz']));

        $this->mockModel = $mockModel;

        $mockCollection = $this->getMockClass('\Lazy\Db\Model\AbstractCollection', ['modelClass']);
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

        $collection = $model::all(['id IN(1, 2, 3)']);
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
            [], '', false, true, true, ['createSqlSelect']);
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
            ->will($this->returnValue(['foo' => 'foo']));

        $mockModel = $this->getMockForAbstractClass('\Lazy\Db\Model\AbstractModel',
            [], '', false, true, true, ['columnSchema', 'createSqlSelect']);

        $mockModel::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue([]));

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
            ['primaryKey', 'columnSchema', 'getPdo']);

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(['foo' => ['type' => 'int'], 'bar' => ['type' => 'varchar']]));

        $model = new $mockModelClass(['foo' => 1, 'bar' => 'bar']);
        $this->assertFalse($model->isNew());

        $model = new $mockModelClass(['bar' => 'bar']);
        $this->assertTrue($model->isNew());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::id
     */
    public function testMethodId()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            ['primaryKey', 'columnSchema', 'getPdo']);

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(['foo' => ['type' => 'int'], 'bar' => ['type' => 'varchar']]));

        $model = new $mockModelClass(['foo' => 10]);
        $this->assertSame(10, $model->id());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::toArray
     */
    public function testMethodToArray()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            ['primaryKey', 'columnSchema', 'getPdo']);

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(['foo' => ['type' => 'int'], 'bar' => ['type' => 'varchar']]));

        $model = new $mockModelClass(['foo' => 1]);
        $this->assertInternalType('array', $model->toArray());
        $this->assertSame(['foo' => 1], $model->toArray());

        $model->bar = 'bar';
        $this->assertSame(['foo' => 1, 'bar' => 'bar'], $model->toArray());
    }

    /**
     * @covers \Lazy\Db\Model\AbstractModel::fromArray
     */
    public function testFromArrayWithExistingRow()
    {
        $mockModelClass = $this->getMockClass('\Lazy\Db\Model\AbstractModel',
            ['primaryKey', 'columnSchema', 'getPdo']);

        $mockModelClass::staticExpects($this->any())
            ->method('primaryKey')
            ->will($this->returnValue('foo'));

        $mockModelClass::staticExpects($this->any())
            ->method('columnSchema')
            ->will($this->returnValue(['foo' => ['type' => 'int'], 'bar' => ['type' => 'varchar']]));

        $model = new $mockModelClass(['foo' => 1, 'bar' => 'bar']);
        $this->assertSame(['foo' => 1, 'bar' => 'bar'], $model->toArray());
        $model->fromArray(['bar' => 'baz']);
        $this->assertSame(['foo' => 1, 'bar' => 'baz'], $model->toArray());
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
        $user = User::first(['name' => 'name4']);
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
        $user = new User(['name' => $name]);
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
        $user = new User(['name' => $name]);
        $user->save();

        $this->assertSame($name, $user->name);
        $newName = uniqid('update');
        $user->name  = $newName;
        $user->save();
        $this->assertSame($newName, $user->name);
        $this->assertSame($newName, User::first($user->id)->name);
        $user->delete();
    }
}