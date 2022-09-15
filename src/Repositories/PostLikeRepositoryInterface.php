<?php

namespace App\Repositories;

use App\Blog\Article\PostLike;

interface PostLikeRepositoryInterface
{
    public function save(PostLike $like): void;
    public function get(int $id): PostLike;
    public function getByPostId(int $id): array;
}