<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractPermission extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'permissions';
    protected static $collectionClass = '\Model\Collection\Permissions';
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
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
    );
    protected static $immediatelySelectColumns = array(
        'id',
        'name'
    );
    protected static $oneToMany = array(
        'UserPermissions' => array(
            'model' => '\Model\UserPermission',
            'key'   => 'permission_id'
        ),
    );
    protected static $manyToOne = array();
    protected static $manyToMany = array(
        'Users' => array(
            'model'         => '\Model\User',
            'throughModel'  => '\Model\UserPermission',
            'leftKey'       => 'permission_id',
            'rightKey'      => 'user_id'
        ),
    );
}