<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractUser extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'users';
    protected static $collectionClass = '\Model\Collection\Users';
    protected static $columnsSchema = array(
        'id' => array(
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => true,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => true
        ),
        'name' => array(
            'type'          => 'varchar',
            'length'        => 255,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'username' => array(
            'type'          => 'varchar',
            'length'        => 50,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'password' => array(
            'type'          => 'varchar',
            'length'        => 50,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'phone' => array(
            'type'          => 'varchar',
            'length'        => 20,
            'nullable'      => true,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'created_time' => array(
            'type'          => 'datetime',
            'length'        => NULL,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'modified_time' => array(
            'type'          => 'timestamp',
            'length'        => NULL,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => 'CURRENT_TIMESTAMP',
            'autoIncrement' => false
        ),
    );
    protected static $immediatelySelectColumns = array(
        'id',
        'name',
        'username',
        'password',
        'phone',
        'created_time',
        'modified_time'
    );
    protected static $oneToMany = array(
        'Orders' => array(
            'model' => '\Model\Order',
            'key'   => 'user_id'
        ),
        'Posts' => array(
            'model' => '\Model\Post',
            'key'   => 'user_id'
        ),
        'UserPermissions' => array(
            'model' => '\Model\UserPermission',
            'key'   => 'user_id'
        ),
    );
    protected static $manyToOne = array();
    protected static $manyToMany = array(
        'Products' => array(
            'model'         => '\Model\Product',
            'throughModel'  => '\Model\Order',
            'leftKey'       => 'user_id',
            'rightKey'      => 'product_id'
        ),
        'Permissions' => array(
            'model'         => '\Model\Permission',
            'throughModel'  => '\Model\UserPermission',
            'leftKey'       => 'user_id',
            'rightKey'      => 'permission_id'
        ),
    );
}