<?php

namespace App\Blog\Http\Actions;

use App\Blog\Http\Request;
use App\Blog\Http\Response;

interface AddLikeToCommentInterface extends ActionInterface
{
    public function handle(Request $request): Response;
}