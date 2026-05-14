<?php
require __DIR__.'/../bootstrap/app.php';
use App\Services\Database;
use App\Models\User;
use App\Policies\DashboardPolicy;
use App\Services\DashboardDataService;

function assert_true($condition, $message) { if (!$condition) { fwrite(STDERR, "FAIL: $message\n"); exit(1); } echo "PASS: $message\n"; }
Database::fresh(); Database::migrate(); Database::seed();
$intern = User::findByEmail('intern@thrivewell.test');
assert_true((bool)$intern, 'demo intern exists');
assert_true(password_verify('password', $intern['password']), 'demo intern password verifies');
assert_true(DashboardPolicy::view($intern), 'intern can view dashboard');
$client = User::findByEmail('client@thrivewell.test');
assert_true(!DashboardPolicy::view($client), 'client is denied intern dashboard permission');
$data = (new DashboardDataService())->intern((int)$intern['id']);
assert_true(count($data['today']) === 4, 'seeded today schedule has four sessions');
assert_true(count($data['earnings']) === 6, 'seeded earnings chart has six periods');
assert_true($data['wallet']['available'] === 12750000, 'wallet stores money as integer minor units');
assert_true(count($data['notifications']) >= 5, 'seeded notifications are available');
echo "All ThriveWell OS foundation tests passed.\n";
