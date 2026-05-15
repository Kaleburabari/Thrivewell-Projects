# ThriveWell OS - Psychological Premium Counselling Platform

Master-spec-driven premium foundation for the ThriveWell OS counselling platform. The app now includes the full intern dashboard experience requested for the next build step: responsive dashboard shell, live-feeling dashboard APIs, role permissions, seeded data, audit logging, consent-aware safety language, and human-first crisis handoff readiness.

> Environment note: external Composer/NPM package registries returned HTTP 403 through the configured proxy in this container. The project therefore remains zero-dependency and Laravel-style, while preserving commands and structure that can be replaced by official Laravel + Inertia when package access is available.

## What is included

- Auth-ready login, logout, Version 1.0 multi-role onboarding, session booking, native video consultation, and counsellor credential verification screens with draft saving, consent capture, accessibility preferences, profile creation, verification-token readiness, credential vault metadata, review queues, verification badges, signed-download readiness, booking holds, double-booking prevention, payment/reminder readiness, video waiting rooms, device checks, consent-before-recording, captions/low-bandwidth readiness, editable AI summaries, and audit logging.
- Role, permission, policy, and audit-log foundation.
- Premium dark/light intern dashboard inspired by the supplied benchmark image and governed by `THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php`.
- Sidebar, mobile navigation, topbar, profile controls, status selector, notification actions, command palette, filter bar, KPI cards, schedule panel, recent sessions, wallet panel, earnings chart, radar chart, CPD progress ring, AI companion panel, client signal board, supervision queue, resource library, privacy/safety index, CPD data table, audit timeline, no-code workflow canvas, consent form builder, master-spec compliance panel, support/upgrade cards, consent banner, and crisis handoff card.
- Dashboard, booking, video consultation, and credential API routes for live widget data, availability updates, notification read state, booking discovery/holds/confirmation/cancellation, video room data/device checks/recording consent/chat/notes/editable AI summaries, credential metadata submission, signed-download tokens, admin review decisions, and human-first crisis/support handoff.
- SQLite migrations and seed data for users, roles, permissions, onboarding drafts/profiles, consent records, email verification tokens, credential documents/reviews/badges/signed-download tokens, counsellor profiles, availability, booking holds/session bookings/payment records/reminders, video rooms/device checks/recording consents/chat/notes/AI summaries, sessions, earnings, CPD modules, notifications, wellness actions, AI companion cards, crisis incidents, client signals, supervision tasks, resources, dashboard widgets, workflow nodes, form-builder fields, preferences, and audit logs.
- Tests for authentication data, dashboard access, role denial, seeded dashboard data, dashboard builder data, master-spec directive coverage, multi-role onboarding, session booking, native video consultation, credential verification, consent records, verification-token readiness, signed-download token readiness, status updates, notification state, integer-money storage, and human-review crisis incidents.


## Master specification workflow

All future work must begin by reading `THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php`. The dashboard now surfaces a Master Spec Compliance panel that checks the local specification for premium UI, dashboard benchmark, design-system, clinical/AI safety, security, acceptance-standard, and improvement-only directives. Do not remove existing features when iterating; only improve or extend them.

## Setup

```bash
# Install all dependencies
composer install
npm install

# Setup environment
cp .env.example .env
php artisan key:generate

# Create & seed database
php artisan migrate

# Start development servers (in separate terminals)
php artisan serve          # Terminal 1: backend on port 8000, bound to 0.0.0.0
npm run dev                # Terminal 2: frontend dev proxy on port 3000, bound to 0.0.0.0
```

Then open <http://127.0.0.1:3000> for the frontend dev proxy, or <http://127.0.0.1:8000> for the backend directly. In a container or cloud workspace, open the forwarded URL for port 3000 instead. In this zero-dependency bridge, `npm run dev` proxies application requests to `php artisan serve` and serves local frontend assets until official Vite/React can be installed.

If the browser says `ERR_CONNECTION_REFUSED`, keep both terminal commands running, confirm port 3000 is forwarded by your environment, and rerun `npm run dev` after `php artisan serve` is listening.

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

## Available screens and API routes

- `GET /login` — calm demo login screen.
- `GET /register` — premium multi-role onboarding screen.
- `GET /onboarding/success` — onboarding completion confirmation.
- `GET /booking` — client session booking discovery and confirmation flow.
- `GET /booking/data` — JSON booking discovery payload.
- `GET /video` — native video consultation waiting room and session space.
- `GET /video/data` — JSON video room payload.
- `GET /credentials` — private credential vault for counsellors/interns.
- `GET /admin/credentials` — admin credential verification review queue.
- `GET /dashboard` — full protected intern dashboard.
- `GET /dashboard/data` — JSON dashboard data payload.
- `POST /dashboard/status` — CSRF-protected availability update with audit log.
- `POST /notifications/read` — CSRF-protected notification read action with audit log.
- `POST /onboarding/draft` — CSRF-protected draft save for progressive onboarding.
- `POST /onboarding/complete` — CSRF-protected onboarding completion with profile, consent, email verification readiness, and audit log.
- `POST /booking/hold` — CSRF-protected short booking hold with double-booking checks.
- `POST /booking/confirm` — CSRF-protected booking confirmation with payment/reminder readiness.
- `POST /booking/cancel` — CSRF-protected calm booking cancellation with audit log.
- `POST /video/device-check` — CSRF-protected camera/microphone/network/caption readiness.
- `POST /video/consent` — CSRF-protected consent-before-recording preference.
- `POST /video/chat` — CSRF-protected session chat with crisis-signal audit readiness.
- `POST /video/notes` — CSRF-protected human-authored clinical notes.
- `POST /video/ai-summary` — CSRF-protected editable AI summary draft with no-diagnosis safety label.
- `POST /credentials/submit` — CSRF-protected credential metadata submission to private vault.
- `POST /credentials/signed-download` — CSRF-protected signed credential download token creation.
- `POST /admin/credentials/approve` — CSRF-protected credential approval with badge readiness and audit log.
- `POST /admin/credentials/revision` — CSRF-protected calm revision request with audit log.
- `POST /crisis/handoff` — CSRF-protected human support handoff with audit log and false-positive review readiness.

## Clinical and AI safety boundaries

Kale AI Companion is represented as a safety-boundary service and dashboard panel. It must never claim to replace therapy, generate diagnoses, provide medication advice, or bypass human crisis handoff. Future AI outputs must be editable before saving to clinical records, memory must be opt-in/exportable/erasable, and crisis false positives must be reviewed with audit logs.


## Official Laravel/Inertia package access gate

Official Laravel/Inertia migration should begin only after package access is available. Check access with:

```bash
bash scripts/check-package-access.sh
# or
composer package:check
# or
npm run check:package-access
# then, from a clean tree, start the guarded migration
bash scripts/migrate-to-official-laravel-inertia.sh
```

If the check fails, follow `docs/package-access-unblock.md` and `docs/official-laravel-inertia-migration.md` to allowlist package hosts, configure internal mirrors, or import a prebuilt official Laravel/Inertia artifact. Continue the master-spec fallback path without reducing existing functionality until access is available. Use `docs/laravel-inertia-parity-map.md` as the acceptance checklist when the migration can run.

## Known environment blocker

Network calls to Packagist and npm returned HTTP 403 through the configured proxy, so this pass could not install the latest Laravel, Inertia, React, Tailwind, or chart packages. The runnable foundation uses zero external dependencies while preserving Laravel-style organization and commands.

## Next prompt

Continue ThriveWell OS Version 1.0 by expanding native video consultation and Kale AI Companion, or by replacing the zero-dependency shell with official Laravel + Inertia React once package registry access is available, then implement full Fortify/Sanctum auth, PostgreSQL support, queues, notifications, Paystack adapter, and real CRUD for sessions, wallet payouts, CPD, audit logs, onboarding, credential verification, and session booking.
