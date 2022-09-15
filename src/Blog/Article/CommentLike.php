<?php

namespace App\Blog\Article;

use App\Traits\Id;
use App\Traits\UserId;

class CommentLike
{
    use Id;
    use UserId;

    public function __construct(
        private int $commentId
    ) {
    }

    public function getCommentId(): ?int
    {
        return $this->commentId;
    }

}
