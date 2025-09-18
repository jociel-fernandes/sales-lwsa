#!/usr/bin/env bash

set -e

LARAVEL_VERSION="12.0"
BACK_DIR="./back-end"
FRONT_DIR="./front-end"

if [ ! -d "$BACK_DIR" ]; then
    mkdir -p "$BACK_DIR" && \
    docker compose run --rm backend sh -c "\
        composer create-project laravel/laravel:^${LARAVEL_VERSION} /var/www/html --prefer-dist --no-install --remove-vcs --dev --no-scripts \
    "
fi

if [ ! -d "$FRONT_DIR" ]; then
    mkdir -p "$FRONT_DIR" && \
    cd "$FRONT_DIR" && \
    npm init vue@latest . -- \
    --typescript \
    --router \
    --pinia \
    --vitest \
    --cypress \
    --eslint \
    --prettier
fi

echo ">>>>> Projeto criado com sucesso!"
