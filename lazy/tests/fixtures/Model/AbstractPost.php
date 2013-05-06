<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractPost extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'posts';
    protected static $collectionClass = '\Model\Collection\Posts';
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
        'user_id' => [
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
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
        'content' => [
            'type'          => 'text',
            'length'        => NULL,
            'nullable'      => false,
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
        'user_id',
        'name',
        'created_time',
        'modified_time'
    ];
    protected static $oneToMany = [];
    protected static $manyToOne = [
        'User' => [
            'model' => '\Model\User',
            'key'   => 'user_id'
        ],
    ];
    protected static $manyToMany = [];
}