<?php
session_start();
require __DIR__.'/../bootstrap/app.php';
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Models\User;

function view(string $template, array $data = []): void {
    extract($data);
    require __DIR__.'/../resources/views/'.$template.'.php';
}
function current_user(): ?array {
    return isset($_SESSION['user_email']) ? User::findByEmail($_SESSION['user_email']) : null;
}
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($path === '/') { header('Location: /dashboard'); exit; }
if ($path === '/login' && $method === 'GET') (new AuthController())->login();
elseif ($path === '/login' && $method === 'POST') (new AuthController())->authenticate();
elseif ($path === '/register') (new AuthController())->register();
elseif ($path === '/logout') (new AuthController())->logout();
elseif ($path === '/dashboard') (new DashboardController())();
else { http_response_code(404); view('errors/404'); }
