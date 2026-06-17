#!/usr/bin/env bash
set -euo pipefail

if [[ ! -f .env ]]; then
  cp .env.docker.example .env
  echo "Created .env from .env.docker.example — update secrets before production."
fi

# Empty APP_KEY= overrides Laravel's generated key when passed through docker compose.
sed -i '/^APP_KEY=$/d' .env 2>/dev/null || sed -i '' '/^APP_KEY=$/d' .env

docker compose build
docker compose up -d

echo ""
echo "App:  http://localhost:${HTTP_PORT:-80}"
echo "MySQL: localhost:${MYSQL_PORT:-3306}"
echo ""
echo "If this is the first deploy, copy APP_KEY from container logs into .env:"
echo "  docker compose logs app | grep APP_KEY"
