<?php

namespace App\Services;

use App\Services\OpenRouterService;

class RiskSuggestionService
{
    private const SYSTEM_PROMPT = '
Kamu adalah Senior Project Risk Analyst.

Kamu diberikan:

1. Scope proyek
2. Ringkasan kondisi proyek saat ini
3. Daftar task penting
4. Daftar issue aktif
5. Seluruh risk register

Tujuanmu adalah MENILAI apakah risk register yang sudah dibuat mulai menunjukkan indikasi akan terjadi.

JANGAN membuat risk baru.

Evaluasi dilakukan berdasarkan:

- tujuan proyek
- ruang lingkup
- jumlah task yang selesai
- jumlah task yang masih berjalan
- jumlah issue
- issue prioritas tinggi
- hubungan issue dengan penyebab risk
- probability & severity risk

Jika sebuah risk belum menunjukkan indikasi, abaikan.

Output HARUS JSON VALID.

{
  "summary":"",

  "overall_risk_level":"Low | Medium | High",

  "predicted_risks":[
      {
        "risk_title":"",
        "likelihood":"High | Medium | Low",
        "confidence":90,
        "reason":"",
        "recommendation":""
      }
  ]
}

Rules:

- Maksimal 5 risk.
- Jangan membuat risk baru.
- Gunakan HANYA risk register yang diberikan.
- confidence berupa angka 0-100.
- Output HARUS JSON valid.
- Jangan gunakan markdown.
- Jangan gunakan ```json.
- Jangan memberikan penjelasan selain JSON.
';

    private function buildPayload($projectScope, $tasks, $issues, $risks): array
    {
        $taskSummary = [
            'total_tasks' => $tasks->count(),
            'todo' => $tasks->where('kanban_status', 'todo')->count(),
            'in_progress' => $tasks->where('kanban_status', 'doing')->count(),
            'review' => $tasks->where('kanban_status', 'review')->count(),
            'done' => $tasks->where('kanban_status', 'done')->count(),
            'high_priority' => $tasks->where('priority', 'high')->count(),
            'medium_priority' => $tasks->where('priority', 'medium')->count(),
            'low_priority' => $tasks->where('priority', 'low')->count(),
        ];

        $issueSummary = [
            'total_issues' => $issues->count(),
            'open' => $issues->where('status', 'open')->count(),
            'in_progress' => $issues->where('status', 'in_progress')->count(),
            'resolved' => $issues->where('status', 'resolved')->count(),
            'closed' => $issues->where('status', 'closed')->count(),
            'high_priority' => $issues->where('priority', 'high')->count(),
            'medium_priority' => $issues->where('priority', 'medium')->count(),
            'low_priority' => $issues->where('priority', 'low')->count(),
        ];

        return [

            'project_scope' => [
                'objective' => $projectScope?->objective,
                'scope_description' => $projectScope?->scope_description,
                'constraints' => $projectScope?->constraints,
            ],

            'project_condition' => [
                'task_summary' => $taskSummary,
                'issue_summary' => $issueSummary,
            ],

            'critical_tasks' => $tasks
                ->whereIn('priority', ['high', 'critical'])
                ->take(10)
                ->map(fn($task) => [
                    'title' => $task->title,
                    'status' => $task->kanban_status,
                    'priority' => $task->priority,
                ])
                ->values()
                ->toArray(),

            'active_issues' => $issues
                ->whereIn('status', ['open', 'in_progress'])
                ->take(10)
                ->map(fn($issue) => [
                    'title' => $issue->title,
                    'priority' => $issue->priority,
                    'status' => $issue->status,
                ])
                ->values()
                ->toArray(),

            'risk_register' => $risks
                ->map(fn($risk) => [
                    'title' => $risk->risk_title,
                    'description' => $risk->risk_description,
                    'cause' => $risk->risk_cause,
                    'impact' => $risk->impact,
                    'probability' => $risk->probability,
                    'severity' => $risk->severity,
                ])
                ->values()
                ->toArray(),
        ];
    }

    public function generate($projectScope, $tasks, $issues, $risks)
    {
        try {

            $payload = $this->buildPayload(
                $projectScope,
                $tasks,
                $issues,
                $risks
            );

            $openRouter = app(OpenRouterService::class);

            $response = $openRouter->chat(
                json_encode($payload, JSON_UNESCAPED_UNICODE),
                self::SYSTEM_PROMPT
            );

            return json_decode($response, true);
        } catch (\Throwable $e) {

            \Log::error($e);

            return [
                'summary' => 'AI Suggestion tidak tersedia.',
                'overall_risk_level' => 'Unknown',
                'predicted_risks' => [],
            ];
        }
    }
}
