<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractUserPermission extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'user_permissions';
    protected static $collectionClass = '\Model\Collection\UserPermissions';
    protected static $columnsSchema = [
        'user_id' => [
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'permission_id' => [
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
        ],
    ];
    protected static $immediatelySelectColumns = [
        'user_id',
        'permission_id'
    ];
    protected static $oneToMany = [];
    protected static $manyToOne = [
        'User' => [
            'model' => '\Model\User',
            'key'   => 'user_id'
        ],
        'Permission' => [
            'model' => '\Model\Permission',
            'key'   => 'permission_id'
        ],
    ];
    protected static $manyToMany = [];
}