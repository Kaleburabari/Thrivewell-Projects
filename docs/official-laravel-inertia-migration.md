# Official Laravel/Inertia migration access plan

The ThriveWell OS master specification requires a Laravel foundation, role permissions, reusable premium design system, dashboard shell, audit logs, and a long-term platform architecture. The current repository is a zero-dependency bridge because the package registries are blocked in this container.

## Current blocker

The official migration is gated by package registry access:

- `composer show laravel/framework --all --no-interaction` fails against Packagist through the configured proxy with HTTP 403.
- `npm view @inertiajs/react version` fails against npm with E403.
- Running Composer/npm without the proxy cannot resolve or reach the public registry from this container.

Use the local diagnostic script before each migration attempt:

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

## Migration gate

Only start replacing the current scaffold after both commands pass:

```bash
composer show laravel/framework --all --no-interaction
npm view @inertiajs/react version
```

## Safe migration order

1. Preserve the current scaffold as the parity reference.
2. Install official Laravel in a migration branch.
3. Add Inertia React, TypeScript, Vite, and Tailwind.
4. Convert current schema into Laravel migrations, models, factories, and seeders.
5. Convert current controllers/services into Laravel controllers, form requests, policies, resources, jobs, events, and notifications.
6. Rebuild the current dashboard and onboarding UI as React components without dropping features.
7. Port the existing test assertions into Pest/PHPUnit feature tests.
8. Run parity tests before removing any bridge code.

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
