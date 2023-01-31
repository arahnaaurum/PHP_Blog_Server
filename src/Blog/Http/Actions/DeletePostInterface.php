<?php

namespace App\Blog\Http\Actions;

use App\Blog\Http\Request;
use App\Blog\Http\Response;

interface DeletePostInterface extends ActionInterface
{
    public function handle(Request $request): Response;
}