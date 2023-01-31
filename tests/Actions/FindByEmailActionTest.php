<?php

namespace Test\Actions;

use App\DummyLogger;
use PDO;
use App\Blog\Http\Actions\FindByEmail;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\Request;
use App\Blog\Http\SuccessfulResponse;
use App\Connection\ConnectorInterface;
use App\Exceptions\UserNotFoundException;
use App\Repositories\UserRepositoryInterface;
use App\User\Entities\User;
use PHPUnit\Framework\TestCase;

class FindByEmailActionTest extends TestCase {

    // Запускаем тест в отдельном процессе
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
// Тест, проверяющий, что будет возвращён неудачный ответ, если в запросе нет параметра email
    public function testItReturnsErrorResponseIfNoEmailProvided(): void
    {
        // Создаём объект запроса, где вместо суперглобальных переменных передаём простые массивы
        $request = new Request([], [], '');

        // Создаём стаб репозитория пользователей
        $userRepository = new class implements UserRepositoryInterface
        {
            public function save(User $user): void
            {
            }
            public function get(int $id): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByEmail(string $email): User
            {
                throw new UserNotFoundException("Not found");
            }
        };

        $action = new FindByEmail($userRepository, new DummyLogger());

        $response = $action->handle($request);

        // Проверяем, что ответ - неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);

        $this->expectOutputString('{"success":false,"reason":"No such query param in the request: email"}');

        // Отправляем ответ в поток вывода
        $response->send();
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    // Тест, проверяющий, что будет возвращён неудачный ответ, если пользователь не найден
    public function testItReturnsErrorResponseIfUserNotFound(): void
    {
        // Теперь запрос будет иметь параметр email
        $request = new Request(['email' => 'some_mail@mail.com'], [], '');

        // стаб репозитория
        $userRepository = new class implements UserRepositoryInterface
        {
            public function save(User $user): void
            {
            }
            public function get(int $id): User
            {
                throw new UserNotFoundException("Not found");
            }
            public function getByEmail(string $email): User
            {
                throw new UserNotFoundException("Not found");
            }
        };

        $action = new FindByEmail($userRepository, new DummyLogger());
        $response = $action->handle($request);
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"Not found"}');
        $response->send();
    }

    // Тест, проверяющий, что будет возвращён удачный ответ, если пользователь найден
    // NB c подключением к БД
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    public function testItReturnsSuccessfulResponse(): void
    {
        $userRepository = new class implements UserRepositoryInterface {
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
                    throw new UserNotFoundException("User with email:$email not found");
                }

                return $user = new User($userObj->email, $userObj->first_name, $userObj->last_name, $userObj->password);
            }
        };

        $request = new Request(['email' => 'dashs@mail.ru'], [], '');

        $action = new FindByEmail($userRepository, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->expectOutputString('{"success":true,"data":{"first_name":"dasha","last_name":"romanova"}}');

        $response->send();
    }

}