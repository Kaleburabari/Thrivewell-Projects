<?php
namespace App\Services;

class AiSafetyService
{
    public const KALE_BOUNDARY = 'Kale AI Companion is supportive and educational. It never replaces therapy, diagnoses conditions, or gives medication advice. Crisis concerns are routed to human handoff first.';

    public function guardrailSummary(): array
    {
        return ['no_diagnosis' => true, 'no_medication_advice' => true, 'human_handoff_first' => true, 'memory_opt_in' => true, 'editable_before_clinical_record' => true];
    }
}
