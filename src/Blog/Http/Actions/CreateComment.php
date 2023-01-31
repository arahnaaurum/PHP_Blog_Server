<?php

namespace App\Blog\Http\Actions;

use App\Blog\Article\Comment;

use App\Repositories\CommentRepositoryInterface;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\UserRepositoryInterface;

use App\Exceptions\UserNotFoundException;
use App\Exceptions\PostNotFoundException;
use App\Exceptions\ArgumentException;
use App\Exceptions\HttpException;

use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;


class CreateComment implements ActionInterface
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private PostRepositoryInterface $postRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
        //проверка автора коммента
        try {
            $authorId = ($request->jsonBodyField('author_id'));
        } catch (HttpException | ArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->userRepository->get($authorId);
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        //проверка поста, к которому относится коммент
        try {
            $postId = ($request->jsonBodyField('post_id'));
        } catch (HttpException | ArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->postRepository->get($postId);
        } catch (PostNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

     try {
    // Пытаемся создать объект статьи из данных запроса
            $comment = new Comment (
                $request->jsonBodyField('text'),
            );
            $comment->setAuthorId($authorId);
            $comment->setPostId($postId);
     } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
     }

     // Сохраняем новую статью в репозитории
     $this->commentRepository->save($comment);

     // Возвращаем успешный ответ, содержащий title новой статьи
     return new SuccessfulResponse([
            'text' => $comment->getText(),
        ]);
    }
}