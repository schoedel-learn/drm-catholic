#!/usr/bin/env zsh

emulate -L zsh
set -euo pipefail

script_dir=${0:A:h}
project_root=${script_dir:h}

cd "$project_root"

prepare_environment_files() {
    if [[ ! -f .env ]]; then
        cp .env.example .env
        print -- "Created .env from .env.example"
    else
        print -- ".env already exists; leaving it unchanged"
    fi

    mkdir -p database

    if [[ ! -f database/database.sqlite ]]; then
        : > database/database.sqlite
        print -- "Created database/database.sqlite"
    else
        print -- "database/database.sqlite already exists; leaving it unchanged"
    fi
}

app_key_is_empty() {
    if [[ ! -f .env ]]; then
        return 0
    fi

    local app_key_line app_key_value
    app_key_line=$(LC_ALL=C grep -m1 '^APP_KEY=' .env || true)
    app_key_value=${app_key_line#APP_KEY=}

    [[ -z "$app_key_value" ]]
}

preview_seed_data_is_missing() {
    php -r '
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        exit(\App\Models\User::query()->where("email", "test@example.com")->exists() ? 1 : 0);
    '
}

run_full_setup() {
    prepare_environment_files

    composer install --no-interaction --prefer-dist

    if app_key_is_empty; then
        php artisan key:generate --ansi
    else
        print -- "APP_KEY already exists; leaving it unchanged"
    fi

    php artisan migrate --force

    if preview_seed_data_is_missing; then
        php artisan db:seed --force
    else
        print -- "Preview seed data already exists; leaving it unchanged"
    fi

    npm ci
    npm run build
}

case "${1-}" in
    "")
        run_full_setup
        ;;
    --prepare-only)
        if (( $# != 1 )); then
            print -u2 -- "usage: ${0:t} [--prepare-only]"
            exit 1
        fi

        prepare_environment_files
        ;;
    *)
        print -u2 -- "usage: ${0:t} [--prepare-only]"
        exit 1
        ;;
esac
