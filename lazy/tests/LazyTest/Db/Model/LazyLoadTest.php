<?php

namespace LazyTest\Db\Model;

use Lazy\Db\Stmt;
use Model\Post;

class LazyLoadTest extends \PHPUnit_Framework_TestCase
{
    public function testLazyLoadInModel()
    {
        Stmt::startLogInstance();

        $post = Post::first();
        $this->assertSame(1, $post->id);

        $stmtInstances = Stmt::getLogInstances();
        $this->assertCount(1, $stmtInstances);
        $this->assertSame("SELECT id, user_id, name, created_time, modified_time FROM posts LIMIT 1", $stmtInstances[0]->queryString);

        Stmt::startLogInstance();
        $this->assertSame('content1', $post->content);

        $stmtInstances = Stmt::getLogInstances();
        $this->assertCount(1, $stmtInstances);
        $this->assertSame("SELECT content FROM posts WHERE (id = 1) LIMIT 1", $stmtInstances[0]->queryString);
    }

    public function testLazyLoadInCollection()
    {
        Stmt::startLogInstance();

        $posts = Post::all()->limit(2);
        $count = 0;
        foreach ($posts as $index => $post) {
            $this->assertSame('content' . ($index + 1), $post->content);
            $count++;
        }

        $this->assertSame(2, $count);

        $stmtInstances = Stmt::getLogInstances();
        $this->assertCount(2, $stmtInstances);
        $this->assertEquals(Post::createSqlSelect()->limit(2)->__toString(), $stmtInstances[0]->queryString);

        $expected = Post::createSqlSelect()->resetColumn()->column('id, content')->where(['id IN(?)' => ['1', '2']])->__toString();
        $this->assertEquals($expected, $stmtInstances[1]->queryString);
    }
}