<?php

namespace App\Blog\Article;

use App\Date\DateTime;
use App\Traits\Created;
use App\Traits\Deleted;
use App\Traits\Updated;
use App\Traits\AuthorId;
use App\Traits\Id;

class Comment
{
    use Id;
    use AuthorId;
    use Created;
    use Updated;
    use Deleted;
    
    private ?int $postId = null;

    public function __construct(
        private string $text
    ) {
        $this->createdAt = new DateTime();
    }

    public function setPostId($postId): self
    {
        $this->postId = $postId;
        return $this;
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

    public function getText (): string
    {
        return $this->text;
    }

    public function __toString()
    {
        return $this->getAuthorId() . ' оставил комментарий: ' . $this->text;
    }
}