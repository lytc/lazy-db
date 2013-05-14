<?php

namespace Lazy\Db\Generator\Adapter;

use Doctrine\Common\Inflector\Inflector;
use Lazy\Db\Connection;

abstract class AbstractAdapter
{
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    abstract public function listTable();
    abstract public function listColumnSchemas($table);
    abstract public function listConstraints($table);

    public function parseConstraints(array &$schemas, $namespace = '')
    {
        if ($namespace) {
            $namespace .= '\\\\';
        }
        $oneToManyMaps = array();
        foreach ($schemas as $table => $schema) {
            if (!isset($schema['manyToOne'])) {
                continue;
            }

            $manyToOne = $schema['manyToOne'];
            foreach ($manyToOne as $refTable => $foreignKey) {
                if (!isset($oneToManyMaps[$refTable])) {
                    $oneToManyMaps[$refTable] = array();
                }
                $oneToManyMaps[$refTable][$table] = $foreignKey;
            }
        }

        foreach ($oneToManyMaps as $table => $oneToMany) {
            $schemas[$table]['oneToMany'] = $oneToMany;
        }

        $manyToManyMaps = array();
        foreach ($schemas as $table => $schema) {
            if (!isset($schema['manyToOne'])) {
                continue;
            }

            $manyToOne = $schema['manyToOne'];
            foreach ($manyToOne as $leftTable => $leftKey) {
                foreach ($manyToOne as $rightTable => $rightKey) {
                    if ($leftTable == $rightTable) {
                        continue;
                    }

                    if (!isset ($manyToManyMaps[$leftTable])) {
                        $manyToManyMaps[$leftTable] = array();
                    }

                    $manyToManyMaps[$leftTable][$rightTable] = array(
                        'leftKey' => $leftKey,
                        'rightKey' => $rightKey,
                        'through' => $table
                    );
                }
            }
        }

        foreach ($manyToManyMaps as $table => $manyToMany) {
            $schemas[$table]['manyToMany'] = $manyToMany;
        }

        foreach ($schemas as $table => &$schema) {
            if (isset($schema['oneToMany'])) {
                $oneToMany = array();
                foreach ($schema['oneToMany'] as $refTable => $foreignKey) {
                    $refName = Inflector::classify($refTable);
                    $oneToMany[$refName] = array(
                        'model' => $namespace . Inflector::singularize($refName),
                        'key' => $foreignKey
                    );
                }
                $schema['oneToMany'] = $oneToMany;
            }

            if (isset($schema['manyToOne'])) {
                $manyToOne =array();
                foreach ($schema['manyToOne'] as $refTable => $foreignKey) {
                    $refName = Inflector::classify(Inflector::singularize($refTable));
                    $manyToOne[$refName] = array(
                        'model' => $namespace . $refName,
                        'key' => $foreignKey
                    );
                }
                $schema['manyToOne'] = $manyToOne;
            }

            if (isset($schema['manyToMany'])) {
                $manyToMany = array();
                foreach ($schema['manyToMany'] as $refTable => $manyToManySchema) {
                    $refName = Inflector::classify($refTable);
                    $manyToMany[$refName] = array(
                        'model' => $namespace . Inflector::singularize($refName),
                        'through' => $namespace . Inflector::classify(Inflector::singularize($manyToManySchema['through'])),
                        'leftKey' => $manyToManySchema['leftKey'],
                        'rightKey' => $manyToManySchema['rightKey'],
                    );
                }
                $schema['manyToMany'] = $manyToMany;
            }
        }
    }
}