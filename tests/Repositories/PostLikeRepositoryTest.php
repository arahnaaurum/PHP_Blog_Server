<?php

namespace Test\Repositories;

use App\Blog\Article\PostLike;
use App\Exceptions\PostLikeNotFoundException;
use App\Repositories\PostLikeRepositoryInterface;
use PHPUnit\Framework\TestCase;
use App\Connection\ConnectorInterface;
use PDO;

class PostLikeRepositoryTest extends TestCase
{
    public function testItReturnsLikeToPostFromDatabase(): void
    {
        $repository = new class implements PostLikeRepositoryInterface {
            public function save(PostLike $like): void
            {
            }

            public function get(int $id): PostLike
            {
                throw new PostLikeNotFoundException("Not found");
            }

            public function getByPostId(int $id): array
            {
                $connector = new class implements ConnectorInterface {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };

                $connection = $connector->getConnection();

                $statement = $connection->prepare(
                    "select * from post_like where post_id = :postId"
                );

                $statement->execute([
                    ':postId' => $id
                ]);

                if(!$statement)
                {
                    throw new PostLikeNotFoundException("Likes for post with id:$id not found");
                }

                while ($statement && $likeObj = $statement->fetch(PDO::FETCH_OBJ)) {
                    $like = new PostLike($likeObj->post_id);

                    $like
                        ->setId($likeObj->id)
                        ->setUserId(($likeObj->user_id));

                    $all_likes[] = $like;

                }

                return $all_likes;
            }
        };

        $expectedArrayLength = 2;
        $receivedArrayLength = count($repository->getByPostId(1));

        $this->assertEquals($expectedArrayLength, $receivedArrayLength);

    }
}
