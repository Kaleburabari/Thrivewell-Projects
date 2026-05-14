<?php $old = $old ?? []; $errors = $errors ?? []; ?>
<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
    <title>Onboarding · ThriveWell OS</title>
    <link rel="stylesheet" href="/build/app.css">
    <script defer src="/build/app.js"></script>
</head>
<body class="onboarding-body">
<main class="onboarding-shell">
    <section class="onboarding-hero card">
        <span class="eyebrow">Version 1.0 foundation</span>
        <h1>Begin safely with ThriveWell OS</h1>
        <p>Progressive onboarding keeps the first trust moment calm, consent-first, accessible, and role-aware.</p>
        <div class="onboarding-steps" aria-label="Onboarding steps">
            <?php foreach ($steps as $index => $step): ?>
                <article>
                    <b><?= $index + 1 ?></b>
                    <div><strong><?= htmlspecialchars($step['title'], ENT_QUOTES) ?></strong><span><?= htmlspecialchars($step['purpose'], ENT_QUOTES) ?></span></div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="onboarding-form card">
        <div class="card-head"><h2>Create your calm account</h2><a class="link-inline" href="/login">I already have access</a></div>
        <?php if ($message): ?><div class="state error"><?= htmlspecialchars($message, ENT_QUOTES) ?></div><?php endif; ?>
        <?php if ($errors): ?><div class="state error"><?php foreach ($errors as $error): ?><p><?= htmlspecialchars($error, ENT_QUOTES) ?></p><?php endforeach; ?></div><?php endif; ?>
        <div class="state-strip" aria-live="polite"><div class="success-state" hidden>Draft saved.</div><div class="error-state" hidden>Something needs attention.</div></div>
        <form method="post" action="/onboarding/complete" data-onboarding-form>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES) ?>">
            <input type="hidden" name="current_step" value="5">

            <label>Role pathway
                <select name="role" required>
                    <?php foreach ($roles as $value => $role): ?>
                        <option value="<?= htmlspecialchars($value, ENT_QUOTES) ?>" <?= ($old['role'] ?? 'client') === $value ? 'selected' : '' ?>><?= htmlspecialchars($role['label'].' — '.$role['description'], ENT_QUOTES) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>

            <div class="form-grid-two">
                <label>Full name<input name="name" value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES) ?>" required minlength="2" autocomplete="name"></label>
                <label>Preferred name<input name="preferred_name" value="<?= htmlspecialchars($old['preferred_name'] ?? '', ENT_QUOTES) ?>" placeholder="What should we call you?"></label>
            </div>

            <div class="form-grid-two">
                <label>Email<input name="email" type="email" value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES) ?>" required autocomplete="email"></label>
                <label>Password<input name="password" type="password" required minlength="8" autocomplete="new-password" placeholder="At least 8 characters"></label>
            </div>

            <label>Support goal
                <textarea name="support_goal" rows="4" placeholder="Share only what feels useful. You can edit this later."><?= htmlspecialchars($old['support_goal'] ?? '', ENT_QUOTES) ?></textarea>
            </label>

            <fieldset class="accessibility-box">
                <legend>Accessibility and sensory preferences</legend>
                <?php foreach (['reduced_motion' => 'Reduced motion', 'high_contrast' => 'High contrast', 'captions' => 'Captions', 'sensory_safe' => 'Sensory-safe layout', 'large_text' => 'Large text', 'interpreter_support' => 'Interpreter support'] as $value => $label): ?>
                    <label><input type="checkbox" name="accessibility[]" value="<?= $value ?>" <?= in_array($value, (array)($old['accessibility'] ?? []), true) ? 'checked' : '' ?>> <?= $label ?></label>
                <?php endforeach; ?>
            </fieldset>

            <section class="consent-check card">
                <strong>Consent-first privacy</strong>
                <p>We store onboarding details to create the right dashboard, protect sensitive spaces, and honour AI safety boundaries. Kale AI never replaces therapy, diagnosis, medication advice, or human crisis support.</p>
                <label><input type="checkbox" name="consent_privacy" value="1" required <?= isset($old['consent_privacy']) ? 'checked' : '' ?>> I understand and consent to ThriveWell storing this onboarding information under consent version 2026.05.</label>
            </section>

            <div class="onboarding-actions">
                <button type="button" data-save-draft>Save draft</button>
                <button type="submit">Complete onboarding</button>
            </div>
        </form>
    </section>
</main>
</body>
</html>
