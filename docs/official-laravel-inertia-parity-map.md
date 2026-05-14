# Official Laravel/Inertia parity map

This checklist governs the migration from the zero-dependency bridge to official Laravel + Inertia React + TypeScript + Vite/Tailwind. The bridge remains the acceptance reference until every item below is ported and tested.

## Package access gate

Run this before any migration rewrite:

```bash
bash scripts/check-package-access.sh
```

The gate must pass for all three package surfaces:

- Composer metadata for `laravel/framework` through Packagist or `THRIVEWELL_COMPOSER_REPOSITORY_URL`.
- npm metadata for `@inertiajs/react` through npm or `THRIVEWELL_NPM_REGISTRY_URL`.
- GitHub source archive access for Laravel ecosystem package downloads.

If it passes, create the official workspace with:

```bash
bash scripts/migrate-to-official-laravel-inertia.sh
```

## Feature parity checklist

### Foundation

- Official Laravel application boots with `.env`, `php artisan key:generate`, `php artisan migrate:fresh --seed`, and `php artisan test`.
- Inertia React + TypeScript + Vite/Tailwind build succeeds with `npm run build`.
- Existing master spec is preserved and read before feature work.
- Existing route coverage is preserved or redirected intentionally.

### Auth, roles, and permissions

- Login/logout behavior remains available for seeded demo accounts.
- Fortify/Sanctum-backed auth replaces the bridge session code.
- Role and permission checks preserve superadmin, intern, counsellor, and client behavior.
- Policies protect dashboard, credential, crisis, and future booking flows.

### Data model

- Bridge migration tables are converted to Laravel migrations with indexes and soft deletes where required.
- Money remains stored as integer minor units.
- Audit logs remain immutable for sensitive actions.
- Credential documents remain private-storage only with signed access.
- Consent records preserve consent version tracking.

### Inertia pages

- Dashboard page preserves premium shell, KPI cards, charts, wallet, schedule, CPD, AI safety, crisis handoff, consent banner, builder widgets, audit timeline, and mobile responsiveness.
- Multi-role onboarding preserves draft save, progressive disclosure, accessibility preferences, consent capture, email verification readiness, and calm success states.
- Credential vault preserves private metadata submission, signed access readiness, document cards, badge status, and revision language.
- Admin credential queue preserves approval, revision, signed access, audit logging, and empty states.
- Permission-denied and not-found pages preserve calm recovery language.

### Tests

- Port every assertion in `tests/run.php` to Pest/PHPUnit feature or unit tests.
- Add Inertia response assertions for dashboard, onboarding, and credential pages.
- Add policy tests for superadmin/intern/counsellor/client access boundaries.
- Add regression tests proving no double storage of money as floats.
- Add audit-log tests for onboarding completion, credential submission/review, notification reads, status updates, and crisis handoff.

## Removal rule

Do not remove the bridge scaffold until the official Laravel/Inertia implementation passes this parity map and the migrated test suite.
