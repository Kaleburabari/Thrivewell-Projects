<?php
class DatabaseSeeder
{
    public static function run(\PDO $pdo): void
    {
        $now = date('c');
        $roles = [['superadmin','Superadmin'], ['intern','Intern Counsellor'], ['client','Client'], ['counsellor','Counsellor']];
        foreach ($roles as $role) $pdo->prepare('INSERT OR IGNORE INTO roles(name,label) VALUES(?,?)')->execute($role);
        $permissions = [
            ['view_dashboard','View role dashboard'], ['manage_platform','Manage platform'], ['view_sessions','View sessions'],
            ['view_wallet','View wallet'], ['view_cpd','View CPD progress'], ['trigger_crisis_handoff','Trigger human crisis handoff']
        ];
        foreach ($permissions as $permission) $pdo->prepare('INSERT OR IGNORE INTO permissions(name,description) VALUES(?,?)')->execute($permission);
        $roleIds = $pdo->query('SELECT name,id FROM roles')->fetchAll(PDO::FETCH_KEY_PAIR);
        $permissionIds = $pdo->query('SELECT name,id FROM permissions')->fetchAll(PDO::FETCH_KEY_PAIR);
        foreach ($permissionIds as $pid) $pdo->prepare('INSERT OR IGNORE INTO role_permission(role_id,permission_id) VALUES(?,?)')->execute([$roleIds['superadmin'], $pid]);
        foreach (['view_dashboard','view_sessions','view_wallet','view_cpd','trigger_crisis_handoff'] as $name) $pdo->prepare('INSERT OR IGNORE INTO role_permission(role_id,permission_id) VALUES(?,?)')->execute([$roleIds['intern'], $permissionIds[$name]]);
        $users = [
            ['Adaeze Okafor','intern@thrivewell.test','password',$roleIds['intern'],'https://i.pravatar.cc/120?img=47','available'],
            ['Maya Hart','admin@thrivewell.test','password',$roleIds['superadmin'],'https://i.pravatar.cc/120?img=32','available'],
            ['Sarah Jonah','client@thrivewell.test','password',$roleIds['client'],'https://i.pravatar.cc/120?img=49','offline'],
        ];
        foreach ($users as $u) $pdo->prepare('INSERT OR IGNORE INTO users(name,email,password,role_id,avatar,status,email_verified_at,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?)')->execute([$u[0],$u[1],password_hash($u[2], PASSWORD_BCRYPT),$u[3],$u[4],$u[5],$now,$now,$now]);
        $internId = (int) $pdo->query("SELECT id FROM users WHERE email='intern@thrivewell.test'")->fetchColumn();
        $sessions = [
            ['Tolu A.','https://i.pravatar.cc/96?img=47','Follow-up Session','10:00','60','video','confirmed',5.0,180000],
            ['David M.','https://i.pravatar.cc/96?img=12','Anxiety Support','12:00','60','video','confirmed',4.8,180000],
            ['Amaka R.','https://i.pravatar.cc/96?img=44','Stress Management','14:00','45','video','pending',5.0,150000],
            ['James K.','https://i.pravatar.cc/96?img=11','Life Coaching','16:00','60','video','confirmed',4.9,180000],
            ['Sarah J.','https://i.pravatar.cc/96?img=49','Self-esteem & Confidence','2026-05-13 10:00','60','video','completed',5.0,180000],
            ['Michael T.','https://i.pravatar.cc/96?img=14','Career Anxiety','2026-05-12 14:00','60','video','completed',4.8,180000],
            ['Blessing O.','https://i.pravatar.cc/96?img=45','Relationship Issues','2026-05-11 11:00','60','video','completed',5.0,180000],
            ['Daniel E.','https://i.pravatar.cc/96?img=15','Academic Stress','2026-05-10 15:00','60','video','completed',4.9,180000],
        ];
        foreach ($sessions as $s) {
            $starts = str_contains($s[3], '-') ? $s[3] : '2026-05-14 '.$s[3];
            $pdo->prepare('INSERT INTO counselling_sessions(intern_id,client_name,client_avatar,topic,starts_at,duration_minutes,type,status,rating,bonus_minor,created_at) VALUES(?,?,?,?,?,?,?,?,?,?,?)')->execute([$internId,$s[0],$s[1],$s[2],$starts,$s[4],$s[5],$s[6],$s[7],$s[8],$now]);
        }
        foreach ([['Jan',3800000],['Feb',5600000],['Mar',7200000],['Apr',13000000],['May',12750000],['Jun',14200000]] as $e) $pdo->prepare('INSERT INTO earnings(user_id,period,amount_minor,source,created_at) VALUES(?,?,?,?,?)')->execute([$internId,$e[0],$e[1],'listening_bonus',$now]);
        foreach ([['Trauma-informed listening','completed',100],['Ethical AI boundaries','completed',100],['Crisis handoff simulation','in_progress',65],['Neurodiversity inclusion','in_progress',50],['Clinical note quality','remaining',0]] as $m) $pdo->prepare('INSERT INTO cpd_modules(user_id,title,status,progress,due_at,created_at) VALUES(?,?,?,?,?,?)')->execute([$internId,$m[0],$m[1],$m[2],'2026-06-30',$now]);
        foreach ([['Session reminder','Tolu A. begins at 10:00 AM','schedule'],['Wallet updated','May listening bonus is available','wallet'],['Safety note','Kale AI is assistive only and never replaces therapy','safety'],['Supervisor check-in','Your weekly reflective supervision is ready','support'],['New message','A client shared a pre-session note','message']] as $n) $pdo->prepare('INSERT INTO notifications(user_id,title,body,type,created_at) VALUES(?,?,?,?,?)')->execute([$internId,$n[0],$n[1],$n[2],$now]);
        $pdo->prepare('INSERT INTO audit_logs(user_id,action,auditable_type,auditable_id,metadata,created_at) VALUES(?,?,?,?,?,?)')->execute([$internId,'seeded_demo_dashboard','dashboard',$internId,json_encode(['consent_version'=>'2026.05','ai_safety'=>'no diagnosis; human handoff first']),$now]);
    }
}
