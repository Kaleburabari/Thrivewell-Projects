<?php
return [
"CREATE TABLE IF NOT EXISTS roles (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, label TEXT NOT NULL)",
"CREATE TABLE IF NOT EXISTS permissions (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL UNIQUE, description TEXT NOT NULL)",
"CREATE TABLE IF NOT EXISTS role_permission (role_id INTEGER NOT NULL, permission_id INTEGER NOT NULL, PRIMARY KEY(role_id, permission_id))",
"CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, name TEXT NOT NULL, email TEXT NOT NULL UNIQUE, password TEXT NOT NULL, role_id INTEGER NOT NULL, avatar TEXT, status TEXT DEFAULT 'available', email_verified_at TEXT, consent_version TEXT DEFAULT '2026.05', created_at TEXT NOT NULL, updated_at TEXT NOT NULL, deleted_at TEXT)",
"CREATE INDEX IF NOT EXISTS users_role_status_index ON users(role_id, status)",
"CREATE TABLE IF NOT EXISTS counselling_sessions (id INTEGER PRIMARY KEY AUTOINCREMENT, intern_id INTEGER NOT NULL, client_name TEXT NOT NULL, client_avatar TEXT, topic TEXT NOT NULL, starts_at TEXT NOT NULL, duration_minutes INTEGER NOT NULL, type TEXT NOT NULL, status TEXT NOT NULL, rating REAL, bonus_minor INTEGER DEFAULT 0, created_at TEXT NOT NULL, deleted_at TEXT)",
"CREATE INDEX IF NOT EXISTS sessions_intern_starts_status_index ON counselling_sessions(intern_id, starts_at, status)",
"CREATE TABLE IF NOT EXISTS earnings (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, period TEXT NOT NULL, amount_minor INTEGER NOT NULL, source TEXT NOT NULL, created_at TEXT NOT NULL)",
"CREATE INDEX IF NOT EXISTS earnings_user_period_index ON earnings(user_id, period)",
"CREATE TABLE IF NOT EXISTS cpd_modules (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, title TEXT NOT NULL, status TEXT NOT NULL, progress INTEGER NOT NULL, due_at TEXT, created_at TEXT NOT NULL)",
"CREATE TABLE IF NOT EXISTS notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER NOT NULL, title TEXT NOT NULL, body TEXT NOT NULL, type TEXT NOT NULL, read_at TEXT, created_at TEXT NOT NULL)",
"CREATE INDEX IF NOT EXISTS notifications_user_read_index ON notifications(user_id, read_at)",
"CREATE TABLE IF NOT EXISTS audit_logs (id INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, action TEXT NOT NULL, auditable_type TEXT NOT NULL, auditable_id INTEGER, metadata TEXT NOT NULL, created_at TEXT NOT NULL)",
"CREATE INDEX IF NOT EXISTS audit_action_created_index ON audit_logs(action, created_at)"
];
