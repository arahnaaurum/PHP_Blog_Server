<?php

namespace App\Blog\Http\Actions;

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

class CreatePost implements ActionInterface
{
// Внедряем репозитории статей и пользователей
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
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

     // Возвращаем успешный ответ, содержащий title новой статьи
     return new SuccessfulResponse([
            'title' => $post->getTitle(),
        ]);
    }
}