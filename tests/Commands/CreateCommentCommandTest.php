<?php

namespace Test\Commands;

use App\Blog\Arguments\Argument;
use App\Blog\Article\Comment;
use App\Exceptions\ArgumentException;
use App\Exceptions\CommandException;
use App\Exceptions\CommentNotFoundException;
use App\Blog\Commands\CreateCommentCommand;
use App\Repositories\CommentRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateCommentCommandTest extends TestCase
{   
    public function testItRequiresText(): void
    {
        $command = new CreateCommentCommand(new class implements CommentRepositoryInterface {
            public function save(Comment $comment): void
            {
            }
            public function get(int $id): Comment
            {
                throw new CommentNotFoundException("Not found");
            }
        });
        $this->expectException(ArgumentException::class);
        $this->expectExceptionMessage('No such argument: text');
        $command->handle(new Argument([]));
    }

    // Тест, проверяющий, что команда сохраняет коммент в репозитории, вызывая метод save()
    public function testItSavesCommentToRepository(): void
    {
        $commentRepository = new class implements CommentRepositoryInterface  //мок-объект
        {
            private bool $called = false;
            public function save(Comment $comment): void
            {
                $this->called = true; // Если метод save был вызван, меняем на true
            }
            public function get(int $id): Comment
            {
                throw new CommentNotFoundException("Not found");
            }
            public function wasCalled(): bool
            {
                return $this->called;
            }
        };
        
        $command = new CreateCommentCommand($commentRepository);
        
        $command->handle(new Argument([
            'text' => 'Some text',
            ]));
        
        $this->assertTrue($commentRepository->wasCalled());
    }
}