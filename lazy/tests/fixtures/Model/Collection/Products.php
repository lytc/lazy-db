<?php
namespace Model\Collection;
use \Lazy\Db\Model\AbstractCollection;

class Products extends AbstractCollection
{
    protected static $modelClass = '\Model\Product';
}