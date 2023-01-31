<?php

namespace App\Repositories;

use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Date\DateTime;
use App\Exceptions\PostNotFoundException;
use App\Blog\Article\Post;
use PDO;

class PostRepository implements PostRepositoryInterface
{
    private PDO $connection;

    public function __construct(private ?ConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? new SqLiteConnector();
        $this->connection = $this->connector->getConnection();
    }

    public function save(Post $post): void
    {
        $statement = $this->connection->prepare(
            '
                insert into post (author_id, title, text, created_at)
                values (:author_id, :title, :text, :created_at)
            '
        );

        $statement->execute(
            [
                ':title' => $post->getTitle(),
                ':text' => $post->getText(),
                ':created_at' => $post->getCreatedAt(),
                ':author_id' => $post->getAuthorId()
            ]
        );
    }

    /**
     * @throws PostNotFoundException
     * @throws \Exception
     */
    public function get(int $id): Post
    {
        $statement = $this->connection->prepare(
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

        return $this->mapPost($postObj);    
    }

    public function getByTitle(string $title): Post
    {
        $statement = $this->connection->prepare(
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

        return $this->mapPost($postObj);    
    }

    private function mapPost(object $postObj)
    {
        $post = new Post($postObj->title, $postObj->text);

        $post
            ->setId($postObj->id)
            ->setAuthorId(($postObj->author_id))
            ->setCreatedAt(new DateTime($postObj->created_at))
            ->setUpdatedAt(($updatedAt = $postObj->updated_at) ? new DateTime($updatedAt) : null)
            ->setDeletedAt(($deletedAt = $postObj->deleted_at) ? new DateTime($deletedAt) : null);

        return $post;
    }
}