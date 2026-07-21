# Ruang Tumbuh Assessment

Monolithic Quiz and Assessment Management System built with Laravel 13 and Livewire 4.

## Features

- Public assessment catalogue, guided Livewire question flow, guest submission, and UUID-protected result pages.
- Admin CMS with Livewire quiz/question/option CRUD, ordering controls, publish state, delete, and submission dashboard.
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

### MySQL / MariaDB

For a MySQL or MariaDB deployment, set the following values in `.env` before migrating:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ruang_tumbuh
DB_USERNAME=root
DB_PASSWORD=
```

Then run `php artisan migrate --seed`. SQLite remains useful for local automated tests.

## Architecture

Domain data is modelled as `Quiz -> Question -> Option` and `Submission -> Answer`. `points` is an active weight in `ScoringService`, not a dormant field. Business rules live in `QuizService`, `SubmissionService`, `ScoringService`, and `InterpretationService`; repositories encapsulate quiz and submission persistence. Livewire single-file components own UI state and call these services. Admin routes are protected by an admin middleware.

Guests can submit without an account or identity. A session-scoped rate limit protects submission attempts; a new browser session can legitimately retake a quiz. Result URLs use a UUID `public_id`, not sequential database IDs.

## API

Published quizzes are also available at `GET /api/quizzes`, `GET /api/quizzes/{slug}`, and `POST /api/quizzes/{slug}/submit`. The API uses the same submission and scoring services as the Livewire flow.

## Verification

Run `php artisan test`. On this Windows environment, use `php -d sys_temp_dir=.tmp artisan test` when the global temporary directory is unavailable.
