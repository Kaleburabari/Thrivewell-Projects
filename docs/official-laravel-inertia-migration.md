# Official Laravel/Inertia migration access plan

The ThriveWell OS master specification requires a Laravel foundation, role permissions, reusable premium design system, dashboard shell, audit logs, and a long-term platform architecture. The current repository is a zero-dependency bridge because the package registries are blocked in this container.

## Current blocker

The official migration is gated by package registry access:

- `composer show laravel/framework --all --no-interaction` fails against Packagist through the configured proxy with HTTP 403.
- `npm view @inertiajs/react version` fails against npm with E403.
- Running Composer/npm without the proxy cannot resolve or reach the public registry from this container.

Use the local diagnostic script before each migration attempt. See `docs/package-access-unblock.md` for public allowlist, internal mirror, proxy override, and prebuilt artifact options:

```bash
bash scripts/check-package-access.sh
# or
composer package:check
# or
npm run check:package-access
```

## Required allowlist

Ask the network/proxy owner to allow HTTPS access to:

- `repo.packagist.org`
- `packagist.org`
- `api.github.com`
- `github.com`
- `codeload.github.com`
- `registry.npmjs.org`

If direct internet access is not allowed, configure internal mirrors instead:

- Composer: Private Packagist, Satis, Nexus, or Artifactory Composer repository.
- npm: Verdaccio, Nexus, or Artifactory npm repository.

The repository scripts support mirror and proxy overrides through `THRIVEWELL_COMPOSER_REPOSITORY_URL`, `THRIVEWELL_NPM_REGISTRY_URL`, `THRIVEWELL_HTTP_PROXY`, and `THRIVEWELL_HTTPS_PROXY`. If packages must be installed outside this container, set `THRIVEWELL_LARAVEL_ARTIFACT` to a prebuilt official Laravel/Inertia archive before running `npm run migrate:official`.

## Migration gate

Only start replacing the current scaffold after both commands pass:

```bash
composer show laravel/framework --all --no-interaction
npm view @inertiajs/react version
```

After both checks pass, run the guarded helper from a clean working tree:

```bash
bash scripts/migrate-to-official-laravel-inertia.sh
```

The helper creates the official Laravel application in `.migration/official-laravel-inertia/thrivewell-os`, installs the official Breeze Inertia React TypeScript stack, runs the frontend build, and copies parity reference files into the migration workspace.

## Safe migration order

1. Preserve the current scaffold as the parity reference.
2. Install official Laravel in a migration branch.
3. Add Inertia React, TypeScript, Vite, and Tailwind.
4. Convert current schema into Laravel migrations, models, factories, and seeders.
5. Convert current controllers/services into Laravel controllers, form requests, policies, resources, jobs, events, and notifications.
6. Rebuild the current dashboard and onboarding UI as React components without dropping features.
7. Port the existing test assertions into Pest/PHPUnit feature tests.
8. Run parity tests before removing any bridge code.
9. Use `docs/laravel-inertia-parity-map.md` as the feature-by-feature acceptance map for the migration PR.

## Required parity features

The official Laravel/Inertia app must preserve:

- Master-spec compliance panel and master-spec-first workflow.
- Multi-role onboarding with draft saving, consent records, accessibility preferences, profile creation, verification-token readiness, and audit logs.
- Role/permission/policy checks.
- Premium intern dashboard panels and seeded data.
- AI safety boundaries for Kale.
- Human-first crisis handoff with false-positive review readiness.
- Money stored as integer minor units.
- Audit logs and indexes for sensitive/dashboard queries.

## Fallback while blocked

If the access checks fail, continue building Version 1.0 modules inside the current scaffold. The next recommended fallback module is Counsellor Credential Verification because it is the next trust/safety module in the master spec and does not require external packages.
