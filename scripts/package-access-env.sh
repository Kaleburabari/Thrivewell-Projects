#!/usr/bin/env bash
# Shared package-access configuration for the official Laravel/Inertia migration.
# Source this file from migration/check scripts; do not execute it directly.

if [ -n "${THRIVEWELL_COMPOSER_REPOSITORY_URL:-}" ]; then
  export COMPOSER_HOME="${COMPOSER_HOME:-$(pwd)/.migration/composer-home}"
  mkdir -p "$COMPOSER_HOME"
  composer config --global repositories.packagist composer "$THRIVEWELL_COMPOSER_REPOSITORY_URL" >/dev/null
fi

if [ -n "${THRIVEWELL_NPM_REGISTRY_URL:-}" ]; then
  export npm_config_registry="$THRIVEWELL_NPM_REGISTRY_URL"
fi

if [ -n "${THRIVEWELL_HTTP_PROXY:-}" ]; then
  export HTTP_PROXY="$THRIVEWELL_HTTP_PROXY"
  export http_proxy="$THRIVEWELL_HTTP_PROXY"
  export npm_config_http_proxy="$THRIVEWELL_HTTP_PROXY"
fi

if [ -n "${THRIVEWELL_HTTPS_PROXY:-}" ]; then
  export HTTPS_PROXY="$THRIVEWELL_HTTPS_PROXY"
  export https_proxy="$THRIVEWELL_HTTPS_PROXY"
  export npm_config_https_proxy="$THRIVEWELL_HTTPS_PROXY"
fi
