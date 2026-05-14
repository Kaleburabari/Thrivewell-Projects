<?php
namespace App\Policies;

use App\Models\User;

class DashboardPolicy
{
    public static function view(array $user): bool
    {
        return User::can($user, 'view_dashboard');
    }
}
