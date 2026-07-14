<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Services\WeeklyReportInsightService;
use App\Models\Project;
use App\Models\WbsItem;

class ReportController extends Controller
{
    public function weekly($projectId)
    {
        $startOfWeek = now()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = now()->endOfWeek(Carbon::FRIDAY);

        $lastWeekStart = $startOfWeek->copy()->subWeek();
        $lastWeekEnd = $endOfWeek->copy()->subWeek();

        // ✅ Project + member
        $project = Project::with('humanResourcePlan.humanResourceItems.teamMember')
            ->findOrFail($projectId);

        // ✅ Tasks
        $tasks = WbsItem::with('timelineItem')
            ->where('project_id', $projectId)
            ->whereIn('kanban_status', ['todo', 'ongoing']) // fokus yang belum selesai
            ->orderBy('priority', 'desc')
            ->take(15)
            ->get();

        // =========================
        // ✅ OVERALL PROGRESS (Done / Total)
        // =========================

        $total = WbsItem::where('project_id', $projectId)->count();

        $done = WbsItem::where('project_id', $projectId)
            ->where('kanban_status', 'done')
            ->count();

        $thisWeek = $total > 0 ? ($done / $total) * 100 : 0;


        // =========================
        // ✅ WEEKLY COMPLETION (berapa task selesai minggu ini)
        // =========================

        $doneThisWeek = WbsItem::where('project_id', $projectId)
            ->where('kanban_status', 'done')
            ->whereBetween('updated_at', [$startOfWeek, $endOfWeek])
            ->count();

        $doneLastWeek = WbsItem::where('project_id', $projectId)
            ->where('kanban_status', 'done')
            ->whereBetween('updated_at', [$lastWeekStart, $lastWeekEnd])
            ->count();

        $diff = $doneThisWeek - $doneLastWeek;


        // =========================
        // (Optional) kalau masih mau tampil persen minggu lalu
        // =========================

        $lastWeek = $total > 0 ? (($done - $diff) / $total) * 100 : 0;

        // =========================
        // ✅ BREAKDOWN
        // =========================

        $todo = WbsItem::where('project_id', $projectId)->where('kanban_status', 'todo')->count();
        $ongoing = WbsItem::where('project_id', $projectId)->where('kanban_status', 'ongoing')->count();
        $done = WbsItem::where('project_id', $projectId)->where('kanban_status', 'done')->count();
        $approved = WbsItem::where('project_id', $projectId)->where('kanban_status', 'approved')->count();

        // =========================
        // ✅ BARU BUAT SUMMARY
        // =========================

        $summary = [
            'this_week' => $thisWeek,
            'last_week' => $lastWeek,
            'diff' => $diff,
            'todo' => $todo,
            'ongoing' => $ongoing,
            'done' => $done,
            'approved' => $approved,
        ];

        // =========================
        // ✅ BARU PANGGIL AI
        // =========================

        $aiInsight = app(WeeklyReportInsightService::class)
            ->generate($summary, $tasks);

        // =========================
        // ✅ PDF
        // =========================

        $pdf = Pdf::loadView('pdf.weekly-report', compact(
            'project',
            'thisWeek',
            'lastWeek',
            'diff',
            'todo',
            'ongoing',
            'done',
            'approved',
            'aiInsight'
        ));

        return $pdf->download('weekly-report.pdf');
    }
}
