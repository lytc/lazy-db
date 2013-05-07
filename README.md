[![Build Status](https://travis-ci.org/lytc/lazy-db.png?branch=master)](https://travis-ci.org/lytc/lazy-db)
#Lazy/Db is an Object Relational Mapper written in PHP

###Release information
THIS RELEASE IS A DEVELOPMENT RELEASE AND NOT INTENDED FOR PRODUCTION USE. PLEASE USE AT YOUR OWN RISK

###Features
- Comprehensive ORM layer for mapping records to PHP objects
- Eager loading associations
- Dirty tracking
- Lazy load support
- Solving N+1 problem

###Getting Start
####1. Define an abstract model
```php
<?php
abstract class AbstractModel extends \Lazy\Db\Model\AbstractModel
{
    public function getPdo() {
        return new \Lazy\Db\Pdo('mysql:host=localhost;dbname=my_db', 'username', 'password');
    }
}
```

###2. Define models & collections
```php
<?php
class User extends AbstractModel
{
    // protected static $primaryKey = 'id'; default is 'id'
    protected static $tableName = 'users';
    protecte static $collectionClass = 'Users';
    
    protected static $columnsSchema = [
      'id'        => 'int',
      'name'      => 'varchar',
      'username'  => 'varchar',
      'password'  => 'varchar',
      'activated' => 'tinyint'
    ],
    
    protected static $immediatelySelectColumns = ['id', 'name', 'username', 'password', 'activated'];
    
    protected static $oneToMany = [
      'Posts' => ['model' => 'Post', 'key' => 'user_id']
    ];
}

class Users extend \Lazy\Db\Model\AbstractCollection
{
    protected static $modelClass = 'User';
}

class Post extends AbstractModel
{
    protected static $tableName = 'posts';
    protected static $collectionClass = 'Posts';
    
    protected static $columnsSchema = [
      'id'            => 'int',
      'user_id'       => 'int',
      'name'          => 'varchar',
      'content'       => 'text',
      'created_time'  => 'datetime',
      'modified_time' => 'timestamp',
    ],
    
    protected static $immediatelySelectColumns = ['id', 'user_id', 'name', 'created_time', 'modified_time'];
    
    protected static $manyToOne = [
      'User' => ['model' => 'User', 'key' => 'user_id']
    ];
}

class Posts extend \Lazy\Db\Model\AbstractCollection
{
    protected static $modelClass = 'Post';
}
```

###3 Interact with models
```php
# find one by primary key value
$user = User::first(1);
// $user = User::first(['name' => 'Join']);
echo $user->name;
foreach ($user->Posts as $post) {
    echo $post->name;
}

# find all
$users = User::all(['activated' => 1])->limit(3);
// $users = User::all()->where(['activated' => 1])->limit(3);
foreach ($users as $user) {
  echo $user->name;
  foreach ($user->Posts as $post) {
      echo $post->name;
  }
}
// in this case, it just execute 2 queries
# SELECT id, name, username, password, activated FROM users WHERE (activated = 1) LIMIT 10;
# SELECT id, user_id, name, created_time, modified_time FROM posts WHERE (user_id IN(1, 2, 3));
```
