# ThriveWell OS Routes

## Browser routes
- `GET /login` — calm auth screen with demo credentials.
- `POST /login` — session authentication.
- `GET /register` — registration readiness screen.
- `GET /dashboard` — protected premium intern dashboard via role permission policy.
- `GET /logout` — destroy session.

## Dashboard API routes
- `GET /dashboard/data` — JSON dashboard payload for live widgets.
- `POST /dashboard/status` — update availability status with CSRF validation and audit logging.
- `POST /notifications/read` — mark dashboard notifications read with audit logging.
- `POST /crisis/handoff` — human-first crisis/support handoff with audit logging and false-positive review readiness.
