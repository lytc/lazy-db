<?php

namespace LazyTest\Db;

use Lazy\Db\Statement;
use LazyTest\Db\Model\Post;
use LazyTest\Db\Model\User;

/**
 * @covers Lazy\Db\AbstractModel
 */
class EagerLoadingTest extends TestCase
{
    public function testWithLazyLoadColumn()
    {
        Statement::clearQueryLog();

        $posts = Post::all()->limit(2);
        $post1 = $posts->get(1);
        $post2 = $posts->get(2);
        $this->assertSame('content1', $post1->content);
        $this->assertSame('content2', $post2->content);

        $this->assertSame(Statement::getQueriesLog(), array(
            "SELECT id, user_id, name FROM posts LIMIT 2",
            "SELECT id, content FROM posts WHERE (id IN('1', '2'))"
        ));
    }

    public function testWithOneToMany()
    {
        Statement::clearQueryLog();
        $users = User::all();

        $posts1 = $users->get(1)->Posts->order('id DESC');
        $posts2 = $users->get(2)->Posts;
        $posts3 = $users->get(3)->Posts;
        $posts4 = $users->get(4)->Posts;

        $this->assertCount(1, Statement::getQueriesLog());

        $this->assertCount(2, $posts1);
        $this->assertCount(2, $posts2);
        $this->assertCount(0, $posts3);
        $this->assertCount(0, $posts4);

        $expected  = array(
            array('id' => '2', 'user_id' => '1', 'name' => 'name2'),
            array('id' => '1', 'user_id' => '1', 'name' => 'name1'),
        );
        $this->assertSame($expected, $users->get(1)->Posts->toArray());

        $expected  = array(
            array('id' => '4', 'user_id' => '2', 'name' => 'name4'),
            array('id' => '3', 'user_id' => '2', 'name' => 'name3'),
        );
        $this->assertSame($expected, $users->get(2)->Posts->toArray());

        $this->assertSame(Statement::getQueriesLog(), array(
            "SELECT id, name FROM users",
            "SELECT id, user_id, name FROM posts WHERE (user_id IN('1', '2', '3', '4')) ORDER BY id DESC"
        ));
    }

    public function testWithManyToOne()
    {
        Statement::clearQueryLog();

        $posts = Post::all();
        $post1 = $posts->get(1);

        $user = $post1->User;
        $this->assertSame($user, $posts->get(2)->User);
        $this->assertSame($posts->get(3)->User, $posts->get(4)->User);

        $this->assertSame(Statement::getQueriesLog(), array(
            "SELECT id, user_id, name FROM posts",
            "SELECT id, name FROM users WHERE (id IN('1', '2'))"
        ));
    }

    public function testWithManyToMany()
    {
        Statement::clearQueryLog();

        $users = User::all();
        $permissions1 = $users->get(1)->Permissions->order('id DESC');
        $permissions2 = $users->get(2)->Permissions;
        $permissions3 = $users->get(3)->Permissions;
        $permissions4 = $users->get(4)->Permissions;

        $this->assertCount(1, Statement::getQueriesLog());

        $this->assertCount(2, $permissions1);
        $this->assertCount(2, $permissions2);
        $this->assertCount(0, $permissions3);
        $this->assertCount(0, $permissions4);

        $expected = array(
            array('user_id' => '1', 'id' => '2', 'name' => 'name2'),
            array('user_id' => '1', 'id' => '1', 'name' => 'name1'),
        );

        $this->assertSame($expected, $permissions1->toArray());

        $this->assertSame(Statement::getQueriesLog(), array(
            "SELECT id, name FROM users",
            "SELECT user_permissions.user_id, id, name FROM permissions"
            . " INNER JOIN user_permissions ON user_permissions.permission_id = permissions.id"
            . " WHERE (user_permissions.user_id IN('1', '2', '3', '4')) ORDER BY id DESC"
        ));
    }
}