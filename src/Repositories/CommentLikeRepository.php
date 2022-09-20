<?php

namespace App\Repositories;

use App\Blog\Article\CommentLike;
use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Exceptions\CommentLikeNotFoundException;
use App\Repositories\CommentLikeRepositoryInterface;
use PDO;
use Psr\Log\LoggerInterface;

class CommentLikeRepository implements CommentLikeRepositoryInterface
{
    private PDO $connection;

    public function __construct(
        private LoggerInterface $logger,
        private ?ConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? new SqLiteConnector();
        $this->connection = $this->connector->getConnection();
    }

    public function save(CommentLike $like): void
    {
        $statement = $this->connection->prepare(
            '
                insert into comment_like (user_id, comment_id)
                values (:user_id, :comment_id)
            '
        );

        $statement->execute(
            [
                ':user_id' => $like->getUserId(),
                ':comment_id' => $like->getCommentId(),
            ]
        );

        $this->logger->info("Comment " . $like->getCommentId() . " liked by user " . $like->getUserId());
    }

    /**
     * @throws CommentLikeNotFoundException
     * @throws \Exception
     */
    public function get(int $id): CommentLike
    {
        $statement = $this->connection->prepare(
            "select * from comment_like where id = :likeId"
        );

        $statement->execute([
            ':likeId' => $id
        ]);

        $likeObj = $statement->fetch(PDO::FETCH_OBJ);

        if(!$likeObj)
        {
            $this->logger->warning("Like with id:$id not found");
            throw new CommentLikeNotFoundException("Like with id:$id not found");
        }

        $like = new CommentLike($likeObj->comment_id);

        $like
            ->setId($likeObj->id)
            ->setUserId(($likeObj->user_id));

        return $like;

    }

    public function getByCommentId(int $id): array
    {
        $all_likes = [];

        $statement = $this->connection->prepare(
            "select * from comment_like where comment_id = :commentId"
        );

        $statement->execute([
            ':commentId' => $id
        ]);

        if(!$statement)
        {
            $this->logger->warning("No likes for comment with id:$id found");
            return $all_likes;
//            throw new CommentLikeNotFoundException("Likes for comment with id:$id not found");
        }

        while ($statement && $likeObj = $statement->fetch(PDO::FETCH_OBJ)) {
            $like = new CommentLike($likeObj->comment_id);

            $like
                ->setId($likeObj->id)
                ->setUserId(($likeObj->user_id));

            $all_likes[] = $like;

        }

        return $all_likes;

    }
}