<?php

namespace App\Repositories;

use App\Connection\ConnectorInterface;
use App\Connection\SqLiteConnector;
use App\Date\DateTime;
use App\Exceptions\UserNotFoundException;
use App\User\Entities\User;
use PDO;
use Psr\Log\LoggerInterface;

class UserRepository implements UserRepositoryInterface
{
    private PDO $connection;

    public function __construct
    (
        private LoggerInterface $logger,
        private ?ConnectorInterface $connector = null
    )
    {
        $this->connector = $connector ?? new SqLiteConnector();
        $this->connection = $this->connector->getConnection();
    }

    public function save(User $user): void
    {
        $statement = $this->connection->prepare(
            '
                    insert into user (email, active, first_name, last_name, password, author_id, created_at)
                    values (:email, :active, :first_name, :last_name, :password, :author_id, :created_at)
                  '
        );

        $statement->execute(
            [
                ':email' => $user->getEmail(),
                ':active' => $user->isActive(),
                ':first_name' => $user->getFirstName(),
                ':last_name' => $user->getLastName(),
                ':password' => $user->getPassword(),
                ':author_id' => $user->getAuthor()?->getId(),
                ':created_at' => $user->getCreatedAt()
            ]
        );
        $this->logger->info("User with email" . $user->getEmail() . " added to database");
    }

    public function update($id, $firstname, $lastname): void
    {
        $statement = $this->connection->prepare(
            '
                    update user
                    set
                    first_name = :first_name,
                    last_name = :last_name
                    where id = :id
                  '
        );

        $statement->execute(
            [
                ':first_name' => $firstname,
                ':last_name' => $lastname,
                ':id' => $id
            ]
        );
        $this->logger->info("User with id $id updated");
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
            $this->logger->warning("User with id:$id not found");
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
            $this->logger->warning("User with email:$email not found");
            throw new UserNotFoundException("User with email:$email not found");
        }

        return $this->mapUser($userObj);
    }

    private function mapUser(object $userObj) : User
    {
        $author = $userObj->author_id ?  $this->get($userObj->author_id) : null;
        $user = new User(
            $userObj->email,
            $userObj->first_name,
            $userObj->last_name,
            $userObj->password,
            $author);

        $user
            ->setId($userObj->id)
            ->setActive($userObj->active)
            ->setCreatedAt(new DateTime($userObj->created_at))
            ->setUpdatedAt(($updatedAt = $userObj->updated_at) ? new DateTime($updatedAt) : null)
            ->setDeletedAt(($deletedAt = $userObj->deleted_at) ? new DateTime($deletedAt) : null);

        return $user;
    }
}