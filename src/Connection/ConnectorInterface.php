<?php

namespace App\Connection;

use PDO;

interface ConnectorInterface
{
    public function getConnection(): PDO;
}