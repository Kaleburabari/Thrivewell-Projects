<?php
namespace App\Models;

use App\Services\Database;

class User
{
    public static function findByEmail(string $email): ?array
    {
        return Database::table('SELECT users.*, roles.name as role, roles.label as role_label FROM users JOIN roles ON roles.id = users.role_id WHERE email = ? AND deleted_at IS NULL LIMIT 1', [$email])[0] ?? null;
    }

    public static function demoIntern(): array
    {
        return self::findByEmail('intern@thrivewell.test');
    }

    public static function can(array $user, string $permission): bool
    {
        return (bool) Database::table('SELECT 1 FROM role_permission rp JOIN permissions p ON p.id = rp.permission_id WHERE rp.role_id = ? AND p.name = ? LIMIT 1', [$user['role_id'], $permission]);
    }
}
