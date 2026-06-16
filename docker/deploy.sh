#!/usr/bin/env bash
set -euo pipefail

if [[ ! -f .env ]]; then
  cp .env.docker.example .env
  echo "Created .env from .env.docker.example — update secrets before production."
fi

docker compose build
docker compose up -d

echo ""
echo "App:  http://localhost:${HTTP_PORT:-80}"
echo "MySQL: localhost:${MYSQL_PORT:-3306}"
