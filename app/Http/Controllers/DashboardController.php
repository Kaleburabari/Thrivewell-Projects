<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Services\DashboardDataService;

class DashboardController
{
    public function __invoke(): void
    {
        $user = \current_user();
        if (!$user) { header('Location: /login'); return; }
        if (!DashboardPolicy::view($user)) { http_response_code(403); view('errors/403', ['user' => $user]); return; }
        $data = (new DashboardDataService())->intern((int) $user['id']);
        view('dashboard', ['user' => $user, 'data' => $data]);
    }
}
