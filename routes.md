# ThriveWell OS Routes

## Browser routes
- `GET /login` — calm auth screen with demo credentials.
- `POST /login` — session authentication.
- `GET /register` — premium multi-role onboarding screen with progressive consent-first form.
- `GET /dashboard` — protected premium intern dashboard via role permission policy.
- `GET /logout` — destroy session.
- `GET /onboarding/success` — onboarding completion confirmation and next-step handoff.

## Onboarding API routes
- `POST /onboarding/draft` — CSRF-protected onboarding draft save for progressive disclosure.
- `POST /onboarding/complete` — CSRF-protected multi-role account creation with profile, consent, verification token, and audit log.

## Dashboard API routes
- `GET /dashboard/data` — JSON dashboard payload for live widgets.
- `POST /dashboard/status` — update availability status with CSRF validation and audit logging.
- `POST /notifications/read` — mark dashboard notifications read with audit logging.
- `POST /crisis/handoff` — human-first crisis/support handoff with audit logging and false-positive review readiness.
