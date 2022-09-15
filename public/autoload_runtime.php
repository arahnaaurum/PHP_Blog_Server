<?php

use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use Psr\Container\ContainerInterface;
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

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../database/config/config.php';

$container = new DIContainer();
$container->bind(
    PDO::class,
    new PDO (databaseConfig()['sqlite']['DATABASE_URL'])
);
$container->bind(ConnectorInterface::class, SqLiteConnector::class);
$container->bind(UserRepositoryInterface::class, UserRepository::class);
$container->bind(PostRepositoryInterface::class, PostRepository::class);
$container->bind(PostLikeRepositoryInterface::class, PostLikeRepository::class);
$container->bind(CommentRepositoryInterface::class, CommentRepository::class);
$container->bind(CommentLikeRepositoryInterface::class, CommentLikeRepository::class);

return $container;