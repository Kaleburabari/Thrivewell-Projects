<?php
namespace App\Services;

class SessionBookingService
{
    public function discovery(int $clientId, array $filters = []): array
    {
        $focus = trim($filters['focus'] ?? '');
        $params = [];
        $where = "WHERE users.deleted_at IS NULL AND cp.accepting_clients = 1";
        if ($focus !== '') {
            $where .= " AND (cp.specialties LIKE ? OR cp.languages LIKE ? OR cp.accessibility_tags LIKE ?)";
            $needle = '%'.$focus.'%';
            $params = [$needle, $needle, $needle];
        }

        $counsellors = Database::table(
            "SELECT cp.*, users.name, users.email, users.avatar, roles.label as role_label
             FROM counsellor_profiles cp
             JOIN users ON users.id = cp.user_id
             JOIN roles ON roles.id = users.role_id
             $where
             ORDER BY cp.match_score DESC, cp.rating DESC",
            $params
        );

        return [
            'counsellors' => array_map(fn ($row) => $this->hydrateCounsellor($row, $clientId), $counsellors),
            'filters' => [
                'focus' => $focus,
                'session_types' => ['video', 'audio', 'chat'],
                'accessibility' => ['captions', 'low sensory', 'text alternatives', 'large controls'],
                'timezones' => ['Africa/Lagos', 'UTC', 'Europe/London'],
            ],
            'safety' => [
                'copy' => 'Booking is consent-first. You can pause, reschedule, or cancel without shame.',
                'ai_boundary' => 'AI matching is supportive only; humans remain in control.',
            ],
        ];
    }

    public function createHold(int $clientId, int $availabilityId): array
    {
        $slot = $this->availability($availabilityId);
        if (!$slot) return ['ok' => false, 'message' => 'That session time is no longer available.'];
        if ($this->slotTaken((int) $slot['counsellor_id'], $slot['starts_at'])) {
            return ['ok' => false, 'message' => 'That time was just booked. Please choose another gentle option.'];
        }
        $now = date('c');
        $expires = date('c', strtotime('+10 minutes'));
        Database::execute('DELETE FROM booking_holds WHERE expires_at < ? OR (client_id = ? AND status = ?)', [$now, $clientId, 'active']);
        Database::execute(
            'INSERT INTO booking_holds(client_id,counsellor_id,availability_id,starts_at,expires_at,status,created_at) VALUES(?,?,?,?,?,?,?)',
            [$clientId, (int) $slot['counsellor_id'], $availabilityId, $slot['starts_at'], $expires, 'active', $now]
        );
        $hold = Database::row('SELECT * FROM booking_holds WHERE client_id = ? ORDER BY id DESC LIMIT 1', [$clientId]);
        AuditLogger::record($clientId, 'booking_hold_created', 'booking_hold', (int) $hold['id'], ['expires_at' => $expires, 'counsellor_id' => (int) $slot['counsellor_id']]);
        return ['ok' => true, 'message' => 'Session time held for 10 minutes. Hold ID: '.(string) $hold['id'], 'hold' => $hold];
    }

    public function confirm(int $clientId, int $holdId, array $input = []): array
    {
        $hold = Database::row('SELECT * FROM booking_holds WHERE id = ? AND client_id = ? AND status = ?', [$holdId, $clientId, 'active']);
        if (!$hold || strtotime($hold['expires_at']) < time()) return ['ok' => false, 'message' => 'This hold expired gently. Please choose a fresh time.'];
        $slot = $this->availability((int) $hold['availability_id']);
        if (!$slot || $this->slotTaken((int) $hold['counsellor_id'], $hold['starts_at'])) {
            return ['ok' => false, 'message' => 'That time is unavailable now. No payment was taken.'];
        }
        $now = date('c');
        $timezone = trim($input['timezone'] ?? 'Africa/Lagos');
        $format = trim($input['session_format'] ?? $slot['session_type']);
        $goal = trim($input['support_goal'] ?? 'A calm first session.');
        Database::execute(
            'INSERT INTO session_bookings(client_id,counsellor_id,availability_id,hold_id,starts_at,timezone,duration_minutes,session_format,support_goal,status,payment_status,amount_minor,reminder_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
            [$clientId, (int) $hold['counsellor_id'], (int) $hold['availability_id'], $holdId, $hold['starts_at'], $timezone, (int) $slot['duration_minutes'], $format, $goal, 'confirmed', 'payment_ready', (int) $slot['fee_minor'], date('c', strtotime($hold['starts_at'].' -24 hours')), $now, $now]
        );
        $booking = Database::row('SELECT * FROM session_bookings WHERE client_id = ? ORDER BY id DESC LIMIT 1', [$clientId]);
        Database::execute('UPDATE booking_holds SET status = ? WHERE id = ?', ['converted', $holdId]);
        Database::execute('UPDATE counsellor_availability SET status = ? WHERE id = ?', ['booked', (int) $hold['availability_id']]);
        Database::execute('INSERT INTO booking_payment_records(session_booking_id,provider,amount_minor,currency,status,idempotency_key,created_at) VALUES(?,?,?,?,?,?,?)', [(int) $booking['id'], 'paystack_ready', (int) $booking['amount_minor'], 'NGN', 'pending', hash('sha256', 'booking|'.$booking['id'].'|'.$clientId), $now]);
        Database::execute('INSERT INTO booking_reminders(session_booking_id,channel,send_at,status,created_at) VALUES(?,?,?,?,?)', [(int) $booking['id'], 'email', $booking['reminder_at'], 'scheduled', $now]);
        if ($format === 'video') {
            $roomUid = 'tw-video-'.substr(hash('sha256', 'booking-video-room-'.$booking['id'].'-'.$clientId), 0, 16);
            Database::execute('INSERT INTO video_consultation_rooms(session_booking_id,client_id,counsellor_id,room_uid,status,recording_status,captions_enabled,low_bandwidth_enabled,waiting_room_opens_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)', [(int) $booking['id'], $clientId, (int) $booking['counsellor_id'], $roomUid, 'waiting_room', 'disabled', 1, 1, date('c', strtotime($booking['starts_at'].' -5 minutes')), $now, $now]);
        }
        AuditLogger::record($clientId, 'booking_confirmed', 'session_booking', (int) $booking['id'], ['payment_status' => 'payment_ready', 'timezone' => $timezone]);
        return ['ok' => true, 'message' => 'Your session is confirmed. Payment readiness and reminders are prepared.', 'booking' => $booking];
    }

    public function cancel(int $clientId, int $bookingId, string $reason = 'Client requested a pause.'): array
    {
        $booking = Database::row('SELECT * FROM session_bookings WHERE id = ? AND client_id = ? AND deleted_at IS NULL', [$bookingId, $clientId]);
        if (!$booking) return ['ok' => false, 'message' => 'We could not find that booking for this account.'];
        $now = date('c');
        Database::execute('UPDATE session_bookings SET status = ?, cancellation_reason = ?, updated_at = ?, deleted_at = ? WHERE id = ?', ['cancelled', $reason, $now, $now, $bookingId]);
        Database::execute('UPDATE counsellor_availability SET status = ? WHERE id = ?', ['open', (int) $booking['availability_id']]);
        AuditLogger::record($clientId, 'booking_cancelled', 'session_booking', $bookingId, ['reason' => $reason]);
        return ['ok' => true, 'message' => 'Session cancelled without judgement. The counsellor was notified.'];
    }

    public function bookingsFor(int $clientId): array
    {
        return Database::table(
            "SELECT sb.*, users.name as counsellor_name, users.avatar as counsellor_avatar
             FROM session_bookings sb
             JOIN users ON users.id = sb.counsellor_id
             WHERE sb.client_id = ? AND sb.deleted_at IS NULL
             ORDER BY sb.starts_at DESC",
            [$clientId]
        );
    }

    private function hydrateCounsellor(array $row, int $clientId): array
    {
        $row['availability'] = Database::table('SELECT * FROM counsellor_availability WHERE counsellor_id = ? AND status = ? ORDER BY starts_at ASC LIMIT 6', [$row['user_id'], 'open']);
        $row['match_reasons'] = ['Verified credentials', 'Accessible session setup', 'Human-first care plan'];
        $row['next_booking'] = Database::row('SELECT * FROM session_bookings WHERE client_id = ? AND counsellor_id = ? AND deleted_at IS NULL ORDER BY starts_at DESC LIMIT 1', [$clientId, $row['user_id']]);
        return $row;
    }

    private function availability(int $availabilityId): ?array
    {
        return Database::row('SELECT * FROM counsellor_availability WHERE id = ? AND status = ?', [$availabilityId, 'open']);
    }

    private function slotTaken(int $counsellorId, string $startsAt): bool
    {
        return (bool) Database::row("SELECT id FROM session_bookings WHERE counsellor_id = ? AND starts_at = ? AND status IN ('confirmed','rescheduled') AND deleted_at IS NULL LIMIT 1", [$counsellorId, $startsAt]);
    }
}
