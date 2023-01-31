<?php

namespace Test\Commands;

use App\Blog\Arguments\Argument;
use App\Blog\Article\Post;
use App\Exceptions\CommandException;
use App\Exceptions\PostNotFoundException;
use App\Exceptions\ArgumentException;
use App\Blog\Commands\CreatePostCommand;
use App\Repositories\PostRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreatePostCommandTest extends TestCase
{
    public function testItThrowsAnExceptionWhenPostAlreadyExists(): void
    {
        $postRepository = new class implements PostRepositoryInterface
        {
            public function save(Post $post): void
            {
            }
            public function get(int $id): Post
            {
                throw new PostNotFoundException("Not found");
            }
            public function getByTitle(string $title): Post
            {
                return new Post("Lorem iprsum", "lorem ipsum dolor");    
            }          
        };
        
        $command = new CreatePostCommand($postRepository);
        
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage('Post with such title already exists: Testtitle');
        $command->handle(new Argument(['title' => 'Testtitle']));
    }  

    private function makePostRepository(): PostRepositoryInterface
    {
        return new class implements PostRepositoryInterface {
            public function save(Post $post): void
            {
            }
            public function get(int $id): Post
            {
                throw new PostNotFoundException("Not found");
            }
            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");    
            }          
        };
    }

    public function testItRequiresTitle(): void
    {
        $command = new CreatePostCommand($this->makePostRepository());
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('No such argument: title');
        $command->handle(new Argument([
            'text' => 'some text'
        ]));
    }

    public function testItRequiresText(): void
    {
        $command = new CreatePostCommand($this->makePostRepository());
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('No such argument: text');
        $command->handle(new Argument([
            'title' => 'some title'
        ]));
    }

    // Тест, проверяющий, что команда сохраняет пост в репозитории, вызывая метод save()
    public function testItSavesPostToRepository(): void
    {
        $postRepository = new class implements PostRepositoryInterface  //мок-объект
        {
            private bool $called = false;
            public function save(Post $post): void
            {
                $this->called = true; // Если метод save был вызван, меняем на true
            }
            public function get(int $id): Post
            {
                throw new PostNotFoundException("Not found");
            }
            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");    
            }          
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
        
        $command = new CreatePostCommand($postRepository);
        
        $command->handle(new Argument([
            'title' => 'Some title',
            'text' => 'Some text',
            ]));
        
        $this->assertTrue($postRepository->wasCalled());
    }
}