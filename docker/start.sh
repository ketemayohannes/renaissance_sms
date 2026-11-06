#!/usr/bin/env bash
#
# Container entrypoint: prepare the app, then hand PID 1 to Supervisor.
#
# `exec` matters: it makes supervisord PID 1, so `docker stop` (SIGTERM) reaches it and
# it shuts Apache and the queue workers down gracefully in about a second. Without it a
# shell was PID 1, ignored SIGTERM, and every deploy waited Docker's 10 s timeout before
# the app was force-killed (queue jobs included).
set -euo pipefail

cd /var/www/html

chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan migrate --force
php artisan storage:link --force

chown -R www-data:www-data storage bootstrap/cache

exec supervisord -n -c /etc/supervisor/supervisord.conf
