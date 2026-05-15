<?php require __DIR__.'/../components/dashboard_components.php'; ?>
<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= h($_SESSION['csrf_token']) ?>">
    <title>Book a Session · ThriveWell OS</title>
    <link rel="stylesheet" href="/build/app.css">
    <script defer src="/build/app.js"></script>
</head>
<body>
<main class="booking-page">
    <section class="booking-hero card">
        <span class="eyebrow">Session booking</span>
        <h1>Choose support without pressure.</h1>
        <p>Discover verified counsellors, hold a time, confirm payment readiness, and keep reminders consent-aware. Kale matching is assistive only — you stay in control.</p>
        <div class="state-strip" aria-live="polite"><div class="success-state" hidden>Saved.</div><div class="error-state" hidden>Check details.</div></div>
        <form data-async-form data-confirm-hold-form action="/booking/confirm" method="post" hidden>
            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="booking_hold_id" value="">
            <input type="hidden" name="accessibility[]" value="captions">
            <input type="hidden" name="accessibility[]" value="low-bandwidth">
            <button type="submit">Confirm held session</button>
            <small>Confirmation prepares payment readiness and reminder timing. No charge is captured in this bridge.</small>
        </form>
        <a class="link-inline" href="/dashboard">← Back to dashboard</a>
    </section>

    <section class="card booking-filters">
        <div class="card-head"><h2>Calm filters</h2><span><?= h($booking['filters']['timezone']) ?></span></div>
        <form method="get" action="/booking">
            <label>Support focus<select name="focus"><option value="">All support areas</option><?php foreach ($booking['filters']['focus_options'] as $focus): ?><option value="<?= h($focus) ?>"><?= h($focus) ?></option><?php endforeach; ?></select></label>
            <button>Apply filters</button>
        </form>
        <p><?= h($booking['safety_note']) ?></p>
    </section>

    <section class="booking-grid">
        <?php foreach ($booking['profiles'] as $profile): ?>
            <article class="card counsellor-card">
                <div class="counsellor-head"><img src="<?= h($profile['avatar']) ?>" alt=""><div><span class="eyebrow"><?= h($profile['display_title']) ?></span><h2><?= h($profile['name']) ?></h2><p><?= h($profile['bio']) ?></p></div><?= status_pill($profile['badge_status'] ?: 'verified') ?></div>
                <div class="match-list"><?php foreach ($profile['ai_match_reasons'] as $reason): ?><span><?= h($reason) ?></span><?php endforeach; ?></div>
                <div class="slot-list">
                    <?php foreach ($profile['next_slots'] as $slot): ?>
                        <form data-async-form action="/booking/hold" method="post">
                            <input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>">
                            <input type="hidden" name="counsellor_profile_id" value="<?= h((string) $profile['id']) ?>">
                            <input type="hidden" name="starts_at" value="<?= h($slot['starts_at']) ?>">
                            <input type="hidden" name="timezone" value="<?= h($booking['filters']['timezone']) ?>">
                            <button type="submit"><strong><?= h(date('D, M j', strtotime($slot['starts_at']))) ?></strong><span><?= h(date('g:i A', strtotime($slot['starts_at']))) ?> · <?= h((string) $slot['duration_minutes']) ?> min</span></button>
                        </form>
                    <?php endforeach; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section class="card booking-list">
        <div class="card-head"><h2>Your bookings</h2><span><?= count($bookings) ?> sessions</span></div>
        <?php foreach ($bookings as $item): ?>
            <article class="booking-row"><div><strong><?= h($item['counsellor_name']) ?></strong><span><?= h(date('M j, g:i A', strtotime($item['starts_at']))) ?> · <?= money((int) $item['amount_minor']) ?> · <?= h($item['payment_status']) ?></span></div><?= status_pill($item['status']) ?><form data-async-form action="/booking/cancel" method="post"><input type="hidden" name="csrf_token" value="<?= h($_SESSION['csrf_token']) ?>"><input type="hidden" name="session_booking_id" value="<?= h((string) $item['id']) ?>"><input type="hidden" name="reason" value="Client cancelled from booking page."><button type="submit">Cancel gently</button></form></article>
        <?php endforeach; ?>
        <?php if (!$bookings) empty_state('No confirmed bookings yet', 'Hold a slot above, then confirm it from the calm confirmation card.'); ?>
    </section>
</main>
</body>
</html>
