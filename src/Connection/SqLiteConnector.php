<?php

namespace App\Connection;

use PDO;

class SqLiteConnector implements ConnectorInterface
{
    public function __construct(private PDO $PDO)
    {
    }
    public function getConnection(): PDO
    {
        return $this->PDO;
    }
}