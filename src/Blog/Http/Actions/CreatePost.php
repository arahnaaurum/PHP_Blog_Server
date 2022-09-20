<?php

namespace App\Blog\Http\Actions;

use App\Blog\Http\Auth\IdentificationInterface;
use App\Exceptions\ArgumentException;
use App\Blog\Http\Actions\ActionInterface;
use App\Blog\Http\ErrorResponse;
use App\Exceptions\HttpException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\SuccessfulResponse;
use App\Blog\Article\Post;
use App\Repositories\PostRepositoryInterface;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
/*
Обработка JSON запроса вида:
POST http://127.0.0.1:8000/posts/create
Content-Type: application/json

{
  "user_id": "1",
  "title": "Some title",
  "text": "Some text"
}
*/

class CreatePost implements ActionInterface
{
// Внедряем репозитории статей и пользователей
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private IdentificationInterface $identification,
        private LoggerInterface $logger,
    ) {
    }

    public function handle(Request $request): Response
    {
        $author = $this->identification->user($request);
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