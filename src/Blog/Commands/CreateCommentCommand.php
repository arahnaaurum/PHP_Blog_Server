<?php

namespace App\Blog\Commands;

use App\Blog\Article\Comment;
use App\Repositories\CommentRepository;
use App\Repositories\CommentRepositoryInterface;
use App\Blog\Arguments\Argument;
use App\Exceptions\CommandException;

final class CreateCommentCommand
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository)
    {
    }

    public function handle(Argument $arguments) : void
    {
        // мне кажется, комментарию не нужна проверка на уникальность
        $this->commentRepository->save(new Comment
            (
                $arguments->get('text')
            )
        ); //передали обязательные значения полей для коммента
    }
    
}