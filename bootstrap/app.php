<?php
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (str_starts_with($class, $prefix)) {
        $path = __DIR__.'/../app/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
        if (file_exists($path)) require $path;
    }
});
if (file_exists(__DIR__.'/../.env')) {
    foreach (file(__DIR__.'/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (!str_contains($line, '=') || str_starts_with(trim($line), '#')) continue;
        [$key, $value] = explode('=', $line, 2);
        $_ENV[$key] = trim($value, "\"'");
    }
}
