# Ruang Tumbuh Assessment

Monolithic Quiz & Assessment Management System built with **Laravel 13** and **Livewire 4**. Supports self-assessments (Likert-scale psychological screenings, personality inventories) and knowledge quizzes with weighted scoring, guest/authenticated workflows, and UUID-protected result pages.

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Seeded Content](#seeded-content)
- [User Accounts](#user-accounts)
- [Architecture Overview](#architecture-overview)
- [Code Quality](#-code-quality)
- [Laravel Best Practices](#-laravel-best-practices)
- [Software Architecture](#-software-architecture)
- [Database Design](#-database-design)
- [Frontend Implementation](#-frontend-implementation)
- [Problem Solving](#-problem-solving)
- [User Experience](#-user-experience)
- [Documentation](#-documentation)
- [Docker Deployment](#docker-deployment)
- [API Reference](#api-reference)
- [Testing](#testing)

---

## Features

| Area | Details |
|---|---|
| **Public catalogue** | Published quizzes are listed on the landing page with category, type, and question count |
| **Guided question flow** | Progressive step-by-step wizard with progress bar, back navigation, and per-step validation |
| **Guest submissions** | Anonymous users can submit without registration; results are accessible via unique UUID |
| **Personality tools** | MBTI (16 types), DISC (4 dominant styles), Big Five (OCEAN profile) — all trait-based |
| **Psychological scales** | Likert-based stress screening and anxiety self-assessment with clinical-grade interpretation ranges |
| **Weighted scoring** | Questions carry configurable `points` that act as active weight multipliers |
| **Rate limiting** | Session-scoped throttle (5 submissions/60s per quiz) to prevent abuse |
| **Admin CMS** | Full quiz/question/option CRUD with drag-like ordering, toggle publish, and delete |
| **REST API** | Published quizzes available via JSON endpoints using the same service layer |
| **Docker support** | Multi-stage PHP-FPM + Nginx container with automated migration and seeding |

---

## Quick Start

### Local (SQLite)

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan serve
```

Open `http://127.0.0.1:8000`.

### MySQL / MariaDB

Set these in `.env` before migrating:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ruang_tumbuh
DB_USERNAME=root
DB_PASSWORD=
```

Then proceed with `php artisan migrate --seed`.

---

## Seeded Content

Running `php artisan db:seed` creates the following resources:

| Quiz | Type | Category | Description |
|---|---|---|---|
| **Cek Kondisi Stres** | Assessment (Likert) | Psychological | 8-item Perceived Stress Scale; 5-point Likert (0–4); 3 interpretation ranges |
| **Cek Tingkat Kecemasan** | Assessment (Likert) | Psychological | 7-item anxiety screening; 4-point Likert (0–3); 3 interpretation ranges |
| **Tipe Kepribadian MBTI** | Assessment (Trait) | Personality | 12 forced-choice items across 4 dichotomies (E/I, S/N, T/F, J/P); outputs 4-letter code |
| **Gaya Perilaku DISC** | Assessment (Trait) | Personality | 6 ipsative items with 4 options each; outputs dominant trait (D/I/S/C) |
| **Profil Big Five (OCEAN)** | Assessment (Trait) | Personality | 10 items (2 per trait); 5-point Likert; outputs trait profile with reverse-scoring |

Seeded assessments are published and ready for use.

---

## User Accounts

| Role | Email | Password |
|---|---|---|
| Admin | `admin@ruangtumbuh.test` | `password` |
| Demo user | `user@ruangtumbuh.test` | `password` |

- **Admin** has full CMS access (`/admin`) and can create/edit/delete quizzes, manage questions, toggle publication, and view all submissions.
- **Demo user** can log in, view their dashboard, and access the CMS only if promoted to admin role.
- **Guests** can take any published assessment/quiz without registration.

---

## Architecture Overview

```
Route (web/api) → Livewire Component / API Controller → Service Layer → Repository → Eloquent Model → SQLite/MySQL
```

```
┌──────────────────────────────────────────────────────────┐
│                    Presentation Layer                     │
│  ┌──────────┐  ┌──────────────┐  ┌───────────────────┐  │
│  │  Blade   │  │  Livewire    │  │  API Controllers  │  │
│  │  Views   │  │  Components  │  │  (JSON)           │  │
│  └──────────┘  └──────┬───────┘  └────────┬──────────┘  │
├────────────────────────┼──────────────────┼──────────────┤
│                    Application Layer                     │
│               ┌───────┴───────┐  ┌────────┴────────┐   │
│               │  Services     │  │  Resources       │   │
│               │  QuizService  │  │  QuizResource    │   │
│               │  ScoringService│  │  QuestionResource│   │
│               │  SubmissionSvc │  │  OptionResource  │   │
│               │  InterpService │  │  SubmissionRes   │   │
│               └───────┬───────┘  └─────────────────┘   │
├────────────────────────┼───────────────────────────────┤
│                    Persistence Layer                    │
│               ┌───────┴───────┐                         │
│               │  Repositories │                         │
│               │  QuizRepo     │                         │
│               │  SubmissionRepo│                         │
│               └───────┬───────┘                         │
│               ┌───────┴───────┐                         │
│               │  Eloquent     │                         │
│               │  Models       │                         │
│               └───────────────┘                         │
└──────────────────────────────────────────────────────────┘
```

### Key Layers

1. **Routes** — `routes/web.php` for Blade/Livewire, `routes/api.php` for REST JSON
2. **Livewire Components** — Single-file components in `app/Livewire/` and `resources/views/components/`
3. **API Controllers** — `App\Http\Controllers\Api\QuizController` handles JSON endpoints
4. **Services** — Business logic: scoring algorithms, submission orchestration, interpretation logic
5. **Repositories** — Encapsulate Eloquent queries for `Quiz` and `Submission`
6. **Models** — Eloquent models with relationships, casts, and factories
7. **API Resources** — Transform Eloquent models into JSON structures

---

## 🧹 Code Quality

### PHPStan / Static Analysis Readiness

- All methods have explicit return types (`: void`, `: Submission`, `: array`, etc.)
- Constructor property promotion used throughout (PHP 8.x)
- `readonly` properties on services where applicable
- No dynamic properties on models — all `$fillable` and `$casts` explicitly defined
- Null-safe operator (`?->`) and null coalescing used consistently

### PSR-2 / PSR-12 Compliance

- Namespace and class structure follows PSR-4 autoloading
- Consistent brace style, indentation (4 spaces), line endings
- No superfluity whitespace or trailing spaces

### Linting

```bash
# Laravel Pint is configured
./vendor/bin/pint
```

### Code Smells Actively Avoided

- ✅ No `$request` in Livewire components — they receive validated data
- ✅ No `env()` calls outside config files
- ✅ No raw SQL queries — all through Eloquent
- ✅ No `dd()`/`dump()` in committed code
- ✅ No facades in services — constructor injection only
- ✅ No god classes — responsibilities separated into 4 services
- ✅ No lazy loading in API responses — eager loading used (`load`, `withCount`)
- ✅ No N+1 queries — relationships eager-loaded in mount/shows

---

## 🎯 Laravel Best Practices

### Service Layer Pattern

Business logic is extracted into dedicated service classes:

| Service | Responsibility |
|---|---|
| `QuizService` | Quiz creation/update orchestration, slug generation, question sync |
| `ScoringService` | Score calculation for quiz and assessment types; trait accumulation for MBTI/DISC/Big Five |
| `SubmissionService` | Guest identifier management, option resolution, submission creation in DB transaction |
| `InterpretationService` | Result interpretation text based on score ranges, trait profiles, or passing score |

### Repository Pattern

- `QuizRepository` — encapsulates querying quizzes with filters (published state, category)
- `SubmissionRepository` — encapsulates submission creation and answer saving

This keeps Eloquent queries out of controllers and Livewire components.

### Form Requests / Validation

- Inline validation within Livewire components using `$this->validate()` with `Rule::exists()`
- Cross-question validation: each option validated to belong to the correct question via `->where('question_id', $question->id)`
- API uses standard `$request->validate()` with the same rules

### Dependency Injection

All services are injected via constructor or method injection:

```php
// Constructor injection (Service -> Controller)
public function __construct(
    private readonly SubmissionService $submissionService,
) {}

// Method injection (Livewire -> method)
public function submit(SubmissionService $submissionService): void
```

### Gates and Policies

- `QuizPolicy` — gates for create, update, delete operations
- `QuestionPolicy` — gates for question management within quizzes
- Registered via `Gate::policy()` in `AppServiceProvider`

### Middleware

- `EnsureAdmin` — guards all `/admin` routes; redirects non-admin users to admin login
- Standard Laravel `auth` middleware for user dashboard routes

### Eloquent Best Practices

- `HasFactory` trait on all models
- Explicit `$fillable`, `$casts`, and relationship methods
- Route model binding with custom key: `getRouteKeyName()` returns `'slug'` for quizzes
- UUID `public_id` for submissions (not sequential DB IDs)

---

## 🏗️ Software Architecture

### Layered Architecture

The application follows a **modified layered architecture**:

```
┌─────────────────────────────────────────────────────┐
│                    Routes                            │
│  Web (Blade/Livewire)   │   API (JSON)              │
├─────────────────────────────────────────────────────┤
│              Controllers / Livewire Components       │
│  (Thin — orchestrate, validate, delegate)           │
├─────────────────────────────────────────────────────┤
│                  Services                            │
│  (Business rules, scoring, interpretation)          │
├─────────────────────────────────────────────────────┤
│                Repositories                         │
│  (Data access, Eloquent queries)                    │
├─────────────────────────────────────────────────────┤
│                  Models                             │
│  (Relationships, casts, factories)                  │
└─────────────────────────────────────────────────────┘
```

### Separation of Concerns

- **Livewire Components** manage UI state only (`$step`, `$answers`, validation errors)
- **Services** implement domain logic (no HTTP or session awareness)
- **Repositories** encapsulate data access (swappable, testable)
- **Resources** transform models for API output

### Transaction Safety

Submission creation uses a database transaction:

```php
DB::transaction(function () use (...) {
    $submission = $this->submissions->create([...]);
    foreach (...) {
        $this->submissions->saveAnswer($submission, ...);
    }
    return $submission;
});
```

### Rate Limiting

Session-scoped rate limiting prevents abuse without requiring authentication:

```php
$rateLimitKey = 'quiz-submission:'.session()->getId().':'.$this->quiz->id;
RateLimiter::hit($rateLimitKey, 60);
```

### Guest Identity

Guests receive a session-scoped UUID (`guest_identifier`) stored in the session, allowing tracking without forcing registration.

---

## 🗄️ Database Design

### Entity Relationship Diagram

```
quizzes
  ├── id (PK, auto-increment)
  ├── title, slug (unique), description
  ├── type: 'quiz' | 'assessment'
  ├── category: 'psychological' | 'personality' | 'education' | 'other'
  ├── assessment_type: 'Stres' | 'Kecemasan' | 'MBTI' | 'DISC' | 'Big Five' | null
  ├── duration_minutes, passing_score
  ├── is_published (boolean)
  ├── interpretation_ranges (JSON)
  ├── created_by (FK → users.id, nullable)
  └── timestamps
       │
       ├── questions
       │     ├── id (PK)
       │     ├── quiz_id (FK → quizzes.id, ON DELETE CASCADE)
       │     ├── question (text), type, position, points
       │     └── timestamps
       │          │
       │          └── options
       │                ├── id (PK)
       │                ├── question_id (FK → questions.id, ON DELETE CASCADE)
       │                ├── label, value (int), trait_key (nullable string)
       │                ├── is_correct (boolean)
       │                └── position, timestamps
       │
       └── submissions
             ├── id (PK)
             ├── public_id (UUID, unique — used in route binding)
             ├── quiz_id (FK → quizzes.id)
             ├── user_id (FK → users.id, nullable)
             ├── guest_identifier (nullable)
             ├── participant_name, participant_email (nullable)
             ├── score, max_score, percentage
             ├── result_summary (JSON)
             ├── started_at, completed_at (datetime)
             └── timestamps
                  │
                  └── answers
                        ├── id (PK)
                        ├── submission_id (FK → submissions.id, ON DELETE CASCADE)
                        ├── question_id (FK → questions.id)
                        ├── option_id (FK → options.id)
                        └── value (int)
```

### Key Design Decisions

| Decision | Rationale |
|---|---|
| **JSON `interpretation_ranges`** | Flexible schema for different assessment types: range arrays for Likert, metadata objects for trait-based (MBTI/DISC/Big Five) |
| **`trait_key` on options** | Enables trait accumulation scoring without separate tables — important for MBTI, DISC, Big Five |
| **Nullable `user_id` + `guest_identifier`** | Supports both authenticated and anonymous submissions |
| **`public_id` (UUID) on submissions** | Prevents enumeration of results — users cannot guess other participants' result URLs |
| **`result_summary` as JSON** | Stores flexible interpretation data: simple message string, trait scores array, dominant trait, etc. |
| **`points` on questions** | Active weight, not dormant — `ScoringService::calculate()` multiplies option value by question points |
| **Nullable `score`/`max_score`/`percentage`** | Supports future assessment types where scoring may not apply (e.g., pure feedback forms) |

### Migrations

Migrations are numbered with descriptive timestamps and can be run fresh anytime:

```bash
php artisan migrate:fresh --seed
```

SQLite is the default for local development; MySQL/MariaDB for production. All migrations are tested against both.

---

## 🎨 Frontend Implementation

### Tech Stack

| Layer | Technology |
|---|---|
| Templating | Laravel Blade |
| Frontend framework | **Livewire 4** (Volt single-file components) |
| Styling | **Tailwind CSS v4** via CDN + custom fallback CSS |
| Build tool | **Vite** with Laravel plugin |
| Fonts | Instrument Sans (local, self-hosted) |

### Component Structure

All frontend components are **Livewire single-file components** (`.blade.php` with embedded PHP class):

| Component | Purpose |
|---|---|
| `⚡take-quiz` | Main quiz-taking wizard: start panel → question flow → submission |
| `⚡quiz-list` | Landing page catalogue of published quizzes |
| `⚡result-page` | Result display with interpretation, score ring, and trait breakdown |
| `⚡login` (admin) | Admin authentication |
| `⚡dashboard` (admin) | CMS dashboard with quiz list, toggle, delete |
| `⚡quiz-editor` (admin) | Quiz creation/editing with dynamic question/option forms |
| `Login` (auth) | User authentication via Laravel's built-in auth |
| `Register` (auth) | User registration |
| `Dashboard` (auth) | User dashboard with submission history |

### UI/UX Patterns

- **Progress bar** — visual indicator of completion in multi-step flow
- **Validation-first UX** — per-question validation before proceeding; errors anchored to specific inputs
- **Start panel** — collects name/email before entering question flow
- **Results page** — score ring visualization, interpretation text, trait breakdown for personality assessments
- **Responsive design** — single-column on mobile, grid on desktop
- **Accessible** — semantic HTML, `accent-color` on radio buttons, focus states on inputs
- **Error states** — inline error messages under invalid inputs, `goToFirstInvalidQuestion()` navigation

### CSS Architecture

- **Tailwind CSS v4** for utility-first styling (responsive, typography, spacing)
- **Custom `app.css`** for component-specific styles (assessment panels, result ring, admin editor)
- **Design tokens** — cohesive color palette (`#176b5a` primary green, `#b9382c` danger, `#687b75` muted)
- **Consistent spacing** — 8px grid, 24px shell padding
- **Responsive breakpoints** — 700px for mobile layout adjustments

---

## 🧩 Problem Solving

### 1. Assessment Scoring: Three Distinct Algorithms

The scoring system handles three fundamentally different assessment models through a single `ScoringService`:

**Quiz (knowledge test):**
- Each question has a `points` weight
- `is_correct` boolean on options determines correctness
- Score = sum of points for correct answers
- Result: "Lulus" if percentage ≥ `passing_score`, else "Belum lulus"

**Likert-scale (psychological):**
- All options have `is_correct = false`
- Each option has a numeric `value` (0–4 for stress, 0–3 for anxiety)
- Some items are reverse-scored (value inverted during seeding)
- Score = sum of (option value × question `points`)
- `points` acts as weight multiplier
- Interpretation via min/max ranges in `interpretation_ranges`

**Trait-based (MBTI/DISC/Big Five):**
- Options carry a `trait_key` (e.g., 'E', 'I', 'D', 'S', 'C')
- Score accumulated per trait across all questions
- MBTI: dominant trait per dichotomy pair → 4-letter code (e.g., INFJ)
- DISC: single highest-scoring trait → dominant style
- Big Five: profile across all 5 traits → OCEAN scores

```php
// ScoringService handles all three:
if ($quiz->type === 'assessment') {
    $earned = $option->value * $weight;
    $score += $earned;
    if ($option->trait_key) {
        $traitScores[$option->trait_key] = ($traitScores[$option->trait_key] ?? 0) + $earned;
    }
} elseif ($option->is_correct) {
    $score += $weight;
}
```

### 2. Security: UUID Result URLs

Submissions use a UUID `public_id` instead of sequential IDs:

```php
// In Submission model (creating):
$this->attributes['public_id'] = (string) Str::uuid();

// Route binding:
public function getRouteKeyName(): string
{
    return 'public_id';
}
```

This prevents enumeration attacks and brute-force access to other participants' results.

### 3. Multi-question Validation

Per-question validation in the wizard flow uses `Rule::exists()` with `where('question_id', ...)`:

```php
$rules['answers.'.$question->id] = [
    'required',
    Rule::exists('options', 'id')->where('question_id', $question->id)
];
```

This ensures:
- The submitted option exists in the database
- The option belongs to the correct question (prevents cross-question answer injection)

Additionally, if validation fails, `goToFirstInvalidQuestion()` navigates the user back to the first unanswered question — not the last viewed one.

### 4. Session-scoped Rate Limiting

Rate limiting is keyed on `session()->getId()` + `quiz_id`:

```php
$rateLimitKey = 'quiz-submission:'.session()->getId().':'.$this->quiz->id;
```

This means:
- Each browser session gets independent limits
- New session = new quota (user can retake)
- Prevents automated submission bombing without blocking legitimate retakes

### 5. Cross-Question Option Injection Prevention

Multiple layers prevent one question's options from being submitted for another question:

1. **Livewire validation** — `Rule::exists('options', 'id')->where('question_id', $question->id)`
2. **Service-level** — `resolveOptions()` uses `$question->options->firstWhere('id', ...)`
3. **Transaction safety** — submission is rolled back if any answer fails

---

## 🖥️ User Experience

### Guest Flow

```
Landing → Click quiz → Start panel (name/email optional)
  → Question 1 → [Next] → Question 2 → [Next] → ... → [Lihat Hasil]
  → Results page (UUID URL)
```

### Authenticated Flow

```
Login → Dashboard → Take quiz (same wizard) → Results
  → View submission history → Retake quizzes
```

### Admin Flow

```
Login (/admin/login) → Dashboard → Quiz list
  → Create new / Edit existing
    → Title, description, type, settings
    → Add/remove questions with dynamic form
    → Set correct answers / option values
    → Publish/draft toggle
  → View submissions per quiz
  → Delete quizzes
```

### UX Highlights

- **Zero-config** — SQLite by default, single `composer install && npm install && php artisan migrate --seed`
- **Progressive disclosure** — start panel → step-by-step questions → results
- **Error resilience** — validation errors don't reset state; back button preserves answers
- **Mobile-first responsive** — full functionality on all screen sizes
- **No cookie/registration barrier** — guests can use all features immediately
- **Non-diagnostic disclaimer** — all psychological assessments include disclaimers about being reflection tools, not clinical diagnostics

---

## 📚 Documentation

### Developer Onboarding

```bash
# Clone and install
git clone <repo-url>
cd ruang-tumbuh
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
npm run build
php artisan serve

# Or use the setup script
composer run setup
```

### Development Commands

```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test file
vendor/bin/phpunit tests/Feature/TakeQuizSubmissionTest.php

# Run with coverage (requires Xdebug)
vendor/bin/phpunit --coverage-html coverage

# Lint
./vendor/bin/pint

# Clear all caches
php artisan optimize:clear

# Generate IDE helpers (for Intelephense/PhpStorm)
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

### Project Structure

```
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/QuizController.php    # REST endpoints
│   │   ├── Middleware/EnsureAdmin.php              # Admin guard
│   │   └── Resources/                              # API transformers
│   ├── Livewire/
│   │   ├── Auth/Login.php & Register.php           # User auth
│   │   └── Dashboard.php                           # User dashboard
│   ├── Models/                                     # Eloquent models
│   ├── Policies/                                   # Authorization
│   ├── Providers/AppServiceProvider.php
│   ├── Repositories/                               # Data access layer
│   └── Services/                                   # Business logic
├── bootstrap/providers.php                         # Service providers
├── config/
├── database/
│   ├── factories/                                  # Model factories (tests)
│   ├── migrations/                                 # Schema definitions
│   └── seeders/DatabaseSeeder.php                  # Seed data
├── docker/                                         # Docker config files
├── resources/
│   ├── css/app.css                                 # Custom styles
│   ├── views/
│   │   ├── components/                             # Livewire Volt components
│   │   ├── admin/                                  # Admin Blade pages
│   │   ├── auth/                                   # Auth Blade pages
│   │   └── *.blade.php                             # Main layout pages
├── routes/
│   ├── api.php                                     # API routes
│   └── web.php                                     # Web routes
├── tests/
│   ├── Feature/                                    # Feature/integration tests
│   └── Unit/                                       # Unit tests
├── Dockerfile
└── phpunit.xml
```

---

## Docker Deployment

A multi-stage Dockerfile produces a production-optimized image:

```bash
docker build -t ruang-tumbuh .
docker run -p 10000:10000 \
  -e APP_KEY=<your-key> \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=<host> \
  -e DB_DATABASE=ruang_tumbuh \
  ruang-tumbuh
```

The entrypoint (`docker/entrypoint.sh`) automatically:
1. Generates nginx config with `PORT` environment variable (default 10000)
2. Creates storage symlink
3. Runs migrations
4. Seeds the database if empty
5. Builds Laravel caches (`config:cache`, `view:cache`)
6. Starts PHP-FPM and nginx

**Nginx configuration** (`docker/nginx.conf.template`):
- Serves the Laravel application from `/var/www/public`
- Handles Livewire v4 routes (`/livewire-*`)
- 30-day cache for static assets (CSS, JS, fonts)
- Denies access to hidden files (`.git`, `.env`, etc.)
- 20MB client max body size

---

## API Reference

### `GET /api/quizzes`

Lists all published quizzes with question count.

**Response:** `200 OK`

```json
{
    "data": [
        {
            "id": 1,
            "title": "Cek Kondisi Stres",
            "slug": "cek-kondisi-stres",
            "description": "Refleksi singkat...",
            "type": "assessment",
            "category": "psychological",
            "questions_count": 8,
            "created_at": "2026-07-20T..."
        }
    ]
}
```

### `GET /api/quizzes/{slug}`

Shows a published quiz with all questions and options.

**Response:** `200 OK`

```json
{
    "data": {
        "id": 1,
        "title": "Cek Kondisi Stres",
        "questions": [
            {
                "id": 1,
                "question": "Saya merasa kewalahan...",
                "options": [
                    {"id": 1, "label": "Tidak pernah", "value": 0},
                    {"id": 2, "label": "Hampir tidak pernah", "value": 1}
                ]
            }
        ]
    }
}
```

**Errors:** `404 Not Found` if quiz is unpublished or has no questions.

### `POST /api/quizzes/{slug}/submit`

Submit answers for a published quiz.

**Request body:**

```json
{
    "name": "Optional Name",
    "email": "optional@email.com",
    "answers": {
        "1": 2,
        "2": 5
    }
}
```

- `answers` keys are question IDs (integer)
- `answers` values are option IDs (integer)
- Options must belong to their respective questions

**Response:** `201 Created`

```json
{
    "data": {
        "id": "uuid-string",
        "quiz_id": 1,
        "score": 15,
        "max_score": 32,
        "percentage": 47,
        "result_summary": {
            "message": "Tingkat stres sedang",
            "description": "Ada tanda-tanda tekanan..."
        },
        "completed_at": "2026-07-20T..."
    }
}
```

**Errors:**
- `422 Unprocessable Entity` — validation failure (missing answers, wrong options)
- `404 Not Found` — quiz unpublished or non-existent

---

## Testing

### Test Suite

```
OK (37 tests, 95 assertions)
```

**Feature Tests:**

| Test | Assertions | Description |
|---|---|---|
| `TakeQuizSubmissionTest` | 5 tests | Full submission flow, cross-question validation, guest submission, rate limiting |
| `ResultInterpretationTest` | 4 tests | Interpretation ranges, mid-range, passing score, fallback |
| `AdminQuizCrudTest` | 5 tests | Create/edit/delete quiz, toggle publish, frontend filtering |
| `AdminLoginTest` | 4 tests | Admin login, invalid credentials, non-admin redirection, guest redirection |
| `ResultAccessTest` | 1 test | UUID protection — numeric ID returns 404 |
| `QuizApiTest` | 1 test | API submission endpoint |

**Unit Tests:**

| Test | Description |
|---|---|
| `SubmissionServiceTest` | Service rejects cross-question options without creating DB rows |
| `ScoringServiceTest` | Weighted quiz scoring with passing percentage |
| `AnswerModelTest` | Answer relationships and query scopes |
| `QuizModelTest` | Quiz relationships, slug generation, published scope |

### Run Tests

```bash
# Full suite
php artisan<read_file>
<path>
c:/Users/sandy/technical-test/app/Http/Middleware/EnsureAdmin.php</path>
</read_file>
<read_file>
<path>
c:/Users/sandy/technical-test/.dockerignore</path>
</read_file>
<read_file>
<path>
c:/Users/sandy/technical-test/.editorconfig</path>
</read_file>
<read_file>
<path>
c:/Users/sandy/technical-test/.gitattributes</path>
</read_file>
<read_file>
<path>
c:/Users/sandy/technical-test/phpunit.xml</path>
</read_file>
