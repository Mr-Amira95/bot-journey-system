<?php

namespace App\Services;

use App\Models\Brd;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AiCanvasService
{
    private const PROGRAM_CONTEXT = <<<'TEXT'
        Program context (true for every use case in this engagement — treat as given, do not contradict it):
        - This engagement deploys AI agents / use cases on top of an already-delivered, existing Azure Databricks
          environment for Al Tanfeethi, a Saudi hospitality company that provides VIP/vendor services catering to
          Royalty, VIP and GVIP customers for airport and travel services.
        - A prior foundation project already built the data pipelines and a handful of agents. Those pipelines
          regularly bring in data from 2-3 source systems.
        - Delivery commitment: a standard use case is designed, built, tested and validated within 1 week. A more
          complex use case that requires integration with outside/external systems or additional data sources
          beyond the existing pipelines takes 2 weeks.
        - Databricks (on Azure) is the delivery platform for every use case. Reference it as the platform lightly
          (e.g. "built and orchestrated on the existing Azure Databricks environment") — do NOT produce deep
          technical solutioning, architecture diagrams, pipeline design, or implementation detail. This document is
          a client-facing business one-sheet, not a technical design doc.
        TEXT;

    public function generate(Brd $brd): array
    {
        $apiKey = config('services.claude.key');

        if (! $apiKey) {
            throw new RuntimeException('Claude API key is not configured. Set Claude_API_KEY in .env.');
        }

        $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])
            ->timeout(90)
            ->post('https://api.anthropic.com/v1/messages', [
                'model'       => config('services.claude.model'),
                'max_tokens'  => 4000,
                'system'      => $this->systemPrompt(),
                'messages'    => [
                    ['role' => 'user', 'content' => $this->brdSummary($brd)],
                ],
                'tools'       => [$this->canvasTool()],
                'tool_choice' => ['type' => 'tool', 'name' => 'render_ai_canvas'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Claude API request failed: ' . $response->body());
        }

        $toolUse = collect($response->json('content', []))->firstWhere('type', 'tool_use');

        if (! $toolUse || empty($toolUse['input'])) {
            throw new RuntimeException('Claude did not return a structured AI Canvas.');
        }

        return $toolUse['input'];
    }

    private function systemPrompt(): string
    {
        return <<<TEXT
            You are an AI solutions consultant preparing a one-page "AI Canvas" — a client-facing summary of a single
            use case, built from its Business Requirements Document (BRD). It will be shared directly with the client,
            so it must be informative, concise and professional.

            {$this->programContext()}

            Ground the content in the BRD provided. Where the BRD gives concrete numbers or metrics, use them. Where it
            doesn't, describe directional/qualitative value (e.g. "reduces manual review time") rather than fabricating
            precise figures. Do not invent client facts beyond the BRD and the program context above. Keep every field
            tight and skimmable — this is a one-sheet, not a report.

            Decide the delivery timeline yourself based on the BRD: "1 week" for a standard use case scoped to the
            existing pipelines/data, or "2 weeks" if it clearly requires integration with an outside system or a new
            data source not already covered by the existing pipelines.

            Respond only by calling the render_ai_canvas tool.
            TEXT;
    }

    private function programContext(): string
    {
        return self::PROGRAM_CONTEXT;
    }

    private function brdSummary(Brd $brd): string
    {
        $stakeholders = $brd->stakeholders->map(fn ($s) => trim(
            $s->name . ($s->role ? " ({$s->role})" : '')
        ))->filter()->implode(', ');

        $lines = [
            'BRD title: ' . $brd->title,
            'Project: ' . ($brd->project?->name ?? '—'),
            'Department: ' . ($brd->department ?? '—'),
            'Priority: ' . ($brd->priority->value ?? $brd->priority ?? '—'),
            'Description: ' . $brd->description,
            'Business objective: ' . ($brd->objective ?? '—'),
            'Scope: ' . ($brd->scope ?? '—'),
            'Current ("as-is") workflow: ' . ($brd->as_is_workflow ?? '—'),
            'Current pain points: ' . ($brd->as_is_pain_points ?? '—'),
            'Existing systems/tools involved: ' . ($brd->as_is_existing_systems ?? '—'),
            'Proposed ("to-be") workflow: ' . ($brd->to_be_workflow ?? '—'),
            'Expected benefits: ' . ($brd->to_be_benefits ?? '—'),
            'KPIs provided in the BRD: ' . ($brd->kpis ?? '—'),
            'Stakeholders: ' . ($stakeholders ?: '—'),
        ];

        return implode("\n", $lines);
    }

    private function canvasTool(): array
    {
        return [
            'name'         => 'render_ai_canvas',
            'description'  => 'Renders the structured content of a one-page, client-facing AI Canvas for this use case.',
            'input_schema' => [
                'type'       => 'object',
                'properties' => [
                    'use_case_name'      => ['type' => 'string', 'description' => 'Short, client-friendly name for the use case (not the raw BRD title if it can be sharper).'],
                    'executive_summary'  => ['type' => 'string', 'description' => '2-3 sentence summary of the use case and the value it delivers.'],
                    'problem_statement'  => ['type' => 'string', 'description' => '1-3 sentences on the current pain point being solved.'],
                    'proposed_solution'  => ['type' => 'string', 'description' => '2-4 sentences on the AI/agent solution at a business level, mentioning it runs on the existing Azure Databricks environment without deep technical detail.'],
                    'data_sources'       => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Short list of the source systems / data involved.'],
                    'kpis'               => [
                        'type'  => 'array',
                        'items' => [
                            'type'       => 'object',
                            'properties' => [
                                'metric' => ['type' => 'string'],
                                'target' => ['type' => 'string'],
                            ],
                            'required' => ['metric', 'target'],
                        ],
                        'description' => '3-5 KPIs with a target or directional expectation for each.',
                    ],
                    'roi_highlights'     => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '2-4 short bullet points on potential ROI / business value.'],
                    'timeline_estimate'  => ['type' => 'string', 'enum' => ['1 week', '2 weeks'], 'description' => 'Delivery estimate including testing and validation.'],
                    'timeline_rationale' => ['type' => 'string', 'description' => 'One short sentence on why this timeline applies.'],
                    'assumptions_risks'  => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '1-3 short assumptions or risks.'],
                    'next_steps'         => ['type' => 'array', 'items' => ['type' => 'string'], 'description' => '2-4 short next steps to kick off delivery.'],
                ],
                'required' => [
                    'use_case_name', 'executive_summary', 'problem_statement', 'proposed_solution',
                    'data_sources', 'kpis', 'roi_highlights', 'timeline_estimate', 'timeline_rationale',
                    'assumptions_risks', 'next_steps',
                ],
            ],
        ];
    }
}
