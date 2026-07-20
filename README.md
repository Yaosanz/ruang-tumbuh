# Ruang Tumbuh Assessment

Monolithic Quiz and Assessment Management System built with Laravel 13 and Livewire 4.

## Features

- Public assessment catalogue, guided question flow, submission storage, and result interpretation.
- Admin CMS for authentication, quiz/assessment creation, question and option management, publishing, and submission dashboard.
- Seeded stress self-assessment with a non-diagnostic result disclaimer.
- SQLite is configured for quick local setup; MySQL/MariaDB can be selected through standard Laravel `DB_*` environment variables.

## Run locally

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`. Admin login: `admin@ruangtumbuh.test` / `password`.

## Architecture

Domain data is modelled as `Quiz -> Question -> Option` and `Submission -> Answer`. Livewire single-file components contain UI state and interaction logic, with Eloquent relations handling persistence. Admin routes are protected by an `is_admin` middleware.

## Verification

Run `php artisan test`. On this Windows environment, use `php -d sys_temp_dir=.tmp artisan test` when the global temporary directory is unavailable.
