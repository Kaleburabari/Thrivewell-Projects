<?php
namespace App\Policies;

use App\Models\User;

class BookingPolicy
{
    public static function book(array $user): bool
    {
        return User::can($user, 'book_sessions');
    }

    public static function manageAvailability(array $user): bool
    {
        return User::can($user, 'manage_availability');
    }
}
