<?php
namespace App\Services;

class DashboardDataService
{
    public function intern(int $userId): array
    {
        $sessions = Database::table('SELECT * FROM counselling_sessions WHERE intern_id = ? ORDER BY starts_at ASC', [$userId]);
        $today = array_values(array_filter($sessions, fn ($s) => str_starts_with($s['starts_at'], '2026-05-14')));
        $recent = array_values(array_filter($sessions, fn ($s) => $s['status'] === 'completed'));
        usort($recent, fn ($a, $b) => strcmp($b['starts_at'], $a['starts_at']));

        $earnings = Database::table('SELECT period, amount_minor FROM earnings WHERE user_id = ? ORDER BY id ASC', [$userId]);
        $cpd = Database::table('SELECT status, COUNT(*) as count, AVG(progress) as progress FROM cpd_modules WHERE user_id = ? GROUP BY status', [$userId]);
        $cpdModules = Database::table('SELECT * FROM cpd_modules WHERE user_id = ? ORDER BY id ASC', [$userId]);
        $notifications = Database::table('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5', [$userId]);
        $wellnessActions = Database::table('SELECT * FROM wellness_actions WHERE user_id = ? ORDER BY id ASC', [$userId]);
        $aiCards = Database::table('SELECT * FROM ai_companion_cards WHERE user_id = ? ORDER BY id ASC', [$userId]);
        $audit = Database::table('SELECT * FROM audit_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 6', [$userId]);
        $clients = Database::table('SELECT * FROM intern_clients WHERE intern_id = ? ORDER BY last_seen_at DESC LIMIT 6', [$userId]);
        $supervisionTasks = Database::table('SELECT * FROM supervision_tasks WHERE user_id = ? ORDER BY due_at ASC', [$userId]);
        $resources = Database::table('SELECT * FROM resource_library_items WHERE user_id = ? ORDER BY id ASC', [$userId]);
        $widgets = Database::table('SELECT * FROM dashboard_widgets WHERE user_id = ? AND enabled = 1 ORDER BY position ASC', [$userId]);
        $workflowNodes = Database::table('SELECT * FROM workflow_nodes WHERE user_id = ? ORDER BY x ASC', [$userId]);
        $formFields = Database::table('SELECT * FROM form_builder_fields WHERE user_id = ? ORDER BY sort_order ASC', [$userId]);
        $preferences = Database::row('SELECT * FROM dashboard_preferences WHERE user_id = ? LIMIT 1', [$userId]);
        $totalBonus = array_sum(array_column($earnings, 'amount_minor'));
        $masterSpec = (new MasterSpecService())->summary();

        return [
            'kpis' => [
                ['label' => 'Sessions This Month', 'value' => 18, 'trend' => '+12%', 'tone' => 'purple', 'icon' => '👥', 'description' => '4 booked today'],
                ['label' => 'Hours Completed', 'value' => '24.5', 'trend' => '+15%', 'tone' => 'cyan', 'icon' => '🕒', 'description' => 'Supervised practice'],
                ['label' => 'Listening Bonus', 'value' => '₦127,500', 'trend' => '+8%', 'tone' => 'amber', 'icon' => '💳', 'description' => 'Available balance'],
                ['label' => 'Revenue Share', 'value' => '30%', 'trend' => 'Tier 1 • 70/30 Split', 'tone' => 'green', 'icon' => '◔', 'description' => 'Upgrade path open'],
                ['label' => 'Client Rating', 'value' => '4.9/5', 'trend' => 'Based on 32 reviews', 'tone' => 'blue', 'icon' => '★', 'description' => 'Quality indicator'],
            ],
            'navigation' => [
                ['label' => 'Dashboard', 'icon' => '⌂', 'active' => true, 'badge' => null],
                ['label' => 'My Schedule', 'icon' => '◴', 'active' => false, 'badge' => null],
                ['label' => 'My Clients', 'icon' => '♧', 'active' => false, 'badge' => null],
                ['label' => 'Sessions', 'icon' => '◎', 'active' => false, 'badge' => null],
                ['label' => 'Session Notes', 'icon' => '▧', 'active' => false, 'badge' => null],
                ['label' => 'Listening Bonus', 'icon' => '◉', 'active' => false, 'badge' => 'New'],
                ['label' => 'Wallet & Payouts', 'icon' => '▤', 'active' => false, 'badge' => null],
                ['label' => 'Resources', 'icon' => '◇', 'active' => false, 'badge' => null],
                ['label' => 'Supervision', 'icon' => '♙', 'active' => false, 'badge' => null],
                ['label' => 'CPD & Training', 'icon' => '☑', 'active' => false, 'badge' => null],
                ['label' => 'Community', 'icon' => '♧', 'active' => false, 'badge' => '12'],
                ['label' => 'Messages', 'icon' => '✉', 'active' => false, 'badge' => '3'],
                ['label' => 'Settings', 'icon' => '⚙', 'active' => false, 'badge' => null],
            ],
            'today' => $today,
            'recent' => $recent,
            'earnings' => $earnings,
            'wallet' => ['available' => 12750000, 'total' => $totalBonus, 'pending' => 2850000, 'withdrawn' => 21300000],
            'cpd' => ['summary' => $cpd, 'modules' => $cpdModules, 'overall' => 65, 'completed' => 13, 'in_progress' => 5, 'remaining' => 7],
            'notifications' => $notifications,
            'wellness_actions' => $wellnessActions,
            'ai_cards' => $aiCards,
            'audit' => $audit,
            'clients' => $clients,
            'supervision_tasks' => $supervisionTasks,
            'resources' => $resources,
            'widgets' => $widgets,
            'workflow_nodes' => $workflowNodes,
            'form_fields' => $formFields,
            'privacy_metrics' => ['consent_coverage' => 92, 'audit_events' => count($audit), 'private_records' => 100, 'ai_review_required' => 2],
            'master_spec' => $masterSpec,
            'preferences' => $preferences,
            'radar' => ['Empathy' => 4.9, 'Communication' => 4.8, 'Knowledge' => 4.7, 'Reliability' => 4.9, 'Professionalism' => 4.8],
            'states' => [
                'loading' => 'Preparing a calm view of your practice day…',
                'empty' => 'No items need attention right now. Enjoy the quiet moment.',
                'error' => 'Something did not load. Nothing is lost; please try again.',
                'success' => 'Saved gently and securely.',
                'permission_denied' => 'This area contains sensitive information and needs permission.',
            ],
            'command_actions' => ['Open schedule', 'Start wellness check', 'Request supervisor', 'Mark notifications read', 'Trigger human crisis handoff', 'Open no-code dashboard builder', 'Preview consent form'],
        ];
    }
}
