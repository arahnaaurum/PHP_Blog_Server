<?php

use App\Authentification\AuthentificationInterface;
use App\Authentification\UserIdAuthentification;
use App\Authentification\PasswordAuthentificationInterface;
use App\Blog\Http\Actions\AddLikeToCommentInterface;
use App\Blog\Http\Actions\AddLikeToPostInterface;
use App\Blog\Http\Actions\CreateCommentInterface;
use App\Blog\Http\Actions\CreatePostInterface;
use App\Blog\Http\Actions\DeletePostInterface;
use App\Blog\Http\Actions\LoginAction;
use App\Blog\Http\Actions\LoginActionInterface;
use App\Blog\Http\Actions\LogoutActionInterface;
use App\Blog\Http\Actions\UserCreateActionInterface;
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
$identification = $container->get(AuthentificationInterface::class);
//$identification = new PasswordAuthentification($userRepository);

$routes = [
    // отделяем маршруты для запросов с разными методами
    'GET'=> [
        '/users/show' => new FindByEmail($userRepository, $logger),
        '/posts/show' => new FindPostById($postRepository),
    ],
    'POST'=> [
        '/login' => $container->get(LoginActionInterface::class),
        '/logout' => $container->get(LogoutActionInterface::class),
        '/users/create' => $container->get(UserCreateActionInterface::class),
        '/posts/create' => $container->get(CreatePostInterface::class),
        '/posts/comment' => $container->get(CreateCommentInterface::class),
        '/posts/like' => $container->get(AddLikeToPostInterface::class),
        '/comments/like' => $container->get(AddLikeToCommentInterface::class),
    ],
    'DELETE'=> [
        '/posts/delete' => $container->get(DeletePostInterface::class),
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