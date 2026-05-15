<?php
namespace App\Services;

use App\Models\User;
use InvalidArgumentException;

class OnboardingService
{
    public function roles(): array
    {
        return [
            'client' => ['label' => 'Client', 'description' => 'Find support, book sessions, journal safely, and control consent.'],
            'intern' => ['label' => 'Intern Counsellor', 'description' => 'Support clients under supervision with CPD, notes, and safety workflows.'],
            'counsellor' => ['label' => 'Counsellor', 'description' => 'Manage bookings, clinical notes, credentials, and client care.'],
            'organization_admin' => ['label' => 'Organization Admin', 'description' => 'Configure teams, privacy-safe analytics, and employee wellbeing access.'],
            'parent' => ['label' => 'Parent / Guardian', 'description' => 'Support dependent wellbeing with consent-aware visibility.'],
            'student' => ['label' => 'Student', 'description' => 'Access school-safe counselling, assessments, and resources.'],
            'hr_user' => ['label' => 'HR User', 'description' => 'Coordinate anonymous, privacy-safe workforce wellbeing support.'],
            'pastor' => ['label' => 'Pastor / Faith Leader', 'description' => 'Offer pastoral support pathways with clinical escalation readiness.'],
            'ngo_volunteer' => ['label' => 'NGO Volunteer', 'description' => 'Coordinate community support with safety and supervision.'],
            'executive' => ['label' => 'Executive', 'description' => 'View privacy-preserving wellness intelligence and ROI.'],
            'superadmin' => ['label' => 'Superadmin', 'description' => 'Configure organizations, builders, permissions, features, and safety systems.'],
        ];
    }

    public function steps(): array
    {
        return [
            ['title' => 'Choose your role', 'purpose' => 'Route you to the safest, clearest ThriveWell experience.'],
            ['title' => 'Your support context', 'purpose' => 'Use progressive disclosure so you only share what is needed.'],
            ['title' => 'Accessibility preferences', 'purpose' => 'Make the platform calmer, predictable, and inclusive from the start.'],
            ['title' => 'Consent and privacy', 'purpose' => 'Explain what is stored, why, and how you remain in control.'],
            ['title' => 'Email verification readiness', 'purpose' => 'Protect sensitive wellbeing spaces before full access.'],
        ];
    }

    public function saveDraft(array $input): array
    {
        $email = strtolower(trim($input['email'] ?? ''));
        $role = $input['role'] ?? 'client';
        $step = max(1, min(5, (int) ($input['current_step'] ?? 1)));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'message' => 'Please enter a valid email so we can save your progress safely.'];
        }
        if (!array_key_exists($role, $this->roles())) {
            return ['ok' => false, 'message' => 'Please choose a supported ThriveWell role.'];
        }

        $now = date('c');
        Database::execute(
            'INSERT INTO onboarding_drafts(email, role, current_step, payload_json, updated_at, created_at) VALUES(?,?,?,?,?,?) ON CONFLICT(email) DO UPDATE SET role = excluded.role, current_step = excluded.current_step, payload_json = excluded.payload_json, updated_at = excluded.updated_at',
            [$email, $role, $step, json_encode($input, JSON_THROW_ON_ERROR), $now, $now]
        );

        return ['ok' => true, 'message' => 'Your onboarding draft has been saved calmly.', 'step' => $step];
    }

    public function complete(array $input, array $server = []): array
    {
        $name = trim($input['name'] ?? '');
        $email = strtolower(trim($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');
        $role = (string) ($input['role'] ?? 'client');
        $goal = trim($input['support_goal'] ?? 'Start safely with ThriveWell.');
        $preferredName = trim($input['preferred_name'] ?? $name);
        $accessibility = $this->normaliseAccessibility($input['accessibility'] ?? []);
        $consentGranted = isset($input['consent_privacy']) && (string) $input['consent_privacy'] === '1';

        $errors = $this->validate($name, $email, $password, $role, $consentGranted);
        if ($errors) {
            return ['ok' => false, 'message' => 'Please review the gentle validation notes.', 'errors' => $errors];
        }
        if (User::findByEmail($email)) {
            return ['ok' => false, 'message' => 'An account already exists for this email. Please sign in or use another email.', 'errors' => ['email' => 'Email already registered.']];
        }

        $roleId = $this->roleIdFor($role);
        $now = date('c');
        Database::execute(
            'INSERT INTO users(name,email,password,role_id,avatar,status,email_verified_at,consent_version,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?)',
            [$name, $email, password_hash($password, PASSWORD_BCRYPT), $roleId, 'https://i.pravatar.cc/120?u='.rawurlencode($email), 'available', null, '2026.05', $now, $now]
        );
        $createdUser = User::findByEmail($email);
        $userId = (int) $createdUser['id'];

        Database::execute(
            'INSERT INTO onboarding_profiles(user_id, role, preferred_name, support_goal, accessibility_preferences, onboarding_status, completed_at, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?,?)',
            [$userId, $role, $preferredName ?: $name, $goal, json_encode($accessibility, JSON_THROW_ON_ERROR), 'completed', $now, $now, $now]
        );
        Database::execute(
            'INSERT INTO consent_records(user_id, consent_version, consent_scope, granted, ip_address, user_agent, created_at) VALUES(?,?,?,?,?,?,?)',
            [$userId, '2026.05', 'platform_privacy_and_ai_safety', 1, $server['REMOTE_ADDR'] ?? '127.0.0.1', $server['HTTP_USER_AGENT'] ?? 'cli', $now]
        );
        Database::execute(
            'INSERT INTO email_verification_tokens(user_id, token, expires_at, created_at) VALUES(?,?,?,?)',
            [$userId, bin2hex(random_bytes(24)), date('c', strtotime('+24 hours')), $now]
        );
        Database::execute(
            'INSERT INTO onboarding_drafts(email, role, current_step, payload_json, updated_at, created_at) VALUES(?,?,?,?,?,?) ON CONFLICT(email) DO UPDATE SET role = excluded.role, current_step = 5, payload_json = excluded.payload_json, updated_at = excluded.updated_at',
            [$email, $role, 5, json_encode($input + ['completed' => true], JSON_THROW_ON_ERROR), $now, $now]
        );

        AuditLogger::record($userId, 'onboarding_completed', 'user', $userId, [
            'role' => $role,
            'consent_version' => '2026.05',
            'email_verification_ready' => true,
            'accessibility_preferences' => $accessibility,
        ]);

        return ['ok' => true, 'message' => 'Welcome to ThriveWell OS. Your safe onboarding is complete.', 'user' => User::findByEmail($email)];
    }

    private function validate(string $name, string $email, string $password, string $role, bool $consentGranted): array
    {
        $errors = [];
        if (mb_strlen($name) < 2) $errors['name'] = 'Please enter your name.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Please enter a valid email.';
        if (mb_strlen($password) < 8) $errors['password'] = 'Use at least 8 characters for a safer password.';
        if (!array_key_exists($role, $this->roles())) $errors['role'] = 'Please choose a supported role.';
        if (!$consentGranted) $errors['consent_privacy'] = 'Consent is required before storing onboarding data.';
        return $errors;
    }

    private function normaliseAccessibility(mixed $value): array
    {
        $items = is_array($value) ? $value : [$value];
        $allowed = ['reduced_motion', 'high_contrast', 'captions', 'sensory_safe', 'large_text', 'interpreter_support'];
        return array_values(array_intersect($allowed, array_map('strval', $items)));
    }

    private function roleIdFor(string $role): int
    {
        $mapped = match ($role) {
            'intern' => 'intern',
            'counsellor' => 'counsellor',
            'superadmin' => 'superadmin',
            default => 'client',
        };
        $row = Database::row('SELECT id FROM roles WHERE name = ? LIMIT 1', [$mapped]);
        if (!$row) {
            throw new InvalidArgumentException('Role seed data is missing. Run migrations and seeders.');
        }
        return (int) $row['id'];
    }
}
