<?php
require __DIR__.'/../bootstrap/app.php';

use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Services\DashboardActionService;
use App\Services\DashboardDataService;
use App\Services\CredentialVerificationService;
use App\Services\Database;
use App\Services\MasterSpecService;
use App\Services\OnboardingService;

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
assert_true(User::can($intern, 'manage_credentials'), 'intern can manage credential vault');

$admin = User::findByEmail('admin@thrivewell.test');
assert_true(User::can($admin, 'review_credentials'), 'admin can review credential queue');
$counsellor = User::findByEmail('counsellor@thrivewell.test');
assert_true(User::can($counsellor, 'manage_credentials'), 'counsellor can manage credential vault');

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


$onboarding = new OnboardingService();
$draft = $onboarding->saveDraft([
    'email' => 'new.intern@thrivewell.test',
    'role' => 'intern',
    'current_step' => 2,
    'support_goal' => 'Learn safely while helping clients.',
]);
assert_true($draft['ok'] === true, 'multi-role onboarding draft can be saved');
$created = $onboarding->complete([
    'name' => 'New Intern',
    'preferred_name' => 'New',
    'email' => 'new.intern@thrivewell.test',
    'password' => 'safe-password',
    'role' => 'intern',
    'support_goal' => 'Learn safely while helping clients.',
    'accessibility' => ['reduced_motion', 'captions'],
    'consent_privacy' => '1',
]);
assert_true($created['ok'] === true, 'multi-role onboarding can complete');
$newUser = User::findByEmail('new.intern@thrivewell.test');
assert_true((bool) $newUser && DashboardPolicy::view($newUser), 'onboarded intern receives dashboard permission');
assert_true(count(Database::table('SELECT * FROM onboarding_profiles WHERE user_id = ?', [$newUser['id']])) === 1, 'onboarding profile is stored');
assert_true(count(Database::table('SELECT * FROM consent_records WHERE user_id = ? AND granted = 1', [$newUser['id']])) === 1, 'onboarding consent is recorded');
assert_true(count(Database::table('SELECT * FROM email_verification_tokens WHERE user_id = ?', [$newUser['id']])) === 1, 'email verification token is prepared');
assert_true(count(Database::table("SELECT * FROM audit_logs WHERE user_id = ? AND action = 'onboarding_completed'", [$newUser['id']])) === 1, 'onboarding completion is audited');


$credentials = new CredentialVerificationService();
$vault = $credentials->vaultFor((int) $intern['id']);
assert_true(count($vault['documents']) === 2, 'intern credential vault has seeded documents');
assert_true($vault['summary']['pending_review'] === 1, 'credential vault tracks pending review');
assert_true(count($credentials->reviewQueue()) >= 2, 'credential review queue is seeded');
$submittedCredential = $credentials->submitMetadata((int) $intern['id'], [
    'document_type' => 'degree',
    'title' => 'Updated counselling certificate',
    'storage_path' => 'private/credentials/updated-certificate.pdf',
]);
assert_true($submittedCredential['ok'] === true, 'credential metadata can be submitted to private vault');
$download = $credentials->signedDownloadToken((int) $intern['id'], (int) $submittedCredential['document']['id']);
assert_true($download['ok'] === true && strlen($download['token']) === 64, 'signed credential download token is generated');
$approved = $credentials->approve((int) $admin['id'], (int) $submittedCredential['document']['id'], 'Approved in test review.');
assert_true($approved['ok'] === true, 'credential review can approve submitted metadata');
$approvedDoc = Database::row('SELECT * FROM credential_documents WHERE id = ?', [$submittedCredential['document']['id']]);
assert_true($approvedDoc['status'] === 'verified', 'credential status updates to verified');
assert_true(count(Database::table('SELECT * FROM verification_badges WHERE user_id = ? AND status = ?', [$intern['id'], 'active'])) >= 1, 'credential approval creates active badge');
$revision = $credentials->requestRevision((int) $admin['id'], 2, 'Test revision request.');
assert_true($revision['ok'] === true, 'credential review can request revision');
assert_true(count(Database::table("SELECT * FROM audit_logs WHERE action LIKE 'credential_%'")) >= 4, 'credential actions are audited');

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

require __DIR__.'/migration_readiness.php';
