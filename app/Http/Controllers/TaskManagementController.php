<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WbsItem;
use App\Models\Project;

use Illuminate\Http\Request;


class TaskManagementController extends Controller
{
    public function index()
    {
        $projects = Project::with(['owner', 'projectManager'])
            ->select(
                'id',
                'title',
                'owner_id',
                'manager_id',
                'start_date',
                'end_date'
            )
            ->get();

        return view('project-executing.task-management.index', compact('projects'));
    }

    public function show(Request $request, Project $project)
    {
        $query = $project->wbsItems()
        ->with([
            'users',
            'timelineItem'
        ]);

        $query = $this->applyFilters($query, $request);
        $allTasksRaw = $query->get();

        $allTasks = $allTasksRaw->groupBy(function ($task) {
            return match (strtolower($task->kanban_status ?? 'todo')) {
                'todo', 'to-do' => 'todo',
                'ongoing', 'on-going', 'in_progress' => 'ongoing',
                'done' => 'done',
                'approved' => 'approved',
                default => 'todo'
            };
        });

        return view(
            'project-executing.task-management.show',
            compact(
                'project',
                'allTasks',
                'allTasksRaw'
            )
        );
    }

    private function applyFilters($query, Request $request)
    {
        // filter by assigned task to user
        if ($request->assigned === 'me') {
            $user = auth()->user();
            $query->whereHas('users', function ($q) use ($user) {
                $q->where('users.id', $user->id);
            });

        }

        if ($request->priority && $request->priority !== 'all') {$query->where('priority', $request->priority);}
        if ($request->due === 'today') {$query->whereHas('timelineItem', function ($q) {$q->whereDate('end_date', now());});}
        if ($request->due === 'overdue') {$query->whereHas('timelineItem', function ($q) {$q->whereDate('end_date', '<', now());});}
        if ($request->due === 'done') {$query->where('kanban_status', 'done');}
        if ($request->due === 'approved') {$query->where('kanban_status', 'approved');}
        if ($request->status === 'finished') {$query->finished();}
        if ($request->status === 'unfinished') {$query->unfinished();}

        return $query;
    }

    public function updateStatus(Request $request, $id)
    {
        $task = WbsItem::findOrFail($id);

        $task->status_updated_by = auth()->id();
        $task->status_updated_at = now();
        $task->kanban_status = $request->status;

        if ($request->status === 'done' || $request->status === 'approved') {
            $task->task_status_finished_at = now();
        } else {
            $task->task_status_finished_at = null;
        }

        $task->save();

        return response()->json([
            'success' => true
        ]);
    }

    public function getTaskInsight($projectId)
    {
        $project = Project::findOrFail($projectId);

        $tasks = $project->wbsItems()->with(['timelineItem','users'])->get();

        if ($tasks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada task'
            ]);
        }

        $importantTasks = $tasks->filter(function ($task) {
            $due = optional($task->timelineItem)->end_date;
            $days = $due ? now()->diffInDays($due, false) : null;

            return $task->priority === 'high' || ($days !== null && $days <= 3);
        })->values();

        if ($importantTasks->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada task penting'
            ]);
        }

        $service = app(\App\Services\TaskInsightService::class);
        $result = $service->analyzeTasks($importantTasks);

        return response()->json([
            'success' => true,
            'message' => $result
        ]);
    }
}
