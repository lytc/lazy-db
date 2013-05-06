<?php
namespace Model;
use \Lazy\Db\Model\AbstractModel as LazyDbAbstractModel;
use LazyTest\Db\DbSample;

class AbstractModel extends LazyDbAbstractModel
{
    public static function getPdo()
    {
        return DbSample::getPdo();
    }
}