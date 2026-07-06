#!/bin/bash
set -e

echo "🚀 Начинаем деплой..."

cd /var/www/baano

# Обновляем код
git pull origin main

# Устанавливаем зависимости
composer install --no-dev --optimize-autoloader
npm ci
npm run build

# Настраиваем окружение
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Миграции
php artisan migrate --force

# Права
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "✅ Деплой завершён!"