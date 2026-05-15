<?php
namespace App\Services;

class DashboardActionService
{
    public function setAvailability(int $userId, string $status): array
    {
        $allowed = ['available', 'busy', 'reflecting', 'offline'];
        if (!in_array($status, $allowed, true)) {
            return ['ok' => false, 'message' => 'Please choose a supported availability state.'];
        }

        Database::execute('UPDATE users SET status = ?, updated_at = ? WHERE id = ?', [$status, date('c'), $userId]);
        AuditLogger::record($userId, 'availability_updated', 'user', $userId, ['status' => $status]);

        return ['ok' => true, 'message' => 'Availability updated with care.', 'status' => $status];
    }

    public function markNotificationsRead(int $userId): array
    {
        Database::execute('UPDATE notifications SET read_at = ? WHERE user_id = ? AND read_at IS NULL', [date('c'), $userId]);
        AuditLogger::record($userId, 'notifications_marked_read', 'notification', null, ['scope' => 'dashboard']);

        return ['ok' => true, 'message' => 'Notifications marked as read.'];
    }

    public function createCrisisHandoff(int $userId, string $concern = 'dashboard_support_request'): array
    {
        Database::execute(
            'INSERT INTO crisis_incidents(user_id, concern, tier, status, human_review_required, created_at, updated_at) VALUES(?,?,?,?,?,?,?)',
            [$userId, $concern, 'supportive_review', 'open', 1, date('c'), date('c')]
        );
        $incidentId = (int) (Database::row('SELECT id FROM crisis_incidents WHERE user_id = ? ORDER BY id DESC LIMIT 1', [$userId])['id'] ?? 0);
        AuditLogger::record($userId, 'crisis_handoff_requested', 'crisis_incident', $incidentId, [
            'human_handoff_first' => true,
            'false_positive_review' => true,
        ]);

        return ['ok' => true, 'message' => 'A human support handoff has been queued.', 'incident_id' => $incidentId];
    }
}
