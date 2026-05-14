<?php
function money(int $minor): string
{
    return '₦'.number_format($minor / 100, 2);
}

function h(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function status_pill(string $status): string
{
    return '<em class="pill '.h($status).'">'.h(ucfirst(str_replace('_', ' ', $status))).'</em>';
}

function stat_card(array $kpi): void
{
    ?>
    <article class="card stat <?= h($kpi['tone']) ?>" data-dashboard-card>
        <div class="stat-icon" aria-hidden="true"><?= h($kpi['icon']) ?></div>
        <span><?= h($kpi['label']) ?></span>
        <strong><?= h((string) $kpi['value']) ?></strong>
        <small><?= h($kpi['trend']) ?></small>
        <p><?= h($kpi['description']) ?></p>
        <svg viewBox="0 0 180 24" aria-hidden="true"><path d="M0 15 C15 5 20 25 35 14 S55 10 70 18 90 4 105 12 125 25 140 13 160 8 180 15"/></svg>
    </article>
    <?php
}

function empty_state(string $title, string $body): void
{
    ?>
    <div class="empty-state" role="status">
        <div aria-hidden="true">🌙</div>
        <strong><?= h($title) ?></strong>
        <p><?= h($body) ?></p>
    </div>
    <?php
}

function loading_skeleton(string $label): void
{
    ?>
    <div class="loading-skeleton" aria-label="<?= h($label) ?>">
        <span></span><span></span><span></span>
    </div>
    <?php
}

function audit_timeline(array $items): void
{
    ?>
    <ol class="audit-timeline">
        <?php foreach ($items as $item): ?>
            <li>
                <span></span>
                <div>
                    <strong><?= h(str_replace('_', ' ', $item['action'])) ?></strong>
                    <small><?= h(date('M j, g:i A', strtotime($item['created_at']))) ?></small>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php
}

function filter_bar(array $filters): void
{
    ?>
    <div class="filter-bar" role="search">
        <input placeholder="Search clients, sessions, resources" aria-label="Search dashboard">
        <?php foreach ($filters as $filter): ?><button type="button"><?= h($filter) ?></button><?php endforeach; ?>
    </div>
    <?php
}

function client_signal_card(array $client): void
{
    ?>
    <div class="client-signal-card <?= h($client['wellbeing_signal']) ?>">
        <img src="<?= h($client['avatar']) ?>" alt="">
        <div>
            <strong><?= h($client['name']) ?></strong>
            <span><?= h($client['focus_area']) ?></span>
            <small><?= h($client['next_step']) ?></small>
        </div>
        <em><?= h($client['wellbeing_signal']) ?></em>
    </div>
    <?php
}

function workflow_node(array $node): void
{
    ?>
    <div class="workflow-node <?= h($node['node_type']) ?>" style="--x: <?= h((string) $node['x']) ?>px; --y: <?= h((string) $node['y']) ?>px">
        <small><?= h($node['node_type']) ?></small>
        <strong><?= h($node['title']) ?></strong>
        <?= status_pill($node['status']) ?>
    </div>
    <?php
}

function form_builder_field(array $field): void
{
    ?>
    <label class="form-builder-field">
        <span><?= h($field['label']) ?><?= (int) $field['required'] === 1 ? ' *' : '' ?></span>
        <input placeholder="<?= h($field['field_type']) ?> field preview" disabled>
        <small><?= h($field['help_text']) ?></small>
    </label>
    <?php
}
