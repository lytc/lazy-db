<?php
namespace Model;
use Model\AbstractModel;

abstract class AbstractProduct extends AbstractModel
{
    protected static $primaryKey = 'id';
    protected static $tableName = 'products';
    protected static $collectionClass = '\Model\Collection\Products';
    protected static $columnsSchema = [
        'id' => [
            'type'          => 'int',
            'length'        => 11,
            'nullable'      => false,
            'primaryKey'    => true,
            'foreignKey'    => false,
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
        'price' => [
            'type'          => 'float',
            'length'        => 30,
            'nullable'      => false,
            'primaryKey'    => false,
            'foreignKey'    => false,
            'default'       => NULL,
            'autoIncrement' => false
        ],
        'vat' => [
            'type'          => 'float',
            'length'        => 30,
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
            'default'       => NULL,
            'autoIncrement' => false
        ],
    ];
    protected static $immediatelySelectColumns = [
        'id',
        'name',
        'price',
        'vat',
        'created_time',
        'modified_time'
    ];
    protected static $oneToMany = [
        'Orders' => [
            'model' => '\Model\Order',
            'key'   => 'product_id'
        ],
    ];
    protected static $manyToOne = [];
    protected static $manyToMany = [
        'Users' => [
            'model'         => '\Model\User',
            'throughModel'  => '\Model\Order',
            'leftKey'       => 'product_id',
            'rightKey'      => 'user_id'
        ],
    ];
}