<?php
namespace App\Policies;

use App\Models\User;

class VideoSessionPolicy
{
    public static function access(array $user): bool
    {
        return User::can($user, 'join_video_sessions');
    }

    public static function writeNotes(array $user, array $room): bool
    {
        return self::access($user) && (int) $user['id'] === (int) $room['counsellor_id'];
    }
}
