<?php

namespace App\Repositories;

use App\Connection\ConnectorInterface;
use App\Date\DateTime;
use App\Entities\AuthToken;
use App\Exceptions\AuthTokenNotFoundException;
use App\Exceptions\AuthTokenRepositoryException;
use DateTimeImmutable;
use DateTimeInterface;
use PDO;
use PDOException;

class AuthTokenRepository implements AuthTokenRepositoryInterface
{
    private PDO $connection;
    public function __construct(
        private ConnectorInterface $connector,
        private UserRepositoryInterface $userRepository)
    {
        $this->connection = $this->connector->getConnection();
    }
    public function save(AuthToken $authToken): void
    {
        $query = "insert into auth_token (token, user_id, expires_on)
                values (:token, :user_id, :expires_on)
                ON CONFLICT (token) DO UPDATE SET expires_on = :expires_on";

        try {
            $statement = $this->connection->prepare($query);

            $statement->execute(
                [
                    ':token' => $authToken->getToken(),
                    ':user_id' => $authToken->getUser()->getId(),
                    ':expires_on' => $authToken->getExpiresOn()->format(DateTimeInterface::ATOM)
                ]
            );
        } catch (PDOException $exception)
        {
            throw new AuthTokenRepositoryException($exception->getMessage());
        }
    }

    public function getToken(string $token): AuthToken
    {
        $statement = $this->connection->prepare(
            "select * from auth_token where token = :token"
        );

        $statement->execute([
            ':token' => $token
        ]);

        $authTokenObject = $statement->fetch(PDO::FETCH_OBJ);

        if(!$authTokenObject)
        {
            throw new AuthTokenNotFoundException("Auth token with token : $token not found");
        }

        return $this->mapAuthToken($authTokenObject);
    }

    public function changeTokenExpirationDate(string $token): void
    {
        $date = new DateTime();

        $statement = $this->connection->prepare(
            "update auth_token set expires_on = :expires_on where token = :token"
        );

        $statement->execute([
            ':expires_on' => $date,
            ':token' => $token
        ]);
    }

    private function mapAuthToken(object $authTokenObject): AuthToken
    {
        return new AuthToken(
            $authTokenObject->token,
            $this->userRepository->get($authTokenObject->user_id),
            new DateTime($authTokenObject->expires_on)
        );
    }
}