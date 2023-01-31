<?php

use App\Repositories\UserRepository;
use App\Repositories\PostRepository;
use App\Repositories\CommentRepository;

use App\Blog\Http\Request;
use App\Exceptions\HttpException;
use App\Blog\Http\ErrorResponse;

use App\Blog\Http\Actions\FindByEmail;
use App\Blog\Http\Actions\FindPostById;
use App\Blog\Http\Actions\CreatePost;
use App\Blog\Http\Actions\DeletePost;
use App\Blog\Http\Actions\CreateComment;

require_once __DIR__ . '/autoload_runtime.php';

$userRepository = new UserRepository();

$postRepository = new PostRepository();

$commentRepository = new CommentRepository();

// file_get_contents('php://input') - поток, содержащий тело запроса
$request = new Request($_GET, $_SERVER, file_get_contents('php://input'));

// 1. Получить рут из запроса
try {
    $path = $request->path();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}

// 2. Получить HTTP-метод запроса
try {
    $method = $request->method();
} catch (HttpException) {
    (new ErrorResponse)->send();
    return;
}

$routes = [
    // отделяем маршруты для запросов с разными методами
    'GET'=> [
        '/users/show' => new FindByEmail($userRepository),
        '/posts/show' => new FindPostById($postRepository),
    ],
    'POST'=> [
        '/posts/create' => new CreatePost($postRepository, $userRepository),
        '/posts/comment' => new CreateComment($commentRepository, $postRepository, $userRepository),
    ],
    'DELETE'=> [
        '/posts/delete' => new DeletePost($postRepository),
    ]

];

// Если у нас нет маршрутов для метода запроса - возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Проверяем, есть ли маршрут среди маршрутов для этого метода
if (!array_key_exists($path, $routes[$method])) {
    (new ErrorResponse('Not found'))->send();
    return;
}

// Выбираем действие по методу и пути
$action = $routes[$method][$path];

try {
    $response = $action->handle($request);
} catch (Exception $e) {
    (new ErrorResponse($e->getMessage()))->send();
}

// Отправляем ответ, который может быть как удачным, так и неудачным
$response->send();