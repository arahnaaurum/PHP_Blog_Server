<?php

namespace App\Repositories;

use App\Blog\Article\Post;

interface PostRepositoryInterface
{
    public function save(Post $post): void;
    public function get(int $id): Post;
}