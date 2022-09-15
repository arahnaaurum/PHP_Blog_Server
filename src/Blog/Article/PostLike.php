<?php

namespace App\Blog\Article;

use App\Traits\Id;
use App\Traits\UserId;

class PostLike
{
    use Id;
    use UserId;

    public function __construct(
        private int $postId
    ) {
    }

    public function getPostId(): ?int
    {
        return $this->postId;
    }

}
