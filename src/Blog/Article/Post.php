<?php

namespace App\Blog\Article;

use App\Date\DateTime;
use App\Traits\Created;
use App\Traits\Deleted;
use App\Traits\Updated;
use App\Traits\AuthorId;
use App\Traits\Id;

class Post
{
    use Id;
    use AuthorId;
    use Created;
    use Updated;
    use Deleted;
    
    public function __construct(
        private string $title,
        private string $text
    ) {
        $this->createdAt = new DateTime();
    }

    public function getTitle(): string
    {
        return $this->title;
    }
    
    public function getText (): string
    {
        return $this->text;
    }

    public function __toString()
    {
        return $this->author . ' пишет: ' . $this->text;
    }
}