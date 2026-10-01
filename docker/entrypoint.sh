#!/bin/bash
set -e

echo "──────────────────────────────────────────"
echo "  Jelajahin — Laravel Startup"
echo "──────────────────────────────────────────"

# Wait for PostgreSQL to be ready
echo "⏳ Waiting for PostgreSQL..."
until php -r "
  \$pdo = new PDO(
    'pgsql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'),
    getenv('DB_USERNAME'),
    getenv('DB_PASSWORD')
  );
" 2>/dev/null; do
  echo "   PostgreSQL not ready, retrying in 2s..."
  sleep 2
done
echo "✅ PostgreSQL is ready."

# Wait for Redis to be ready
echo "⏳ Waiting for Redis..."
until php -r "
  \$redis = new Redis();
  \$redis->connect(getenv('REDIS_HOST'), (int) getenv('REDIS_PORT'));
" 2>/dev/null; do
  echo "   Redis not ready, retrying in 2s..."
  sleep 2
done
echo "✅ Redis is ready."

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:PLACEHOLDER" ]; then
  echo "🔑 Generating application key..."
  php artisan key:generate --force
fi

# Storage symlink
echo "🔗 Linking storage..."
php artisan storage:link --force 2>/dev/null || true

# Run migrations
echo "🗄️  Running migrations..."
php artisan migrate --force

# Optimize for production
if [ "$APP_ENV" = "production" ]; then
  echo "⚡ Optimizing for production..."
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  php artisan event:cache
fi

echo "✅ Laravel is ready. Starting PHP-FPM..."
exec "$@"
