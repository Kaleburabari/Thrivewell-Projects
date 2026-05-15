<?php
namespace App\Services;

class AuditLogger
{
    public static function record(?int $userId, string $action, string $type, ?int $id = null, array $metadata = []): void
    {
        Database::execute(
            'INSERT INTO audit_logs(user_id, action, auditable_type, auditable_id, metadata, created_at) VALUES(?,?,?,?,?,?)',
            [$userId, $action, $type, $id, json_encode($metadata, JSON_THROW_ON_ERROR), date('c')]
        );
    }
}
