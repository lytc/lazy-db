<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractUser extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'users';
    protected static $collectionClass = '\Model\Collection\Users';
    protected static $columnsSchema = [
        'id' => [
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => true,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => true
        ],
        'name' => [
            'type'          => 'varchar',
            'length'        => 255,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'username' => [
            'type'          => 'varchar',
            'length'        => 50,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'password' => [
            'type'          => 'varchar',
            'length'        => 50,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'phone' => [
            'type'          => 'varchar',
            'length'        => 20,
            'nullable'      => true,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'created_time' => [
            'type'          => 'datetime',
            'length'        => NULL,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'modified_time' => [
            'type'          => 'timestamp',
            'length'        => NULL,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => 'CURRENT_TIMESTAMP',
            'autoIncrement' => false
        ],
    ];
    protected static $immediatelySelectColumns = [
        'id',
        'name',
        'username',
        'password',
        'phone',
        'created_time',
        'modified_time'
    ];
    protected static $oneToMany = [
        'Orders' => [
            'model' => '\Model\Order',
            'key'   => 'user_id'
        ],
        'Posts' => [
            'model' => '\Model\Post',
            'key'   => 'user_id'
        ],
        'UserPermissions' => [
            'model' => '\Model\UserPermission',
            'key'   => 'user_id'
        ],
    ];
    protected static $manyToOne = [];
    protected static $manyToMany = [
        'Products' => [
            'model'         => '\Model\Product',
            'throughModel'  => '\Model\Order',
            'leftKey'       => 'user_id',
            'rightKey'      => 'product_id'
        ],
        'Permissions' => [
            'model'         => '\Model\Permission',
            'throughModel'  => '\Model\UserPermission',
            'leftKey'       => 'user_id',
            'rightKey'      => 'permission_id'
        ],
    ];
}