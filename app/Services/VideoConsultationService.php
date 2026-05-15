<?php
namespace App\Services;

class VideoConsultationService
{
    public function roomsFor(int $userId): array
    {
        return Database::table(
            "SELECT vr.*, sb.starts_at, sb.timezone, sb.duration_minutes, sb.session_format, sb.support_goal, client.name client_name, client.avatar client_avatar, counsellor.name counsellor_name, counsellor.avatar counsellor_avatar
             FROM video_consultation_rooms vr
             JOIN session_bookings sb ON sb.id = vr.session_booking_id
             JOIN users client ON client.id = vr.client_id
             JOIN users counsellor ON counsellor.id = vr.counsellor_id
             WHERE (vr.client_id = ? OR vr.counsellor_id = ?) AND vr.deleted_at IS NULL
             ORDER BY sb.starts_at ASC",
            [$userId, $userId]
        );
    }

    public function roomFor(int $userId, ?int $roomId = null): ?array
    {
        $params = [$userId, $userId];
        $where = '(vr.client_id = ? OR vr.counsellor_id = ?)';
        if ($roomId) {
            $where .= ' AND vr.id = ?';
            $params[] = $roomId;
        }
        $room = Database::row(
            "SELECT vr.*, sb.starts_at, sb.timezone, sb.duration_minutes, sb.session_format, sb.support_goal, client.name client_name, client.avatar client_avatar, counsellor.name counsellor_name, counsellor.avatar counsellor_avatar
             FROM video_consultation_rooms vr
             JOIN session_bookings sb ON sb.id = vr.session_booking_id
             JOIN users client ON client.id = vr.client_id
             JOIN users counsellor ON counsellor.id = vr.counsellor_id
             WHERE $where AND vr.deleted_at IS NULL
             ORDER BY sb.starts_at ASC LIMIT 1",
            $params
        );
        if (!$room) return null;
        $room['device_checks'] = Database::table('SELECT * FROM video_device_checks WHERE video_consultation_room_id = ? ORDER BY created_at DESC', [$room['id']]);
        $room['recording_consents'] = Database::table('SELECT * FROM video_recording_consents WHERE video_consultation_room_id = ? ORDER BY created_at DESC', [$room['id']]);
        $room['messages'] = Database::table('SELECT m.*, users.name sender_name FROM video_chat_messages m JOIN users ON users.id = m.sender_id WHERE video_consultation_room_id = ? ORDER BY created_at ASC', [$room['id']]);
        $room['notes'] = Database::table('SELECT * FROM video_session_notes WHERE video_consultation_room_id = ? ORDER BY updated_at DESC', [$room['id']]);
        $room['ai_summaries'] = Database::table('SELECT * FROM video_ai_summaries WHERE video_consultation_room_id = ? ORDER BY created_at DESC', [$room['id']]);
        $room['join_window'] = [
            'opens_at' => date('c', strtotime($room['starts_at'].' -5 minutes')),
            'copy' => 'The waiting room opens 5 minutes before the session for grounding and device checks.',
        ];
        $room['safety'] = [
            'recording' => 'Recording stays disabled until every participant gives clear consent.',
            'ai_summary' => 'AI summaries are draft-only, never diagnostic, and must be edited before clinical saving.',
            'crisis' => 'Urgent concerns use human crisis handoff first.',
        ];
        return $room;
    }

    public function recordDeviceCheck(int $userId, int $roomId, array $input): array
    {
        $room = $this->roomFor($userId, $roomId);
        if (!$room) return ['ok' => false, 'message' => 'Video room access is not available.'];
        $now = date('c');
        $camera = $input['camera_status'] ?? 'ready';
        $microphone = $input['microphone_status'] ?? 'ready';
        $network = $input['network_status'] ?? 'stable';
        $captions = isset($input['caption_enabled']) ? 1 : 0;
        $lowBandwidth = isset($input['low_bandwidth_mode']) ? 1 : 0;
        Database::execute('INSERT INTO video_device_checks(video_consultation_room_id,user_id,camera_status,microphone_status,network_status,caption_enabled,low_bandwidth_mode,created_at) VALUES(?,?,?,?,?,?,?,?)', [(int) $room['id'], $userId, $camera, $microphone, $network, $captions, $lowBandwidth, $now]);
        Database::execute('UPDATE video_consultation_rooms SET status = ?, updated_at = ? WHERE id = ?', ['device_ready', $now, (int) $room['id']]);
        AuditLogger::record($userId, 'video_device_check_completed', 'video_consultation_room', (int) $room['id'], ['captions' => (bool) $captions, 'low_bandwidth' => (bool) $lowBandwidth]);
        return ['ok' => true, 'message' => 'Device check saved. Audio-only, captions, and low-bandwidth options remain available.'];
    }

    public function setRecordingConsent(int $userId, int $roomId, bool $granted): array
    {
        $room = $this->roomFor($userId, $roomId);
        if (!$room) return ['ok' => false, 'message' => 'Video room access is not available.'];
        $now = date('c');
        Database::execute('INSERT INTO video_recording_consents(video_consultation_room_id,user_id,consent_scope,granted,created_at) VALUES(?,?,?,?,?)', [(int) $room['id'], $userId, 'session_recording', $granted ? 1 : 0, $now]);
        $client = Database::row('SELECT id FROM video_recording_consents WHERE video_consultation_room_id = ? AND user_id = ? AND granted = 1 LIMIT 1', [(int) $room['id'], (int) $room['client_id']]);
        $counsellor = Database::row('SELECT id FROM video_recording_consents WHERE video_consultation_room_id = ? AND user_id = ? AND granted = 1 LIMIT 1', [(int) $room['id'], (int) $room['counsellor_id']]);
        $status = ($client && $counsellor) ? 'consented' : 'disabled';
        Database::execute('UPDATE video_consultation_rooms SET recording_status = ?, updated_at = ? WHERE id = ?', [$status, $now, (int) $room['id']]);
        AuditLogger::record($userId, 'video_recording_consent_updated', 'video_consultation_room', (int) $room['id'], ['recording_status' => $status]);
        return ['ok' => true, 'message' => $status === 'consented' ? 'Recording consent is complete for all participants.' : 'Consent saved. Recording remains disabled until everyone agrees.'];
    }

    public function sendChat(int $userId, int $roomId, string $message): array
    {
        $room = $this->roomFor($userId, $roomId);
        if (!$room) return ['ok' => false, 'message' => 'Video room access is not available.'];
        $message = trim($message);
        if ($message === '') return ['ok' => false, 'message' => 'Please type a message before sending.'];
        $crisis = preg_match('/\b(suicide|self-harm|kill myself|end it)\b/i', $message) ? 1 : 0;
        Database::execute('INSERT INTO video_chat_messages(video_consultation_room_id,sender_id,message,contains_crisis_signal,created_at) VALUES(?,?,?,?,?)', [(int) $room['id'], $userId, $message, $crisis, date('c')]);
        if ($crisis) AuditLogger::record($userId, 'video_chat_crisis_signal_detected', 'video_consultation_room', (int) $room['id'], ['human_handoff_required' => true]);
        return ['ok' => true, 'message' => $crisis ? 'Message saved. Human crisis review should be initiated first.' : 'Message sent safely.'];
    }

    public function saveNotes(int $userId, int $roomId, string $notes, bool $shared = false): array
    {
        $room = $this->roomFor($userId, $roomId);
        if (!$room || (int) $room['counsellor_id'] !== $userId) return ['ok' => false, 'message' => 'Only the assigned counsellor can write clinical notes.'];
        $notes = trim($notes);
        if ($notes === '') return ['ok' => false, 'message' => 'Clinical notes cannot be empty.'];
        $now = date('c');
        Database::execute('INSERT INTO video_session_notes(video_consultation_room_id,author_id,notes,shared_with_client,created_at,updated_at) VALUES(?,?,?,?,?,?)', [(int) $room['id'], $userId, $notes, $shared ? 1 : 0, $now, $now]);
        AuditLogger::record($userId, 'video_session_notes_saved', 'video_consultation_room', (int) $room['id'], ['shared_with_client' => $shared]);
        return ['ok' => true, 'message' => 'Clinical note saved with audit trail.'];
    }

    public function draftAiSummary(int $userId, int $roomId, string $summary): array
    {
        $room = $this->roomFor($userId, $roomId);
        if (!$room || (int) $room['counsellor_id'] !== $userId) return ['ok' => false, 'message' => 'Only the assigned counsellor can prepare editable AI summaries.'];
        $summary = trim($summary);
        if ($summary === '') return ['ok' => false, 'message' => 'Summary draft cannot be empty.'];
        $now = date('c');
        Database::execute('INSERT INTO video_ai_summaries(video_consultation_room_id,author_id,summary,status,safety_label,created_at,updated_at) VALUES(?,?,?,?,?,?,?)', [(int) $room['id'], $userId, $summary, 'draft_requires_human_edit', 'No diagnosis · no medication advice · editable before saving', $now, $now]);
        AuditLogger::record($userId, 'video_ai_summary_drafted', 'video_consultation_room', (int) $room['id'], ['requires_human_edit' => true, 'diagnosis_allowed' => false]);
        return ['ok' => true, 'message' => 'AI summary draft saved. Please edit before saving to clinical records.'];
    }
}
