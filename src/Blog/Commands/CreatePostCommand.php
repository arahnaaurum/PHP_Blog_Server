<?php

namespace App\Blog\Commands;

use App\Blog\Article\Post;
use App\Repositories\PostRepository;
use App\Repositories\PostRepositoryInterface;
use App\Blog\Arguments\Argument;
use App\Exceptions\CommandException;
use App\Exceptions\PostNotFoundException;

final class CreatePostCommand
{
    public function __construct(
        private PostRepositoryInterface $postRepository)
    {
    }

    public function handle(Argument $arguments) : void
    {
        $title = $arguments->get('title');
        if ($this->postExists($title))
        {
            throw new CommandException("Post with such title already exists: $title");
        }
        $this->postRepository->save(new Post
            (
                $arguments->get('title'),
                $arguments->get('text')
            )
        ); //передали обязательные значения полей для поста
    }
    
    // предположим, что название поста уникально
    private function postExists (string $title) : bool
    {
        try
        {
            $this->postRepository->getByTitle($title);//по названию
        }
        catch (PostNotFoundException) {
            return false;
        }
        return true;
    }
}