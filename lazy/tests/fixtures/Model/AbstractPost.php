<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractPost extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'posts';
    protected static $collectionClass = '\Model\Collection\Posts';
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
        'user_id' => array(
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
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
        'content' => array(
            'type'          => 'text',
            'length'        => NULL,
            'nullable'      => false,
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
        'user_id',
        'name',
        'created_time',
        'modified_time'
    );
    protected static $oneToMany = array();
    protected static $manyToOne = array(
        'User' => array(
            'model' => '\Model\User',
            'key'   => 'user_id'
        ),
    );
    protected static $manyToMany = array();
}