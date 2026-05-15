<?php
/*
================================================================================
THRIVEWELL OS — PSYCHOLOGICALLY INTELLIGENT 10-YEAR MASTER FEATURE SPECIFICATION
================================================================================

DOCUMENT TYPE:
    PHP-formatted master instruction file for Codex / Google Antigravity.

PURPOSE:
    This file is an engineering and product instruction source. It is not normal
    runtime application logic. All future ThriveWell OS implementation work must
    read this file first and improve the product without reducing existing scope,
    safety, quality, or visual fidelity.

ABSOLUTE PRODUCT STANDARD:
    ThriveWell OS must become a premium, calm, psychologically safe, AI-assisted
    wellness and counselling operating system. It must not feel like a generic
    Laravel dashboard, hospital admin portal, cold clinical records system, copied
    SaaS template, or basic CRM. It must feel Apple-level, Stripe-quality,
    Headspace-warm, Linear-smooth, OpenAI-intelligent, and enterprise-ready.

PSYCHOLOGICAL DESIGN DIRECTIVE:
    - Emotional Safety: never shame, judge, overwhelm, trap, or expose users.
    - Cognitive Load Reduction: progressive disclosure, clear hierarchy, fewer choices.
    - Trauma-Informed UX: allow pause/save/exit, avoid forced disclosure, explain sensitive steps.
    - Neurodiversity Inclusion: ADHD-friendly layouts, dyslexia-friendly language, reduced motion,
      high contrast, sensory-safe UI, predictable navigation, and clear icon labels.
    - Behavioural Science: ethical progress reinforcement, no guilt mechanics, no dark patterns.
    - Trust Formation: clear privacy controls, AI limitations, human support, consent, data export/delete.

HEAVENLY UI / PREMIUM PRODUCT FEEL DIRECTIVE:
    Every page must look visually stunning, calm, modern, smooth, elegant, emotionally warm,
    spacious, refined, intelligent, accessible, and human. Required patterns include cinematic
    dark mode, equally beautiful light mode, glassmorphism panels, soft gradients, premium cards,
    subtle motion, beautiful charts, progress rings, radar charts, activity timelines, avatars,
    empty states, loading skeletons, success states, calm errors, permission-denied states,
    mobile/tablet/desktop layouts, and WCAG-conscious contrast.

MANDATORY DASHBOARD VISUAL BENCHMARK:
    The supplied intern dashboard image is the minimum quality benchmark. Dashboards must include
    left sidebar, role navigation, top greeting, profile controls, notifications, availability pill,
    KPI cards, charts, wallet/listening bonus panels, schedule panels, activity lists, CPD progress,
    radar charts, soft neon accents, cinematic background, premium spacing, responsive behaviour,
    and role-specific information architecture. Do not copy the image exactly; exceed it.

GLOBAL DESIGN SYSTEM COMPONENTS:
    AppShell, Sidebar, MobileNavigation, TopBar, ThemeToggle, NotificationBell, UserProfileMenu,
    StatusPill, StatCard, SparklineCard, ChartCard, RadarChart, ProgressRing, WalletCard,
    SchedulePanel, RecentActivityPanel, AICompanionPanel, EmptyState, LoadingSkeleton, ErrorState,
    SuccessState, DataTablePro, FilterBar, SearchCommandPalette, AuditTimeline, ConsentBanner,
    CrisisButton, FormBuilderField, NoCodeCanvas, DragDropWidget, WorkflowNode.

BUILD ORDER:
    Phase 0 - Repository preparation and architecture.
    Phase 1 - Laravel foundation, auth, roles, permissions, design system, AppShell, Sidebar,
              TopBar, theme system, dashboard shell, audit logs.
    Phase 2 - Version 1.0 core platform foundation.
    Phase 3 - Version 2.0 enhanced tools.
    Phase 4 - Version 3.0 advanced features.
    Phase 5 - Version 3.1 optimisation and security.
    Phase 6 - Version 4.0 AI and innovation.
    Phase 7 - Version 5.0 enterprise expansion.
    Phase 8 - Version 5.1 safety and infrastructure.
    Phase 9 - Version 6.0 final expansion.
    Phase 10 - QA, performance, security, documentation, seed data, and deployment readiness.

CORE MODULES TO IMPLEMENT OVER TIME:
    - Multi-Role Registration & Onboarding with progressive disclosure, consent, email verification,
      draft saving, role-specific welcome screens, audit logs, and route-to-dashboard behaviour.
    - Counsellor Credential Verification with private storage, signed URLs, secure document cards,
      admin review queues, revision requests, verification badges, and access audit logs.
    - Session Booking with counsellor discovery, AI-assisted matching, filters, availability calendar,
      timezone handling, confirmation, payment, reminders, reschedule/cancel, and double-booking prevention.
    - Native Video Consultation with waiting room, device checks, low-bandwidth mode, captions, chat,
      notes side panel, crisis button, consent-before-recording, and editable AI summaries.
    - Kale AI Companion with mood check-ins, journaling prompts, guided breathing, session preparation,
      resource recommendations, consent-controlled memory, prompt-injection defence, and human handoff.
    - Digital Twin Wellness Model with consent-first pattern insight, explainability, export/delete controls,
      encryption, and no hidden profiling.
    - Special Needs Session Mode with captions, sign-language readiness, interpreter role, text alternatives,
      large controls, visual alerts, confidence labels, and human fallback.
    - Crisis Response Network with panic button, risk tiers, emergency contacts, crisis dashboard, incident logs,
      follow-up tasks, false-positive review, human override, and mandatory-reporting readiness.
    - Journal System with private entries, mood prompts, voice journaling, AI reflection, search, export,
      consent-controlled sharing, and crisis escalation.
    - Assessment System with one-question-at-a-time UX, scoring, trends, thresholds, client-safe explanations,
      and non-judgemental language.
    - The Circle Peer Support with anonymity, topic spaces, reporting, AI moderation, human moderation,
      crisis detection, no doxxing, and community safety guidelines.
    - Superadmin No-Code Builder with dashboard/sidebar/page/form/workflow/theme/feature/permission builders,
      role preview, version history, publish approval, undo/redo, audit logs, rollback, and no arbitrary code.
    - Analytics & Executive Intelligence with aggregated privacy-safe wellness index, utilization, ROI,
      engagement, minimum group thresholds, no personal clinical details, and audited exports.
    - Payment, Wallets & Revenue Splits with Paystack first, Stripe/Flutterwave/OPay readiness, subscriptions,
      wallets, intern listening bonus, payouts, revenue splits, invoices, receipts, reconciliation, refunds,
      webhook signatures, HMAC validation, idempotency, and immutable financial audit logs.

CLINICAL AND AI SAFETY REQUIREMENTS:
    - Kale AI Companion must never claim to replace therapy.
    - No AI diagnosis, no medication advice, and no false clinical authority.
    - Crisis escalation must be human handoff first with audit logs and consent-aware monitoring.
    - Crisis false positives must be reviewed and tiered.
    - AI memory must be opt-in, explainable, exportable, and erasable.
    - All AI outputs must be editable before saving to clinical records.

SECURITY REQUIREMENTS:
    - Use policies and gates for role access.
    - Use audit logs for sensitive actions.
    - Use encrypted/private storage and signed URLs for private downloads.
    - Use rate limiting for login, OTP, chat, AI, booking, and payment endpoints.
    - Verify payment webhooks with signatures.
    - Store money as integer minor units.
    - Add database indexes for dashboard queries.
    - Add soft deletes where required.
    - Add data retention and consent version tracking.

ACCEPTANCE STANDARD:
    A feature is not accepted unless it is visually premium, works end-to-end, uses real data,
    has permissions, validation, audit logs, security controls, dark/light mode, responsive layouts,
    loading/empty/error/success/permission-denied states, accessibility support, tests, documentation,
    psychological safety, AI safety where applicable, and compliance safeguards.

FINAL CODING DIRECTIVE:
    Build ThriveWell OS like a category-defining 10-year platform. Every screen must be beautiful,
    every workflow humane, every dashboard premium, every AI interaction safe, every sensitive action
    audited, every organization configurable, every page responsive, and every feature production-minded.
    Do not reduce scope; only improve the implementation.
================================================================================
END OF FILE
================================================================================
*/
