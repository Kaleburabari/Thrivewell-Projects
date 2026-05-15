<?php require __DIR__.'/components/dashboard_components.php'; ?>
<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= h($_SESSION['csrf_token']) ?>">
    <title>Intern Dashboard · ThriveWell OS</title>
    <link rel="stylesheet" href="/build/app.css">
    <script defer src="/build/app.js"></script>
</head>
<body>
<div class="ambient"></div>
<div class="app-shell">
    <aside class="sidebar" aria-label="Primary navigation">
        <div class="logo">
            <span class="leaf" aria-hidden="true">✦</span>
            <div><strong>ThriveWell</strong><small>Intern Portal</small></div>
        </div>
        <nav>
            <?php foreach ($data['navigation'] as $item): ?>
                <a class="<?= $item['active'] ? 'active' : '' ?>" href="#">
                    <span aria-hidden="true"><?= h($item['icon']) ?></span><?= h($item['label']) ?>
                    <?php if ($item['badge']): ?><b><?= h($item['badge']) ?></b><?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>
        <section class="upgrade">
            <strong>⭐ Upgrade to Tier 2</strong>
            <p>Unlock higher earnings, advanced supervision pathways, and a 35% revenue share.</p>
            <button type="button">View Requirements</button>
        </section>
        <section class="support">
            <span aria-hidden="true">🎧</span>
            <strong>Need Support?</strong>
            <p>We're here for you<br><a href="mailto:support@thrivewell.org">support@thrivewell.org</a></p>
        </section>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <p class="eyebrow">Thursday, May 14, 2026</p>
                <h1>Good morning, <?= h(explode(' ', $user['name'])[0]) ?>! 👋</h1>
                <p>You're making a real difference in someone's life today.</p>
            </div>
            <div class="top-actions">
                <form data-async-form action="/dashboard/status" method="post" class="status-form">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <select name="status" aria-label="Availability status">
                        <?php foreach (['available' => 'Available for Sessions', 'busy' => 'In Session', 'reflecting' => 'Reflecting', 'offline' => 'Offline'] as $value => $label): ?>
                            <option value="<?= h($value) ?>" <?= $user['status'] === $value ? 'selected' : '' ?>><?= h($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="status-dot" type="submit">● Update</button>
                </form>
                <form data-async-form action="/notifications/read" method="post">
                    <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                    <button class="bell" type="submit" aria-label="Mark notifications read">♢<sup><?= count($data['notifications']) ?></sup></button>
                </form>
                <button class="profile" type="button"><img src="<?= h($user['avatar']) ?>" alt="<?= h($user['name']) ?>"><span><?= h($user['name']) ?><small><?= h($user['role_label']) ?> (Tier 1)</small></span>⌄</button>
                <button id="themeToggle" type="button" aria-label="Toggle theme">☾</button>
                <button id="commandToggle" type="button" aria-label="Open command palette">⌘K</button>
                <a class="logout" href="/logout">Logout</a>
            </div>
        </header>

        <section class="command-palette" id="commandPalette" hidden>
            <label>Search ThriveWell actions<input id="commandSearch" placeholder="Try: supervisor, crisis, schedule"></label>
            <div>
                <?php foreach ($data['command_actions'] as $action): ?><button type="button"><?= h($action) ?></button><?php endforeach; ?>
            </div>
        </section>

        <section class="state-strip" aria-live="polite">
            <div class="success-state">✓ <?= h($data['states']['success']) ?></div>
            <div class="error-state" hidden><?= h($data['states']['error']) ?></div>
            <?php loading_skeleton($data['states']['loading']); ?>
        </section>

        <?php filter_bar(['Today', 'High priority', 'Consent needed', 'AI review', 'Supervision']); ?>

        <section class="kpi-grid" aria-label="Dashboard key metrics">
            <?php foreach ($data['kpis'] as $kpi) stat_card($kpi); ?>
        </section>

        <section class="dashboard-grid">
            <article class="card wide schedule-card">
                <div class="card-head"><h2>Today's Schedule</h2><button type="button">View Full Schedule</button></div>
                <?php if (!$data['today']) empty_state('No sessions today', $data['states']['empty']); ?>
                <?php foreach ($data['today'] as $s): ?>
                    <div class="schedule-row">
                        <time><?= h(date('g:i A', strtotime($s['starts_at']))) ?><small><?= h($s['duration_minutes']) ?> min</small></time>
                        <img src="<?= h($s['client_avatar']) ?>" alt="">
                        <div><strong><?= h($s['client_name']) ?></strong><span><?= h($s['topic']) ?></span></div>
                        <?= status_pill($s['status']) ?>
                        <button type="button" aria-label="Open secure video room">▣</button>
                    </div>
                <?php endforeach; ?>
                <a class="link" href="#">View Full Schedule →</a>
            </article>

            <article class="card recent-card">
                <div class="card-head"><h2>Recent Sessions</h2><button type="button">View All⌄</button></div>
                <?php foreach (array_slice($data['recent'], 0, 4) as $s): ?>
                    <div class="recent-row">
                        <img src="<?= h($s['client_avatar']) ?>" alt="">
                        <div><strong><?= h($s['client_name']) ?></strong><span><?= h($s['topic']) ?></span></div>
                        <time><?= h(date('M j, Y', strtotime($s['starts_at']))) ?><small><?= h(date('g:i A', strtotime($s['starts_at']))) ?></small></time>
                        <b>★ <?= h((string) $s['rating']) ?></b>
                    </div>
                <?php endforeach; ?>
                <a class="link" href="#">View All Sessions →</a>
            </article>

            <article class="card wallet">
                <div class="card-head"><h2>Listening Bonus</h2><button type="button">This Month⌄</button></div>
                <strong><?= money($data['wallet']['available']) ?></strong>
                <span>Available Balance ◉</span>
                <button type="button">Withdraw Funds</button>
                <dl><dt>Total Earned</dt><dd><?= money(15600000) ?></dd><dt>Pending Payout</dt><dd><?= money($data['wallet']['pending']) ?></dd><dt>Total Withdrawn</dt><dd><?= money($data['wallet']['withdrawn']) ?></dd></dl>
                <a class="link" href="#">View Earnings Breakdown →</a>
            </article>

            <article class="card chart">
                <div class="card-head"><h2>Earnings Overview</h2><button type="button">This Year⌄</button></div>
                <div class="area-chart"><?php foreach ($data['earnings'] as $e): ?><span style="--h:<?= max(20, $e['amount_minor'] / 160000) ?>px" data-label="<?= h($e['period']) ?>" title="<?= money((int) $e['amount_minor']) ?>"></span><?php endforeach; ?></div>
            </article>

            <article class="card chart">
                <div class="card-head"><h2>Performance Overview</h2><small>━ You &nbsp; -- Average</small></div>
                <div class="radar"><div>Empathy<br><b>4.9</b></div><svg viewBox="0 0 200 200" aria-hidden="true"><polygon points="100,20 174,75 146,164 54,164 26,75"/><polygon class="fill" points="100,38 160,82 135,150 65,150 40,82"/></svg><div class="radar-labels">Professionalism 4.8 · Communication 4.8 · Reliability 4.9 · Knowledge 4.7</div></div>
            </article>

            <article class="card cpd">
                <div class="card-head"><h2>CPD Progress</h2><button type="button">View CPD Hub</button></div>
                <div class="ring" style="--pct:<?= h((string) $data['cpd']['overall']) ?>"><b><?= h((string) $data['cpd']['overall']) ?>%</b><span>Overall Progress</span></div>
                <dl><dt>Completed</dt><dd><?= h((string) $data['cpd']['completed']) ?> Modules</dd><dt>In Progress</dt><dd><?= h((string) $data['cpd']['in_progress']) ?> Modules</dd><dt>Remaining</dt><dd><?= h((string) $data['cpd']['remaining']) ?> Modules</dd></dl>
                <a class="link" href="#">Continue Learning →</a>
            </article>

            <article class="card ai-panel">
                <div class="card-head"><h2>Kale AI Companion</h2><span>Safety-first</span></div>
                <?php foreach ($data['ai_cards'] as $card): ?>
                    <div class="ai-card"><strong><?= h($card['title']) ?></strong><p><?= h($card['body']) ?></p><small><?= h($card['safety_label']) ?></small></div>
                <?php endforeach; ?>
            </article>

            <article class="card data-table-pro">
                <div class="card-head"><h2>CPD Module Tracker</h2><input placeholder="Filter modules" data-table-filter></div>
                <table><thead><tr><th>Module</th><th>Status</th><th>Progress</th></tr></thead><tbody>
                <?php foreach ($data['cpd']['modules'] as $module): ?><tr><td><?= h($module['title']) ?></td><td><?= status_pill($module['status']) ?></td><td><progress max="100" value="<?= h((string) $module['progress']) ?>"></progress></td></tr><?php endforeach; ?>
                </tbody></table>
            </article>

            <article class="card audit-card">
                <div class="card-head"><h2>Audit Timeline</h2><span>Consent-aware</span></div>
                <?php audit_timeline($data['audit']); ?>
            </article>

            <article class="card clients-panel">
                <div class="card-head"><h2>Client Signal Board</h2><span>Consent-aware</span></div>
                <?php foreach ($data['clients'] as $client): ?>
                    <?php client_signal_card($client); ?>
                <?php endforeach; ?>
            </article>

            <article class="card supervision-panel">
                <div class="card-head"><h2>Supervision Queue</h2><button type="button">Request Review</button></div>
                <?php foreach ($data['supervision_tasks'] as $task): ?>
                    <div class="task-row <?= h($task['priority']) ?>">
                        <div><strong><?= h($task['title']) ?></strong><span><?= h($task['supervisor']) ?></span></div>
                        <time><?= h(date('M j, g:i A', strtotime($task['due_at']))) ?></time>
                        <?= status_pill($task['status']) ?>
                    </div>
                <?php endforeach; ?>
            </article>

            <article class="card resources-panel">
                <div class="card-head"><h2>Resource Library</h2><button type="button">Open Hub</button></div>
                <?php foreach ($data['resources'] as $resource): ?>
                    <div class="resource-row">
                        <strong><?= h($resource['title']) ?></strong>
                        <p><?= h($resource['description']) ?></p>
                        <small><?= h($resource['category']) ?> • <?= h((string) $resource['reading_minutes']) ?> min • <?= h($resource['accessibility_tags']) ?></small>
                    </div>
                <?php endforeach; ?>
            </article>

            <article class="card privacy-panel">
                <div class="card-head"><h2>Privacy & Safety Index</h2><span>Live posture</span></div>
                <div class="privacy-metrics">
                    <div><strong><?= h((string) $data['privacy_metrics']['consent_coverage']) ?>%</strong><span>Consent coverage</span></div>
                    <div><strong><?= h((string) $data['privacy_metrics']['private_records']) ?>%</strong><span>Private records</span></div>
                    <div><strong><?= h((string) $data['privacy_metrics']['ai_review_required']) ?></strong><span>AI review items</span></div>
                </div>
            </article>

            <article class="card no-code-canvas">
                <div class="card-head"><h2>No-Code Workflow Canvas</h2><button type="button">Preview Builder</button></div>
                <div class="canvas">
                    <?php foreach ($data['workflow_nodes'] as $node): ?>
                        <?php workflow_node($node); ?>
                    <?php endforeach; ?>
                </div>
            </article>

            <article class="card form-builder">
                <div class="card-head"><h2>Consent Form Builder</h2><button type="button">Safe Edit Mode</button></div>
                <div class="form-field-grid">
                    <?php foreach ($data['form_fields'] as $field): ?>
                        <?php form_builder_field($field); ?>
                    <?php endforeach; ?>
                </div>
            </article>


            <article class="card master-spec-panel">
                <div class="card-head"><h2>Master Spec Compliance</h2><span><?= h((string) $data['master_spec']['coverage_percent']) ?>% read coverage</span></div>
                <p class="spec-source">Source: <?= h($data['master_spec']['source_path']) ?> · <?= h(substr($data['master_spec']['checksum'], 0, 12)) ?></p>
                <div class="spec-coverage">
                    <?php foreach ($data['master_spec']['coverage'] as $label => $covered): ?>
                        <span class="<?= $covered ? 'covered' : 'missing' ?>"><?= h(str_replace('_', ' ', $label)) ?></span>
                    <?php endforeach; ?>
                </div>
                <ol class="phase-list">
                    <?php foreach (array_slice($data['master_spec']['phases'], 0, 6) as $phase): ?>
                        <li><strong>Phase <?= h((string) $phase['phase']) ?></strong><span><?= h($phase['title']) ?></span></li>
                    <?php endforeach; ?>
                </ol>
                <div class="directive-list">
                    <?php foreach ($data['master_spec']['directives'] as $directive): ?><p>✓ <?= h($directive) ?></p><?php endforeach; ?>
                </div>
            </article>

        </section>

        <section class="care-strip">
            <div>💚 <strong>You're changing lives!</strong><span>Remember to take care of your wellbeing too.</span></div>
            <?php foreach ($data['wellness_actions'] as $action): ?><button type="button" title="<?= h($action['description']) ?>"><?= h($action['title']) ?></button><?php endforeach; ?>
        </section>

        <section class="consent-banner card">
            <strong>Consent & privacy:</strong> Your dashboard uses role-based permissions, audit logs, private-by-default clinical posture, and consent version <?= h($user['consent_version']) ?>.
        </section>

        <form class="crisis-card card" data-async-form action="/crisis/handoff" method="post">
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="concern" value="dashboard_support_request">
            <div><strong>Need help now?</strong><p>Crisis escalation is human handoff first, tiered, audited, and reviewed for false positives.</p></div>
            <button type="submit">Request Human Support</button>
        </form>
    </main>
</div>
<nav class="mobile-nav" aria-label="Mobile navigation"><a href="#">⌂<span>Home</span></a><a href="#">◴<span>Schedule</span></a><a href="#">◉<span>Bonus</span></a><a href="#">☑<span>CPD</span></a></nav>
</body>
</html>
