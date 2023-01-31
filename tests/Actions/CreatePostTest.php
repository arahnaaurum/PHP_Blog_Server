<?php

namespace Test\Actions;

use App\Blog\Article\Post;
use App\Blog\Http\Actions\CreatePost;
use PDO;
use App\Blog\Http\ErrorResponse;
use App\Blog\Http\Request;
use App\Blog\Http\SuccessfulResponse;
use App\Connection\ConnectorInterface;
use App\Exceptions\PostNotFoundException;
use App\Repositories\PostRepositoryInterface;
use App\User\Entities\User;
use PHPUnit\Framework\TestCase;
use App\DummyLogger;
use App\Authentification\AuthentificationInterface;

class CreatePostTest extends TestCase {

    // Запускаем тест в отдельном процессе
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
// Тест, проверяющий, что будет возвращён неудачный ответ, если в запросе нет необходимых параметров
    public function testItReturnsErrorResponseIfNoArgumentsProvided(): void
    {
        // Создаём "пустой" запрос
        $request = new Request([], [], '{}');

        // Создаём стаб репозитория постов
        $postRepository = new class implements PostRepositoryInterface
        {
            public function save(Post $post): void
            {
            }
            public function delete(int $id): void
            {
            }
            public function get(int $id): Post
            {
                throw new PostNotFoundException('Not found');
            }
            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException('Not found');
            }
        };

        // Создаём стаб класса, отвечающего за индентификацию юзеров
        $userIdentification = new class implements AuthentificationInterface
        {
            public function user(Request $request): User
            {
                return new User ('ex@mail.com', 'Sasha', 'Petrov', '123');
            }
        };

        $action = new CreatePost($postRepository, $userIdentification, new DummyLogger());

        $response = $action->handle($request);

        // Проверяем, что ответ - неудачный
        $this->assertInstanceOf(ErrorResponse::class, $response);
        $this->expectOutputString('{"success":false,"reason":"No such field: title"}');

        // Отправляем ответ в поток вывода
        $response->send();
    }

    // Тест, проверяющий, что будет возвращён удачный ответ, если пост создан (вызван метод save)
    // NB c подключением к БД
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */

    public function testItReturnsSuccessfulResponse(): void
    {
           // Создаём стаб класса, отвечающего за индентификацию юзеров
        $userIdentification = new class implements AuthentificationInterface
        {
            public function user(Request $request): User
            {
                $testUser = new User('somemail', 'somename', 'somesurname', '123');
                $testUser->setId(100);
                return $testUser;
            }
        };

        $postRepository = new class implements PostRepositoryInterface {
            public function save(Post $post): void
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
                    '
                    insert into post (author_id, title, text, created_at)
                    values (:author_id, :title, :text, :created_at)
                    '
                );

                $statement->execute(
                    [
                        ':title' => $post->getTitle(),
                        ':text' => $post->getText(),
                        ':created_at' => $post->getCreatedAt(),
                        ':author_id' => $post->getAuthorId()
                    ]
                );
            }
            public function delete(int $id): void
            {
            }
            public function get(int $id): Post
            {
                throw new PostNotFoundException("Not found");
            }
            public function getByTitle(string $title): Post
            {
                throw new PostNotFoundException("Not found");
            }
        };

        $request = new Request([], [], '{"author_id": "1000", "text": "test text", "title": "test title"}');

        $action = new CreatePost($postRepository, $userIdentification, new DummyLogger());

        $response = $action->handle($request);

        $this->assertInstanceOf(SuccessfulResponse::class, $response);

        $this->expectOutputString('{"success":true,"data":{"title":"test title"}}');

        $response->send();
    }

}