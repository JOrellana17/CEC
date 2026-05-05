# SIGEH-PHP Installation Guide

## Requirements

- PHP 8.3+
- Composer
- Node.js and npm
- MySQL or MariaDB

## Setup

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Default users:

- `admin@hotel.com` / `password`
- `supervisor@hotel.com` / `password`

## Production Checklist

- Set `APP_ENV=production` and `APP_DEBUG=false`.
- Configure database, mail, queue, and filesystem values in `.env`.
- Use HTTPS and set `SESSION_SECURE_COOKIE=true`.
- Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache`.
- Schedule `php artisan schedule:run` and run queue workers if async jobs are enabled.
