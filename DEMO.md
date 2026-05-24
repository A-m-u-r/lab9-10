# Demo launch

This file is for running the project on another Windows PC after cloning the repository.

## One command

```bat
start-demo.bat
```

The script will:

- find PHP 8.2+;
- download Composer if Composer is not installed;
- run `composer install`;
- create `.env` from `.env.demo`;
- create `database/database.sqlite`;
- generate `APP_KEY`;
- run fresh migrations and seed demo data;
- start the app at `http://127.0.0.1:8000/`.

Press `Ctrl+C` in the terminal to stop the server.

## Demo accounts

| Role | Email | Password |
| --- | --- | --- |
| master | `olga@example.com` | `Master1234` |
| master | `sergey@example.com` | `Master1234` |
| master | `maria@example.com` | `Master1234` |
| visitor | `visitor@example.com` | `Visitor1234` |

## Requirements

The PC must have PHP 8.2 or newer. Required PHP extensions:

- `fileinfo`
- `pdo_sqlite`
- `sqlite3`
- `mbstring`
- `dom`
- `openssl`

If the script cannot find PHP, install PHP and add it to `PATH`, or put `php.exe` in `C:\php\php.exe`.

## Useful options

Run setup without starting the server:

```bat
start-demo.bat -NoServe
```

Use another port:

```bat
start-demo.bat -Port 8080
```

Do not reset the SQLite database:

```bat
start-demo.bat -NoFresh
```
