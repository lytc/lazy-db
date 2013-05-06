<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractProduct extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'products';
    protected static $collectionClass = '\Model\Collection\Products';
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
        'price' => array(
            'type'          => 'float',
            'length'        => 30,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ),
        'vat' => array(
            'type'          => 'float',
            'length'        => 30,
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
        'name',
        'price',
        'vat',
        'created_time',
        'modified_time'
    );
    protected static $oneToMany = array(
        'Orders' => array(
            'model' => '\Model\Order',
            'key'   => 'product_id'
        ),
    );
    protected static $manyToOne = array();
    protected static $manyToMany = array(
        'Users' => array(
            'model'         => '\Model\User',
            'throughModel'  => '\Model\Order',
            'leftKey'       => 'product_id',
            'rightKey'      => 'user_id'
        ),
    );
}