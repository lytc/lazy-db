<?php
namespace Model\Collection;
use \Lazy\Db\Model\AbstractCollection;

class UserPermissions extends AbstractCollection
{
    protected static $modelClass = '\Model\UserPermission';
}