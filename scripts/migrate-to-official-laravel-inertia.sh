#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
MIGRATION_DIR="${THRIVEWELL_LARAVEL_MIGRATION_DIR:-$ROOT_DIR/.migration/official-laravel-inertia}"
APP_NAME="${THRIVEWELL_LARAVEL_APP_NAME:-thrivewell-os}"

cd "$ROOT_DIR"
# shellcheck source=scripts/package-access-env.sh
source scripts/package-access-env.sh

printf 'ThriveWell OS official Laravel + Inertia migration\n'
printf '=================================================\n\n'

if [ -n "$(git status --porcelain -- . ":!.migration")" ]; then
  cat <<'MSG'
Your working tree has uncommitted changes. Commit or stash them before running the official migration so the zero-dependency scaffold remains a clean parity reference.
MSG
  exit 2
fi

if [ -n "${THRIVEWELL_LARAVEL_ARTIFACT:-}" ]; then
  printf '1/5 Importing official Laravel/Inertia artifact...\n'
  if [ ! -f "$THRIVEWELL_LARAVEL_ARTIFACT" ]; then
    printf 'Artifact not found: %s\n' "$THRIVEWELL_LARAVEL_ARTIFACT"
    exit 1
  fi
  rm -rf "$MIGRATION_DIR"
  mkdir -p "$MIGRATION_DIR/$APP_NAME"
  tar -xzf "$THRIVEWELL_LARAVEL_ARTIFACT" -C "$MIGRATION_DIR/$APP_NAME" --strip-components=1
  cd "$MIGRATION_DIR/$APP_NAME"
  npm run build
else
  printf '1/5 Checking package registry access...\n'
  if ! bash scripts/check-package-access.sh; then
    cat <<'MSG'

Migration stopped before modifying files because Composer/npm package access is blocked.
To unblock the migration, allow the hosts printed above, configure internal mirrors with THRIVEWELL_COMPOSER_REPOSITORY_URL and THRIVEWELL_NPM_REGISTRY_URL, or import a prebuilt artifact with THRIVEWELL_LARAVEL_ARTIFACT.
MSG
    exit 1
  fi

  printf '\n2/5 Preparing migration workspace at %s...\n' "$MIGRATION_DIR"
  rm -rf "$MIGRATION_DIR"
  mkdir -p "$MIGRATION_DIR"

  printf '\n3/5 Creating official Laravel application...\n'
  composer create-project laravel/laravel "$MIGRATION_DIR/$APP_NAME" --no-interaction
  cd "$MIGRATION_DIR/$APP_NAME"

  printf '\n4/5 Installing official Inertia React TypeScript starter stack...\n'
  composer require laravel/breeze --dev --no-interaction
  php artisan breeze:install react --typescript --no-interaction
  npm install
  npm run build
fi

printf '\n5/5 Capturing parity reference files...\n'
mkdir -p "$MIGRATION_DIR/parity-reference"
cp "$ROOT_DIR/THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php" "$MIGRATION_DIR/parity-reference/"
cp "$ROOT_DIR/tests/run.php" "$MIGRATION_DIR/parity-reference/zero-dependency-tests.php"
cp "$ROOT_DIR/routes.md" "$MIGRATION_DIR/parity-reference/"
cp "$ROOT_DIR/docs/laravel-inertia-parity-map.md" "$MIGRATION_DIR/parity-reference/"

cat <<MSG

Official Laravel + Inertia React TypeScript application created at:
  $MIGRATION_DIR/$APP_NAME

Next manual porting steps:
  1. Convert the current SQLite schema into Laravel migrations.
  2. Port controllers/services to Laravel controllers, form requests, policies, resources, jobs, and notifications.
  3. Rebuild dashboard/onboarding/credential screens as Inertia React TypeScript pages.
  4. Port parity assertions from tests/run.php into Pest/PHPUnit feature tests.
  5. Do not remove the bridge scaffold until parity tests pass.
MSG
