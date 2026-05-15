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
        return User::can($user, 'manage_booking_availability');
    }

    public static function cancel(array $user, array $booking): bool
    {
        return self::book($user) && (int) $booking['client_id'] === (int) $user['id'];
    }
}
