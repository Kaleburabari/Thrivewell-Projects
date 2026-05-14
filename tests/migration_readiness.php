<?php

function assert_ready($condition, $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: $message\n");
        exit(1);
    }
    echo "PASS: $message\n";
}

$root = dirname(__DIR__);
$checkScript = $root.'/scripts/check-package-access.sh';
$migrationScript = $root.'/scripts/migrate-to-official-laravel-inertia.sh';
$parityMap = $root.'/docs/official-laravel-inertia-parity-map.md';
$composer = json_decode(file_get_contents($root.'/composer.json'), true);
$package = json_decode(file_get_contents($root.'/package.json'), true);
$spec = file_get_contents($root.'/THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php');
$migrationScriptText = file_get_contents($migrationScript);
$parityText = file_get_contents($parityMap);

assert_ready(is_executable($checkScript), 'package access gate is executable');
assert_ready(is_executable($migrationScript), 'official migration script is executable');
assert_ready(isset($composer['scripts']['migrate:official']), 'composer exposes migrate:official command');
assert_ready(isset($package['scripts']['migrate:official']), 'npm exposes migrate:official command');
assert_ready(str_contains($migrationScriptText, 'laravel/laravel'), 'migration script creates an official Laravel project');
assert_ready(str_contains($migrationScriptText, 'inertiajs/inertia-laravel'), 'migration script installs official Inertia Laravel adapter');
assert_ready(str_contains($migrationScriptText, '@inertiajs/react'), 'migration script installs official Inertia React adapter');
assert_ready(str_contains($migrationScriptText, 'typescript'), 'migration script installs TypeScript tooling');
assert_ready(str_contains($migrationScriptText, '@vitejs/plugin-react'), 'migration script installs Vite React tooling');
assert_ready(str_contains($migrationScriptText, 'tailwindcss'), 'migration script installs Tailwind tooling');
assert_ready(str_contains($parityText, 'Port every assertion in `tests/run.php`'), 'parity map requires porting existing tests');
assert_ready(str_contains($parityText, 'Do not remove the bridge scaffold'), 'parity map protects existing scaffold until parity passes');
assert_ready(str_contains($spec, 'Do not reduce scope; only improve the implementation.'), 'master spec no-reduction directive remains present');

foreach (['Dashboard page', 'Multi-role onboarding', 'Credential vault', 'Admin credential queue'] as $requiredParityItem) {
    assert_ready(str_contains($parityText, $requiredParityItem), "$requiredParityItem parity is documented");
}

echo "Official Laravel/Inertia migration readiness checks passed.\n";
