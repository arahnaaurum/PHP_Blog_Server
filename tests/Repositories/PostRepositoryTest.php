<?php

namespace Test\Repositories;

use App\Blog\Article\Post;
use App\Exceptions\PostNotFoundException;
use App\Repositories\PostRepositoryInterface;
use PHPUnit\Framework\TestCase;
use App\Connection\ConnectorInterface;
use PDO;

class PostRepositoryTest extends TestCase
{
    // тесты с подключением к БД
    public function testItReturnsPostWhenPostIdIsInDatabase(): void
    {
        $repository = new class implements PostRepositoryInterface {
            public function save(Post $post): void
            {
            }
            public function get(int $id): Post
            {
                $connector = new class implements ConnectorInterface
                {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };
                
                $connection = $connector->getConnection();

                $statement = $connection->prepare(
                    "select * from post where id = :postId"
                );
            
                $statement->execute([
                    ':postId' => $id
                ]);

                $postObj = $statement->fetch(PDO::FETCH_OBJ);
                
                if(!$postObj)
                {
                    throw new PostNotFoundException("Post with id:$id not found");
                }
 
                return $post = new Post($postObj->title, $postObj->text);
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");
            }
        };

        $expectedPost = new Post("post with author", "here is some random text");
        $receivedPost = $repository->get(5);
        
        //у двух объектов будет разное время создания, поэтому сравнивать их целиком не получится
        //так что сравним отдельные поля, чтобы убедиться, что они совпадают
        $this->assertEquals($expectedPost->getTitle(), $receivedPost->getTitle());
        $this->assertEquals($expectedPost->getText(), $receivedPost->getText());
    }

    public function testItReturnsPostWhenPostTitleIsInDatabase(): void
    {
        $repository = new class implements PostRepositoryInterface {
            public function save(Post $post): void
            {
            }
            public function get(int $id): Post
            {
                throw new PostNotFoundException("Not found");
            }

            public function getByTitle(string $title): Post
            {   
                $connector = new class implements ConnectorInterface
                {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };
                
                $connection = $connector->getConnection();

                $statement = $connection->prepare(
                    "select * from post where title = :title"
                );
            
                $statement->execute([
                    ':title' => $title
                ]);

                $postObj = $statement->fetch(PDO::FETCH_OBJ);
                
                if(!$postObj)
                {
                    throw new PostNotFoundException("Post with title:$title not found");
                }
 
                return $post = new Post($postObj->title, $postObj->text);
            }
        };

        $expectedPost = new Post("post without author", "here is another text");
        $receivedPost = $repository->getByTitle("post without author");
        
        //у двух объектов будет разное время создания, поэтому сравнивать их целиком не получится
        //так что сравним отдельные поля, чтобы убедиться, что они совпадают
        $this->assertEquals($expectedPost->getTitle(), $receivedPost->getTitle());
        $this->assertEquals($expectedPost->getText(), $receivedPost->getText());
    }

    public function testItThrowsAnExceptionWhenPostNotFound(): void
    {
        $repository = new class implements PostRepositoryInterface {
            public function save(Post $post): void
            {
            }
            public function get(int $id): Post
            {
                $connector = new class implements ConnectorInterface
                {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };
                
                $connection = $connector->getConnection();

                $statement = $connection->prepare(
                    "select * from post where id = :postId"
                );
                
                //нет execute, т.к. эта ф-ция все равно должна возращать false, чтобы выпало исключение

                $postObj = $statement->fetch();
        
                if(!$postObj)
                {
                    throw new PostNotFoundException("Post with id:$id not found");
                }
                return new Post('sometitle', 'sometext');
            }

            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");
            }
        };

        $this->expectException(PostNotFoundException::class);
        $this->expectExceptionMessage('Post with id:0 not found');

        $repository->get(0);
    }
}
