<?php

namespace App\Blog\Http\Actions;

use App\Authentification\AuthentificationInterface;
use App\Blog\Article\Comment;
use App\Exceptions\AuthException;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\PostRepositoryInterface;
use App\Exceptions\PostNotFoundException;
use App\Exceptions\ArgumentException;
use App\Exceptions\HttpException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;

/*
Обработка JSON запроса вида:
POST http://127.0.0.1:8000/posts/comment
Content-Type: application/json

{
  "post_id": "1",
  "text": "Some text"
}
Также могут быть необходимы поля для различных типов аутентификации:
user_id - по id автора;
auth_user - по мэйлу автора;
auth_mail/auth_password - мэйл/пароль;

либо заголовок Authorization: Bearer [токен] при идентификации по токену
*/

class CreateComment implements CreateCommentInterface
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private PostRepositoryInterface $postRepository,
        private AuthentificationInterface $authentication,
    ) {
    }

    public function handle(Request $request): Response
    {

        try {
            $author = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $authorId = $author->getId();

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