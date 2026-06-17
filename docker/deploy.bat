@echo off
setlocal

if not exist .env (
    copy .env.docker.example .env
    echo Created .env from .env.docker.example — update passwords before production.
)

powershell -NoProfile -Command "(Get-Content .env) | Where-Object { $_ -notmatch '^APP_KEY=$' } | Set-Content .env"

docker compose build
docker compose up -d
echo.
echo App: http://localhost:%HTTP_PORT%
echo MySQL: localhost:%MYSQL_PORT%
echo.
echo If first deploy, copy APP_KEY from: docker compose logs app ^| findstr APP_KEY
