# Copilot instructions — finance-app

Short, focused guidance to help an AI coding agent be productive in this repo.

## Big picture
- This is a Laravel (PHP 8.3+) web app for financial management. It using Inertia.js as bridge with Vue.js to build single-page application Key responsibilities:
  - HTTP app: routes in `routes/web.php` -> controllers in `app/Http/Controllers`.
  - Pages and Components: `resources/js` uses Vue.js.

## Architecture & data flow (practical view)
- Requests hit `routes/web.php` and controller actions; controllers use models (in `app/Models`) to read/write data and return Inertia responses (views in `resources/js/Pages`).

## Developer workflows & commands
- Environment: copy `.env.example` → `.env`, set DB and mail.
- Install PHP deps: `php8.3 composer install` (or your system `composer install`).
- Frontend: `bun install` then `bun run dev`
- Artisan basics: `php artisan key:generate`, `php artisan migrate --seed`.
- Tests: `vendor/bin/phpunit` or `phpunit`.

## Editing guidance for AI agents
- Make minimal, localizable changes: update controllers, jobs, or mailers in-place and add failing tests that demonstrate intent.
- When changing invoice/pdf/storage logic, include a migration plan: update code → run a small migration or data-fix job → backfill stored PDFs.
- Preserve existing public endpoints and scheduled job timing unless the change explicitly requires adjustement.

---
If anything critical is missing or you want deeper detail (e.g., model fields, sample .env values, or typical dev container setup), tell me which area to expand.
