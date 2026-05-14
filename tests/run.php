<?php
require __DIR__.'/../bootstrap/app.php';

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Services\DashboardActionService;
use App\Services\DashboardDataService;
use App\Services\Database;
use App\Services\MasterSpecService;

function assert_true($condition, $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: $message\n");
        exit(1);
    }
    echo "PASS: $message\n";
}

Database::fresh();
Database::migrate();
Database::seed();

$intern = User::findByEmail('intern@thrivewell.test');
assert_true((bool) $intern, 'demo intern exists');
assert_true(password_verify('password', $intern['password']), 'demo intern password verifies');
assert_true(DashboardPolicy::view($intern), 'intern can view dashboard');
assert_true(User::can($intern, 'trigger_crisis_handoff'), 'intern can request human crisis handoff');

$client = User::findByEmail('client@thrivewell.test');
assert_true(!DashboardPolicy::view($client), 'client is denied intern dashboard permission');

$data = (new DashboardDataService())->intern((int) $intern['id']);
assert_true(count($data['today']) === 4, 'seeded today schedule has four sessions');
assert_true(count($data['earnings']) === 6, 'seeded earnings chart has six periods');
assert_true($data['wallet']['available'] === 12750000, 'wallet stores money as integer minor units');
assert_true(count($data['notifications']) >= 5, 'seeded notifications are available');
assert_true(count($data['ai_cards']) === 2, 'Kale AI safety cards are seeded');
assert_true(count($data['wellness_actions']) === 3, 'wellness action cards are seeded');
assert_true((int) $data['cpd']['overall'] === 65, 'CPD overall progress is available');
assert_true(count($data['clients']) === 4, 'client signal board is seeded');
assert_true(count($data['supervision_tasks']) === 3, 'supervision queue is seeded');
assert_true(count($data['resources']) === 3, 'resource library is seeded');
assert_true(count($data['widgets']) === 4, 'dashboard widget builder data is seeded');
assert_true(count($data['workflow_nodes']) === 4, 'workflow canvas nodes are seeded');
assert_true(count($data['form_fields']) === 4, 'consent form builder fields are seeded');
assert_true($data['privacy_metrics']['private_records'] === 100, 'privacy metrics are available');
assert_true($data['master_spec']['coverage_percent'] === 100, 'master specification directives are read into dashboard data');
assert_true(count($data['master_spec']['phases']) === 11, 'master specification build phases are detected');

$spec = (new MasterSpecService())->summary();
assert_true($spec['coverage']['no_reduction'] === true, 'master specification enforces improvement-only work');
assert_true(str_contains((new MasterSpecService())->text(), 'Kale AI Companion must never claim to replace therapy'), 'master specification includes Kale AI safety boundary');

$actions = new DashboardActionService();
$status = $actions->setAvailability((int) $intern['id'], 'reflecting');
assert_true($status['ok'] === true, 'availability status can be updated');
assert_true(User::findByEmail('intern@thrivewell.test')['status'] === 'reflecting', 'availability update persists');

$read = $actions->markNotificationsRead((int) $intern['id']);
assert_true($read['ok'] === true, 'notifications can be marked read');
$unread = Database::table('SELECT * FROM notifications WHERE user_id = ? AND read_at IS NULL', [$intern['id']]);
assert_true(count($unread) === 0, 'notification read state persists');

$incident = $actions->createCrisisHandoff((int) $intern['id']);
assert_true($incident['ok'] === true, 'human crisis handoff can be requested');
$incidentRows = Database::table('SELECT * FROM crisis_incidents WHERE user_id = ?', [$intern['id']]);
assert_true(count($incidentRows) === 1 && (int) $incidentRows[0]['human_review_required'] === 1, 'crisis handoff requires human review');

echo "All ThriveWell OS full dashboard tests passed.\n";
