<?php

namespace App\Services;

use App\Models\WbsItem;
use App\Services\OpenRouterService;

class TaskInsightService
{
    public function analyzeTasks($tasks)
    {
        $payload = $tasks->map(function ($task) {
            $dueDate = optional($task->timelineItem)->end_date;

            return [
                'id' => $task->id,
                'title' => $task->title,
                'description' => $task->description,
                'priority' => $task->priority,
                'estimated_days' => $task->estimated_days,
                'due_date' => $dueDate,
                'kanban_status' => $task->kanban_status,
                'remaining_days' => $dueDate ? now()->diffInDays($dueDate, false) : null,
            ];
        })->values()->toArray();

        try {
            $openRouter = app(OpenRouterService::class);

            $result = $openRouter->chat(
                json_encode($payload),
                'Kamu adalah AI Project Manager.

    Analisa seluruh daftar task berikut secara bersamaan.

    Tugas kamu:
    1. Urutkan task dari yang PALING HARUS Dikerjakan terlebih dahulu
    2. Identifikasi mana yang berpotensi delay
    3. Berikan 1 paragraf insight keseluruhan

    Aturan output:
    - 1 paragraf saja
    - 3–5 kalimat
    - Bahasa Indonesia profesional
    - Jangan pakai pembuka seperti "baik berikut analisisnya"
    - Langsung jawab isi insight
    - Tidak perlu menjawab dari task 1 sampai akhir, jawab saja 1-2 task yang mempunyai potensi keterlambatan
    - Kalau ada task yang terlambat, Suruh user yang bersangkutan untuk segerea menyelesaikan'
            );

            return $result;

        } catch (\Exception $e) {
            \Log::error('TaskInsight AI Error: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'AI service failed',
            ];
        }
    }
}
