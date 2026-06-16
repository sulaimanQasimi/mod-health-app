@echo off
setlocal

if not exist .env (
    copy .env.docker.example .env
    echo Created .env from .env.docker.example — update passwords and APP_KEY before production.
)

docker compose build
docker compose up -d
echo.
echo App: http://localhost:%HTTP_PORT%
echo MySQL: localhost:%MYSQL_PORT%
