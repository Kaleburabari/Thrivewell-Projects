<?php
namespace App\Http\Controllers;

use App\Models\User;

class AuthController
{
    public function login(): void
    {
        view('login', ['error' => $_GET['error'] ?? null]);
    }

    public function authenticate(): void
    {
        $user = User::findByEmail($_POST['email'] ?? '');
        if ($user && password_verify($_POST['password'] ?? '', $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['user_email'] = $user['email'];
            header('Location: /dashboard');
            return;
        }
        header('Location: /login?error=calm');
    }

    public function register(): void
    {
        view('register');
    }

    public function logout(): void
    {
        session_destroy();
        header('Location: /login');
    }
}
