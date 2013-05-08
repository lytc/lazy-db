<?php

namespace LazyTest\Db\Model\TestDefaultProp;

class Comment extends \Lazy\Db\Model\AbstractModel
{
    protected static $columnsSchema = array(
        'id'            => 'int',
        'name'          => 'varchar',
        'username'      => 'varchar',
        'password'      => 'varchar',
        'created_time'  => 'datetime',
        'modified_time' => 'timestamp',
        'status'        => 'tinyint'
    );
}

class Post extends \Lazy\Db\Model\AbstractModel
{
    protected static $columnsSchema = array(
        'id'            => 'int',
        'name'          => 'varchar',
        'summary'       => 'text',
        'content'       => 'mediumtext',
        'created_time'  => 'datetime',
        'modified_time' => 'timestamp',
        'status'        => 'tinyint'
    );

    protected static $oneToMany = array('Comments');
    protected static $manyToOne = array('User');
}

class User extends \Lazy\Db\Model\AbstractModel
{
    protected static $manyToMany = array('Permissions');
}

class Posts extends \Lazy\Db\Model\AbstractCollection
{

}

class DefaultPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaultPrimaryKey()
    {
        Comment::primaryKey();
        $this->assertSame('id', Post::primaryKey());
    }

    public function testModelDefaultTableName()
    {
        Comment::tableName();
        $this->assertSame('posts', Post::tableName());
    }

    public function testModelDefaultCollectionClassName()
    {
        Comment::collectionClass();
        $this->assertSame('\LazyTest\Db\Model\TestDefaultProp\Posts', Post::collectionClass());
    }

    public function testModelDefaultColumnsSchema()
    {
        Comment::columnSchema();
        $expected = array(
            'id'            => array('type' => 'int', 'length' => 11, 'nullable' => false, 'unsigned' => true,
                                    'primaryKey' => true, 'autoIncrement' => true),
            'name'          => array('type' => 'varchar', 'length' => 255, 'nullable' => false),
            'summary'       => array('type' => 'text', 'nullable' => false),
            'content'       => array('type' => 'mediumtext', 'nullable' => false),
            'created_time'  => array('type' => 'datetime', 'nullable' => false, 'default' => Post::exprNow()),
            'modified_time' => array('type' => 'timestamp', 'nullable' => false,
                                    'onUpdateCurrentTimeStamp' => true, 'default' => Post::exprNow()),
            'status'        => array('type' => 'tinyint', 'length' => 1, 'nullable' => false, 'default' => 0)
        );

        $columnsSchema = Post::columnSchema();
        $this->assertSame($expected['id'], $columnsSchema['id']);
        $this->assertSame($expected['name'], $columnsSchema['name']);
        $this->assertSame($expected['summary'], $columnsSchema['summary']);
        $this->assertSame($expected['content'], $columnsSchema['content']);
        $this->assertSame($expected['created_time'], $columnsSchema['created_time']);
        $this->assertSame($expected['modified_time'], $columnsSchema['modified_time']);
        $this->assertSame($expected['status'], $columnsSchema['status']);
    }

    public function testModelDefaultImmediatelySelectColumns()
    {
        Comment::immediatelySelectColumns();
        $this->assertSame(array('id', 'name', 'created_time', 'modified_time', 'status'), Post::immediatelySelectColumns());
    }

    public function testModelAutoProcessOneToMany()
    {
        $expected = array(
            'Comments' => array('model' => '\LazyTest\Db\Model\TestDefaultProp\Comment', 'key' => 'post_id')
        );

        $this->assertSame($expected, Post::oneToMany());
    }

    public function testModelAutoProcessManyToOne()
    {
        $expected = array(
            'User' => array('model' => '\LazyTest\Db\Model\TestDefaultProp\User', 'key' => 'user_id')
        );

        $this->assertSame($expected, Post::manyToOne());
    }

    public function testModelAutoProcessManyToMany()
    {
        $expected = array(
            'Permissions' => array(
                'model'         => '\LazyTest\Db\Model\TestDefaultProp\Permission',
                'throughModel'  => '\LazyTest\Db\Model\TestDefaultProp\UserPermission',
                'leftKey'       => 'user_id',
                'rightKey'      => 'permission_id',
            )
        );

        $this->assertSame($expected, User::manyToMany());
    }

    public function testCollecitonAutoGetModelClass()
    {
        $this->assertSame('\LazyTest\Db\Model\TestDefaultProp\Post', Posts::modelClass());
    }
}