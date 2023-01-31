Блог - сервер на чистом PHP (Blog Server on core/native PHP)

Краткое описание:
Проект представляет собой бэкенд (API) для стандартного коллективного блога, в котором пользователи могут писать/
читать статьи и комментарии, а также ставить им лайки. Не включает в себя фронтенд.
При разработке проекта не использовался какой-либо фреймворк (Laravel, Symfony), все паттерны реализованы "вручную".

Полное описание:
1.
cli.php – дял запуска консольных команд
(index.php)
Запуск: public> php -S 127.0.0.1:8080 index.php


2. Основные модели:
    - пользователи: /User/Entities/User/php
    - статьи: /Blog/Article/Post.php
    - комментарии: /Blog/Article/Comment.php
    - лайки: /Blog/Article/PostLike.php
             /Blog/Article/CommentLike.php

3. Паттерн «Репозиторий»
   В приложении созданы персистентные репозитории (/Repositories) с использованием базы данных SQLite.

4. Роутинг:
   Основные API-руты (см. index.php):
    GET:
        /users/show?email=[userEmail] - поиск пользователя по email
        /posts/show?id=[postID] - поиск поста по id
    POST:
        /login
        /logout
        /users/create - создать нового пользователя
        /posts/create - создать новый пост
        /posts/comment - добавить комментарий к посту
        /posts/like - добавить лайк к посту
        /comments/like - добавить лайк к комментарию
    DELETE:
        /posts/delete?id=[postID] - удалить пост

Дополнительные комментарии о необходимом формате запроса при разных типах авторизации содержатся в виде комментариев в файлах соответствующих действий (/src/Blog/Http/Actions).

5. Паттерн DIContainer: в проект добавлен контейнер внедрения зависимостей (src/Container, /public/autoload_runtime.php)

6. Логирование: подключен логгер из библиотеки Monolog.

7. Аутентификация пользователя:
Возможно подключение одного из следующих видов аутентификации пользователя (/src/Authentication):
   - по email;
   - по userId;
   - по паролю;
   - по токену (Bearer Token).
Также добавлено хэширование паролей при помощи алгоритма SHA-256.

7. PHPUnit тесты
К проекту подключены юнит-тесты, в том числен с подключением к БД и/или с использованием стабов (stubs).
