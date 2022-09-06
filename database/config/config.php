<?php

function databaseConfig(): array
{
    return [
        'sqlite' =>
            [
                'DATABASE_URL' => "sqlite:" . __DIR__ . '/../dump/blog.sqlite'
            ],

        'mysql' => [
            'driver' => 'mysql',
            'user' => 'root',
            'password' => '******',
            'database'   =>  'test',
        ]
    ];
}