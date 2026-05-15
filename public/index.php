<?php
session_start();
require __DIR__.'/../bootstrap/app.php';

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CredentialController;
use App\Http\Controllers\DashboardApiController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnboardingController;
use App\Models\User;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

function view(string $template, array $data = []): void
{
    extract($data);
    require __DIR__.'/../resources/views/'.$template.'.php';
}

function current_user(): ?array
{
    return isset($_SESSION['user_email']) ? User::findByEmail($_SESSION['user_email']) : null;
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($payload, JSON_THROW_ON_ERROR);
}

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if ($path === '/') {
    header('Location: /dashboard');
    exit;
}

if ($path === '/login' && $method === 'GET') {
    (new AuthController())->login();
} elseif ($path === '/login' && $method === 'POST') {
    (new AuthController())->authenticate();
} elseif ($path === '/register' && $method === 'GET') {
    (new OnboardingController())->show();
} elseif ($path === '/onboarding/draft' && $method === 'POST') {
    (new OnboardingController())->saveDraft();
} elseif ($path === '/onboarding/complete' && $method === 'POST') {
    (new OnboardingController())->complete();
} elseif ($path === '/onboarding/success' && $method === 'GET') {
    (new OnboardingController())->success();
} elseif ($path === '/booking' && $method === 'GET') {
    (new BookingController())->index();
} elseif ($path === '/booking/data' && $method === 'GET') {
    (new BookingController())->data();
} elseif ($path === '/booking/hold' && $method === 'POST') {
    (new BookingController())->hold();
} elseif ($path === '/booking/confirm' && $method === 'POST') {
    (new BookingController())->confirm();
} elseif ($path === '/booking/reschedule' && $method === 'POST') {
    (new BookingController())->reschedule();
} elseif ($path === '/booking/cancel' && $method === 'POST') {
    (new BookingController())->cancel();
} elseif ($path === '/credentials' && $method === 'GET') {
    (new CredentialController())->vault();
} elseif ($path === '/credentials/submit' && $method === 'POST') {
    (new CredentialController())->submit();
} elseif ($path === '/credentials/signed-download' && $method === 'POST') {
    (new CredentialController())->signedDownload();
} elseif ($path === '/admin/credentials' && $method === 'GET') {
    (new CredentialController())->reviewQueue();
} elseif ($path === '/admin/credentials/approve' && $method === 'POST') {
    (new CredentialController())->approve();
} elseif ($path === '/admin/credentials/revision' && $method === 'POST') {
    (new CredentialController())->requestRevision();
} elseif ($path === '/logout') {
    (new AuthController())->logout();
} elseif ($path === '/dashboard' && $method === 'GET') {
    (new DashboardController())();
} elseif ($path === '/dashboard/data' && $method === 'GET') {
    (new DashboardApiController())->data();
} elseif ($path === '/dashboard/status' && $method === 'POST') {
    (new DashboardApiController())->status();
} elseif ($path === '/notifications/read' && $method === 'POST') {
    (new DashboardApiController())->notificationsRead();
} elseif ($path === '/crisis/handoff' && $method === 'POST') {
    (new DashboardApiController())->crisisHandoff();
} else {
    http_response_code(404);
    view('errors/404');
}
