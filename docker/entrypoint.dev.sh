#!/bin/bash
set -e

echo "=== SatsetUI Dev Container Starting ==="

# ---------- Fix permissions ----------
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# ---------- Install PHP dependencies ----------
echo ">>> Installing PHP dependencies (composer install)..."
composer install --no-interaction --prefer-dist

# ---------- Install Node.js dependencies ----------
echo ">>> Installing Node.js dependencies (npm install)..."
npm install --no-audit --no-fund

# ---------- Fix npm cache ownership so www-data can run npm in workspaces ----------
mkdir -p /var/www/.npm
chown -R www-data:www-data /var/www/.npm

# ---------- Ensure storage dirs ----------
mkdir -p storage/app/workspaces
mkdir -p storage/framework/{cache,sessions,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# ---------- Clear all caches (never cache in dev) ----------
echo ">>> Clearing all caches for dev environment..."
php artisan config:clear 2>/dev/null || true
php artisan route:clear 2>/dev/null || true
php artisan view:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true

# ---------- Run migrations ----------
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    echo ">>> Running database migrations..."
    php artisan migrate --force
fi

# ---------- Run seeders ----------
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    echo ">>> Running database seeders..."
    php artisan db:seed --force
fi

# ---------- Storage link ----------
php artisan storage:link 2>/dev/null || true

# ---------- Build frontend assets (always fresh on start) ----------
# This ensures /public/build has the latest assets.
# Vite dev server (managed by supervisor) will create public/hot automatically
# when it starts, enabling HMR. If Vite fails, Laravel falls back to these.
echo ">>> Building frontend assets..."
rm -f public/hot
npm run build 2>/dev/null || echo "WARN: npm run build failed, Vite HMR will be the only source"

echo ">>> Node.js: $(node --version) | npm: $(npm --version)"
echo ">>> PHP: $(php --version | head -1)"
echo ""
echo "=== App:  http://localhost:${APP_PORT:-8000} ==="
echo "=== Vite HMR: http://localhost:5173       ==="
echo ""
echo "=== Starting Supervisor (Apache + Queue Worker + Vite Dev Server) ==="
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.dev.conf
