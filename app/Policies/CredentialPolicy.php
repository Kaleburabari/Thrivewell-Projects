<?php
namespace App\Policies;

use App\Models\User;

class CredentialPolicy
{
    public static function manage(array $user): bool
    {
        return User::can($user, 'manage_credentials');
    }

    public static function review(array $user): bool
    {
        return User::can($user, 'review_credentials');
    }

    public static function signedDownload(array $user, array $document): bool
    {
        return self::review($user) || ((int) $document['user_id'] === (int) $user['id'] && User::can($user, 'view_signed_credentials'));
    }
}
