<?php
namespace App\Blog\Http\Actions;

use App\Blog\Http\Request;
use App\Blog\Http\Response;

interface LoginActionInterface
{
    public function handle(Request $request): Response;
}