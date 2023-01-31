<?php

use App\Console\CreateUser;
use App\Console\DeletePost;
use App\Console\PopulateDB;
use App\Console\UpdateUser;
use App\Repositories\UserRepositoryInterface;
use App\Repositories\UserRepository;
use App\User\Entities\User;
use App\Blog\Commands\CreateUserCommand;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\PostRepository;
use App\Blog\Article\Post;
use App\Blog\Commands\CreatePostCommand;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\CommentRepository;
use App\Blog\Article\Comment;
use App\Blog\Commands\CreateCommentCommand;
use App\Blog\Arguments\Argument;
use \App\Exceptions\CommandException;
use Symfony\Component\Console\Application;



$container = require_once __DIR__ . '/autoload_runtime.php';

$logger = $container->get(\Psr\Log\LoggerInterface::class);

$userRepository = $container->get(UserRepositoryInterface::class);

$application = new Application();

$commandsClasses = [
    CreateUser::class,
    DeletePost::class,
    UpdateUser::class,
    PopulateDB::class
];

foreach ($commandsClasses as $commandClass) {
    $command = $container->get($commandClass);
    $application->add($command);
}
// Запускаем приложение
$application->run();


//$postRepository = $container->get(PostRepositoryInterface::class);
// $newCommand = new CreatePostCommand($postRepository, $logger);
// try {
//     $newCommand->handle(Argument::fromArgv($argv));
// } catch (CommandException)
// {
//     echo 'Post with this title already exists!';
// }

//$commentRepository = $container->get(CommentRepositoryInterface::class);
// $newCommand = new CreateCommentCommand($commentRepository);
// $newCommand->handle(Argument::fromArgv($argv));
