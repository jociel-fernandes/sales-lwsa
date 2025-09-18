#!/bin/sh
set -e

# para caso em que a pasta git nao esta dentro do volume montado ./src
# chown -R www-data:www-data /var/www/html

# para caso em que a pasta git esta dentro do volume montado ./src, assim a ignorando
find /var/www/html -not -path "/var/www/html/.git*" -exec chown www-data:www-data {} +
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Laravel
cd /var/www/html

apply_var_env() {
  local key="$1"
  local value="$2"

  if grep -qE "^[# ]*${key}=" .env; then
    sed -i "s|^[# ]*${key}=.*|${key}=${value}|" .env
  else
    echo "${key}=${value}" >> .env
  fi
}

set_env(){

    apply_var_env "APP_NAME" "${APP_NAME:-StartProject}"
    apply_var_env "APP_ENV" "${APP_ENV:-development}"
#    apply_var_env "APP_KEY" "${APP_KEY}"
    apply_var_env "APP_PREVIOUS_KEY" "${APP_PREVIOUS_KEY}"
    apply_var_env "APP_DEBUG" "${APP_DEBUG:-true}"
    apply_var_env "APP_URL" "${APP_URL:-http://localhost:80}"
    apply_var_env "FRONTEND_URL" "${FRONTEND_URL:-http://localhost:4200}"

    apply_var_env "DB_CONNECTION" "${DB_CONNECTION:-mysql}"
    apply_var_env "DB_HOST" "${DB_HOST:-localhost}"
    apply_var_env "DB_PORT" "${DB_PORT:-3306}"
    apply_var_env "DB_DATABASE" "${DB_DATABASE:-development}"
    apply_var_env "DB_USERNAME" "${DB_USERNAME:-developer}"
    apply_var_env "DB_PASSWORD" "${DB_PASSWORD:-developer}"

    apply_var_env "REDIS_HOST" "${REDIS_HOST:-redis}"
    apply_var_env "REDIS_PORT" "${REDIS_PORT:-6379}"

    apply_var_env "SESSION_DRIVER" "${SESSION_DRIVER:-database}"
    apply_var_env "SESSION_LIFETIME" "${SESSION_LIFETIME:-120}"
    apply_var_env "SESSION_ENCRYPT" "${SESSION_ENCRYPT:-false}"
    apply_var_env "SESSION_PATH" "${SESSION_PATH:-/}"
    apply_var_env "SESSION_DOMAIN" "${SESSION_DOMAIN:-localhost}"
    apply_var_env "SESSION_SAME_SITE" "${SESSION_SAME_SITE:-Lax}"
    apply_var_env "SESSION_SECURE_COOKIE" "${SESSION_SECURE_COOKIE:-false}"

    apply_var_env "APP_LOCALE" "${APP_LOCALE:-pt_BR}"
    apply_var_env "APP_FALLBACK_LOCALE" "${APP_FALLBACK_LOCALE:-pt_BR}"
    apply_var_env "APP_FAKER_LOCALE" "${APP_FAKER_LOCALE:-pt_BR}"
    apply_var_env "APP_TIMEZONE" "${APP_TIMEZONE:-America/Sao_Paulo}"

    apply_var_env "MAIL_MAILER" "${MAIL_MAILER:-log}"
    apply_var_env "MAIL_HOST" "${MAIL_HOST:-null}"
    apply_var_env "MAIL_PORT" "${MAIL_PORT:-127.0.0.1}"
    apply_var_env "MAIL_USERNAME" "${MAIL_USERNAME:-2525}"
    apply_var_env "MAIL_PASSWORD" "${MAIL_PASSWORD:-null}"
    apply_var_env "MAIL_ENCRYPTION" "${MAIL_ENCRYPTION:-null}"
    apply_var_env "MAIL_FROM_ADDRESS" "${MAIL_FROM_ADDRESS:-null}"
    apply_var_env "MAIL_FROM_NAME" "${MAIL_FROM_NAME:-null}"

    apply_var_env "SANCTUM_STATEFUL_DOMAINS" "${SANCTUM_STATEFUL_DOMAINS:-localhost:4200}"

    apply_var_env "QUEUE_CONNECTION" "${QUEUE_CONNECTION:-redis}"
    apply_var_env "REDIS_CLIENT" "${REDIS_CLIENT:-phpredis}"
    apply_var_env "REDIS_HOST" "${REDIS_HOST:-127.0.0.1}"
    apply_var_env "REDIS_PASSWORD" "${REDIS_PASSWORD:-null}"
    apply_var_env "REDIS_PORT" "${REDIS_PORT:-6379}"

    # Optional keys useful for application behavior
    apply_var_env "GCP_MAPS_API_KEY" "${GCP_MAPS_API_KEY:-}"
    apply_var_env "SALES_PERCENT_COMMISSION" "${SALES_PERCENT_COMMISSION:-0}"
    apply_var_env "SALES_DAILY_SUMMARY_TIME" "${SALES_DAILY_SUMMARY_TIME:-18:00}"

}

if [ ! -f ".env" ]; then
  cp .env.example .env

  set_env

  if [ ! -d "vendor" ]; then
      composer install --no-interaction --prefer-dist --optimize-autoloader --quiet
  fi

  php artisan key:generate --force --no-interaction

  until php artisan migrate --no-interaction; do
      echo "Waiting for database..."
      sleep 3
  done

  php artisan db:seed --no-interaction

fi

## Apply system timezone and PHP timezone setting based on APP_TIMEZONE
if [ -n "${APP_TIMEZONE}" ]; then
  echo "Setting system timezone to ${APP_TIMEZONE}"
  # write /etc/timezone and link localtime if zoneinfo exists
  if [ -f "/usr/share/zoneinfo/${APP_TIMEZONE}" ]; then
    echo "${APP_TIMEZONE}" > /etc/timezone || true
    ln -sf "/usr/share/zoneinfo/${APP_TIMEZONE}" /etc/localtime || true
  fi
  # update php custom ini date.timezone line if present
  if [ -f "/usr/local/etc/php/conf.d/custom.ini" ]; then
    sed -i "s|^date.timezone = .*|date.timezone = \"${APP_TIMEZONE}\"|" /usr/local/etc/php/conf.d/custom.ini || true
  fi
fi

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf