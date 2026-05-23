# Лабораторная работа 9: мастер-классы на Laravel

Сервис записи на мастер-классы клуба «ОчУмелые ручки».

## Что реализовано

- регистрация и вход пользователей с ролями `visitor` и `master`;
- личный кабинет ведущего;
- создание мастер-классов ведущим;
- запрет пересечения слотов ведущего на одну дату;
- редактирование только описания и стоимости занятия;
- просмотр участников мастер-класса;
- запись посетителя на мастер-класс с подтверждением;
- запрет повторной записи и записи ведущего на собственный мастер-класс;
- проверка вместимости группы в транзакции;
- CSRF-защита Laravel и серверная валидация форм;
- AJAX API занятых слотов для формы создания мастер-класса;
- сиды с категориями, ведущими и демо-расписанием.

## Демо-аккаунты

| Роль | Email | Пароль |
| --- | --- | --- |
| ведущий | `olga@example.com` | `Master1234` |
| ведущий | `sergey@example.com` | `Master1234` |
| ведущий | `maria@example.com` | `Master1234` |
| посетитель | `visitor@example.com` | `Visitor1234` |

## Запуск

```bash
php C:\php\composer.phar install
php -d extension=fileinfo -d extension=pdo_sqlite -d extension=sqlite3 artisan key:generate
php -d extension=fileinfo -d extension=pdo_sqlite -d extension=sqlite3 artisan migrate:fresh --seed
```

Если SQLite-расширения включены в `php.ini`, можно запускать короче: `php artisan ...`.

Для локального сервера в текущем окружении:

```bash
cd public
php -d extension=fileinfo -d extension=pdo_sqlite -d extension=sqlite3 -S 127.0.0.1:8000 -t . ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
```

Откройте `http://127.0.0.1:8000/`.

## Проверка

```bash
php -d extension=fileinfo -d extension=pdo_sqlite -d extension=sqlite3 vendor\phpunit\phpunit\phpunit
```

В текущем окружении `artisan test` запускает PHPUnit дочерним PHP-процессом и теряет временно подключённый `pdo_sqlite`, поэтому для проверки используется прямой запуск PHPUnit через `php -d`.
