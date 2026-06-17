#!/bin/sh
set -eu

cd /var/www/html

bootstrap_app_volume() {
    if [ -f artisan ]; then
        return 0
    fi

    if [ -d "${APP_SOURCE:-/opt/mod-health-app}" ] && [ -f "${APP_SOURCE}/artisan" ]; then
        echo "Initializing application from image bundle..."
        cp -a "${APP_SOURCE}/." /var/www/html/
        chown -R www-data:www-data /var/www/html
        return 0
    fi

    return 1
}

run_as_www_data() {
    if [ "$(id -u)" = "0" ]; then
        exec gosu www-data "$@"
    fi
    exec "$@"
}

read_app_key_from_dotenv() {
    if [ ! -f .env ]; then
        return 1
    fi

    key=$(grep -m1 '^APP_KEY=' .env | cut -d= -f2- | tr -d '\r' || true)

    if [ -n "$key" ] && [ "$key" != "base64:" ]; then
        export APP_KEY="$key"
        return 0
    fi

    return 1
}

ensure_app_key() {
    # Docker env_file may inject APP_KEY= (empty) and override Laravel's .env file.
    if read_app_key_from_dotenv; then
        return 0
    fi

    if [ -n "${APP_KEY:-}" ] && [ "${APP_KEY}" != "base64:" ]; then
        return 0
    fi

    unset APP_KEY

    if [ ! -f .env ] && [ -f .env.example ]; then
        cp .env.example .env
        chown www-data:www-data .env
    fi

    echo "Generating application encryption key..."
    gosu www-data php artisan key:generate --force --no-interaction

    if read_app_key_from_dotenv; then
        echo "APP_KEY generated. Copy this into the host .env used by docker compose:"
        echo "APP_KEY=${APP_KEY}"
        return 0
    fi

    echo "Failed to generate APP_KEY."
    exit 1
}

fix_storage_permissions() {
    mkdir -p \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache

    touch storage/logs/php-fpm.log storage/logs/laravel.log

    chown -R www-data:www-data storage bootstrap/cache
    chmod -R ug+rwx storage bootstrap/cache
}

wait_for_database() {
    if [ -z "${DB_HOST:-}" ]; then
        return 0
    fi

    echo "Waiting for database at ${DB_HOST}:${DB_PORT:-3306}..."

    attempt=0
    max_attempts="${DB_WAIT_RETRIES:-60}"

    until php -r "
        try {
            new PDO(
                sprintf('mysql:host=%s;port=%s;dbname=%s', getenv('DB_HOST'), getenv('DB_PORT') ?: '3306', getenv('DB_DATABASE')),
                getenv('DB_USERNAME'),
                getenv('DB_PASSWORD'),
                [PDO::ATTR_TIMEOUT => 3]
            );
            exit(0);
        } catch (Throwable \$e) {
            exit(1);
        }
    " 2>/dev/null; do
        attempt=$((attempt + 1))
        if [ "$attempt" -ge "$max_attempts" ]; then
            echo "Database is not reachable after ${max_attempts} attempts."
            exit 1
        fi
        sleep 2
    done

    echo "Database is ready."
}

clone_from_git() {
    if [ -z "${GIT_REPO_URL:-}" ]; then
        return 0
    fi

    if [ -f artisan ]; then
        echo "Application already present, skipping git clone."
        return 0
    fi

    echo "Cloning ${GIT_REPO_URL} (branch: ${GIT_BRANCH:-main})..."

    if [ -n "${GIT_SSH_KEY_PATH:-}" ] && [ -f "${GIT_SSH_KEY_PATH}" ]; then
        export GIT_SSH_COMMAND="ssh -i ${GIT_SSH_KEY_PATH} -o IdentitiesOnly=yes -o StrictHostKeyChecking=accept-new"
    fi

    git clone --depth 1 --branch "${GIT_BRANCH:-main}" "${GIT_REPO_URL}" /tmp/app-src
    cp -a /tmp/app-src/. /var/www/html/
    rm -rf /tmp/app-src
    chown -R www-data:www-data /var/www/html
}

install_dependencies() {
    if [ ! -d vendor ] && [ "${RUN_COMPOSER_INSTALL:-false}" = "true" ]; then
        echo "Installing Composer dependencies..."
        gosu www-data composer install \
            --no-dev \
            --no-interaction \
            --prefer-dist \
            --optimize-autoloader \
            --no-progress
    fi

    if [ ! -d public/build ] && [ "${RUN_NPM_BUILD:-false}" = "true" ]; then
        echo "Building frontend assets..."
        npm ci --no-audit --no-fund --legacy-peer-deps
        npm run build
        chown -R www-data:www-data public/build
    fi
}

prepare_laravel() {
    bootstrap_app_volume || true
    clone_from_git

    if [ ! -f artisan ]; then
        echo "Laravel application not found in /var/www/html"
        exit 1
    fi

    if [ ! -f .env ] && [ -f .env.example ]; then
        cp .env.example .env
        chown www-data:www-data .env
    fi

    ensure_app_key
    fix_storage_permissions

    gosu www-data php artisan storage:link --force --no-interaction 2>/dev/null || true

    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        gosu www-data php artisan migrate --force --no-interaction
    fi

    if [ "${APP_ENV:-production}" = "production" ]; then
        gosu www-data php artisan config:clear --no-interaction
        gosu www-data php artisan config:cache --no-interaction
        gosu www-data php artisan route:cache --no-interaction || echo "Warning: route cache skipped."
        gosu www-data php artisan view:cache --no-interaction
        gosu www-data php artisan event:cache --no-interaction 2>/dev/null || true
    fi

    fix_storage_permissions
}

install_dependencies
wait_for_database
prepare_laravel

if [ "$1" = "php-fpm" ]; then
    # PHP-FPM master must run as root; workers drop to www-data per pool config.
    read_app_key_from_dotenv || true
    exec php-fpm
fi

if [ "$1" = "queue" ]; then
    read_app_key_from_dotenv || true
    run_as_www_data php artisan queue:work --queue=messages,default --sleep=3 --tries=3 --max-time=3600
fi

if [ "$1" = "scheduler" ]; then
    read_app_key_from_dotenv || true
    run_as_www_data php artisan schedule:work
fi

read_app_key_from_dotenv || true
run_as_www_data "$@"
