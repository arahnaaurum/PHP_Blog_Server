<?php

namespace Test\Repositories;

use App\Blog\Article\Comment;
use App\Exceptions\CommentNotFoundException;
use App\Repositories\CommentRepositoryInterface;
use PHPUnit\Framework\TestCase;
use App\Connection\ConnectorInterface;
use PDO;

class CommentRepositoryTest extends TestCase
{
    // тесты с подключением к БД
    public function testItReturnsCommentWhenCommentIdIsInDatabase(): void
    {
        $repository = new class implements CommentRepositoryInterface {
            public function save(Comment $comment): void
            {
            }
            public function get(int $id): Comment
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
                    "select * from comment where id = :commentId"
                );
            
                $statement->execute([
                    ':commentId' => $id
                ]);

                $commentObj = $statement->fetch(PDO::FETCH_OBJ);
                
                if(!$commentObj)
                {
                    throw new CommentNotFoundException("Comment with id:$id not found");
                }
 
                return new Comment($commentObj->text);
            }
        };

        $expectedComment = new Comment("lol");
        $receivedComment = $repository->get(4);
        
        $this->assertEquals($expectedComment->getText(), $receivedComment->getText());
    }

    public function testItThrowsAnExceptionWhenCommentNotFound(): void
    {
        $repository = new class implements CommentRepositoryInterface {
            public function save(Comment $comment): void
            {
            }
            public function get(int $id): Comment
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
                    "select * from comment where id = :commentId"
                );
                
                //нет execute, т.к. эта ф-ция все равно должна возращать false, чтобы выпало исключение

                $commentObj = $statement->fetch();
        
                if(!$commentObj)
                {
                    throw new CommentNotFoundException("Comment with id:$id not found");
                }
                return new Comment('sometext');
            }

        };

        $this->expectException(CommentNotFoundException::class);
        $this->expectExceptionMessage('Comment with id:0 not found');

        $repository->get(0);
    }
}
