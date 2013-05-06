<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractOrder extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'orders';
    protected static $collectionClass = '\Model\Collection\Orders';
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
        'product_id' => array(
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => true,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'status' => array(
            'type'          => 'tinyint',
            'length'        => 1,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => '0',
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
        'product_id',
        'status',
        'created_time',
        'modified_time'
    );
    protected static $oneToMany = array();
    protected static $manyToOne = array(
        'Product' => array(
            'model' => '\Model\Product',
            'key'   => 'product_id'
        ),
        'User' => array(
            'model' => '\Model\User',
            'key'   => 'user_id'
        ),
    );
    protected static $manyToMany = array();
}