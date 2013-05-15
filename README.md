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
####1. Setup connection
```php
<?php
use Lazy\Db\Connection;

Connection::setEnv(Connection::ENV_DEVELOPMENT);
Connection::setDefaultConfig(array(
    'development' => array(
        'dsn' => 'mysql:host=localhost;dbname=lazy_db_test',
        'username' => 'root'
    )
));
```

###2. Define models
```php
<?php
use Lazy\Db\AbstractModel;

class User extends AbstractModel
{
    protected static $columnsSchema = [
      'id'        => 'int',
      'name'      => 'varchar',
      'username'  => 'varchar',
      'password'  => 'varchar',
      'activated' => 'tinyint'
    ],

    protected static $oneToMany = ['Posts'];
}

class Post extends AbstractModel
{
    protected static $columnsSchema = [
      'id'            => 'int',
      'user_id'       => 'int',
      'name'          => 'varchar',
      'content'       => 'text',
      'created_time'  => 'datetime',
      'modified_time' => 'timestamp',
    ],

    protected static $manyToOne = ['User'];
}
```

###3 Interact with models
```php
# find one by primary key value
$user = User::first(1);
echo $user->name;
foreach ($user->Posts as $post) {
    echo $post->name;
}

# find all
$users = User::all(['activated' => 1])->limit(3);
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
