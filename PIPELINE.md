# CI/CD pipeline

Пайплайн находится в `.github/workflows/ci.yml` и рассчитан на GitHub Actions.

## Ветки

Проверки запускаются на `push` в любые ветки и на `pull_request` в долгоживущие ветки:

- `dev`, `develop`, `development`;
- `qa`, `uat`;
- `main`, `master`.

Симуляция деплоя запускается только на `push` в долгоживущие ветки после успешных тестов, статического анализа и lint.

## Среды

- `dev`, `develop`, `development` используют `.env.dev`;
- `qa`, `uat` используют `.env.uat`;
- `main`, `master` используют `.env.prod`;
- CI-проверки используют `.env.ci`.

Основной `.env` добавлен в `.gitignore` и не должен попадать в репозиторий. Значения `APP_KEY` в `.env.dev`, `.env.uat`, `.env.prod` оставлены пустыми намеренно: в реальном окружении ключ задаётся секретом или генерируется при деплое.

## Jobs

### Tests and coverage gate

1. Устанавливает PHP 8.4 и расширения `pdo_sqlite`, `sqlite3`, `mbstring`, `dom`, `fileinfo`.
2. Устанавливает Composer-зависимости.
3. Копирует `.env.ci` в `.env`.
4. Генерирует `APP_KEY`.
5. Запускает PHPUnit с Clover-отчётом:

```bash
vendor/bin/phpunit --coverage-clover build/logs/clover.xml
```

6. Проверяет минимальное покрытие 50%:

```bash
php scripts/check-coverage.php build/logs/clover.xml 50
```

Если любой тест падает или покрытие ниже 50%, job завершается ошибкой.

### Static analysis

Запускает Larastan/PHPStan:

```bash
vendor/bin/phpstan analyse --error-format=github --memory-limit=1G
```

Любая ошибка PHPStan завершает job ошибкой.

### Lint

Для pull request и долгоживущих веток Pint запускается в test mode:

```bash
vendor/bin/pint --test app database routes tests
```

Для push в feature-ветки Pint запускается в режиме автоформатирования и коммитит исправления обратно в ветку, если они появились.

### Deploy simulation

После успешных `tests`, `static-analysis` и `lint` выбирает env-файл по ветке, копирует его как `.env` и выводит:

```text
Deploying to [ENV] with .env.[env]
```

Для `main` и `master` job привязан к GitHub Environment `production`. Чтобы получить ручной approval, в настройках GitHub нужно открыть `Settings -> Environments -> production` и включить `Required reviewers`.

### Notify maintainers

Опциональный шаг уведомления отправляет результат пайплайна в webhook, если в secrets задан `MAINTAINER_WEBHOOK_URL`. Если секрет не задан, job выводит сообщение, что уведомление пропущено.

## Локальная проверка

```bash
php -d extension=fileinfo -d extension=pdo_sqlite -d extension=sqlite3 vendor\phpunit\phpunit\phpunit
php -d extension=fileinfo -d extension=pdo_sqlite -d extension=sqlite3 vendor\bin\phpstan analyse --memory-limit=1G
php -d extension=fileinfo vendor\bin\pint --test app database routes tests
```

Для проверки покрытия локально нужен Xdebug или PCOV:

```bash
php -d extension=xdebug -d xdebug.mode=coverage vendor\phpunit\phpunit\phpunit --coverage-clover build\logs\clover.xml
php scripts\check-coverage.php build\logs\clover.xml 50
```

## Что приложить к сдаче

1. Ссылку на GitHub/GitLab репозиторий.
2. `.github/workflows/ci.yml`.
3. `.env.dev`, `.env.uat`, `.env.prod`, `.env.ci`.
4. Этот файл `PIPELINE.md`.
5. Скриншоты из GitHub Actions:
   - успешный пайплайн;
   - пайплайн с падением тестов;
   - пайплайн с падением lint.
