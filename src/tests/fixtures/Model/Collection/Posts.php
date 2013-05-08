<?php
namespace Model\Collection;
use \Lazy\Db\Model\AbstractCollection;

class Posts extends AbstractCollection
{
    protected static $modelClass = '\Model\Post';
}