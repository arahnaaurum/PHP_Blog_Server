<?php

namespace App\Connection;

use PDO;

class SqLiteConnector implements ConnectorInterface
{
    public function getConnection(): PDO
    {
        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
    }
}