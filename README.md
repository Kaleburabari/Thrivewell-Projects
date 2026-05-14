# ThriveWell OS - Psychological Premium Counselling Platform

First-pass premium foundation for the ThriveWell OS counselling platform. The build implements a runnable PHP/Laravel-style application shell because external Composer/NPM package registries were blocked in this environment; the structure, commands, migrations, seeders, policies, services, and dashboard are ready to migrate into a full Laravel/Inertia stack when package access is available.

## What is included

- Auth-ready login, logout, and registration-readiness screens.
- Role, permission, policy, and audit-log foundation.
- Premium dark/light intern dashboard inspired by the supplied benchmark image.
- Sidebar, topbar, profile controls, status pill, notification count, KPI cards, schedule panel, recent sessions, wallet panel, earnings chart, radar chart, CPD progress ring, support/upgrade cards, and clinical AI safety banner.
- SQLite migrations and seed data for users, roles, permissions, sessions, earnings, CPD modules, notifications, and audit logs.
- Tests for authentication data, dashboard access, role denial, seeded schedule, wallet integer-money storage, and notifications.

## Setup

```bash
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate:fresh --seed
npm install
npm run build
php -S 127.0.0.1:8000 -t public
```

Then open <http://127.0.0.1:8000>.

## Demo logins

| Role | Email | Password |
| --- | --- | --- |
| Intern Counsellor | `intern@thrivewell.test` | `password` |
| Superadmin | `admin@thrivewell.test` | `password` |
| Client | `client@thrivewell.test` | `password` |

## Testing

```bash
composer test
# or
php artisan test
```

## Clinical and AI safety boundaries

Kale AI Companion is represented as a safety-boundary service and dashboard banner. It must never claim to replace therapy, generate diagnoses, provide medication advice, or bypass human crisis handoff. Future AI outputs must be editable before saving to clinical records, memory must be opt-in/exportable/erasable, and crisis false positives must be reviewed with audit logs.

## Known environment blocker

Network calls to Packagist and npm returned HTTP 403 through the configured proxy, so the first pass could not install the latest Laravel, Inertia, React, Tailwind, or chart packages. The runnable foundation uses zero external dependencies while preserving Laravel-style organization and commands.

## Next prompt

Continue ThriveWell OS Version 1.0 by replacing the zero-dependency shell with official Laravel + Inertia React once package registry access is available, then implement full Fortify/Sanctum auth, PostgreSQL support, queues, notifications, Paystack adapter, and real CRUD for sessions, wallet payouts, CPD, audit logs, and onboarding.
