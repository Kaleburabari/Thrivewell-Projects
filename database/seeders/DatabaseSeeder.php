<?php
class DatabaseSeeder
{
    public static function run(\PDO $pdo): void
    {
        $now = date('c');
        $roles = [
            ['superadmin', 'Superadmin'],
            ['intern', 'Intern Counsellor'],
            ['client', 'Client'],
            ['counsellor', 'Counsellor'],
        ];
        foreach ($roles as $role) {
            $pdo->prepare('INSERT OR IGNORE INTO roles(name,label) VALUES(?,?)')->execute($role);
        }

        $permissions = [
            ['view_dashboard', 'View role dashboard'],
            ['manage_platform', 'Manage platform'],
            ['view_sessions', 'View sessions'],
            ['view_wallet', 'View wallet'],
            ['view_cpd', 'View CPD progress'],
            ['use_ai_companion', 'Use Kale AI companion with safety boundaries'],
            ['view_audit_timeline', 'View own dashboard audit timeline'],
            ['trigger_crisis_handoff', 'Trigger human crisis handoff'],
            ['manage_credentials', 'Manage own credential vault'],
            ['review_credentials', 'Review credential verification queue'],
            ['view_signed_credentials', 'View signed credential downloads'],
            ['book_sessions', 'Book and manage own sessions'],
            ['manage_booking_availability', 'Manage counselling booking availability'],
            ['join_video_sessions', 'Join native video consultation rooms'],
            ['write_video_clinical_notes', 'Write video clinical notes and editable AI summaries'],
        ];
        foreach ($permissions as $permission) {
            $pdo->prepare('INSERT OR IGNORE INTO permissions(name,description) VALUES(?,?)')->execute($permission);
        }

        $roleIds = $pdo->query('SELECT name,id FROM roles')->fetchAll(\PDO::FETCH_KEY_PAIR);
        $permissionIds = $pdo->query('SELECT name,id FROM permissions')->fetchAll(\PDO::FETCH_KEY_PAIR);
        foreach ($permissionIds as $pid) {
            $pdo->prepare('INSERT OR IGNORE INTO role_permission(role_id,permission_id) VALUES(?,?)')->execute([$roleIds['superadmin'], $pid]);
        }
        foreach (['view_dashboard', 'view_sessions', 'view_wallet', 'view_cpd', 'use_ai_companion', 'view_audit_timeline', 'trigger_crisis_handoff', 'manage_credentials', 'view_signed_credentials', 'join_video_sessions', 'write_video_clinical_notes'] as $name) {
            $pdo->prepare('INSERT OR IGNORE INTO role_permission(role_id,permission_id) VALUES(?,?)')->execute([$roleIds['intern'], $permissionIds[$name]]);
        }
        foreach (['view_dashboard', 'view_sessions', 'manage_credentials', 'view_signed_credentials', 'manage_booking_availability', 'join_video_sessions', 'write_video_clinical_notes'] as $name) {
            $pdo->prepare('INSERT OR IGNORE INTO role_permission(role_id,permission_id) VALUES(?,?)')->execute([$roleIds['counsellor'], $permissionIds[$name]]);
        }
        foreach (['book_sessions', 'join_video_sessions'] as $name) {
            $pdo->prepare('INSERT OR IGNORE INTO role_permission(role_id,permission_id) VALUES(?,?)')->execute([$roleIds['client'], $permissionIds[$name]]);
        }

        $users = [
            ['Adaeze Okafor', 'intern@thrivewell.test', 'password', $roleIds['intern'], 'https://i.pravatar.cc/120?img=47', 'available'],
            ['Maya Hart', 'admin@thrivewell.test', 'password', $roleIds['superadmin'], 'https://i.pravatar.cc/120?img=32', 'available'],
            ['Sarah Jonah', 'client@thrivewell.test', 'password', $roleIds['client'], 'https://i.pravatar.cc/120?img=49', 'offline'],
            ['Dr. Kelechi Mensah', 'counsellor@thrivewell.test', 'password', $roleIds['counsellor'], 'https://i.pravatar.cc/120?img=52', 'available'],
        ];
        foreach ($users as $u) {
            $pdo->prepare('INSERT OR IGNORE INTO users(name,email,password,role_id,avatar,status,email_verified_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?)')
                ->execute([$u[0], $u[1], password_hash($u[2], PASSWORD_BCRYPT), $u[3], $u[4], $u[5], $now, $now, $now]);
        }

        $internId = (int) $pdo->query("SELECT id FROM users WHERE email='intern@thrivewell.test'")->fetchColumn();
        $pdo->prepare('INSERT INTO dashboard_preferences(user_id, theme, reduced_motion, high_contrast, density, widgets_json, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?)')
            ->execute([$internId, 'system', 0, 0, 'comfortable', json_encode(['kpis', 'schedule', 'wallet', 'performance', 'cpd', 'ai', 'audit']), $now, $now]);

        $sessions = [
            ['Tolu A.', 'https://i.pravatar.cc/96?img=47', 'Follow-up Session', '10:00', '60', 'video', 'confirmed', 5.0, 180000],
            ['David M.', 'https://i.pravatar.cc/96?img=12', 'Anxiety Support', '12:00', '60', 'video', 'confirmed', 4.8, 180000],
            ['Amaka R.', 'https://i.pravatar.cc/96?img=44', 'Stress Management', '14:00', '45', 'video', 'pending', 5.0, 150000],
            ['James K.', 'https://i.pravatar.cc/96?img=11', 'Life Coaching', '16:00', '60', 'video', 'confirmed', 4.9, 180000],
            ['Sarah J.', 'https://i.pravatar.cc/96?img=49', 'Self-esteem & Confidence', '2026-05-13 10:00', '60', 'video', 'completed', 5.0, 180000],
            ['Michael T.', 'https://i.pravatar.cc/96?img=14', 'Career Anxiety', '2026-05-12 14:00', '60', 'video', 'completed', 4.8, 180000],
            ['Blessing O.', 'https://i.pravatar.cc/96?img=45', 'Relationship Issues', '2026-05-11 11:00', '60', 'video', 'completed', 5.0, 180000],
            ['Daniel E.', 'https://i.pravatar.cc/96?img=15', 'Academic Stress', '2026-05-10 15:00', '60', 'video', 'completed', 4.9, 180000],
        ];
        foreach ($sessions as $s) {
            $starts = str_contains($s[3], '-') ? $s[3] : '2026-05-14 '.$s[3];
            $pdo->prepare('INSERT INTO counselling_sessions(intern_id,client_name,client_avatar,topic,starts_at,duration_minutes,type,status,rating,bonus_minor,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$internId, $s[0], $s[1], $s[2], $starts, $s[4], $s[5], $s[6], $s[7], $s[8], $now]);
        }

        foreach ([['Jan', 3800000], ['Feb', 5600000], ['Mar', 7200000], ['Apr', 13000000], ['May', 12750000], ['Jun', 14200000]] as $e) {
            $pdo->prepare('INSERT INTO earnings(user_id,period,amount_minor,source,created_at) VALUES(?,?,?,?,?)')->execute([$internId, $e[0], $e[1], 'listening_bonus', $now]);
        }
        foreach ([['Trauma-informed listening', 'completed', 100], ['Ethical AI boundaries', 'completed', 100], ['Crisis handoff simulation', 'in_progress', 65], ['Neurodiversity inclusion', 'in_progress', 50], ['Clinical note quality', 'remaining', 0]] as $m) {
            $pdo->prepare('INSERT INTO cpd_modules(user_id,title,status,progress,due_at,created_at) VALUES(?,?,?,?,?,?)')->execute([$internId, $m[0], $m[1], $m[2], '2026-06-30', $now]);
        }
        foreach ([['Session reminder', 'Tolu A. begins at 10:00 AM', 'schedule'], ['Wallet updated', 'May listening bonus is available', 'wallet'], ['Safety note', 'Kale AI is assistive only and never replaces therapy', 'safety'], ['Supervisor check-in', 'Your weekly reflective supervision is ready', 'support'], ['New message', 'A client shared a pre-session note', 'message']] as $n) {
            $pdo->prepare('INSERT INTO notifications(user_id,title,body,type,created_at) VALUES(?,?,?,?,?)')->execute([$internId, $n[0], $n[1], $n[2], $now]);
        }
        foreach ([['Take a Break', 'A 4-minute decompression ritual before your next session.', 'break'], ['Wellness Check', 'Record your own wellbeing without storing clinical detail.', 'wellness'], ['Talk to Supervisor', 'Escalate reflection needs to your assigned human supervisor.', 'supervision']] as $action) {
            $pdo->prepare('INSERT INTO wellness_actions(user_id,title,description,action_type,created_at) VALUES(?,?,?,?,?)')->execute([$internId, $action[0], $action[1], $action[2], $now]);
        }
        foreach ([['Gentle preparation', 'Review the client goal and prepare one open question. Kale will not diagnose or write clinical facts.', 'Assistive reflection only', 0], ['Human handoff reminder', 'If a client appears at risk, use the crisis handoff button before relying on AI interpretation.', 'Human handoff first', 1]] as $card) {
            $pdo->prepare('INSERT INTO ai_companion_cards(user_id,title,body,safety_label,requires_human_review,created_at) VALUES(?,?,?,?,?,?)')->execute([$internId, $card[0], $card[1], $card[2], $card[3], $now]);
        }


        foreach ([
            ['Nkechi A.', 'https://i.pravatar.cc/96?img=31', 'Exam stress', 'Send grounding worksheet', 'steady', 1, '2026-05-13 16:30'],
            ['Omar B.', 'https://i.pravatar.cc/96?img=18', 'Career anxiety', 'Review goals before Friday', 'watch', 1, '2026-05-12 09:15'],
            ['Ife R.', 'https://i.pravatar.cc/96?img=29', 'Relationship boundaries', 'Confirm consent for shared notes', 'improving', 0, '2026-05-11 13:10'],
            ['Chidi K.', 'https://i.pravatar.cc/96?img=13', 'Academic pressure', 'Prepare supervisor question', 'support', 1, '2026-05-10 12:00'],
        ] as $client) {
            $pdo->prepare('INSERT INTO intern_clients(intern_id,name,avatar,focus_area,next_step,wellbeing_signal,consent_to_share,last_seen_at,created_at) VALUES(?,?,?,?,?,?,?,?,?)')
                ->execute([$internId, $client[0], $client[1], $client[2], $client[3], $client[4], $client[5], $client[6], $now]);
        }

        foreach ([
            ['Reflect on crisis handoff simulation', 'Dr. Eniola West', 'high', '2026-05-15 09:00', 'open'],
            ['Submit anonymised session reflection', 'Dr. Eniola West', 'medium', '2026-05-16 17:00', 'open'],
            ['Review ethical AI checklist', 'Clinical Safety Board', 'medium', '2026-05-18 12:00', 'scheduled'],
        ] as $task) {
            $pdo->prepare('INSERT INTO supervision_tasks(user_id,title,supervisor,priority,due_at,status,created_at) VALUES(?,?,?,?,?,?,?)')
                ->execute([$internId, $task[0], $task[1], $task[2], $task[3], $task[4], $now]);
        }

        foreach ([
            ['Grounding guide for acute stress', 'Clinical Resource', 'A calm worksheet with sensory-safe breathing prompts.', 8, 'printable,low-literacy,trauma-informed'],
            ['Consent-first note sharing', 'Policy', 'Explains what can be shared with supervisors and when.', 6, 'privacy,consent,audit'],
            ['Neurodiversity-inclusive session setup', 'Accessibility', 'Checklist for predictable, low-sensory counselling rooms.', 10, 'autism-friendly,adhd-friendly,reduced-motion'],
        ] as $resource) {
            $pdo->prepare('INSERT INTO resource_library_items(user_id,title,category,description,reading_minutes,accessibility_tags,created_at) VALUES(?,?,?,?,?,?,?)')
                ->execute([$internId, $resource[0], $resource[1], $resource[2], $resource[3], $resource[4], $now]);
        }

        foreach ([
            ['Schedule Panel', 'schedule', 1, ['size' => 'wide', 'required_permission' => 'view_sessions']],
            ['Listening Bonus Wallet', 'wallet', 2, ['currency' => 'NGN', 'minor_units' => true]],
            ['Kale AI Safety', 'ai_companion', 3, ['diagnosis_allowed' => false, 'human_handoff_first' => true]],
            ['Audit Timeline', 'audit', 4, ['consent_aware' => true]],
        ] as $widget) {
            $pdo->prepare('INSERT INTO dashboard_widgets(user_id,title,widget_type,position,enabled,config_json,created_at) VALUES(?,?,?,?,?,?,?)')
                ->execute([$internId, $widget[0], $widget[1], $widget[2], 1, json_encode($widget[3]), $now]);
        }

        foreach ([
            ['Client check-in received', 'trigger', 'ready', 42, 80, ['event' => 'client_note_created']],
            ['Consent gate', 'decision', 'active', 248, 80, ['requires' => 'consent_to_share']],
            ['Supervisor review', 'human_review', 'queued', 454, 80, ['human_first' => true]],
            ['Safe follow-up sent', 'notification', 'draft', 660, 80, ['channel' => 'in_app']],
        ] as $node) {
            $pdo->prepare('INSERT INTO workflow_nodes(user_id,title,node_type,status,x,y,config_json,created_at) VALUES(?,?,?,?,?,?,?,?)')
                ->execute([$internId, $node[0], $node[1], $node[2], $node[3], $node[4], json_encode($node[5]), $now]);
        }

        foreach ([
            ['Preferred session name', 'text', 1, 'Use the name the client feels safe hearing.', 1],
            ['Support need today', 'select', 1, 'Offer choices; do not force disclosure.', 2],
            ['Consent to share with supervisor', 'boolean', 1, 'Explain exactly what will be shared and why.', 3],
            ['Accessibility preferences', 'checkboxes', 0, 'Reduced motion, captions, sensory-safe layout, or interpreter support.', 4],
        ] as $field) {
            $pdo->prepare('INSERT INTO form_builder_fields(user_id,label,field_type,required,help_text,sort_order,created_at) VALUES(?,?,?,?,?,?,?)')
                ->execute([$internId, $field[0], $field[1], $field[2], $field[3], $field[4], $now]);
        }


        $adminId = (int) $pdo->query("SELECT id FROM users WHERE email='admin@thrivewell.test'")->fetchColumn();
        $clientId = (int) $pdo->query("SELECT id FROM users WHERE email='client@thrivewell.test'")->fetchColumn();
        $counsellorId = (int) $pdo->query("SELECT id FROM users WHERE email='counsellor@thrivewell.test'")->fetchColumn();
        foreach ([
            [$internId, 'university_letter', 'HOD reference letter', 'private/credentials/intern-hod-reference.pdf', 'pending_review', null, null],
            [$internId, 'government_id', 'Government ID', 'private/credentials/intern-government-id.pdf', 'revision_requested', $adminId, 'Please upload a clearer scan with all four corners visible.'],
            [$counsellorId, 'license', 'Professional counselling license', 'private/credentials/counsellor-license.pdf', 'verified', $adminId, null],
            [$counsellorId, 'degree', 'Clinical psychology degree', 'private/credentials/counsellor-degree.pdf', 'verified', $adminId, null],
        ] as $doc) {
            $pdo->prepare('INSERT INTO credential_documents(user_id,document_type,title,storage_disk,storage_path,file_hash,status,reviewer_id,revision_reason,submitted_at,reviewed_at,expires_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
                ->execute([$doc[0], $doc[1], $doc[2], 'private', $doc[3], hash('sha256', $doc[3]), $doc[4], $doc[5], $doc[6], $now, $doc[5] ? $now : null, '2027-05-14', $now, $now]);
        }
        $verifiedDocs = $pdo->query("SELECT id FROM credential_documents WHERE status='verified'")->fetchAll(\PDO::FETCH_COLUMN);
        foreach ($verifiedDocs as $documentId) {
            $pdo->prepare('INSERT INTO credential_reviews(credential_document_id,reviewer_id,decision,notes,created_at) VALUES(?,?,?,?,?)')
                ->execute([$documentId, $adminId, 'approved', 'Verified against submitted private vault metadata.', $now]);
        }
        $pdo->prepare('INSERT INTO credential_reviews(credential_document_id,reviewer_id,decision,notes,created_at) VALUES(?,?,?,?,?)')
            ->execute([2, $adminId, 'revision_requested', 'Please upload a clearer scan with all four corners visible.', $now]);
        foreach ([[$counsellorId, 'Verified Counsellor', 'professional', 'active'], [$internId, 'Credential Review In Progress', 'intern', 'pending']] as $badge) {
            $pdo->prepare('INSERT INTO verification_badges(user_id,badge,level,status,issued_at,expires_at,created_at) VALUES(?,?,?,?,?,?,?)')
                ->execute([$badge[0], $badge[1], $badge[2], $badge[3], $badge[3] === 'active' ? $now : null, '2027-05-14', $now]);
        }

        $pdo->prepare('INSERT INTO counsellor_profiles(user_id,title,bio,specialties,languages,accessibility_tags,rating,match_score,accepting_clients,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$counsellorId, 'Trauma-informed Counsellor', 'Warm, structured support for anxiety, work stress, and life transitions.', 'anxiety, stress, transitions, self-esteem', 'English, Igbo, Yoruba', 'captions, low sensory, text alternatives', 4.95, 97, 1, $now, $now]);
        foreach ([
            ['2026-05-15 15:00', 'video', 1500000, 'booked'],
            ['2026-05-16 09:00', 'video', 1500000, 'open'],
            ['2026-05-16 11:00', 'audio', 1200000, 'open'],
            ['2026-05-17 14:00', 'video', 1500000, 'open'],
            ['2026-05-18 16:00', 'chat', 900000, 'open'],
        ] as $slot) {
            $pdo->prepare('INSERT INTO counsellor_availability(counsellor_id,starts_at,timezone,duration_minutes,session_type,fee_minor,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?)')
                ->execute([$counsellorId, $slot[0], 'Africa/Lagos', 60, $slot[1], $slot[2], $slot[3], $now, $now]);
        }
        $pdo->prepare('INSERT INTO session_bookings(client_id,counsellor_id,availability_id,hold_id,starts_at,timezone,duration_minutes,session_format,support_goal,status,payment_status,amount_minor,reminder_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$clientId, $counsellorId, 1, null, '2026-05-15 15:00', 'Africa/Lagos', 60, 'video', 'Understand anxiety triggers with gentle support.', 'confirmed', 'payment_ready', 1500000, '2026-05-14 15:00', $now, $now]);
        $seedBookingId = (int) $pdo->query('SELECT id FROM session_bookings ORDER BY id DESC LIMIT 1')->fetchColumn();
        $pdo->prepare('INSERT INTO booking_payment_records(session_booking_id,provider,amount_minor,currency,status,idempotency_key,created_at) VALUES(?,?,?,?,?,?,?)')
            ->execute([$seedBookingId, 'paystack_ready', 1500000, 'NGN', 'pending', hash('sha256', 'seed-booking-'.$seedBookingId), $now]);
        $pdo->prepare('INSERT INTO booking_reminders(session_booking_id,channel,send_at,status,created_at) VALUES(?,?,?,?,?)')
            ->execute([$seedBookingId, 'email', '2026-05-14 15:00', 'scheduled', $now]);
        $roomUid = 'tw-video-'.substr(hash('sha256', 'seed-video-room-'.$seedBookingId), 0, 16);
        $pdo->prepare('INSERT INTO video_consultation_rooms(session_booking_id,client_id,counsellor_id,room_uid,status,recording_status,captions_enabled,low_bandwidth_enabled,waiting_room_opens_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$seedBookingId, $clientId, $counsellorId, $roomUid, 'waiting_room', 'disabled', 1, 1, '2026-05-15 14:55', $now, $now]);
        $videoRoomId = (int) $pdo->query('SELECT id FROM video_consultation_rooms ORDER BY id DESC LIMIT 1')->fetchColumn();
        $pdo->prepare('INSERT INTO video_chat_messages(video_consultation_room_id,sender_id,message,contains_crisis_signal,created_at) VALUES(?,?,?,?,?)')
            ->execute([$videoRoomId, $counsellorId, 'Welcome. We can start with audio-only or captions if that feels safer.', 0, $now]);
        $pdo->prepare('INSERT INTO video_ai_summaries(video_consultation_room_id,author_id,summary,status,safety_label,created_at,updated_at) VALUES(?,?,?,?,?,?,?)')
            ->execute([$videoRoomId, $counsellorId, 'Draft only: client wants to understand anxiety triggers and identify one grounding practice. No diagnosis or medication guidance.', 'draft_requires_human_edit', 'No diagnosis · no medication advice · editable before saving', $now, $now]);

        $pdo->prepare('INSERT INTO audit_logs(user_id,action,auditable_type,auditable_id,metadata,created_at) VALUES(?,?,?,?,?,?)')
            ->execute([$internId, 'seeded_demo_dashboard', 'dashboard', $internId, json_encode(['consent_version' => '2026.05', 'ai_safety' => 'no diagnosis; human handoff first']), $now]);
    }
}
