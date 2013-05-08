<?php
namespace Model\Collection;
use \Lazy\Db\Model\AbstractCollection;

class Orders extends AbstractCollection
{
    protected static $modelClass = '\Model\Order';
}