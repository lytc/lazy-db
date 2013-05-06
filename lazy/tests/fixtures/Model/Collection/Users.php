<?php
namespace Model\Collection;
use \Lazy\Db\Model\AbstractCollection;

class Users extends AbstractCollection
{
    protected static $modelClass = '\Model\User';
}