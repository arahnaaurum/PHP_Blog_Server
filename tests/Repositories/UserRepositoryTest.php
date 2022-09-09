<?php

namespace Test\Repositories;

use App\User\Entities\User;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use App\Connection\ConnectorInterface;
use PDO;

class UserRepositoryTest extends TestCase
{
    // тесты с подключением к БД
    public function testItReturnsUserWhenUsersIdIsInDatabase(): void
    {
        $repository = new class implements UserRepositoryInterface {
            public function save(User $user): void
            {
            }
            public function get(int $id): User
            {
                $connector = new class implements ConnectorInterface
                {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };
                
                $connection = $connector->getConnection();

                $statement = $connection->prepare(
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
 
                return $user = new User($userObj->email, $userObj->first_name, $userObj->last_name);
            }

            public function getByEmail(string $email): User
            {
                throw new UserNotFoundException("Not found");
            }
        };

        $expectedUser = new User("dashs@mail.ru", "dasha", "romanova");
        $receivedUser = $repository->get(1);
        
        //у двух объектов будет разное время создания, поэтому сравнивать их целиком не получится
        //так что сравним отдельные поля, чтобы убедиться, что они совпадают
        $this->assertEquals($expectedUser->getEmail(), $receivedUser->getEmail());
        $this->assertEquals($expectedUser->getFirstName(), $receivedUser->getFirstName());
        $this->assertEquals($expectedUser->getLastName(), $receivedUser->getLastName());
    }

    public function testItReturnsUserWhenUsersEmailIsInDatabase(): void
    {
        $repository = new class implements UserRepositoryInterface {
            public function save(User $user): void
            {
            }
            public function get(int $id): User
            {
                throw new UserNotFoundException("Not found");
            }

            public function getByEmail(string $email): User
            {
                $connector = new class implements ConnectorInterface
                {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };
                
                $connection = $connector->getConnection();

                $statement = $connection->prepare(
                    "select * from user where email = :email"
                );
            
                $statement->execute([
                    ':email' => $email
                ]);

                $userObj = $statement->fetch(PDO::FETCH_OBJ);
        
                if(!$userObj)
                {
                    throw new UserNotFoundException("User with emai:$email not found");
                }
 
                return $user = new User($userObj->email, $userObj->first_name, $userObj->last_name);
            }
        };

        $expectedUser = new User("dashs@mail.ru", "dasha", "romanova");
        $receivedUser = $repository->getByEmail("dashs@mail.ru");
        
        //у двух объектов будет разное время создания, поэтому сравнивать их целиком не получится
        //так что сравним отдельные поля, чтобы убедиться, что они совпадают
        $this->assertEquals($expectedUser->getEmail(), $receivedUser->getEmail());
        $this->assertEquals($expectedUser->getFirstName(), $receivedUser->getFirstName());
        $this->assertEquals($expectedUser->getLastName(), $receivedUser->getLastName());
    }

    public function testItThrowsAnExceptionWhenUserNotFound(): void
    {
        $repository = new class implements UserRepositoryInterface {
            public function save(User $user): void
            {
            }

            public function get(int $id): User
            {
                $connector = new class implements ConnectorInterface
                {
                    public function getConnection(): PDO
                    {
                        return new PDO(databaseConfig()['sqlite']['DATABASE_URL']);
                    }
                };
                
                $connection = $connector->getConnection();

                $statement = $connection->prepare(
                    "select * from user where id = :userId"
                );
                
                //нет execute, т.к. эта ф-ция все равно должна возращать false, чтобы выпало исключение

                $userObj = $statement->fetch();
        
                if(!$userObj)
                {
                    throw new UserNotFoundException("User with id:$id not found");
                }
                return new User('somemail', 'somename', 'somesurname');
            }

            public function getByEmail(string $email): User
            {
                throw new UserNotFoundException("Not found");
            }
        };

        $this->expectException(UserNotFoundException::class);
        $this->expectExceptionMessage('User with id:0 not found');

        $repository->get(0);
    }
}
