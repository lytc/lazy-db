<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractPermission extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'permissions';
    protected static $collectionClass = '\Model\Collection\Permissions';
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
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
    ];
    protected static $immediatelySelectColumns = [
        'id',
        'name'
    ];
    protected static $oneToMany = [
        'UserPermissions' => [
            'model' => '\Model\UserPermission',
            'key'   => 'permission_id'
        ],
    ];
    protected static $manyToOne = [];
    protected static $manyToMany = [
        'Users' => [
            'model'         => '\Model\User',
            'throughModel'  => '\Model\UserPermission',
            'leftKey'       => 'permission_id',
            'rightKey'      => 'user_id'
        ],
    ];
}