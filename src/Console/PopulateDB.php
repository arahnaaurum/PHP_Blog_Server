<?php

namespace App\Console;

use App\Blog\Article\Comment;
use App\Blog\Article\Post;
use App\Repositories\CommentRepositoryInterface;
use App\Repositories\PostRepositoryInterface;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PopulateDB extends Command
{
    public function __construct(
        private \Faker\Generator $faker,
        private UserRepositoryInterface $userRepository,
        private PostRepositoryInterface $postRepository,
        private CommentRepositoryInterface $commentRepository,

    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('users:populate-db')
            ->setDescription('Creates fake users, posts and comments for database')
            ->addOption('users-number', 'u', InputOption::VALUE_OPTIONAL, 'Number of users to be created')
            ->addOption('posts-number', 'p', InputOption::VALUE_OPTIONAL, 'Number of posts to be created for each user');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->info('Database population command started');

        $usersNumber = $input->getOption('users-number');
        $postsNumber = $input->getOption('posts-number');

        $io->progressStart($usersNumber);
        $users = [];
        for ($i=0; $i<$usersNumber; $i++) {
            $user = $this->createFakeUser($io);
            $users[] = $user;
            $io->progressAdvance();
        }
        $io->progressFinish();

        $io->success('All users created');

        $io->progressStart($usersNumber);
        $posts = [];
        foreach ($users as $user) {
            for ($i = 0; $i < $postsNumber; $i++) {
                $post = $this->createFakePost($user, $io);
                $posts[] = $post;
                $io->progressAdvance();
            }
        }
        $io->progressFinish();
        $io->success('All posts created');

        foreach ($posts as $post) {
            for ($i = 0; $i < 1; $i++) {
                $this->createFakeComment($post);
                $io->success('Comment to post ' . $post->getTitle() . ' created.');
            }
        }

        return Command::SUCCESS;
    }

    private function createFakeUser(SymfonyStyle $style): User
    {
        $io = $style;
        $user = User::createFrom(
            $this->faker->email,
            $this->faker->firstName,
            $this->faker->lastName,
            $this->faker->password,
            $author = null
        );
        try {
            $this->userRepository->save($user);
        } catch (\PDOException $exception) {
            $io->error($exception->getMessage());
        }
        return $user;
    }

    private function createFakePost(User $user, SymfonyStyle $style): Post
    {
        $io = $style;
        $post = new Post(
            $this->faker->sentence(6, true),
            $this->faker->realText
        );
        $author = $this->userRepository->getByEmail($user->getEmail());
        $post->setAuthorId($author->getId());
        try {
            $this->postRepository->save($post);
        } catch(\PDOException $exception) {
            $io->error($exception->getMessage());
        }
        return $post;
    }

    private function createFakeComment(Post $post): Comment
    {
        $comment = new Comment(
            $this->faker->sentence(11, true)
        );
        $commentedPost = $this->postRepository->getByTitle($post->getTitle());
        $comment->setPostId($commentedPost->getId());
        $this->commentRepository->save($comment);
        return $comment;
    }
}