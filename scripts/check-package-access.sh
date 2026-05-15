#!/usr/bin/env bash
set -u

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT_DIR"
# shellcheck source=scripts/package-access-env.sh
source scripts/package-access-env.sh

printf 'ThriveWell OS package access diagnostic\n'
printf '======================================\n\n'
printf 'Composer repository: %s\n' "${THRIVEWELL_COMPOSER_REPOSITORY_URL:-https://repo.packagist.org}"
printf 'npm registry: %s\n' "${npm_config_registry:-https://registry.npmjs.org}"
printf 'HTTPS proxy: %s\n\n' "${HTTPS_PROXY:-none}"

status=0

run_check() {
  local label="$1"
  shift
  printf 'Checking %s...\n' "$label"
  if "$@" >/tmp/thrivewell-package-check.out 2>&1; then
    printf '  PASS: %s is reachable.\n\n' "$label"
  else
    status=1
    printf '  BLOCKED: %s is not reachable.\n' "$label"
    sed 's/^/    /' /tmp/thrivewell-package-check.out | head -40
    printf '\n'
  fi
}

run_check 'Packagist / Laravel framework metadata' composer show laravel/framework --all --no-interaction
run_check 'npm / Inertia React metadata' npm view @inertiajs/react version

printf 'Required allowlist for official Laravel/Inertia migration:\n'
printf '  - repo.packagist.org\n'
printf '  - packagist.org\n'
printf '  - api.github.com\n'
printf '  - github.com\n'
printf '  - codeload.github.com\n'
printf '  - registry.npmjs.org\n\n'

if [ "$status" -ne 0 ]; then
  cat <<'MSG'
Package access is still blocked from this environment.

Recommended fixes:
  1. Ask the network/proxy owner to allow the hosts above.
  2. Or configure internal Composer/npm mirrors and export THRIVEWELL_COMPOSER_REPOSITORY_URL and THRIVEWELL_NPM_REGISTRY_URL.
  3. Or build the Laravel/Inertia lockfiles/artifacts in an internet-enabled environment and import them with THRIVEWELL_LARAVEL_ARTIFACT.

Until the checks pass, continue the master-spec fallback path in the current scaffold without reducing scope.
MSG
else
  cat <<'MSG'
Package access is available. You can proceed with the official Laravel + Inertia React migration.
MSG
fi

exit "$status"
