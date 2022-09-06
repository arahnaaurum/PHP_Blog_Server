<?php

use App\Repositories\UserRepository;
use App\User\Entities\User;
use App\Repositories\PostRepository;
use App\Blog\Article\Post;
use App\Repositories\CommentRepository;
use App\Blog\Article\Comment;

require_once __DIR__ . '/autoload_runtime.php';

$userRepository = new UserRepository();
$postRepository = new PostRepository();
$commentRepository = new CommentRepository();

//$user = new User('Ivan', 'Fadeev');
// $userRepository->save($user);
// $user = $userRepository->get(4);
// var_dump($user);

// $post = new Post('post with author', 'here is some random text');
// $post = new Post('post without author', 'here is another text');
// $post->setAuthorId(2);
// $postRepository->save($post);
// $post = $postRepository->get(1);
// var_dump($post);

// $comment = new Comment ('Such wow much cool!');
// $comment->setAuthorId(2);
// $comment->setPostId(1);
// $commentRepository->save($comment);
// $comment = $commentRepository->get(1);
// var_dump($comment);

// для автоматической генерации юзеров, постов, комментов:
$faker = Faker\Factory::create();

$argument = $_SERVER['argv'][1];

switch ($argument) {
    case 'user':
        $firstName = $faker->firstName();
        $lastName = $faker->lastName();
        $user = new User($firstName, $lastName);
        $userRepository->save($user);
        break;
    case 'post':
        $title = $faker->text(50);
        $text = $faker->text();
        $post = new Post($title, $text);
        $postRepository->save($post);
        break;
    case 'comment':
        $text = $faker->text(100);
        $comment = new Comment($text);
        $commentRepository->save($comment);
        break;
    default:
        break;
}

die();