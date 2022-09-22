<?php

namespace App\Blog\Http\Actions;


use App\Authentification\AuthentificationInterface;
use App\Blog\Article\CommentLike;
use App\Exceptions\AuthException;
use App\Exceptions\CommentLikeAlreadyExistsException;
use App\Exceptions\CommentNotFoundException;
use App\Repositories\CommentLikeRepositoryInterface;
use App\Repositories\CommentRepositoryInterface;
use App\Exceptions\UserNotFoundException;
use App\Exceptions\ArgumentException;
use App\Exceptions\HttpException;
use App\Blog\Http\Request;
use App\Blog\Http\Response;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\SuccessfulResponse;

/* Для реализации действия нужен POST-запрос вида
POST http://127.0.0.1:8000/comments/like
Content-Type: application/json

{
"comment_id": "1"
}
+ данные для различных видов аутентификации
*/

class AddLikeToComment implements AddLikeToCommentInterface
{
    public function __construct(
        private CommentLikeRepositoryInterface $likeRepository,
        private CommentRepositoryInterface $commentRepository,
        private AuthentificationInterface $authentication,
    ) {
    }

    public function handle(Request $request): Response
    {
        //аутентификация юзера, ставящего лайк
        try {
            $user = $this->authentication->user($request);
        } catch (AuthException $e) {
            return new ErrorResponse($e->getMessage());
        }

        $userId = $user->getId();

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

        $existingLikes = $this->likeRepository->getByCommentId($commentId) ? : null;

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