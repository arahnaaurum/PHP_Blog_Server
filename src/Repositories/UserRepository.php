<?php

namespace App\Repositories;

use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Date\DateTime;
use App\Exceptions\UserNotFoundException;
use App\User\Entities\User;
use PDO;

class UserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct(private ?ConnectorInterface $connector = null)
    {
        $this->connector = $connector ?? new SqLiteConnector();
        $this->connection = $this->connector->getConnection();
    }


    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            '
                    insert into user (email, active, first_name, last_name, created_at)
                    values (:email, :active, :first_name, :last_name, :created_at)
                  '
        );

        $statement->execute(
            [
                ':email' => $user->getEmail(),
                ':active' => $user->isActive(),
                ':first_name' => $user->getFirstName(),
                ':last_name' => $user->getLastName(),
                ':created_at' => $user->getCreatedAt()
            ]
        );
    }

    /**
     * @throws UserNotFoundException
     * @throws \Exception
     */
    public function get(int $id): User
    {
        $statement = $this->connection->prepare(
            "select * from user where id = :userId"
        );

        $statement->execute([
            ':userId' => $id
        ]);

        $userObj = $statement->fetch(PDO::FETCH_OBJ);

        if(!$userObj)
        {
            throw new UserNotFoundException("User with id:$id not found");
        }

        return $this->mapUser($userObj);
    }

    public function getByEmail (string $email): User
    {
        $statement = $this->connection->prepare(
            "select * from user where email = :email"
        );

        $statement->execute([
            ':email' => $email
        ]);

        $userObj = $statement->fetch(PDO::FETCH_OBJ);

        if(!$userObj)
        {
            throw new UserNotFoundException("User with email:$email not found");
        }

        return $this->mapUser($userObj);
    }

    private function mapUser(object $userObj) : User
    {
        $user = new User($userObj->email, $userObj->first_name, $userObj->last_name);

        $user
            ->setId($userObj->id)
            ->setActive($userObj->active)
            ->setCreatedAt(new DateTime($userObj->created_at))
            ->setUpdatedAt(($updatedAt = $userObj->updated_at) ? new DateTime($updatedAt) : null)
            ->setDeletedAt(($deletedAt = $userObj->deleted_at) ? new DateTime($deletedAt) : null);

        return $user;
    }
}