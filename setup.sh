#!/usr/bin/env bash
set -euo pipefail

PROJECT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

if [[ ! -f "$PROJECT_DIR/.env.local" ]]; then
    POSTGRES_VERSION="16"
    POSTGRES_PORT="5432"
    POSTGRES_DB="app"
    POSTGRES_USER="app"
    POSTGRES_PASSWORD="!ChangeMe!"
    APP_SECRET="$(openssl rand -hex 32)"

    cat > "$PROJECT_DIR/.env.local" <<EOF
POSTGRES_VERSION=${POSTGRES_VERSION}
POSTGRES_PORT=${POSTGRES_PORT}
POSTGRES_DB=${POSTGRES_DB}
POSTGRES_USER=${POSTGRES_USER}
POSTGRES_PASSWORD=${POSTGRES_PASSWORD}

APP_SECRET=${APP_SECRET}
DATABASE_URL="postgresql://${POSTGRES_USER}:${POSTGRES_PASSWORD}@127.0.0.1:${POSTGRES_PORT}/${POSTGRES_DB}?serverVersion=${POSTGRES_VERSION}&charset=utf8"
EOF
    echo "Created $PROJECT_DIR/.env.local"
else
    echo ".env.local already exists. Leaving it unchanged."
fi


composer install --no-interaction

if [[ ! -f "$PROJECT_DIR/config/jwt/private.pem" || ! -f "$PROJECT_DIR/config/jwt/public.pem" ]]; then
    mkdir -p "$PROJECT_DIR/config/jwt"
    cd "$PROJECT_DIR"
    php bin/console lexik:jwt:generate-keypair
    echo "Generated JWT keys in $PROJECT_DIR/config/jwt"
else
    echo "JWT keys already exist. Leaving them unchanged."
fi

docker compose --env-file "$PROJECT_DIR/.env.local" up -d --wait

php bin/console doctrine:migrations:migrate --no-interaction

php bin/console doctrine:fixtures:load --no-interaction

symfony serve -d