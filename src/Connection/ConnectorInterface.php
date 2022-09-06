<?php

namespace App\Connection;

use PDO;

interface ConnectorInterface
{
    public static function getConnection(): PDO;
}