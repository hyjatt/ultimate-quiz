# F1Quiz Grid

A Formula racing quiz application built with Laravel 13, React 19, Inertia 3, Tailwind CSS 4, Vite, and MySQL.

## Features

- Email/username login, registration, email verification, and password recovery
- Player profiles with constructor selection, password changes, and recent race telemetry
- Secure server-scored quizzes with easy, medium, and hard programmes
- Driver and constructor standings with competition ranking
- Seed data for 93 questions, 11 teams, and 6 driver ranks
- Reserved `admin` role for a future separate administration application; no admin routes are exposed here

## Local setup

Requirements: PHP 8.3+, Composer 2, Node.js 22+, and MySQL 8+.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

Create an empty MySQL database named `ultimate_quiz`, update the `DB_*` values in `.env`, then run:

```bash
php artisan migrate --seed
npm run build
composer run dev
```

The application will be available at `http://localhost:8000`. In development, the default `MAIL_MAILER=log` writes verification and reset links to `storage/logs/laravel.log`. Configure a real SMTP provider before production use.

## Verification

```bash
php artisan test
npm test
npm run build
```

## Production deployment

1. Point the web server document root at the application's `public/` directory.
2. Set `APP_ENV=production`, `APP_DEBUG=false`, a production `APP_URL`, MySQL credentials, and SMTP credentials in `.env`.
3. Run `composer install --no-dev --optimize-autoloader`, `npm ci && npm run build`, and `php artisan migrate --seed --force`.
4. Run `php artisan config:cache`, `php artisan route:cache`, and `php artisan view:cache` after environment configuration is complete.

## Security note

The legacy application contained a database password in committed source. Its SQL dump and legacy connection file have been removed, but that credential must still be rotated anywhere it was used. Never commit `.env` or production secrets.
