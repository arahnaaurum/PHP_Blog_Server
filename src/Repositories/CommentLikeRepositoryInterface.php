<?php

namespace App\Repositories;

use App\Blog\Article\CommentLike;

interface CommentLikeRepositoryInterface
{
    public function save(CommentLike $like): void;
    public function get(int $id): CommentLike;
    public function getByCommentId(int $id): array;
}