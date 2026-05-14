#!/usr/bin/env bash
set -u

printf 'ThriveWell OS package access diagnostic\n'
printf '======================================\n\n'

status=0
probe_dir="${TMPDIR:-/tmp}/thrivewell-package-access"
mkdir -p "$probe_dir"
composer_probe="$probe_dir/composer-probe.json"
composer_home="$probe_dir/composer-home"
npm_cache="$probe_dir/npm-cache"
mkdir -p "$composer_home" "$npm_cache"

composer_repository_url="${THRIVEWELL_COMPOSER_REPOSITORY_URL:-${COMPOSER_REPOSITORY_URL:-https://repo.packagist.org}}"
npm_registry_url="${THRIVEWELL_NPM_REGISTRY_URL:-${NPM_CONFIG_REGISTRY:-https://registry.npmjs.org}}"

cat > "$composer_probe" <<JSON
{
  "name": "thrivewell/package-access-probe",
  "description": "Temporary package access probe for official Laravel/Inertia migration.",
  "type": "project",
  "repositories": [
    {"type": "composer", "url": "$composer_repository_url"}
  ],
  "require": {}
}
JSON

print_environment() {
  printf 'Composer repository probe: %s\n' "$composer_repository_url"
  printf 'npm registry probe:        %s\n' "$npm_registry_url"
  if [ -n "${HTTPS_PROXY:-${https_proxy:-}}" ]; then
    printf 'HTTPS proxy detected:      %s\n' "${HTTPS_PROXY:-${https_proxy:-}}"
  fi
  if [ -n "${HTTP_PROXY:-${http_proxy:-}}" ]; then
    printf 'HTTP proxy detected:       %s\n' "${HTTP_PROXY:-${http_proxy:-}}"
  fi
  printf '\n'
}

run_check() {
  local label="$1"
  shift
  printf 'Checking %s...\n' "$label"
  if "$@" >"$probe_dir/check.out" 2>&1; then
    printf '  PASS: %s is reachable.\n\n' "$label"
  else
    status=1
    printf '  BLOCKED: %s is not reachable.\n' "$label"
    sed 's/^/    /' "$probe_dir/check.out" | head -50
    printf '\n'
  fi
}

print_environment

run_check 'Packagist or configured Composer mirror / Laravel framework metadata' \
  env COMPOSER="$composer_probe" COMPOSER_HOME="$composer_home" composer show laravel/framework --all --no-interaction

run_check 'npm registry or configured npm mirror / Inertia React metadata' \
  npm --cache "$npm_cache" --registry "$npm_registry_url" view @inertiajs/react version

run_check 'GitHub source archives for Laravel ecosystem packages' \
  git ls-remote https://github.com/laravel/laravel.git HEAD

printf 'Required allowlist for official Laravel/Inertia migration:\n'
printf '  - repo.packagist.org or your THRIVEWELL_COMPOSER_REPOSITORY_URL mirror\n'
printf '  - packagist.org\n'
printf '  - api.github.com\n'
printf '  - github.com\n'
printf '  - codeload.github.com\n'
printf '  - registry.npmjs.org or your THRIVEWELL_NPM_REGISTRY_URL mirror\n\n'

if [ "$status" -ne 0 ]; then
  cat <<'MSG'
Package access is still blocked from this environment.

Recommended fixes:
  1. Ask the network/proxy owner to allow the hosts above.
  2. Or configure internal mirrors and export:
       THRIVEWELL_COMPOSER_REPOSITORY_URL=https://your-composer-mirror.example
       THRIVEWELL_NPM_REGISTRY_URL=https://your-npm-mirror.example
  3. Or build the Laravel/Inertia lockfiles/artifacts in an internet-enabled environment and import them.

The official migration script intentionally refuses to rewrite the scaffold until these checks pass,
because feature parity cannot be preserved safely without official Laravel/Inertia packages.
MSG
else
  cat <<'MSG'
Package access is available. You can proceed with:
  bash scripts/migrate-to-official-laravel-inertia.sh
MSG
fi

exit "$status"
