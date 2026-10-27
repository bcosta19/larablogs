#!/bin/sh
set -eu
mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs storage/app/private storage/app/public
php artisan config:cache --no-ansi >/dev/null
exec docker-php-entrypoint "$@"
