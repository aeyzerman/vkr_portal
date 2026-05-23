#!/bin/sh
cd /var/www

composer install --no-interaction

php -r "file_exists('.env') || copy('.env.example', '.env');"
php artisan key:generate
php artisan migrate --force
php artisan portal:ensure-admin

npm install --ignore-scripts
chmod +x node_modules/.bin/vite
npm run build
