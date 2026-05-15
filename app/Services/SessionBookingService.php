<?php
namespace App\Services;

class SessionBookingService
{
    public function discovery(array $filters = []): array
    {
        $focus = trim($filters['focus'] ?? '');
        $sql = "SELECT cp.*, users.name, users.avatar, users.status, vb.badge, vb.status as badge_status
                FROM counsellor_profiles cp
                JOIN users ON users.id = cp.user_id
                LEFT JOIN verification_badges vb ON vb.user_id = users.id AND vb.status = 'active'
                WHERE cp.accepting_clients = 1 AND cp.deleted_at IS NULL";
        $params = [];
        if ($focus !== '') {
            $sql .= ' AND (cp.specialties LIKE ? OR cp.modality LIKE ?)';
            $params[] = '%'.$focus.'%';
            $params[] = '%'.$focus.'%';
        }
        $sql .= ' ORDER BY cp.match_score DESC, cp.hourly_rate_minor ASC';

        $profiles = Database::table($sql, $params);
        foreach ($profiles as &$profile) {
            $profile['next_slots'] = $this->slotsFor((int) $profile['id']);
            $profile['ai_match_reasons'] = $this->matchReasons($profile);
        }

        return [
            'profiles' => $profiles,
            'filters' => [
                'focus_options' => ['Anxiety', 'Academic stress', 'Career anxiety', 'Relationship boundaries', 'Trauma-informed support'],
                'timezone' => $filters['timezone'] ?? 'Africa/Lagos',
                'accessibility' => ['captions', 'low-bandwidth', 'reduced-motion', 'text-first'],
            ],
            'safety_note' => 'Kale matching is assistive only. Clients choose freely; no diagnosis or hidden profiling is used.',
        ];
    }

    public function createHold(int $clientId, array $input): array
    {
        $profileId = (int) ($input['counsellor_profile_id'] ?? 0);
        $startsAt = trim($input['starts_at'] ?? '');
        $timezone = trim($input['timezone'] ?? 'Africa/Lagos');
        if (!$profileId || !$startsAt) return ['ok' => false, 'message' => 'Please choose a counsellor and available time.'];

        $slot = Database::row('SELECT * FROM counsellor_availability_windows WHERE counsellor_profile_id = ? AND starts_at = ? AND status = ?', [$profileId, $startsAt, 'open']);
        if (!$slot) return ['ok' => false, 'message' => 'That time is no longer available. Please choose another calm option.'];
        if (!$this->slotAvailable($profileId, $startsAt)) return ['ok' => false, 'message' => 'That time was just reserved. Please choose another slot.'];

        $now = date('c');
        Database::execute(
            'INSERT INTO booking_holds(client_id,counsellor_profile_id,starts_at,duration_minutes,timezone,status,expires_at,created_at) VALUES(?,?,?,?,?,?,?,?)',
            [$clientId, $profileId, $startsAt, (int) $slot['duration_minutes'], $timezone, 'held', date('c', strtotime('+10 minutes')), $now]
        );
        $hold = Database::row('SELECT * FROM booking_holds WHERE client_id = ? ORDER BY id DESC LIMIT 1', [$clientId]);
        AuditLogger::record($clientId, 'booking_hold_created', 'booking_hold', (int) $hold['id'], ['starts_at' => $startsAt, 'timezone' => $timezone]);

        return ['ok' => true, 'message' => 'This session time is held for 10 minutes.', 'hold' => $hold];
    }

    public function confirm(int $clientId, int $holdId, array $input = []): array
    {
        $hold = Database::row('SELECT * FROM booking_holds WHERE id = ? AND client_id = ? AND status = ?', [$holdId, $clientId, 'held']);
        if (!$hold) return ['ok' => false, 'message' => 'The booking hold was not found. Please choose a time again.'];
        if (strtotime($hold['expires_at']) < time()) return ['ok' => false, 'message' => 'This hold expired gently. Please choose the slot again.'];
        if (!$this->slotAvailable((int) $hold['counsellor_profile_id'], $hold['starts_at'], $holdId)) return ['ok' => false, 'message' => 'This slot has already been confirmed.'];

        $profile = Database::row('SELECT * FROM counsellor_profiles WHERE id = ?', [(int) $hold['counsellor_profile_id']]);
        $now = date('c');
        Database::execute(
            'INSERT INTO session_bookings(client_id,counsellor_profile_id,booking_hold_id,starts_at,duration_minutes,timezone,status,payment_status,amount_minor,accessibility_json,reminder_plan_json,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$clientId, (int) $hold['counsellor_profile_id'], $holdId, $hold['starts_at'], (int) $hold['duration_minutes'], $hold['timezone'], 'confirmed', 'payment_ready', (int) $profile['hourly_rate_minor'], json_encode($input['accessibility'] ?? ['captions']), json_encode(['24h', '2h', '15m']), $now, $now]
        );
        $booking = Database::row('SELECT * FROM session_bookings WHERE client_id = ? ORDER BY id DESC LIMIT 1', [$clientId]);
        Database::execute('UPDATE booking_holds SET status = ? WHERE id = ?', ['confirmed', $holdId]);
        $this->event((int) $booking['id'], $clientId, 'booking_confirmed', ['payment_status' => 'payment_ready']);
        AuditLogger::record($clientId, 'booking_confirmed', 'session_booking', (int) $booking['id'], ['amount_minor' => (int) $booking['amount_minor'], 'timezone' => $hold['timezone']]);

        return ['ok' => true, 'message' => 'Session confirmed. Payment readiness and reminders are prepared.', 'booking' => $booking];
    }

    public function reschedule(int $clientId, int $bookingId, string $startsAt): array
    {
        $booking = Database::row('SELECT * FROM session_bookings WHERE id = ? AND client_id = ? AND status = ?', [$bookingId, $clientId, 'confirmed']);
        if (!$booking) return ['ok' => false, 'message' => 'Confirmed booking was not found.'];
        if (!$this->slotAvailable((int) $booking['counsellor_profile_id'], $startsAt)) return ['ok' => false, 'message' => 'That reschedule time is not available.'];
        Database::execute('UPDATE session_bookings SET starts_at = ?, status = ?, updated_at = ? WHERE id = ?', [$startsAt, 'rescheduled', date('c'), $bookingId]);
        $this->event($bookingId, $clientId, 'booking_rescheduled', ['starts_at' => $startsAt]);
        AuditLogger::record($clientId, 'booking_rescheduled', 'session_booking', $bookingId, ['starts_at' => $startsAt]);
        return ['ok' => true, 'message' => 'Session rescheduled with reminders refreshed.'];
    }

    public function cancel(int $clientId, int $bookingId, string $reason = 'Client requested cancellation.'): array
    {
        $booking = Database::row('SELECT * FROM session_bookings WHERE id = ? AND client_id = ? AND status IN (\'confirmed\',\'rescheduled\')', [$bookingId, $clientId]);
        if (!$booking) return ['ok' => false, 'message' => 'Booking was not found or is already closed.'];
        Database::execute('UPDATE session_bookings SET status = ?, cancellation_reason = ?, updated_at = ? WHERE id = ?', ['cancelled', $reason, date('c'), $bookingId]);
        $this->event($bookingId, $clientId, 'booking_cancelled', ['reason' => $reason]);
        AuditLogger::record($clientId, 'booking_cancelled', 'session_booking', $bookingId, ['reason' => $reason]);
        return ['ok' => true, 'message' => 'Session cancelled. No shame, no pressure — you can rebook anytime.'];
    }

    public function bookingsFor(int $clientId): array
    {
        return Database::table(
            'SELECT sb.*, cp.display_title, users.name as counsellor_name, users.avatar as counsellor_avatar
             FROM session_bookings sb JOIN counsellor_profiles cp ON cp.id = sb.counsellor_profile_id JOIN users ON users.id = cp.user_id
             WHERE sb.client_id = ? ORDER BY sb.starts_at DESC',
            [$clientId]
        );
    }

    private function slotsFor(int $profileId): array
    {
        return Database::table('SELECT * FROM counsellor_availability_windows WHERE counsellor_profile_id = ? AND status = ? AND starts_at >= ? ORDER BY starts_at ASC LIMIT 4', [$profileId, 'open', '2026-05-14 00:00']);
    }

    private function slotAvailable(int $profileId, string $startsAt, ?int $ignoreHoldId = null): bool
    {
        $confirmed = Database::row('SELECT id FROM session_bookings WHERE counsellor_profile_id = ? AND starts_at = ? AND status IN (\'confirmed\',\'rescheduled\') LIMIT 1', [$profileId, $startsAt]);
        if ($confirmed) return false;
        $params = [$profileId, $startsAt, date('c')];
        $sql = 'SELECT id FROM booking_holds WHERE counsellor_profile_id = ? AND starts_at = ? AND status = \'held\' AND expires_at > ?';
        if ($ignoreHoldId) {
            $sql .= ' AND id != ?';
            $params[] = $ignoreHoldId;
        }
        return !Database::row($sql.' LIMIT 1', $params);
    }

    private function event(int $bookingId, int $actorId, string $event, array $metadata = []): void
    {
        Database::execute('INSERT INTO booking_events(session_booking_id,actor_id,event,metadata,created_at) VALUES(?,?,?,?,?)', [$bookingId, $actorId, $event, json_encode($metadata), date('c')]);
    }

    private function matchReasons(array $profile): array
    {
        return [
            'Matches '.$profile['modality'].' preference',
            'Specialties: '.$profile['specialties'],
            'Transparent fee: ₦'.number_format(((int) $profile['hourly_rate_minor']) / 100, 2),
        ];
    }
}
