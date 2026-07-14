<?php

namespace App\Services;

use App\Models\Project;
use App\Models\WbsItem;
use App\Models\TimelineItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\MeetingSchedule;

class DashboardService
{
    public function getDashboardData($user)
    {
        $projectQuery = $this->buildProjectQuery($user);
        $itSummary = $this->getITTaskSummary($user);

        // take data
        $data = [
            'totalProjects' => $this->countProjects($projectQuery),
            'draftProjects' => $this->countByStatus($projectQuery, 'draft'),
            'submittedProjects' => $this->countByStatus($projectQuery, 'submitted'),
            'planningProjects' => $this->countPlanning($projectQuery),
            'completedProjects' => $this->countByStatus($projectQuery, 'completed'),
            'projectAnalytics' => $this->getProjectAnalytics($projectQuery),
        ];

        $valueMap = [
            'total_projects' => $data['totalProjects'],
            'draft' => $data['draftProjects'],
            'on_request' => $data['submittedProjects'],
            'planning' => $data['planningProjects'],
            'done' => $data['completedProjects'],

            'on_progress_task' => $itSummary['on_progress_task'],
            'todo_task'        => $itSummary['todo_task'],
            'overdue_task'     => $itSummary['overdue_task'],
            'done_task'        => $itSummary['done'],
        ];

        // config dan inject value to cards
        $cards = collect($this->getCards($user))
            ->map(function ($card) use ($valueMap) {
                $card['value'] = $valueMap[$card['type']] ?? 0;
                return $card;
            });

        $nextMeeting = MeetingSchedule::with('project')
            ->where('status', 'Scheduled')
            ->where(function ($query) {
                $query->whereDate('meeting_date', '>', now()->toDateString())
                    ->orWhere(function ($query) {
                        $query->whereDate('meeting_date', now()->toDateString())
                            ->whereTime('start_time', '>=', now()->format('H:i:s'));
                    });
            })
            ->orderBy('meeting_date')
            ->orderBy('start_time')
            ->first();

        return [
            'showCards' => count($cards) > 0,
            'cards' => $cards,
            'recentProjects' => $this->getRecentProjects($projectQuery),
            'nextActions' => $this->getNextActions($user),
            'assignedTask' => $this->getAssignedTask($user),
            'projectAnalytics' => $data['projectAnalytics'],
            'nextMeeting' => $nextMeeting,
        ];
    }

    public function getDetailProjectDashboard($id)
    {
        // get all data from only one project by id
        $project = Project::with([
            'owner',
            'manager',
            'wbsItems.users',
            'timelineItems',
            'proposal'
        ])->findOrFail($id);

        $remainingDays = null;
        $tasks = $project->wbsItems;

        $memberStats = $tasks->flatMap(function ($task) {
            return $task->users->map(function ($user) use ($task) {
                return [
                    'name' => $user->name,
                    'role' => $user->pivot->role ?? '-',
                    'status' => $task->kanban_status,
                ];
            });
        })
            ->groupBy('name')
            ->map(function ($items, $name) {

                $total = $items->count();
                $done = $items->where('status', 'done')->count();

                $progress = $total > 0
                    ? round(($done / $total) * 100)
                    : 0;

                return [
                    'name' => $name,
                    'role' => $items->pluck('role')->unique()->implode(', ') ?: '-',
                    'total' => $total,
                    'done' => $done,
                    'progress' => $progress,
                ];
            })
            ->values();

        if ($project->end_date) {
            $remainingDays = floor(Carbon::now()->diffInDays($project->end_date, false));
        }

        $totalTasks = $tasks->count();
        $todoTasks = $tasks->where('kanban_status', 'todo')->count();
        $inProgressTasks = $tasks->where('kanban_status', 'ongoing')->count();
        $doneTasks = $tasks->where('kanban_status', 'done')->count();
        $overdueTasks = $tasks->filter(function ($task) {
            return $task->end_date
                && $task->kanban_status !== 'done'
                && now()->gt(Carbon::parse($task->end_date));
        })->count();

        $cards = [
            [
                'label' => 'Total Task',
                'value' => $totalTasks,
                'background' => 'bg-gradient-to-br from-blue-500 to-gradientBlue',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'route' => null,
                'infoColor' => 'text-white',
            ],
            [
                'label' => 'To Do',
                'value' => $todoTasks,
                'background' => 'bg-pink-400',
                'titleColor' => '',
                'valueColor' => '',
                'route' => null,
                'infoColor' => '',
            ],
            [
                'label' => 'In Progress',
                'value' => $inProgressTasks,
                'background' => 'bg-cyan-400',
                'titleColor' => '',
                'valueColor' => '',
                'route' => null,
                'infoColor' => '',
            ],
            [
                'label' => 'Overdue',
                'value' => $overdueTasks,
                'background' => 'bg-gradient-to-br from-orangeBg to-gradientOrange',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'route' => null,
                'infoColor' => 'text-white',
            ],
            [
                'label' => 'Done',
                'value' => $doneTasks,
                'background' => 'bg-gradient-to-br from-greenBg to-gradientGreen',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'route' => null,
                'infoColor' => 'text-white',
            ],
        ];

        return [
            'cards' => $cards,
            'showCards' => count($cards) > 0,
            'project' => $project,
            'title' => $project->title,
            'background' => $project->proposal->background ?? '-',
            'start_date' => $project->start_date ?? '-',
            'end_date' => $project->end_date ?? '-',
            'remainingDays' => $remainingDays,

            // contoh data tambahan (biar dashboard lebih hidup)
            'totalTasks' => $project->wbsItems->count(),
            'completedTasks' => $project->wbsItems->where('kanban_status', 'done')->count(),
            'pendingTasks' => $project->wbsItems->where('kanban_status', '!=', 'done')->count(),
            'totalTimeline' => $project->timelineItems->count(),
            'todoTasks' => $todoTasks,
            'inProgressTasks' => $inProgressTasks,
            'doneTasks' => $doneTasks,
            'overdueTasks' => $overdueTasks,
            'memberStats' => $memberStats,
        ];
    }

    private function getITTaskSummary($user)
    {
        if (!$user) {
            return [
                'on_progress_task' => 0,
                'todo_task' => 0,
                'overdue_task' => 0,
                'done' => 0,
            ];
        }

        $tasks = WbsItem::query()
            ->join('task_user', 'wbs_items.id', '=', 'task_user.wbs_item_id')
            ->where('task_user.user_id', $user->id);

        $overdueTask = (clone $tasks)
            ->where('wbs_items.kanban_status', '!=', 'done')
            ->get()
            ->filter(function ($task) {
                $dueDate = Carbon::parse($task->created_at)
                    ->addDays($task->estimated_duration_days);

                return $dueDate->isPast();
            })
            ->count();

        return [
            'on_progress_task' => (clone $tasks)
                ->where('wbs_items.kanban_status', 'ongoing')
                ->count(),

            'todo_task' => (clone $tasks)
                ->where('wbs_items.kanban_status', 'todo')
                ->count(),

            'done' => (clone $tasks)
                ->where('wbs_items.kanban_status', 'done')
                ->count(),

            'overdue_task' => $overdueTask,
        ];
    }

    private function getAssignedTask($user)
    {
        // kalau bukan user IT
        if (!$user->teamMember) {
            return collect();
        }

        $tasks = DB::table('task_user')
            ->join('wbs_items', 'task_user.wbs_item_id', '=', 'wbs_items.id')
            ->join('projects', 'wbs_items.project_id', '=', 'projects.id')
            ->where('task_user.user_id', $user->id)
            ->where('wbs_items.kanban_status', '!=', 'done')
            ->orderByRaw("
            CASE
                WHEN wbs_items.priority = 'high' THEN 1
                WHEN wbs_items.priority = 'medium' THEN 2
                ELSE 3
            END
        ")
            ->select([
                'projects.id as project_id',
                'projects.title as project_name',
                'wbs_items.title as task_name',
                'wbs_items.priority',
                'wbs_items.kanban_status',
                'wbs_items.created_at',
                'wbs_items.estimated_duration_days',
            ])
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $dueDate = Carbon::parse($row->created_at)
                    ->addDays($row->estimated_duration_days);

                $row->due_date = $dueDate->toDateTimeString();
                return $row;
            });

        return $tasks;
    }

    // Query blueprint for projects table
    private function buildProjectQuery($user)
    {
        $query = Project::query();

        if (strtolower($user->role) === 'project manager') {
            $query->where('owner_id', $user->id);
        }

        return $query;
    }

    // check recent projects
    private function getRecentProjects($query)
    {
        return (clone $query)
            ->with(['owner', 'manager'])
            ->latest('updated_at')
            ->take(3)
            ->get()
            ->map(function ($proj) {
                return $this->formatProject($proj);
            });
    }

    // count project for total-project card
    private function countProjects($query)
    {
        return (clone $query)->count();
    }

    // count project for total-project card
    private function countByStatus($query, $status)
    {
        return (clone $query)->where('status', $status)->count();
    }

    // count project for total-planning project card
    private function countPlanning($query)
    {
        return (clone $query)
            ->whereIn('status', ['approved', 'planning'])
            ->count();
    }

    // next acction (suggestion per role what todo)
    private function getNextActions($user)
    {
        $actions = [];

        if ($this->isManager($user)) {
            $actions = array_merge($actions, $this->getManagerActions());
        }

        $actions = array_merge($actions, $this->getPlanningActions($user));

        return $actions;
    }

    private function getManagerActions()
    {
        $nextActions = [];

        $submittedProjectsList = Project::where('status', 'submitted')->get();

        foreach ($submittedProjectsList as $proj) {
            $nextActions[] = [
                'title' => 'Review Proposal: ' . $proj->title,
                'subtext' => 'Menunggu Persetujuan Anda',
                'link' => route('projects.edit', $proj->id),
                'action_text' => 'Tinjau Sekarang',
                'color' => 'rose',
                'icon' => 'fa-file-signature',
            ];
        }

        return $nextActions;
    }

    private function getPlanningActions($user)
    {
        $nextActions = [];
        $planningProjectsList = Project::where('status', 'planning')->get();

        foreach ($planningProjectsList as $proj) {
            if (!$proj->scope || strtolower($proj->scope->status) !== 'finalized') {
                if ($this->isManager($user)) {
                    $nextActions[] = [
                        'title' => 'Finalisasi Scope: ' . $proj->title,
                        'subtext' => 'Tahap Perencanaan Scope',
                        'link' => route('projects.scope.show', $proj->id),
                        'action_text' => 'Lengkapi Scope',
                        'color' => 'blue',
                        'icon' => 'fa-compass',
                    ];
                }
            } elseif (
                !$proj->wbsItems()->exists() ||
                WbsItem::where('project_id', $proj->id)->where('status', 'draft')->exists()
            ) {
                if ($this->isPMO($user)) {
                    $nextActions[] = [
                        'title' => 'Susun WBS Proyek: ' . $proj->title,
                        'subtext' => 'Tugas/WBS Belum Selesai',
                        'link' => route('projects.wbs.show', $proj->id),
                        'action_text' => 'Kelola WBS',
                        'color' => 'amber',
                        'icon' => 'fa-sitemap',
                    ];
                }
            } elseif (
                !$proj->timelineItems()->exists() ||
                TimelineItem::where('project_id', $proj->id)->where('status', 'draft')->exists()
            ) {
                if ($this->isPMO($user)) {
                    $nextActions[] = [
                        'title' => 'Jadwalkan Timeline: ' . $proj->title,
                        'subtext' => 'Timeline Belum Final',
                        'link' => route('projects.timeline.show', $proj->id),
                        'action_text' => 'Buka Gantt Chart',
                        'color' => 'indigo',
                        'icon' => 'fa-calendar-days',
                    ];
                }
            }
        }
        return $nextActions;
    }

    private function isManager($user)
    {
        return $user->role === 'Manager';
    }

    private function isPMO($user)
    {
        return in_array($user->role, ['PMO', 'Project Management Officer']);
    }

    private function resolveCardValue($type, $data)
    {
        return match ($type) {
            'total_projects' => $data['totalProjects'],
            'draft' => $data['draftProjects'],
            'on_request' => $data['submittedProjects'],
            'planning' => $data['planningProjects'],
            'done' => $data['completedProjects'],
            default => 0,
        };
    }

    // control user card
    public function getCards($user)
    {
        $role = $user->role;
        $defaultCards = [
            [
                'type' => 'total_projects',
                'label' => 'Total Proyek',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'infoColor' => 'text-white',
                'route' => route('projects.index'),
                'background' => 'bg-gradient-to-br from-blue-500 to-gradientBlue',
            ],
            [
                'type' => 'draft',
                'label' => 'Draft',
                'titleColor' => '',
                'valueColor' => '',
                'infoColor' => '',
                'route' => route('projects.index'),
                'background' => '',
            ],
            [
                'type' => 'on_request',
                'label' => 'On Request',
                'titleColor' => '',
                'valueColor' => '',
                'infoColor' => '',
                'route' => route('projects.index'),
                'background' => '',
            ],
            [
                'type' => 'planning',
                'label' => 'Planning',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'infoColor' => 'text-white',
                'route' => route('projects.index'),
                'background' => 'bg-gradient-to-br from-orangeBg to-gradientOrange',
            ],
            [
                'type' => 'done',
                'label' => 'Done',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'infoColor' => 'text-white',
                'route' => route('projects.index'),
                'background' => 'bg-gradient-to-br from-greenBg to-gradientGreen',
            ],
        ];

        $itCards = [
            [
                'type' => 'total_projects',
                'label' => 'Total Proyek',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'infoColor' => 'text-white',
                'route' => null,
                'background' => 'bg-gradient-to-br from-blue-500 to-gradientBlue',
            ],
            [
                'type' => 'on_progress_task',
                'label' => 'On Progress',
                'titleColor' => '',
                'valueColor' => '',
                'infoColor' => '',
                'route' => null,
                'background' => 'bg-cyan-400',
            ],
            [
                'type' => 'todo_task',
                'label' => 'To Do',
                'titleColor' => '',
                'valueColor' => '',
                'infoColor' => '',
                'route' => null,
                'background' => 'bg-pink-400',
            ],
            [
                'type' => 'overdue_task',
                'label' => 'Overdue',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'infoColor' => 'text-white',
                'route' => null,
                'background' => 'bg-gradient-to-br from-orangeBg to-gradientOrange',
            ],
            [
                'type' => 'done_task',
                'label' => 'Done',
                'titleColor' => 'white',
                'valueColor' => 'white',
                'infoColor' => 'text-white',
                'route' => null,
                'background' => 'bg-gradient-to-br from-greenBg to-gradientGreen',
            ],
        ];

        $roleCards = [
            'Project Management Officer' => $defaultCards,
            'Manager' => $defaultCards,
            'Project Manager' => $defaultCards,
            'IT' => $itCards,
        ];

        return $roleCards[$role] ?? [];
    }

    private function formatProject($proj)
    {
        // Owner name
        $name = $proj->owner ? $proj->owner->name : 'System';

        // Initials
        $words = explode(' ', $name);
        if (count($words) >= 2) {
            $initials = strtoupper(substr($words[0], 0, 1) . substr($words[1], 0, 1));
        } else {
            $initials = strtoupper(substr($name, 0, 2));
        }

        // Category
        $category = __('General Project');

        if (
            stripos($proj->title, 'it') !== false ||
            stripos($proj->title, 'cloud') !== false ||
            stripos($proj->title, 'software') !== false ||
            stripos($proj->title, 'sistem') !== false
        ) {
            $category = __('Infrastruktur IT');
        } elseif (
            stripos($proj->title, 'gudang') !== false ||
            stripos($proj->title, 'logistik') !== false ||
            stripos($proj->title, 'distribusi') !== false
        ) {
            $category = __('Logistik & Distribusi');
        } elseif (
            stripos($proj->title, 'hr') !== false ||
            stripos($proj->title, 'sdm') !== false ||
            stripos($proj->title, 'human') !== false
        ) {
            $category = __('Sumber Daya Manusia');
        }

        // Activity
        $activityText = __('Mengupdate status proyek');

        // Status label + style
        $statusLabel = ucfirst($proj->status);
        $statusClass = 'bg-rose-50 text-rose-700 border-rose-100';

        switch ($proj->status) {
            case 'draft':
                $statusLabel = __('Draf');
                $statusClass = 'bg-slate-100 text-slate-700 border-slate-200';
                break;
            case 'submitted':
                $statusLabel = __('Dalam Review');
                $statusClass = 'bg-blue-50 text-blue-700 border-blue-100';
                break;
            case 'approved':
            case 'planning':
                $statusLabel = __('Planning');
                $statusClass = 'bg-indigo-50 text-indigo-700 border-indigo-100';
                break;
            case 'completed':
                $statusLabel = __('Selesai');
                $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-100';
                break;
        }

        return [
            'title' => $proj->title,
            'category' => $category,
            'name' => $name,
            'initials' => $initials,
            'activity' => $activityText,
            'time' => $proj->updated_at->diffForHumans(),
            'status_label' => $statusLabel,
            'status_class' => $statusClass,
        ];
    }

    private function getProjectAnalytics($query)
    {
        $months = collect(range(1, 6))->map(function ($i) {
            return now()->subMonths(5 - $i + 1)->startOfMonth();
        });

        return $months->map(function ($month) use ($query) {

            $planned = (clone $query)
                ->whereYear('start_date', $month->year)
                ->whereMonth('start_date', $month->month)
                ->count();

            $done = (clone $query)
                ->where('status', 'completed')
                ->whereYear('updated_at', $month->year)
                ->whereMonth('updated_at', $month->month)
                ->count();

            return [
                'month' => $month->format('M'),
                'planned' => $planned,
                'done' => $done,
            ];
        });
    }
}
