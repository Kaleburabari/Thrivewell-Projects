#!/usr/bin/env bash
set -euo pipefail

printf 'ThriveWell OS official Laravel + Inertia migration\n'
printf '==================================================\n\n'

if ! bash scripts/check-package-access.sh; then
  cat <<'MSG'

Migration stopped before changing application code.
Resolve package access first, then rerun this script.
MSG
  exit 1
fi

root_dir="$(pwd)"
migration_dir="${THRIVEWELL_OFFICIAL_MIGRATION_DIR:-.migration/official-laravel-inertia}"
composer_repository_url="${THRIVEWELL_COMPOSER_REPOSITORY_URL:-${COMPOSER_REPOSITORY_URL:-https://repo.packagist.org}}"
npm_registry_url="${THRIVEWELL_NPM_REGISTRY_URL:-${NPM_CONFIG_REGISTRY:-https://registry.npmjs.org}}"

if [ -e "$migration_dir" ]; then
  printf 'Migration directory already exists: %s\n' "$migration_dir"
  printf 'Move or remove it before rerunning to avoid overwriting work.\n'
  exit 1
fi

mkdir -p "$(dirname "$migration_dir")"

printf 'Creating official Laravel project in %s...\n' "$migration_dir"
composer create-project laravel/laravel "$migration_dir" --no-interaction --repository-url="$composer_repository_url"

pushd "$migration_dir" >/dev/null

printf 'Installing official Laravel/Inertia server packages...\n'
composer require inertiajs/inertia-laravel laravel/sanctum laravel/fortify spatie/laravel-permission --no-interaction

printf 'Installing official React, TypeScript, Vite, and Tailwind packages...\n'
npm --registry "$npm_registry_url" install @inertiajs/react react react-dom
npm --registry "$npm_registry_url" install -D typescript vite @vitejs/plugin-react tailwindcss postcss autoprefixer @types/react @types/react-dom

printf 'Capturing current scaffold parity reference...\n'
mkdir -p docs/thrivewell-parity app/LegacyReference
cp "$root_dir/THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php" docs/thrivewell-parity/THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php
cp "$root_dir/routes.md" docs/thrivewell-parity/routes.md
cp "$root_dir/docs/official-laravel-inertia-parity-map.md" docs/thrivewell-parity/official-laravel-inertia-parity-map.md

cat > docs/thrivewell-parity/README.md <<'MSG'
# ThriveWell OS parity reference

This directory is copied from the zero-dependency bridge during the official Laravel/Inertia migration.
Do not delete the bridge or this parity reference until all feature parity tests pass.
MSG

printf 'Official package installation complete. Next manual steps:\n'
printf '  1. Port migrations, seeders, models, policies, controllers, and services from the bridge.\n'
printf '  2. Rebuild dashboard/onboarding/credential screens as Inertia React + TypeScript pages.\n'
printf '  3. Port tests/run.php assertions to Pest/PHPUnit feature tests.\n'
printf '  4. Run docs/official-laravel-inertia-parity-map.md as the acceptance checklist.\n'

popd >/dev/null

printf '\nOfficial Laravel/Inertia workspace prepared at %s.\n' "$migration_dir"
