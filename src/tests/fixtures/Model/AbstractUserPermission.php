<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractUserPermission extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'user_permissions';
    protected static $collectionClass = '\Model\Collection\UserPermissions';
    protected static $columnsSchema = array(
        'user_id' => array(
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'permission_id' => array(
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
        ),
    );
    protected static $immediatelySelectColumns = array(
        'user_id',
        'permission_id'
    );
    protected static $oneToMany = array();
    protected static $manyToOne = array(
        'User' => array(
            'model' => '\Model\User',
            'key'   => 'user_id'
        ),
        'Permission' => array(
            'model' => '\Model\Permission',
            'key'   => 'permission_id'
        ),
    );
    protected static $manyToMany = array();
}