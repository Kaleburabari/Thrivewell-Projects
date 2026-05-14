<?php
namespace App\Services;

class DashboardDataService
{
    public function intern(int $userId): array
    {
        $sessions = Database::table('SELECT * FROM counselling_sessions WHERE intern_id = ? ORDER BY starts_at ASC', [$userId]);
        $today = array_values(array_filter($sessions, fn ($s) => str_starts_with($s['starts_at'], '2026-05-14')));
        $recent = array_values(array_filter($sessions, fn ($s) => $s['status'] === 'completed'));
        $earnings = Database::table('SELECT period, amount_minor FROM earnings WHERE user_id = ? ORDER BY id ASC', [$userId]);
        $cpd = Database::table('SELECT status, COUNT(*) as count, AVG(progress) as progress FROM cpd_modules WHERE user_id = ? GROUP BY status', [$userId]);
        $notifications = Database::table('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5', [$userId]);
        $totalBonus = array_sum(array_column($earnings, 'amount_minor'));
        return [
            'kpis' => [
                ['label'=>'Sessions This Month','value'=>18,'trend'=>'+12%','tone'=>'purple'],
                ['label'=>'Hours Completed','value'=>'24.5','trend'=>'+15%','tone'=>'cyan'],
                ['label'=>'Listening Bonus','value'=>'₦127,500','trend'=>'+8%','tone'=>'amber'],
                ['label'=>'Revenue Share','value'=>'30%','trend'=>'Tier 1 • 70/30 Split','tone'=>'green'],
                ['label'=>'Client Rating','value'=>'4.9/5','trend'=>'Based on 32 reviews','tone'=>'blue'],
            ],
            'today' => $today,
            'recent' => $recent,
            'earnings' => $earnings,
            'wallet' => ['available'=>12750000,'total'=>$totalBonus,'pending'=>2850000,'withdrawn'=>21300000],
            'cpd' => $cpd,
            'notifications' => $notifications,
            'radar' => ['Empathy'=>4.9,'Communication'=>4.8,'Knowledge'=>4.7,'Reliability'=>4.9,'Professionalism'=>4.8],
        ];
    }
}
