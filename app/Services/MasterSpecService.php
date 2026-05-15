<?php
namespace App\Services;

class MasterSpecService
{
    public const PATH = __DIR__.'/../../THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php';

    public function text(): string
    {
        return file_get_contents(self::PATH) ?: '';
    }

    public function summary(): array
    {
        $text = $this->text();
        $required = [
            'premium_ui' => 'HEAVENLY UI / PREMIUM PRODUCT FEEL DIRECTIVE',
            'dashboard_benchmark' => 'MANDATORY DASHBOARD VISUAL BENCHMARK',
            'design_system' => 'GLOBAL DESIGN SYSTEM COMPONENTS',
            'clinical_ai_safety' => 'CLINICAL AND AI SAFETY REQUIREMENTS',
            'security' => 'SECURITY REQUIREMENTS',
            'acceptance' => 'ACCEPTANCE STANDARD',
            'no_reduction' => 'Do not reduce scope; only improve the implementation.',
        ];

        $coverage = [];
        foreach ($required as $key => $needle) {
            $coverage[$key] = str_contains($text, $needle);
        }

        preg_match_all('/Phase\s+(\d+)\s+-\s+([^\n]+)/', $text, $matches, PREG_SET_ORDER);
        $phases = array_map(fn ($match) => ['phase' => (int) $match[1], 'title' => trim($match[2])], $matches);

        return [
            'source_path' => 'THRIVEWELL_OS_PSYCHOLOGICAL_PREMIUM_MASTER_SPEC.php',
            'checksum' => hash('sha256', $text),
            'coverage' => $coverage,
            'coverage_percent' => (int) floor((count(array_filter($coverage)) / count($coverage)) * 100),
            'phases' => $phases,
            'directives' => [
                'Only improvements are allowed; no feature reduction.',
                'Every dashboard must meet or exceed the supplied benchmark.',
                'Kale AI is assistive only and human handoff remains first for crisis concerns.',
                'Every sensitive action needs permissions, validation, audit logs, and safety language.',
            ],
        ];
    }
}
