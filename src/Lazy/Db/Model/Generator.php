<?php

namespace Lazy\Db\Model;

use Lazy\Db\Pdo;
use Lazy\Db\Inflector;

class Generator
{
    protected $pdo;
    protected $dbName;
    protected $directory;
    protected $namespace;
    protected $tables;
    protected $columnsSchema;
    protected $lazyLoad = array('text', 'mediumtext', 'longtext');
    protected $associations;
    protected $manyToOne = array();
    protected $oneToMany = array();
    protected $manyToMany = array();
    protected $inflector;

    protected $abstractBaseModelTemplate = <<<'EOD'
<?php
namespace {$namespace};
use \Lazy\Db\Model\AbstractModel as LazyDbAbstractModel;

class AbstractModel extends LazyDbAbstractModel
{
}
EOD;

    protected $abstractModelTemplate = <<<'EOD'
<?php
namespace {$namespace};
use {$namespace}\AbstractModel;

abstract class Abstract{$modelName} extends AbstractModel
{
    protected static $primaryKey = '{$primaryKey}';
    protected static $tableName = '{$tableName}';
    protected static $collectionClass = '\{$namespace}\Collection\{$collectionClassName}';
    protected static $columnsSchema = {$columnsSchema};
    protected static $immediatelySelectColumns = {$immediatelySelectColumns};
    protected static $oneToMany = {$oneToMany};
    protected static $manyToOne = {$manyToOne};
    protected static $manyToMany = {$manyToMany};
}
EOD;

    protected $modelTemplate = <<<'EOD'
<?php
namespace {$namespace};

class {$modelName} extends Abstract{$modelName}
{

}
EOD;

    protected $collectionTemplate = <<<'EOD'
<?php
namespace {$namespace}\Collection;
use \Lazy\Db\Model\AbstractCollection;

class {$collectionClassName} extends AbstractCollection
{
    protected static $modelClass = '\{$namespace}\{$modelName}';
}
EOD;


    public function __construct(Pdo $pdo, $directory, $namespace)
    {
        $this->pdo = $pdo;

        # get db name
        $stmt = $this->pdo->query("SELECT DATABASE()");
        $this->dbName = $stmt->fetchColumn();

        $this->directory = $directory;
        $this->namespace = $namespace;
        $this->inflector = new Inflector();

        $this->initTableList();
        $this->initColumnSchema();
        $this->initAssociation();
    }

    protected function initTableList()
    {
        $query = "SHOW TABLES";
        $stmt = $this->pdo->query($query);
        $this->tables = $stmt->fetchAll(\PDO::FETCH_COLUMN, 0);

        $this->tables = array_combine($this->tables, array_map(array($this->inflector, 'classify'), $this->tables));
    }

    protected function initColumnSchema()
    {
        $query = "SHOW COLUMNS FROM %s";
        foreach ($this->tables as $tableName => $modelClassName)
        {
            $columns = array();
            $stmt = $this->pdo->query(sprintf($query, $tableName));
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                preg_match('/^(\w+)\(?(\d+)?\)?/', $row['Type'], $matches);
                $name = $row['Field'];
                $columns[$name] = array(
                    'type'          => $matches[1],
                    'length'        => isset($matches[2])? (int) $matches[2] : null,
                    'nullable'      => $row['Null'] == 'YES',
                    'primaryKey'    => $row['Key'] == 'PRI',
                    'foreignKey'    => $row['Key'] == 'MUL',
                    'default'       => $row['Default'],
                    'autoIncrement' => $row['Extra'] == 'auto_increment'
                );
            }

            $this->columnsSchema[$tableName] = $columns;
        }
    }

    protected function initAssociation()
    {
        $query = "
            SELECT
                TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME
            FROM
                information_schema.KEY_COLUMN_USAGE
            WHERE
                TABLE_SCHEMA = '{$this->dbName}'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ";
        $stmt = $this->pdo->query($query);
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $associations = array();
        foreach ($rows as $row) {
            isset($associations[$row['TABLE_NAME']]) || $associations[$row['TABLE_NAME']] = array();
            $associations[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row['REFERENCED_TABLE_NAME'];
        }

        // manyToOne & oneToMany
        foreach ($associations as $tableName => $assocs) {
            foreach ($assocs as $foreignKey => $referenceTableName) {
                $modelName = $this->tables[$tableName];
                $referenceModelName = $this->tables[$referenceTableName];
                isset($this->manyToOne[$modelName]) || ($this->manyToOne[$modelName] = array());
                $this->manyToOne[$modelName][$this->inflector->camelize(preg_replace('/_id$/', '', $foreignKey))] = array($foreignKey, $this->tables[$referenceTableName]);
                isset($this->oneToMany[$referenceModelName]) || ($this->oneToMany[$referenceModelName] = array());
                $this->oneToMany[$referenceModelName][$this->inflector->camelize($tableName)] = array($foreignKey, $modelName);
            }
        }
        // manyToMany
        foreach ($this->manyToOne as $modelName => $associations) {
            if (count($associations) == 2) {
                foreach ($associations as $key => $referenceModelOption) {
                    foreach ($associations as $key2 => $referenceModelOption2) {
                        if ($referenceModelOption == $referenceModelOption2) {
                            continue;
                        }

                        $refModelName = $this->inflector->pluralize($referenceModelOption2[1]);
                        isset($this->manyToMany[$referenceModelOption[1]]) || ($this->manyToMany[$referenceModelOption[1]] = array());
                        $this->manyToMany[$referenceModelOption[1]][$refModelName] = array($referenceModelOption[0], $referenceModelOption2[0], $modelName);
                    }
                }
            }
        }
    }

    public function generate()
    {
//        // cleanup base models
        $directory = $this->directory;
//        $baseModelDirectory = $directory . '/Base';
        $collectionDirectory = $directory . '/Collection';
        $namespace = $this->namespace;
//
//        if (is_file($baseModelDirectory)) {
//            $dir = dir($baseModelDirectory);
//            while (false !== ($f = $dir->read())) {
//                if ('.' == $f || '..' == $f) {
//                    continue;
//                }
//
//                unlink($baseModelDirectory . '/' . $f);
//            }
//            $dir->close();
//        }
//
//        if (is_file($collectionDirectory)) {
//            $dir = dir($collectionDirectory);
//            while (false !== ($f = $dir->read())) {
//                if ('.' == $f || '..' == $f) {
//                    continue;
//                }
//
//                unlink($collectionDirectory . '/' . $f);
//            }
//            $dir->close();
//        }

        // create directory
//        if (!file_exists($baseModelDirectory)) {
//            mkdir($baseModelDirectory, 0777, true);
//        }

        if (!file_exists($collectionDirectory)) {
            mkdir($collectionDirectory, 0777, true);
        }

        // create abstract model class if not existing
        $abstractModelFile = $directory . '/AbstractModel.php';
        if (!file_exists($abstractModelFile)) {
            $abstractModelClassContent = str_replace('{$namespace}', $namespace, $this->abstractBaseModelTemplate);
            file_put_contents($abstractModelFile, $abstractModelClassContent);
        }

        foreach ($this->tables as $tableName => $modelName) {
            // create abstract model
            $baseModelFile = $directory . '/Abstract' . $modelName . '.php';

            // get primaryKey
            foreach ($this->columnsSchema[$tableName] as $columnName => $schema) {
                if ($schema['primaryKey']) {
                    $primaryKey = $columnName;
                    break;
                }
            }

            // generate columns schema
            # find longest column name
            $longestColumnNameLength = max(array_map('strlen', array_keys($this->columnsSchema[$tableName])));
            $columns = 'array(' . PHP_EOL;
            $columnsSchema = 'array(' . PHP_EOL;
            $immediatelySelectColumns = array();
            foreach ($this->columnsSchema[$tableName] as $columnName => $schemas) {
                if (!in_array($schemas['type'], $this->lazyLoad)) {
                    $immediatelySelectColumns[] = $columnName;
                }


                $columnNameCamelize = lcfirst($this->inflector->camelize($columnName));
                $space = str_repeat(' ', $longestColumnNameLength - strlen($columnNameCamelize));
                $columns .= "        '$columnNameCamelize' $space=> '$columnName'," . PHP_EOL;
                $columnSchema = array();
                foreach ($schemas as $key => $value) {
                    switch (gettype($value)) {
                        case 'boolean':
                            $value = $value? 'true' : 'false';
                            break;

                        case 'NULL':
                            $value = 'NULL';
                            break;

                        case 'integer':
                        case 'double':
                            break;

                        default: $value = "'$value'";
                    }
                    $space = str_repeat(' ', 13 - strlen($key));
                    $columnSchema[] = "            '$key' $space=> $value";
                }
                $columnsSchema .= "        '$columnName' => array(" . PHP_EOL;
                $columnsSchema .= implode(',' . PHP_EOL, $columnSchema);
                $columnsSchema .= PHP_EOL . '        ),' . PHP_EOL;
            }
            $columns .= '    )';
            $columnsSchema .= '    )';

            # generate association
            $spaces12 = "\n            ";
            $spaces8 = "\n        ";

            # one to many
            if (isset($this->oneToMany[$modelName])) {
                # find longest key
                $maxKeyLength = max(array_map(function($key) {return strlen($key);}, array_keys($this->oneToMany[$modelName])));

                $oneToMany = 'array(' . PHP_EOL;
                foreach ($this->oneToMany[$modelName] as $key => $referenceModelOption) {

                    $oneToMany .= "        '$key' => array({$spaces12}'model' => '\\$namespace\\$referenceModelOption[1]',{$spaces12}'key'   => '$referenceModelOption[0]'{$spaces8})," . PHP_EOL;
                }
                $oneToMany .= '    )';
            } else {
                $oneToMany = 'array()';
            }

            # many to one
            if (isset($this->manyToOne[$modelName])) {
                # find longest key
                $maxKeyLength = max(array_map(function($key) {return strlen($key);}, array_keys($this->manyToOne[$modelName])));

                $manyToOne = 'array(' . PHP_EOL;
                foreach ($this->manyToOne[$modelName] as $key => $referenceModelOption) {
                    $spaces = str_repeat(' ', $maxKeyLength - strlen($key));
                    $manyToOne .= "        '$key' => array({$spaces12}'model' => '\\$namespace\\$referenceModelOption[1]',{$spaces12}'key'   => '$referenceModelOption[0]'{$spaces8})," . PHP_EOL;
                }
                $manyToOne .= '    )';
            } else {
                $manyToOne = 'array()';
            }

            # many to many
            if (isset($this->manyToMany[$modelName])) {
                # find longest key
                $maxKeyLength = max(array_map(function($key) {return strlen($key);}, array_keys($this->manyToMany[$modelName])));

                $manyToMany = 'array(' . PHP_EOL;
                foreach ($this->manyToMany[$modelName] as $key => $referenceModelOption) {
                    $refModel = $this->inflector->singularize($key);
                    $manyToMany .= "        '$key' => array({$spaces12}'model'         => '\\$namespace\\$refModel',{$spaces12}'throughModel'  => '\\$namespace\\$referenceModelOption[2]',{$spaces12}'leftKey'       => '$referenceModelOption[0]',{$spaces12}'rightKey'      => '$referenceModelOption[1]'{$spaces8})," . PHP_EOL;
                }
                $manyToMany .= '    )';
            } else {
                $manyToMany = 'array()';
            }

            $collectionClassName = ucfirst($this->inflector->camelize($tableName));

            $params = array(
                '{$namespace}'                  => $namespace,
                '{$primaryKey}'                 => $primaryKey,
                '{$tableName}'                  => $tableName,
                '{$modelName}'                  => $modelName,
                '{$collectionClassName}'        => $collectionClassName,
                '{$columnsSchema}'              => $columnsSchema,
                '{$columns}'                    => $columns,
                '{$immediatelySelectColumns}'   => 'array(' . PHP_EOL . "        '" . implode("'," . PHP_EOL . "        '", $immediatelySelectColumns) . "'" . PHP_EOL . '    )',
                '{$oneToMany}'                  => $oneToMany,
                '{$manyToOne}'                  => $manyToOne,
                '{$manyToMany}'                 => $manyToMany,
            );

            $baseModelClassContent = str_replace(array_keys($params), $params, $this->abstractModelTemplate);
            file_put_contents($baseModelFile, $baseModelClassContent);

            // create model if not existing
            $modelFile = $directory . '/' . $modelName . '.php';
            if (!file_exists($modelFile)) {
                $modelClassContent = str_replace(array_keys($params), $params, $this->modelTemplate);
                file_put_contents($modelFile, $modelClassContent);
            }

            // create collection
            $collectionFile = $collectionDirectory . '/' . $collectionClassName . '.php';
            $params = array(
                '{$namespace}'              => $namespace,
                '{$collectionClassName}'    => $collectionClassName,
                '{$modelName}'              => $modelName,
            );
            $collectionClassContent = str_replace(array_keys($params), $params, $this->collectionTemplate);
            file_put_contents($collectionFile, $collectionClassContent);
        }
    }
}