<?php

namespace App\Repositories;

use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Date\DateTime;
use App\Exceptions\PostLikeNotFoundException;
use App\Blog\Article\PostLike;
use PDO;

class PostLikeRepository implements PostLikeRepositoryInterface
{
    private PDO $connection;

    public function __construct(private ?ConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? new SqLiteConnector();
        $this->connection = $this->connector->getConnection();
    }

    public function save(PostLike $like): void
    {
        $statement = $this->connection->prepare(
            '
                insert into post_like (user_id, post_id)
                values (:user_id, :post_id)
            '
        );

        $statement->execute(
            [
                ':user_id' => $like->getUserId(),
                ':post_id' => $like->getPostId(),
            ]
        );
    }

    /**
     * @throws PostLikeNotFoundException
     * @throws \Exception
     */
    public function get(int $id): PostLike
    {
        $statement = $this->connection->prepare(
            "select * from post_like where id = :likeId"
        );

        $statement->execute([
            ':likeId' => $id
        ]);

        $likeObj = $statement->fetch(PDO::FETCH_OBJ);

        if(!$likeObj)
        {
            throw new PostLikeNotFoundException("Like with id:$id not found");
        }

        $like = new PostLike($likeObj->post_id);

        $like
            ->setId($likeObj->id)
            ->setUserId(($likeObj->user_id));

        return $like;

    }

    public function getByPostId(int $id): array
    {
        $all_likes = [];

        $statement = $this->connection->prepare(
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
}