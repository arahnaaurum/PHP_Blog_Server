<?php

namespace App\Blog\Http\Actions;


use App\Blog\Article\CommentLike;
use App\Exceptions\CommentLikeAlreadyExistsException;
use App\Exceptions\CommentNotFoundException;
use App\Repositories\CommentLikeRepositoryInterface;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\UserRepositoryInterface;

use App\Exceptions\UserNotFoundException;
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
"comment_id": "1"
}
*/

class AddLikeToComment implements ActionInterface
{
    public function __construct(
        private CommentLikeRepositoryInterface $likeRepository,
        private CommentRepositoryInterface $commentRepository,
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

        //проверка коммента, к которому относится лайк
        try {
            $commentId = ($request->jsonBodyField('comment_id'));
        } catch (HttpException | ArgumentException $exception) {
            return new ErrorResponse($exception->getMessage());
        }

        try {
            $this->commentRepository->get($commentId);
        } catch (CommentNotFoundException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $existingLikes = [];
        $existingLikes = $this->likeRepository->getByCommentId($commentId);

        // проверяем уникальность лайка
        try {
            if ($existingLikes) {
                foreach ($existingLikes as $like) {
                    if ($like->getUserId() == $userId) {
                        throw new CommentLikeAlreadyExistsException();
                    }
                }
            }
        } catch (CommentLikeAlreadyExistsException $e) {
            return new ErrorResponse($e->getMessage());
        }

     try {
    // Пытаемся создать объект лайка из данных запроса
            $like = new CommentLike($commentId);
            $like->setUserId($userId);
     } catch (HttpException $e) {
            return new ErrorResponse($e->getMessage());
     }

     // Сохраняем лайк в БД
     $this->likeRepository->save($like);

     // Возвращаем успешный ответ
     return new SuccessfulResponse([
            'comment_id' => $like->getCommentId(),
            'user_id' => $like->getUserId()
        ]);
    }
}