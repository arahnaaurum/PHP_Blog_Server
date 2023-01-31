<?php

namespace App\Blog\Http\Actions;

use App\Authentification\AuthentificationInterface;
use App\Blog\Http\ErrorResponse;
use App\Exceptions\AuthException;
use App\Exceptions\HttpException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\SuccessfulResponse;
use App\Blog\Article\Post;
use App\Repositories\PostRepositoryInterface;
use Psr\Log\LoggerInterface;
/*
Обработка JSON запроса вида:
POST http://127.0.0.1:8000/posts/create
Content-Type: application/json

{
  "title": "Some title",
  "text": "Some text"
}
Также могут быть необходимы поля для различных типов аутентификации:
user_id - по id автора;
auth_user - по мэйлу автора;
auth_mail/auth_password - мэйл/пароль;

либо заголовок Authorization: Bearer [токен] при идентификации по токену
*/

class CreatePost implements CreatePostInterface
{
// Внедряем репозитории статей и пользователей
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private AuthentificationInterface $authentication,
        private LoggerInterface         $logger,
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

     try {
    // Пытаемся создать объект статьи из данных запроса
            $post = new Post (
                $request->jsonBodyField('title'),
                $request->jsonBodyField('text'),
            );
            $post->setAuthorId($authorId);
     } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
     }

     // Сохраняем новую статью в репозитории
     $this->postRepository->save($post);

     $this->logger->info("Post created:" . $post->getTitle());

     // Возвращаем успешный ответ, содержащий title новой статьи
     return new SuccessfulResponse([
            'title' => $post->getTitle(),
        ]);
    }
}