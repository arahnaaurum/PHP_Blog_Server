<?php

namespace App\Repositories;

use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Date\DateTime;
use App\Exceptions\CommentNotFoundException;
use App\Blog\Article\Comment;
use PDO;

class CommentRepository implements CommentRepositoryInterface
{
    private PDO $connection;

    public function __construct(private ?ConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? new SqLiteConnector();
        $this->connection = $this->connector->getConnection();
    }

    public function save(Comment $comment): void
    {
        $statement = $this->connection->prepare(
            '
                insert into comment (author_id, post_id, text, created_at)
                values (:author_id, :post_id, :text, :created_at)
            '
        );

        $statement->execute(
            [
                ':author_id' => $comment->getAuthorId(),
                ':post_id' => $comment->getPostId(),
                ':text' => $comment->getText(),
                ':created_at' => $comment->getCreatedAt()
            ]
        );
    }

    /**
     * @throws CommentNotFoundException
     * @throws \Exception
     */
    public function get(int $id): Comment
    {
        $statement = $this->connection->prepare(
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

        $comment = new Comment($commentObj->text);

        $comment
            ->setId($commentObj->id)
            ->setAuthorId(($commentObj->author_id))
            ->setPostId($commentObj->post_id)
            ->setCreatedAt(new DateTime($commentObj->created_at))
            ->setUpdatedAt(($updatedAt = $commentObj->updated_at) ? new DateTime($updatedAt) : null)
            ->setDeletedAt(($deletedAt = $commentObj->deleted_at) ? new DateTime($deletedAt) : null);

        return $comment;

    }
}