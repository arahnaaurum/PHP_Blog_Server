<?php

use App\Repositories\UserRepository;
use App\User\Entities\User;
use App\Blog\Commands\CreateUserCommand;

use App\Repositories\PostRepository;
use App\Blog\Article\Post;
use App\Blog\Commands\CreatePostCommand;

use App\Repositories\CommentRepository;
use App\Blog\Article\Comment;
use App\Blog\Commands\CreateCommentCommand;

use App\Blog\Arguments\Argument;


require_once __DIR__ . '/autoload_runtime.php';

$userRepository = new UserRepository();
// $newCommand = new CreateUserCommand($userRepository);
// try {
//     $newCommand->handle(Argument::fromArgv($argv));
// } catch (\App\Exceptions\CommandException)
// {
//     echo 'User with this email already exists!';
// }

$postRepository = new PostRepository();
// $newCommand = new CreatePostCommand($postRepository);
// try {
//     $newCommand->handle(Argument::fromArgv($argv));
// } catch (\App\Exceptions\CommandException)
// {
//     echo 'Post with this title already exists!';
// }

$commentRepository = new CommentRepository();
// $newCommand = new CreateCommentCommand($commentRepository);
// $newCommand->handle(Argument::fromArgv($argv));



// для автоматической генерации юзеров, постов, комментов:
// $faker = Faker\Factory::create();

// $argument = $_SERVER['argv'][1];

// switch ($argument) {
//     case 'user':
//         $firstName = $faker->firstName();
//         $lastName = $faker->lastName();
//         $user = new User($firstName, $lastName);
//         $userRepository->save($user);
//         break;
//     case 'post':
//         $title = $faker->text(50);
//         $text = $faker->text();
//         $post = new Post($title, $text);
//         $postRepository->save($post);
//         break;
//     case 'comment':
//         $text = $faker->text(100);
//         $comment = new Comment($text);
//         $commentRepository->save($comment);
//         break;
//     default:
//         break;
// }

die();