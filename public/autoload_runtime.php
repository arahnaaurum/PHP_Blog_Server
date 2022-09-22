<?php

use App\Authentification\AuthentificationInterface;
use App\Authentification\JsonBodyAuthentification;
use App\Authentification\PasswordAuthentification;
use App\Authentification\PasswordAuthentificationInterface;
use App\Authentification\TokenAuthentification;
use App\Authentification\UserIdAuthentification;
use App\Blog\Commands\CreateUserCommand;
use App\Blog\Commands\CreateUserCommandInterface;
use App\Blog\Http\Actions\AddLikeToComment;
use App\Blog\Http\Actions\AddLikeToCommentInterface;
use App\Blog\Http\Actions\AddLikeToPost;
use App\Blog\Http\Actions\AddLikeToPostInterface;
use App\Blog\Http\Actions\CreateComment;
use App\Blog\Http\Actions\CreateCommentInterface;
use App\Blog\Http\Actions\CreatePost;
use App\Blog\Http\Actions\CreatePostInterface;
use App\Blog\Http\Actions\DeletePost;
use App\Blog\Http\Actions\DeletePostInterface;
use App\Blog\Http\Actions\LoginAction;
use App\Blog\Http\Actions\LoginActionInterface;
use App\Blog\Http\Actions\LogoutAction;
use App\Blog\Http\Actions\LogoutActionInterface;
use App\Blog\Http\Actions\UserCreateActionInterface;
use App\Blog\Http\Actions\UserCreateAction;
use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Container\DIContainer;
use App\Repositories\AuthTokenRepository;
use App\Repositories\AuthTokenRepositoryInterface;
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
use Psr\Log\LoggerInterface;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Dotenv\Dotenv;
use Psr\Container\ContainerInterface;

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/config/config.php';

Dotenv::createImmutable(__DIR__. '/../')->safeLoad();

$container = new DIContainer();

// Добавляем логгер в контейнер
$logger = (new Logger('blog'));
if ('yes' === $_SERVER['LOG_TO_FILES']) {
    $logger
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.log'
        ))
        ->pushHandler(new StreamHandler(
            __DIR__ . '/logs/blog.error.log',
            level: Logger::ERROR,
            bubble: false,
        ));
}
if ('yes' === $_SERVER['LOG_TO_CONSOLE']) {
    $logger
        ->pushHandler(
            new StreamHandler("php://stdout")
        );
}
$container->bind(
    LoggerInterface::class,
    $logger
);

//$container->bind(
//    PDO::class,
//    new PDO (databaseConfig()['sqlite']['DATABASE_URL'])
//);

//конфигурация БД через файл .env
$container->bind(
    PDO::class,
    new PDO('sqlite:' . __DIR__ . '/' . $_SERVER['SQLITE_DB_PATH'])
);

$container->bind(ConnectorInterface::class, SqLiteConnector::class);
$container->bind(UserRepositoryInterface::class, UserRepository::class);
$container->bind(PostRepositoryInterface::class, PostRepository::class);
$container->bind(PostLikeRepositoryInterface::class, PostLikeRepository::class);
$container->bind(CommentRepositoryInterface::class, CommentRepository::class);
$container->bind(CommentLikeRepositoryInterface::class, CommentLikeRepository::class);
$container->bind(UserCreateActionInterface::class, UserCreateAction::class);
$container->bind(CreateUserCommandInterface::class, CreateUserCommand::class);
$container->bind(AuthTokenRepositoryInterface::class,AuthTokenRepository::class);
$container->bind(CreatePostInterface::class, CreatePost::class);
$container->bind(CreateCommentInterface::class, CreateComment::class);
$container->bind(AddLikeToPostInterface::class, AddLikeToPost::class);
$container->bind(AddLikeToCommentInterface::class, AddLikeToComment::class);
$container->bind(DeletePostInterface::class, DeletePost::class);
$container->bind(LogoutActionInterface::class, LogoutAction::class);

//разные варианты аутентификации
//$container->bind(AuthentificationInterface::class,UserIdAuthentification::class);
//$container->bind(AuthentificationInterface::class, JsonBodyAuthentification::class);
//$container->bind(AuthentificationInterface::class,PasswordAuthentification::class);
$container->bind(PasswordAuthentificationInterface::class,PasswordAuthentification::class);
$container->bind(AuthentificationInterface::class,TokenAuthentification::class);
$container->bind(LoginActionInterface::class, LoginAction::class);
return $container;