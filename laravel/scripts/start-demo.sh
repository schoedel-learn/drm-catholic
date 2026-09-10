#!/bin/sh

set -eu

fail()
{
    stage=$1
    message=$2

    printf '{"severity":"ERROR","event":"demo_startup_failed","stage":"%s","message":"%s"}\n' \
        "$stage" "$message" >&2
    exit 1
}

require_variable()
{
    variable_name=$1
    variable_value=$2

    if [ -z "$variable_value" ]; then
        fail "validation" "$variable_name is required."
    fi
}

require_writable_directory()
{
    directory=$1

    if [ ! -d "$directory" ] || [ ! -w "$directory" ]; then
        fail "permission" "A required runtime directory is not writable: $directory"
    fi
}

run_artisan_step()
{
    stage=$1
    message=$2
    shift 2

    if ! php artisan "$@"; then
        fail "$stage" "$message"
    fi
}

require_variable DEMO_MODE "${DEMO_MODE:-}"
require_variable APP_KEY "${APP_KEY:-}"
require_variable DEMO_USER_PASSWORD "${DEMO_USER_PASSWORD:-}"
require_variable DEPLOYMENT_GIT_SHA "${DEPLOYMENT_GIT_SHA:-}"
require_variable DB_CONNECTION "${DB_CONNECTION:-}"
require_variable DB_DATABASE "${DB_DATABASE:-}"

if [ "$DEMO_MODE" != "true" ]; then
    fail "validation" "DEMO_MODE must be exactly true."
fi

if [ "$DB_CONNECTION" != "sqlite" ]; then
    fail "validation" "DB_CONNECTION must be exactly sqlite."
fi

case "$DB_DATABASE" in
    /*) ;;
    *) fail "validation" "DB_DATABASE must be an absolute path." ;;
esac

database_directory=${DB_DATABASE%/*}
if [ -z "$database_directory" ]; then
    database_directory=/
fi

if ! mkdir -p "$database_directory"; then
    fail "permission" "The SQLite parent directory could not be created."
fi

if ! touch "$DB_DATABASE"; then
    fail "permission" "The SQLite database file could not be created."
fi

if [ ! -w "$database_directory" ]; then
    fail "permission" "The SQLite parent directory is not writable."
fi

if [ ! -w "$DB_DATABASE" ]; then
    fail "permission" "The SQLite database file is not writable."
fi

for writable_directory in \
    /app/storage/framework/cache \
    /app/storage/framework/sessions \
    /app/storage/framework/views \
    /app/storage/logs \
    /app/bootstrap/cache
do
    require_writable_directory "$writable_directory"
done

run_artisan_step "cache_clear" "Laravel caches could not be cleared." optimize:clear
run_artisan_step "database_rebuild" "The demo database could not be rebuilt and seeded." migrate:fresh --seed --force
run_artisan_step "cache_build" "Laravel caches could not be built." optimize

exec frankenphp run --config /etc/frankenphp/Caddyfile
