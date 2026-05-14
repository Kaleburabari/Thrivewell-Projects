<?php
return [
    'name' => 'ThriveWell OS',
    'clinical_safety' => [
        'ai_disclaimer' => 'Kale AI Companion never replaces therapy, diagnosis, medication guidance, or emergency support.',
        'crisis_handoff' => 'Human handoff first with audit logging and consent-aware monitoring.',
    ],
    'payments' => ['primary' => 'paystack', 'adapters' => ['paystack', 'stripe_ready', 'flutterwave_ready', 'opay_ready']],
];
