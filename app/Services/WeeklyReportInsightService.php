<?php

namespace App\Services;

use App\Services\OpenRouterService;

class WeeklyReportInsightService
{
    public function generate($summary, $tasks)
    {
        $payload = [
            'summary' => $summary,
            'tasks' => $tasks->map(function ($task) {
                $dueDate = optional($task->timelineItem)->end_date;

                return [
                    'title' => $task->title,
                    'priority' => $task->priority,
                    'kanban_status' => $task->kanban_status,
                    'due_date' => $dueDate,
                    'remaining_days' => $dueDate ? now()->diffInDays($dueDate, false) : null,
                ];
            })->values()->toArray()
        ];

        try {
            $openRouter = app(OpenRouterService::class);

            $result = $openRouter->chat(
                json_encode($payload),
                'Kamu adalah AI Project Manager.

Analisa laporan mingguan proyek berdasarkan data berikut.

Tugas kamu:
1. Evaluasi performa minggu ini
2. Bandingkan dengan minggu lalu
3. Identifikasi risiko / bottleneck
4. Berikan rekomendasi singkat

Aturan output:
- 1 paragraf saja
- 3–5 kalimat
- Bahasa Indonesia profesional
- Fokus pada insight, bukan listing data
- Highlight hanya hal paling penting'
            );

            return $result;

        } catch (\Exception $e) {
            \Log::error('WeeklyReport AI Error: ' . $e->getMessage());

            return 'Insight tidak tersedia saat ini.';
        }
    }
}