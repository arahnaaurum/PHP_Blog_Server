<?php

use Psr\Container\ContainerInterface;
use App\Blog\Http\Actions\ActionInterface;
use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Container\DIContainer;
use App\Repositories\UserRepository;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\PostRepository;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\CommentRepository;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\PostLikeRepository;
use App\Repositories\PostLikeRepositoryInterface;
use App\Repositories\CommentLikeRepository;
use App\Repositories\CommentLikeRepositoryInterface;

use App\Blog\Http\Request;
use App\Exceptions\HttpException;
use App\Blog\Http\ErrorResponse;

//это те же хэндеры (handlers)
use App\Blog\Http\Actions\FindByEmail;
use App\Blog\Http\Actions\FindPostById;
use App\Blog\Http\Actions\CreatePost;
use App\Blog\Http\Actions\DeletePost;
use App\Blog\Http\Actions\CreateComment;
use App\Blog\Http\Actions\AddLikeToPost;
use App\Blog\Http\Actions\AddLikeToComment;

use App\Blog\Http\Auth\UserIdIdentification;

$container = require_once __DIR__ . '/autoload_runtime.php';

//0. Подключаем логирование
$logger = $container->get(\Psr\Log\LoggerInterface::class);

$userRepository = $container->get(UserRepositoryInterface::class);
$postRepository = $container->get(PostRepositoryInterface::class);
$postLikeRepository = $container->get(PostLikeRepositoryInterface::class);
$commentRepository = $container->get(CommentRepositoryInterface::class);
$commentLikeRepository = $container->get(CommentLikeRepositoryInterface::class);

// file_get_contents('php://input') - поток, содержащий тело запроса
$request = new Request($_GET, $_SERVER, file_get_contents('php://input'));


// 1. Получить рут из запроса
try {
    $path = $request->path();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}

// 2. Получить HTTP-метод запроса
try {
    $method = $request->method();
} catch (HttpException $e) {
    $logger->warning($e->getMessage());
    (new ErrorResponse)->send();
    return;
}

//3.Подключаем идентификацию юзеров
$identification = new UserIdIdentification($userRepository);

$routes = [
    // отделяем маршруты для запросов с разными методами
    'GET'=> [
        '/users/show' => new FindByEmail($userRepository, $logger),
        '/posts/show' => new FindPostById($postRepository),
    ],
    'POST'=> [
        '/posts/create' => new CreatePost($postRepository, $identification, $logger),
        '/posts/comment' => new CreateComment($commentRepository, $postRepository, $userRepository),
        '/posts/like' => new AddLikeToPost($postLikeRepository, $postRepository, $userRepository),
        '/comments/like' => new AddLikeToComment($commentLikeRepository, $commentRepository, $userRepository),
    ],
    'DELETE'=> [
        '/posts/delete' => new DeletePost($postRepository),
    ]

];

// Если у нас нет маршрутов для метода запроса - возвращаем неуспешный ответ
if (!array_key_exists($method, $routes)
    || !array_key_exists($path, $routes[$method])) {
// Логируем сообщение с уровнем NOTICE
    $message = "Route not found: $method $path";
    $logger->notice($message);
    (new ErrorResponse($message))->send();
    return;
}

// Выбираем действие по методу и пути
$action = $routes[$method][$path];

try {
    $response = $action->handle($request);
} catch (Exception $e) {
    $logger->error($e->getMessage(), ['exception' => $e]);
    (new ErrorResponse($e->getMessage()))->send();
}

// Отправляем ответ, который может быть как удачным, так и неудачным
$response->send();