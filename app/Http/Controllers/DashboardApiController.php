<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Services\DashboardActionService;
use App\Services\DashboardDataService;

class DashboardApiController
{
    public function data(): void
    {
        $user = \current_user();
        if (!$user || !DashboardPolicy::view($user)) {
            json_response(['ok' => false, 'message' => 'Permission needed.'], 403);
            return;
        }

        json_response(['ok' => true, 'dashboard' => (new DashboardDataService())->intern((int) $user['id'])]);
    }

    public function status(): void
    {
        $user = \current_user();
        if (!$user || !User::can($user, 'view_dashboard')) {
            json_response(['ok' => false, 'message' => 'Permission needed.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }

        json_response((new DashboardActionService())->setAvailability((int) $user['id'], $_POST['status'] ?? 'available'));
    }

    public function notificationsRead(): void
    {
        $user = \current_user();
        if (!$user || !User::can($user, 'view_dashboard')) {
            json_response(['ok' => false, 'message' => 'Permission needed.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }

        json_response((new DashboardActionService())->markNotificationsRead((int) $user['id']));
    }

    public function crisisHandoff(): void
    {
        $user = \current_user();
        if (!$user || !User::can($user, 'trigger_crisis_handoff')) {
            json_response(['ok' => false, 'message' => 'A human safety permission is required.'], 403);
            return;
        }
        if (!\verify_csrf()) {
            json_response(['ok' => false, 'message' => 'Your secure session token expired. Please refresh gently.'], 419);
            return;
        }

        json_response((new DashboardActionService())->createCrisisHandoff((int) $user['id'], $_POST['concern'] ?? 'dashboard_support_request'));
    }
}
