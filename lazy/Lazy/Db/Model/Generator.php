<?php

namespace Lazy\Db\Model;

use Lazy\Db\Pdo;

class Generator
{
    protected $pdo;
    protected $dbName;
    protected $directory;
    protected $namespace;
    protected $tables;
    protected $columnsSchema;
    protected $lazyLoad = ['text', 'mediumtext', 'longtext'];
    protected $associations;
    protected $manyToOne = [];
    protected $oneToMany = [];
    protected $manyToMany = [];
    protected $inflector;

    protected $abstractBaseModelTemplate = <<<'EOD'
<?php
namespace {$namespace};
use \Lazy\Db\Model\AbstractModel as LazyDbAbstractModel;

class AbstractModel extends LazyDbAbstractModel
{
    public static function getPdo()
    {

    }
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

        $this->tables = array_combine($this->tables, array_map([$this->inflector, 'classify'], $this->tables));
    }

    protected function initColumnSchema()
    {
        $query = "SHOW COLUMNS FROM %s";
        foreach ($this->tables as $tableName => $modelClassName)
        {
            $columns = [];
            $stmt = $this->pdo->query(sprintf($query, $tableName));
            $stmt->execute();
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                preg_match('/^(\w+)\(?(\d+)?\)?/', $row['Type'], $matches);
                $name = $row['Field'];
                $columns[$name] = [
                    'type'          => $matches[1],
                    'length'        => $matches[2]? (int) $matches[2] : null,
                    'nullable'      => $row['Null'] == 'YES',
                    'primaryKey'    => $row['Key'] == 'PRI',
                    'foreignKey'    => $row['Key'] == 'MUL',
                    'default'       => $row['default'],
                    'autoIncrement' => $row['auto_increment'] == 'auto_increment'
                ];
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

        $associations = [];
        foreach ($rows as $row) {
            $associations[$row['TABLE_NAME']] || $associations[$row['TABLE_NAME']] = [];
            $associations[$row['TABLE_NAME']][$row['COLUMN_NAME']] = $row['REFERENCED_TABLE_NAME'];
        }

        // manyToOne & oneToMany
        foreach ($associations as $tableName => $assocs) {
            foreach ($assocs as $foreignKey => $referenceTableName) {
                $modelName = $this->tables[$tableName];
                $referenceModelName = $this->tables[$referenceTableName];
                $this->manyToOne[$modelName] || ($this->manyToOne[$modelName] = []);
                $this->manyToOne[$modelName][$this->inflector->camelize(preg_replace('/_id$/', '', $foreignKey))] = [$foreignKey, $this->tables[$referenceTableName]];
                $this->oneToMany[$referenceModelName] || ($this->oneToMany[$referenceModelName] = []);
                $this->oneToMany[$referenceModelName][$this->inflector->camelize($tableName)] = [$foreignKey, $modelName];
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
                        $this->manyToMany[$referenceModelOption[1]] || ($this->manyToMany[$referenceModelOption[1]] = []);
                        $this->manyToMany[$referenceModelOption[1]][$refModelName] = [$referenceModelOption[0], $referenceModelOption2[0], $modelName];
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
            $columns = '[' . PHP_EOL;
            $columnsSchema = '[' . PHP_EOL;
            $immediatelySelectColumns = [];
            foreach ($this->columnsSchema[$tableName] as $columnName => $schemas) {
                if (!in_array($schemas['type'], $this->lazyLoad)) {
                    $immediatelySelectColumns[] = $columnName;
                }


                $columnNameCamelize = lcfirst($this->inflector->camelize($columnName));
                $space = str_repeat(' ', $longestColumnNameLength - strlen($columnNameCamelize));
                $columns .= "        '$columnNameCamelize' $space=> '$columnName'," . PHP_EOL;
                $columnSchema = [];
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
                $columnsSchema .= "        '$columnName' => [" . PHP_EOL;
                $columnsSchema .= implode(',' . PHP_EOL, $columnSchema);
                $columnsSchema .= PHP_EOL . '        ],' . PHP_EOL;
            }
            $columns .= '    ]';
            $columnsSchema .= '    ]';

            # generate association
            $spaces12 = "\n            ";
            $spaces8 = "\n        ";

            # one to many
            if (isset($this->oneToMany[$modelName])) {
                # find longest key
                $maxKeyLength = max(array_map(function($key) {return strlen($key);}, array_keys($this->oneToMany[$modelName])));

                $oneToMany = '[' . PHP_EOL;
                foreach ($this->oneToMany[$modelName] as $key => $referenceModelOption) {

                    $oneToMany .= "        '$key' => [{$spaces12}'model' => '\\$namespace\\$referenceModelOption[1]',{$spaces12}'key'   => '$referenceModelOption[0]'{$spaces8}]," . PHP_EOL;
                }
                $oneToMany .= '    ]';
            } else {
                $oneToMany = '[]';
            }

            # many to one
            if (isset($this->manyToOne[$modelName])) {
                # find longest key
                $maxKeyLength = max(array_map(function($key) {return strlen($key);}, array_keys($this->manyToOne[$modelName])));

                $manyToOne = '[' . PHP_EOL;
                foreach ($this->manyToOne[$modelName] as $key => $referenceModelOption) {
                    $spaces = str_repeat(' ', $maxKeyLength - strlen($key));
                    $manyToOne .= "        '$key' => [{$spaces12}'model' => '\\$namespace\\$referenceModelOption[1]',{$spaces12}'key'   => '$referenceModelOption[0]'{$spaces8}]," . PHP_EOL;
                }
                $manyToOne .= '    ]';
            } else {
                $manyToOne = '[]';
            }

            # many to many
            if (isset($this->manyToMany[$modelName])) {
                # find longest key
                $maxKeyLength = max(array_map(function($key) {return strlen($key);}, array_keys($this->manyToMany[$modelName])));

                $manyToMany = '[' . PHP_EOL;
                foreach ($this->manyToMany[$modelName] as $key => $referenceModelOption) {
                    $refModel = $this->inflector->singularize($key);
                    $manyToMany .= "        '$key' => [{$spaces12}'model'         => '\\$namespace\\$refModel',{$spaces12}'throughModel'  => '\\$namespace\\$referenceModelOption[2]',{$spaces12}'leftKey'       => '$referenceModelOption[0]',{$spaces12}'rightKey'      => '$referenceModelOption[1]'{$spaces8}]," . PHP_EOL;
                }
                $manyToMany .= '    ]';
            } else {
                $manyToMany = '[]';
            }

            $collectionClassName = ucfirst($this->inflector->camelize($tableName));

            $params = [
                '{$namespace}'                  => $namespace,
                '{$primaryKey}'                 => $primaryKey,
                '{$tableName}'                  => $tableName,
                '{$modelName}'                  => $modelName,
                '{$collectionClassName}'        => $collectionClassName,
                '{$columnsSchema}'              => $columnsSchema,
                '{$columns}'                    => $columns,
                '{$immediatelySelectColumns}'   => '[' . PHP_EOL . "        '" . implode("'," . PHP_EOL . "        '", $immediatelySelectColumns) . "'" . PHP_EOL . '    ]',
                '{$oneToMany}'                  => $oneToMany,
                '{$manyToOne}'                  => $manyToOne,
                '{$manyToMany}'                 => $manyToMany,
                '{$associations}'               => $associations
            ];

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
            $params = [
                '{$namespace}'              => $namespace,
                '{$collectionClassName}'    => $collectionClassName,
                '{$modelName}'              => $modelName,
            ];
            $collectionClassContent = str_replace(array_keys($params), $params, $this->collectionTemplate);
            file_put_contents($collectionFile, $collectionClassContent);
        }
    }
}

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @package       Cake.Utility
 * @since         CakePHP(tm) v 0.2.9
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Pluralize and singularize English words.
 *
 * Inflector pluralizes and singularizes English nouns.
 * Used by Cake's naming conventions throughout the framework.
 *
 * @package       Cake.Utility
 * @link          http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html
 * @codeCoverageIgnore
 */
class Inflector {

    /**
     * Plural inflector rules
     *
     * @var array
     */
    protected static $_plural = array(
        'rules' => array(
            '/(s)tatus$/i' => '\1\2tatuses',
            '/(quiz)$/i' => '\1zes',
            '/^(ox)$/i' => '\1\2en',
            '/([m|l])ouse$/i' => '\1ice',
            '/(matr|vert|ind)(ix|ex)$/i' => '\1ices',
            '/(x|ch|ss|sh)$/i' => '\1es',
            '/([^aeiouy]|qu)y$/i' => '\1ies',
            '/(hive)$/i' => '\1s',
            '/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
            '/sis$/i' => 'ses',
            '/([ti])um$/i' => '\1a',
            '/(p)erson$/i' => '\1eople',
            '/(m)an$/i' => '\1en',
            '/(c)hild$/i' => '\1hildren',
            '/(buffal|tomat)o$/i' => '\1\2oes',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|vir)us$/i' => '\1i',
            '/us$/i' => 'uses',
            '/(alias)$/i' => '\1es',
            '/(ax|cris|test)is$/i' => '\1es',
            '/s$/' => 's',
            '/^$/' => '',
            '/$/' => 's',
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', 'people'
        ),
        'irregular' => array(
            'atlas' => 'atlases',
            'beef' => 'beefs',
            'brother' => 'brothers',
            'cafe' => 'cafes',
            'child' => 'children',
            'cookie' => 'cookies',
            'corpus' => 'corpuses',
            'cow' => 'cows',
            'ganglion' => 'ganglions',
            'genie' => 'genies',
            'genus' => 'genera',
            'graffito' => 'graffiti',
            'hoof' => 'hoofs',
            'loaf' => 'loaves',
            'man' => 'men',
            'money' => 'monies',
            'mongoose' => 'mongooses',
            'move' => 'moves',
            'mythos' => 'mythoi',
            'niche' => 'niches',
            'numen' => 'numina',
            'occiput' => 'occiputs',
            'octopus' => 'octopuses',
            'opus' => 'opuses',
            'ox' => 'oxen',
            'penis' => 'penises',
            'person' => 'people',
            'sex' => 'sexes',
            'soliloquy' => 'soliloquies',
            'testis' => 'testes',
            'trilby' => 'trilbys',
            'turf' => 'turfs'
        )
    );

    /**
     * Singular inflector rules
     *
     * @var array
     */
    protected static $_singular = array(
        'rules' => array(
            '/(s)tatuses$/i' => '\1\2tatus',
            '/^(.*)(menu)s$/i' => '\1\2',
            '/(quiz)zes$/i' => '\\1',
            '/(matr)ices$/i' => '\1ix',
            '/(vert|ind)ices$/i' => '\1ex',
            '/^(ox)en/i' => '\1',
            '/(alias)(es)*$/i' => '\1',
            '/(alumn|bacill|cact|foc|fung|nucle|radi|stimul|syllab|termin|viri?)i$/i' => '\1us',
            '/([ftw]ax)es/i' => '\1',
            '/(cris|ax|test)es$/i' => '\1is',
            '/(shoe|slave)s$/i' => '\1',
            '/(o)es$/i' => '\1',
            '/ouses$/' => 'ouse',
            '/([^a])uses$/' => '\1us',
            '/([m|l])ice$/i' => '\1ouse',
            '/(x|ch|ss|sh)es$/i' => '\1',
            '/(m)ovies$/i' => '\1\2ovie',
            '/(s)eries$/i' => '\1\2eries',
            '/([^aeiouy]|qu)ies$/i' => '\1y',
            '/([lr])ves$/i' => '\1f',
            '/(tive)s$/i' => '\1',
            '/(hive)s$/i' => '\1',
            '/(drive)s$/i' => '\1',
            '/([^fo])ves$/i' => '\1fe',
            '/(^analy)ses$/i' => '\1sis',
            '/(analy|diagno|^ba|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i' => '\1\2sis',
            '/([ti])a$/i' => '\1um',
            '/(p)eople$/i' => '\1\2erson',
            '/(m)en$/i' => '\1an',
            '/(c)hildren$/i' => '\1\2hild',
            '/(n)ews$/i' => '\1\2ews',
            '/eaus$/' => 'eau',
            '/^(.*us)$/' => '\\1',
            '/s$/i' => ''
        ),
        'uninflected' => array(
            '.*[nrlm]ese', '.*deer', '.*fish', '.*measles', '.*ois', '.*pox', '.*sheep', '.*ss'
        ),
        'irregular' => array(
            'foes' => 'foe',
            'waves' => 'wave',
            'curves' => 'curve'
        )
    );

    /**
     * Words that should not be inflected
     *
     * @var array
     */
    protected static $_uninflected = array(
        'Amoyese', 'bison', 'Borghese', 'bream', 'breeches', 'britches', 'buffalo', 'cantus',
        'carp', 'chassis', 'clippers', 'cod', 'coitus', 'Congoese', 'contretemps', 'corps',
        'debris', 'diabetes', 'djinn', 'eland', 'elk', 'equipment', 'Faroese', 'flounder',
        'Foochowese', 'gallows', 'Genevese', 'Genoese', 'Gilbertese', 'graffiti',
        'headquarters', 'herpes', 'hijinks', 'Hottentotese', 'information', 'innings',
        'jackanapes', 'Kiplingese', 'Kongoese', 'Lucchese', 'mackerel', 'Maltese', '.*?media',
        'mews', 'moose', 'mumps', 'Nankingese', 'news', 'nexus', 'Niasese',
        'Pekingese', 'Piedmontese', 'pincers', 'Pistoiese', 'pliers', 'Portuguese',
        'proceedings', 'rabies', 'rice', 'rhinoceros', 'salmon', 'Sarawakese', 'scissors',
        'sea[- ]bass', 'series', 'Shavese', 'shears', 'siemens', 'species', 'swine', 'testes',
        'trousers', 'trout', 'tuna', 'Vermontese', 'Wenchowese', 'whiting', 'wildebeest',
        'Yengeese'
    );

    /**
     * Default map of accented and special characters to ASCII characters
     *
     * @var array
     */
    protected static $_transliteration = array(
        '/ä|æ|ǽ/' => 'ae',
        '/ö|œ/' => 'oe',
        '/ü/' => 'ue',
        '/Ä/' => 'Ae',
        '/Ü/' => 'Ue',
        '/Ö/' => 'Oe',
        '/À|Á|Â|Ã|Å|Ǻ|Ā|Ă|Ą|Ǎ/' => 'A',
        '/à|á|â|ã|å|ǻ|ā|ă|ą|ǎ|ª/' => 'a',
        '/Ç|Ć|Ĉ|Ċ|Č/' => 'C',
        '/ç|ć|ĉ|ċ|č/' => 'c',
        '/Ð|Ď|Đ/' => 'D',
        '/ð|ď|đ/' => 'd',
        '/È|É|Ê|Ë|Ē|Ĕ|Ė|Ę|Ě/' => 'E',
        '/è|é|ê|ë|ē|ĕ|ė|ę|ě/' => 'e',
        '/Ĝ|Ğ|Ġ|Ģ/' => 'G',
        '/ĝ|ğ|ġ|ģ/' => 'g',
        '/Ĥ|Ħ/' => 'H',
        '/ĥ|ħ/' => 'h',
        '/Ì|Í|Î|Ï|Ĩ|Ī|Ĭ|Ǐ|Į|İ/' => 'I',
        '/ì|í|î|ï|ĩ|ī|ĭ|ǐ|į|ı/' => 'i',
        '/Ĵ/' => 'J',
        '/ĵ/' => 'j',
        '/Ķ/' => 'K',
        '/ķ/' => 'k',
        '/Ĺ|Ļ|Ľ|Ŀ|Ł/' => 'L',
        '/ĺ|ļ|ľ|ŀ|ł/' => 'l',
        '/Ñ|Ń|Ņ|Ň/' => 'N',
        '/ñ|ń|ņ|ň|ŉ/' => 'n',
        '/Ò|Ó|Ô|Õ|Ō|Ŏ|Ǒ|Ő|Ơ|Ø|Ǿ/' => 'O',
        '/ò|ó|ô|õ|ō|ŏ|ǒ|ő|ơ|ø|ǿ|º/' => 'o',
        '/Ŕ|Ŗ|Ř/' => 'R',
        '/ŕ|ŗ|ř/' => 'r',
        '/Ś|Ŝ|Ş|Š/' => 'S',
        '/ś|ŝ|ş|š|ſ/' => 's',
        '/Ţ|Ť|Ŧ/' => 'T',
        '/ţ|ť|ŧ/' => 't',
        '/Ù|Ú|Û|Ũ|Ū|Ŭ|Ů|Ű|Ų|Ư|Ǔ|Ǖ|Ǘ|Ǚ|Ǜ/' => 'U',
        '/ù|ú|û|ũ|ū|ŭ|ů|ű|ų|ư|ǔ|ǖ|ǘ|ǚ|ǜ/' => 'u',
        '/Ý|Ÿ|Ŷ/' => 'Y',
        '/ý|ÿ|ŷ/' => 'y',
        '/Ŵ/' => 'W',
        '/ŵ/' => 'w',
        '/Ź|Ż|Ž/' => 'Z',
        '/ź|ż|ž/' => 'z',
        '/Æ|Ǽ/' => 'AE',
        '/ß/' => 'ss',
        '/Ĳ/' => 'IJ',
        '/ĳ/' => 'ij',
        '/Œ/' => 'OE',
        '/ƒ/' => 'f'
    );

    /**
     * Method cache array.
     *
     * @var array
     */
    protected static $_cache = array();

    /**
     * The initial state of Inflector so reset() works.
     *
     * @var array
     */
    protected static $_initialState = array();

    /**
     * Cache inflected values, and return if already available
     *
     * @param string $type Inflection type
     * @param string $key Original value
     * @param string $value Inflected value
     * @return string Inflected value, from cache
     */
    protected static function _cache($type, $key, $value = false) {
        $key = '_' . $key;
        $type = '_' . $type;
        if ($value !== false) {
            self::$_cache[$type][$key] = $value;
            return $value;
        }
        if (!isset(self::$_cache[$type][$key])) {
            return false;
        }
        return self::$_cache[$type][$key];
    }

    /**
     * Clears Inflectors inflected value caches. And resets the inflection
     * rules to the initial values.
     *
     * @return void
     */
    public static function reset() {
        if (empty(self::$_initialState)) {
            self::$_initialState = get_class_vars('Inflector');
            return;
        }
        foreach (self::$_initialState as $key => $val) {
            if ($key !== '_initialState') {
                self::${$key} = $val;
            }
        }
    }

    /**
     * Adds custom inflection $rules, of either 'plural', 'singular' or 'transliteration' $type.
     *
     * ### Usage:
     *
     * {{{
     * Inflector::rules('plural', array('/^(inflect)or$/i' => '\1ables'));
     * Inflector::rules('plural', array(
     *     'rules' => array('/^(inflect)ors$/i' => '\1ables'),
     *     'uninflected' => array('dontinflectme'),
     *     'irregular' => array('red' => 'redlings')
     * ));
     * Inflector::rules('transliteration', array('/å/' => 'aa'));
     * }}}
     *
     * @param string $type The type of inflection, either 'plural', 'singular' or 'transliteration'
     * @param array $rules Array of rules to be added.
     * @param boolean $reset If true, will unset default inflections for all
     *        new rules that are being defined in $rules.
     * @return void
     */
    public static function rules($type, $rules, $reset = false) {
        $var = '_' . $type;

        switch ($type) {
            case 'transliteration':
                if ($reset) {
                    self::$_transliteration = $rules;
                } else {
                    self::$_transliteration = $rules + self::$_transliteration;
                }
                break;

            default:
                foreach ($rules as $rule => $pattern) {
                    if (is_array($pattern)) {
                        if ($reset) {
                            self::${$var}[$rule] = $pattern;
                        } else {
                            if ($rule === 'uninflected') {
                                self::${$var}[$rule] = array_merge($pattern, self::${$var}[$rule]);
                            } else {
                                self::${$var}[$rule] = $pattern + self::${$var}[$rule];
                            }
                        }
                        unset($rules[$rule], self::${$var}['cache' . ucfirst($rule)]);
                        if (isset(self::${$var}['merged'][$rule])) {
                            unset(self::${$var}['merged'][$rule]);
                        }
                        if ($type === 'plural') {
                            self::$_cache['pluralize'] = self::$_cache['tableize'] = array();
                        } elseif ($type === 'singular') {
                            self::$_cache['singularize'] = array();
                        }
                    }
                }
                self::${$var}['rules'] = $rules + self::${$var}['rules'];
                break;
        }
    }

    /**
     * Return $word in plural form.
     *
     * @param string $word Word in singular
     * @return string Word in plural
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::pluralize
     */
    public static function pluralize($word) {
        if (isset(self::$_cache['pluralize'][$word])) {
            return self::$_cache['pluralize'][$word];
        }

        if (!isset(self::$_plural['merged']['irregular'])) {
            self::$_plural['merged']['irregular'] = self::$_plural['irregular'];
        }

        if (!isset(self::$_plural['merged']['uninflected'])) {
            self::$_plural['merged']['uninflected'] = array_merge(self::$_plural['uninflected'], self::$_uninflected);
        }

        if (!isset(self::$_plural['cacheUninflected']) || !isset(self::$_plural['cacheIrregular'])) {
            self::$_plural['cacheUninflected'] = '(?:' . implode('|', self::$_plural['merged']['uninflected']) . ')';
            self::$_plural['cacheIrregular'] = '(?:' . implode('|', array_keys(self::$_plural['merged']['irregular'])) . ')';
        }

        if (preg_match('/(.*)\\b(' . self::$_plural['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$_cache['pluralize'][$word] = $regs[1] . substr($word, 0, 1) . substr(self::$_plural['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$_cache['pluralize'][$word];
        }

        if (preg_match('/^(' . self::$_plural['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$_cache['pluralize'][$word] = $word;
            return $word;
        }

        foreach (self::$_plural['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$_cache['pluralize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$_cache['pluralize'][$word];
            }
        }
    }

    /**
     * Return $word in singular form.
     *
     * @param string $word Word in plural
     * @return string Word in singular
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::singularize
     */
    public static function singularize($word) {
        if (isset(self::$_cache['singularize'][$word])) {
            return self::$_cache['singularize'][$word];
        }

        if (!isset(self::$_singular['merged']['uninflected'])) {
            self::$_singular['merged']['uninflected'] = array_merge(
                self::$_singular['uninflected'],
                self::$_uninflected
            );
        }

        if (!isset(self::$_singular['merged']['irregular'])) {
            self::$_singular['merged']['irregular'] = array_merge(
                self::$_singular['irregular'],
                array_flip(self::$_plural['irregular'])
            );
        }

        if (!isset(self::$_singular['cacheUninflected']) || !isset(self::$_singular['cacheIrregular'])) {
            self::$_singular['cacheUninflected'] = '(?:' . implode('|', self::$_singular['merged']['uninflected']) . ')';
            self::$_singular['cacheIrregular'] = '(?:' . implode('|', array_keys(self::$_singular['merged']['irregular'])) . ')';
        }

        if (preg_match('/(.*)\\b(' . self::$_singular['cacheIrregular'] . ')$/i', $word, $regs)) {
            self::$_cache['singularize'][$word] = $regs[1] . substr($word, 0, 1) . substr(self::$_singular['merged']['irregular'][strtolower($regs[2])], 1);
            return self::$_cache['singularize'][$word];
        }

        if (preg_match('/^(' . self::$_singular['cacheUninflected'] . ')$/i', $word, $regs)) {
            self::$_cache['singularize'][$word] = $word;
            return $word;
        }

        foreach (self::$_singular['rules'] as $rule => $replacement) {
            if (preg_match($rule, $word)) {
                self::$_cache['singularize'][$word] = preg_replace($rule, $replacement, $word);
                return self::$_cache['singularize'][$word];
            }
        }
        self::$_cache['singularize'][$word] = $word;
        return $word;
    }

    /**
     * Returns the given lower_case_and_underscored_word as a CamelCased word.
     *
     * @param string $lowerCaseAndUnderscoredWord Word to camelize
     * @return string Camelized word. LikeThis.
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::camelize
     */
    public static function camelize($lowerCaseAndUnderscoredWord) {
        if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
            $result = str_replace(' ', '', Inflector::humanize($lowerCaseAndUnderscoredWord));
            self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
        }
        return $result;
    }

    /**
     * Returns the given camelCasedWord as an underscored_word.
     *
     * @param string $camelCasedWord Camel-cased word to be "underscorized"
     * @return string Underscore-syntaxed version of the $camelCasedWord
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::underscore
     */
    public static function underscore($camelCasedWord) {
        if (!($result = self::_cache(__FUNCTION__, $camelCasedWord))) {
            $result = strtolower(preg_replace('/(?<=\\w)([A-Z])/', '_\\1', $camelCasedWord));
            self::_cache(__FUNCTION__, $camelCasedWord, $result);
        }
        return $result;
    }

    /**
     * Returns the given underscored_word_group as a Human Readable Word Group.
     * (Underscores are replaced by spaces and capitalized following words.)
     *
     * @param string $lowerCaseAndUnderscoredWord String to be made more readable
     * @return string Human-readable string
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::humanize
     */
    public static function humanize($lowerCaseAndUnderscoredWord) {
        if (!($result = self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord))) {
            $result = ucwords(str_replace('_', ' ', $lowerCaseAndUnderscoredWord));
            self::_cache(__FUNCTION__, $lowerCaseAndUnderscoredWord, $result);
        }
        return $result;
    }

    /**
     * Returns corresponding table name for given model $className. ("people" for the model class "Person").
     *
     * @param string $className Name of class to get database table name for
     * @return string Name of the database table for given class
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::tableize
     */
    public static function tableize($className) {
        if (!($result = self::_cache(__FUNCTION__, $className))) {
            $result = Inflector::pluralize(Inflector::underscore($className));
            self::_cache(__FUNCTION__, $className, $result);
        }
        return $result;
    }

    /**
     * Returns Cake model class name ("Person" for the database table "people".) for given database table.
     *
     * @param string $tableName Name of database table to get class name for
     * @return string Class name
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::classify
     */
    public static function classify($tableName) {
        if (!($result = self::_cache(__FUNCTION__, $tableName))) {
            $result = Inflector::camelize(Inflector::singularize($tableName));
            self::_cache(__FUNCTION__, $tableName, $result);
        }
        return $result;
    }

    /**
     * Returns camelBacked version of an underscored string.
     *
     * @param string $string
     * @return string in variable form
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::variable
     */
    public static function variable($string) {
        if (!($result = self::_cache(__FUNCTION__, $string))) {
            $camelized = Inflector::camelize(Inflector::underscore($string));
            $replace = strtolower(substr($camelized, 0, 1));
            $result = preg_replace('/\\w/', $replace, $camelized, 1);
            self::_cache(__FUNCTION__, $string, $result);
        }
        return $result;
    }

    /**
     * Returns a string with all spaces converted to underscores (by default), accented
     * characters converted to non-accented characters, and non word characters removed.
     *
     * @param string $string the string you want to slug
     * @param string $replacement will replace keys in map
     * @return string
     * @link http://book.cakephp.org/2.0/en/core-utility-libraries/inflector.html#Inflector::slug
     */
    public static function slug($string, $replacement = '_') {
        $quotedReplacement = preg_quote($replacement, '/');

        $merge = array(
            '/[^\s\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]/mu' => ' ',
            '/\\s+/' => $replacement,
            sprintf('/^[%s]+|[%s]+$/', $quotedReplacement, $quotedReplacement) => '',
        );

        $map = self::$_transliteration + $merge;
        return preg_replace(array_keys($map), array_values($map), $string);
    }

}

// Store the initial state
Inflector::reset();