# ThriveWell OS Routes

## Browser routes
- `GET /login` — calm auth screen with demo credentials.
- `POST /login` — session authentication.
- `GET /register` — premium multi-role onboarding screen with progressive consent-first form.
- `GET /credentials` — protected private credential vault.
- `GET /admin/credentials` — protected admin credential verification review queue.
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

## Credential verification routes
- `POST /credentials/submit` — submit private credential metadata for review.
- `POST /credentials/signed-download` — create short-lived signed download token readiness.
- `POST /admin/credentials/approve` — approve credential and issue badge readiness.
- `POST /admin/credentials/revision` — request calm credential revision.
