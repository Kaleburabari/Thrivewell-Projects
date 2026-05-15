# Laravel + Inertia React parity map

This map exists so the zero-dependency bridge can be replaced with official Laravel + Inertia React + TypeScript without losing product scope, safety controls, or visual fidelity.

## Migration command

Run the guarded migration helper from a clean working tree:

```bash
bash scripts/migrate-to-official-laravel-inertia.sh
```

The helper stops before changing files if Packagist, GitHub, or npm access is blocked. When access is available, it creates an official Laravel application in `.migration/official-laravel-inertia/thrivewell-os`, installs the official Breeze Inertia React TypeScript stack, builds assets, and copies parity reference files into the migration workspace.

## Package access requirements

The migration requires HTTPS access to:

- `repo.packagist.org`
- `packagist.org`
- `api.github.com`
- `github.com`
- `codeload.github.com`
- `registry.npmjs.org`

If public internet access is not allowed, configure internal Composer and npm mirrors before running the migration helper, or import a prebuilt official Laravel/Inertia artifact as described in `docs/package-access-unblock.md`.

## Current bridge feature -> Laravel/Inertia target

| Current bridge area | Official Laravel/Inertia target | Parity requirement |
| --- | --- | --- |
| `public/index.php` route switch | `routes/web.php`, `routes/api.php`, named routes | Preserve login, onboarding, dashboard, credential, notification, status, and crisis handoff routes. |
| Manual CSRF/session helpers | Laravel sessions, CSRF middleware, auth guards | All state-changing routes remain CSRF protected. |
| `app/Models/User.php` array model | Eloquent `User` model with relationships | Preserve role/permission checks and demo users. |
| `DashboardPolicy` / `CredentialPolicy` | Laravel policies registered in `AuthServiceProvider` | Preserve dashboard, credential management, review, and signed access authorization. |
| `Database` service migrations | Laravel migration classes and seeders | Preserve indexes, soft-delete columns, integer money fields, audit tables, credential tables, onboarding tables, and crisis tables. |
| `OnboardingService` | Form requests, service/action classes, Eloquent models | Preserve draft saving, consent capture, accessibility preferences, profile creation, verification-token readiness, and audit logs. |
| `CredentialVerificationService` | Form requests, policies, storage disks, signed routes, notifications | Preserve private credential metadata, signed access tokens, review queue, revision requests, verification badges, and audit logs. |
| `DashboardDataService` | Eloquent queries, API resources, Inertia props | Preserve all premium dashboard cards, panels, charts, builder data, audit timeline, master-spec panel, and safety copy. |
| `AiSafetyService` | Dedicated domain service and policy-backed actions | Preserve no-diagnosis/no-medication/human-handoff boundaries. |
| PHP views | Inertia React TypeScript pages/components | Preserve premium dark/light responsive UI, accessible states, empty/error/success states, and calm language. |
| `tests/run.php` | Pest/PHPUnit feature and unit tests | Port every existing assertion before deleting the bridge test script. |

## First official Laravel PR scope

The first migration PR should not add product features. It should only prove parity:

1. Install Laravel + Inertia React TypeScript.
2. Port schema and seeders.
3. Port auth, role, permission, policy, audit, onboarding, dashboard, credential, and crisis flows.
4. Port tests.
5. Keep route and screen behavior equivalent to the bridge.

## Exit criteria

- `composer test` or `php artisan test` passes in the official Laravel app.
- `npm run build` passes in the official Laravel app.
- Existing demo accounts work.
- Every route in `routes.md` exists in the official app or has a documented renamed route.
- No feature from the master-spec compliance panel is removed.
