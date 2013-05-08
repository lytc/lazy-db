<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
$loader = require_once __DIR__ . '/../../vendor/autoload.php';
//$loader->add('Lazy\\Db', __DIR__ . '/../');
$loader->add('Model\\', __DIR__ . '/fixtures/');
require_once __DIR__  .'/fixtures/DbSample.php';
require_once __DIR__  .'/fixtures/PdoMock.php';

\LazyTest\Db\DbSample::getPdo();
//$generator = new \Lazy\Db\Model\Generator(\LazyTest\Db\DbSample::getPdo(), __DIR__ . '/fixtures/Model', 'Model');
//$generator->generate();