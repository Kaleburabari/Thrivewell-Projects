# Package access unblock playbook

The official Laravel + Inertia React TypeScript migration requires Composer, npm, and GitHub package access. In this container those hosts currently return HTTP 403 through the configured proxy, so the migration cannot download official packages until access is changed outside the repository.

## Confirm the blocker

Run:

```bash
bash scripts/check-package-access.sh
```

The diagnostic prints the Composer repository, npm registry, and HTTPS proxy currently in use, then verifies:

- `composer show laravel/framework --all --no-interaction`
- `npm view @inertiajs/react version`

Both must pass before the migration helper can install official packages directly.

## Option A: allow public package hosts

Ask the network/proxy owner to allow HTTPS CONNECT traffic to:

- `repo.packagist.org`
- `packagist.org`
- `api.github.com`
- `github.com`
- `codeload.github.com`
- `registry.npmjs.org`

Then rerun:

```bash
npm run migrate:official
```

## Option B: use internal mirrors

If public package hosts are not allowed, configure internal mirrors and export these variables before running checks or migration:

```bash
export THRIVEWELL_COMPOSER_REPOSITORY_URL="https://composer.example.internal"
export THRIVEWELL_NPM_REGISTRY_URL="https://npm.example.internal"
bash scripts/check-package-access.sh
npm run migrate:official
```

If your mirror requires a different proxy than the container default, also set:

```bash
export THRIVEWELL_HTTP_PROXY="http://proxy.example.internal:8080"
export THRIVEWELL_HTTPS_PROXY="http://proxy.example.internal:8080"
```

The shared `scripts/package-access-env.sh` file applies these values to Composer and npm for both the diagnostic and migration helper.

## Option C: import a prebuilt official Laravel/Inertia artifact

If package installation must happen outside this container, build the official app in an internet-enabled environment, archive it, copy the archive into this repo, and run:

```bash
export THRIVEWELL_LARAVEL_ARTIFACT="/absolute/path/to/thrivewell-official-laravel-inertia.tar.gz"
npm run migrate:official
```

The artifact should contain the official Laravel application root with `composer.json`, `package.json`, Laravel app directories, Inertia React TypeScript files, and lockfiles. The migration helper extracts it into `.migration/official-laravel-inertia/thrivewell-os`, runs `npm run build`, and copies the parity reference files next to it.

## After access is unblocked

Use `docs/laravel-inertia-parity-map.md` as the acceptance checklist. The first official migration PR should prove parity only; do not add new product features until the Laravel/Inertia app passes equivalent tests and preserves the current routes, screens, safety controls, audit logs, credential verification, onboarding, and dashboard behavior.
