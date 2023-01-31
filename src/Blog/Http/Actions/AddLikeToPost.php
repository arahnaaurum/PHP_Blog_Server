<?php

namespace App\Blog\Http\Actions;


use App\Blog\Article\PostLike;
use App\Exceptions\PostLikeAlreadyExistsException;
use App\Repositories\PostLikeRepository;
use App\Repositories\PostLikeRepositoryInterface;
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

/* Для реализации действия нужен POST-запрос вида
POST http://127.0.0.1:8000/posts/like
Content-Type: application/json

{
"user_id": "1",
"post_id": "1"
}
*/

class AddLikeToPost implements ActionInterface
{
    public function __construct(
        private PostLikeRepositoryInterface $likeRepository,
        private PostRepositoryInterface $postRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(Request $request): Response
    {
        //проверка юзера, ставящего лайк
        try {
            $userId = ($request->jsonBodyField('user_id'));
        } catch (HttpException | ArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->userRepository->get($userId);
        } catch (UserNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        //проверка поста, к которому относится лайк
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

        $existingLikes = [];
        $existingLikes = $this->likeRepository->getByPostId($postId);

        // проверяем уникальность лайка
        try {
            if ($existingLikes) {
                foreach ($existingLikes as $like) {
                    if ($like->getUserId() == $userId) {
                        throw new PostLikeAlreadyExistsException();
                    }
                }
            }
        } catch (PostLikeAlreadyExistsException $e) {
            return new ErrorResponse($e->getMessage());
        }

     try {
    // Пытаемся создать объект лайка из данных запроса
            $like = new PostLike( $postId);
            $like->setUserId($userId);
     } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
     }

     // Сохраняем лайк в БД
     $this->likeRepository->save($like);

     // Возвращаем успешный ответ
     return new SuccessfulResponse([
            'post_id' => $like->getPostId(),
            'user_id' => $like->getUserId()
        ]);
    }
}