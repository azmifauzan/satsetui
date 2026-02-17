#!/bin/bash
set -e

echo "=== SatsetUI Container Starting ==="

# ---------- Fix permissions ----------
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ---------- Rebuild config cache with runtime env vars ----------
# IMPORTANT: config:cache is done here (not in Dockerfile) because
# .env is mounted at runtime and may differ from build time.
echo "Caching config with runtime environment..."
php artisan config:clear 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ---------- Run migrations if requested ----------
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "Running database migrations..."
    php artisan migrate --force
fi

# ---------- Run seeders if requested ----------
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo "Running database seeders..."
    php artisan db:seed --force
fi

# ---------- Generate storage link ----------
php artisan storage:link 2>/dev/null || true

# ---------- Verify Node.js is available (needed for preview) ----------
echo "Node.js: $(node --version), npm: $(npm --version)"

echo "=== Starting Supervisor (Apache + Queue Worker) ==="
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
