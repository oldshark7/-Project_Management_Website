<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\HumanResourcePlan;
use App\Models\HumanResourceItem;
use App\Models\WbsItem;
use App\Services\HrSummaryService;
use App\Services\HumanResourceService;
use Illuminate\Http\Request;
use App\Models\TeamMember;
use Illuminate\Support\Facades\Auth;

class ProjectHumanResourceController extends Controller
{
    /**
     * Display a listing of projects in planning status and their HR status.
     */
    public function index(HumanResourceService $hrService)
    {
        $hrService->checkBaseAccess();

        // Managers and PMO see all planning projects
        $projects = Project::where('status', 'planning')
            ->with(['scope', 'wbsItems', 'timelineItems', 'budgetPlan', 'humanResourcePlan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-planning.human-resource.index', compact('projects'));
    }

    /**
     * Display the specified project's HR plan and items.
     */
    public function show(Project $project, HrSummaryService $summaryService, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        $hrService->checkPlanningAccess($project);
        $hrPlan = $project->humanResourcePlan;

        if (!$hrPlan) {
            if ($role === 'project management officer' || $role === 'pmo') {
                return redirect()->route('projects.human-resource.create', $project->id)
                    ->with('info', 'HR Plan belum dibuat. Silakan inisialisasi terlebih dahulu.');
            }
        }

        $wbsItems = WbsItem::where('project_id', $project->id)
            ->whereNull('parent_id')
            ->with([
                'children',
                'timelineItem',
            ])->get();

        $hrItems = $hrPlan ? $hrPlan->humanResourceItems()->with('teamMember')->orderBy('created_at', 'desc')->get() : collect();
        $isHrFinalized = $hrPlan && $hrPlan->status === 'finalized';
        
        // get project duration for timeline display
        ['minDate' => $minDate, 'projectDurationDays' => $projectDurationDays,] = $hrService->getProjectDuration($project);
        ['totalResources' => $totalResources, 'roleCount' => $roleCount, 'picCount' => $picCount,] = $hrService->getHrStatistics($hrItems);
        ['isPmo' => $isPmo, 'isDraft' => $isDraft, 'isEditable' => $isEditable,] = $hrService->getEditPermission($hrPlan);
        $teamMembers = TeamMember::whereIn('id',$hrItems->pluck('team_member_id')->filter())->with('user')->get();
        $groupedItems = $hrItems->groupBy('team_member_id');
        $memberWorkloads = $hrService->getMemberWorkloads($teamMembers);
        $memberTasks = $hrService->getMemberTasks($teamMembers);
        $summary = $summaryService->calculate($memberWorkloads);
        
        return view('project-planning.human-resource.show', compact(
            'project',
            'hrPlan',
            'hrItems',
            'isHrFinalized',
            'totalResources',
            'memberTasks',
            'roleCount',
            'picCount',
            'summary',
            'isPmo',
            'isDraft',
            'isEditable',
            'wbsItems',
            'projectDurationDays',
            'minDate',
            'teamMembers',
            'groupedItems',
            'memberWorkloads',
        ));
    }

    public function assignMembers(Request $request, Project $project, $wbsId, HumanResourceService $hrService)
    {
        $hrService->checkBaseAccess();
        $hrService->checkPlanningAccess($project);

        $request->validate([
            'team_member_ids' => 'required|array',
            'team_member_ids.*' => 'exists:team_members,id',
            'workloads' => 'required|array',
        ]);

        $hrService->assignMembers($project, $wbsId, $request->team_member_ids, $request->workloads);

        return back()->with('success', 'Berhasil assign member');
    }

    /**
     * Show the form for creating a new HR plan.
     */
    public function create(Project $project, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat Human Resource Plan.');
        }

        $hrService->checkPlanningAccess($project);

        if ($project->humanResourcePlan) {
            return redirect()->route('projects.human-resource.edit', $project->id)
                ->with('info', 'HR Plan sudah diinisialisasi. Anda dialihkan ke halaman edit.');
        }

        return view('project-planning.human-resource.create', compact('project'));
    }

    /**
     * Store a newly initialized HR plan.
     */
    public function store(Request $request, Project $project, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat Human Resource Plan.');
        }

        $hrService->checkPlanningAccess($project);

        if ($project->humanResourcePlan) {
            return redirect()->route('projects.human-resource.edit', $project->id)
                ->with('info', 'HR Plan sudah diinisialisasi.');
        }

        $request->validate(['notes' => 'nullable|string',]);

        $hrPlan = new HumanResourcePlan();
        $hrPlan->project_id = $project->id;
        $hrPlan->status = 'draft';
        $hrPlan->notes = $request->notes;
        $hrPlan->created_by = Auth::id();
        $hrPlan->updated_by = Auth::id();
        $hrPlan->save();

        return redirect()->route('projects.human-resource.edit', $project->id)
            ->with('success', 'HR Plan berhasil diinisialisasi. Silakan tambahkan item perencanaan SDM.');
    }

    /**
     * Show the dashboard to manage HR plan items.
     */
    public function edit(Project $project, HrSummaryService $summaryService, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat mengedit Human Resource Plan.');
        }

        $hrService->checkPlanningAccess($project);
        $hrPlan = $project->humanResourcePlan;

        if (!$hrPlan) {
            return redirect()->route('projects.human-resource.create', $project->id);
        }

        if ($hrPlan->status === 'finalized') {
            abort(403, 'HR Plan sudah difinalisasi dan tidak dapat diedit lagi.');
        }

        $hrItems = $hrPlan->humanResourceItems()->with(['wbsItem', 'teamMember'])->orderBy('created_at', 'desc')->get();
        $wbsItems = $project->wbsItems()->orderBy('title')->get();
        $teamMembers = $hrService->getAvailableTeamMembers($hrPlan);
        $summary = $summaryService->calculate($hrPlan, $hrItems);

        ['groupedItems' => $groupedItems, 'memberWorkloads' => $memberWorkloads,] = $hrService->getMemberWorkloads($hrItems);
        ['totalResources' => $totalResources, 'roleCount' => $roleCount, 'picCount' => $picCount,] = $hrService->getHrStatistics($hrItems);
        ['isPmo' => $isPmo, 'isDraft' => $isDraft, 'isEditable' => $isEditable,] = $hrService->getEditPermission($hrPlan);


        return view('project-planning.human-resource.edit', compact(
            'project',
            'hrPlan',
            'hrItems',
            'wbsItems',
            'teamMembers',
            'totalResources',
            'roleCount',
            'picCount',
            'summary',
            'isPmo',
            'isDraft',
            'isEditable',
            'groupedItems',
            'memberWorkloads',
        ));
    }

    /**
     * Update the HR plan general metadata.
     */
    public function update(Request $request, Project $project, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memperbarui Human Resource Plan.');
        }

        $hrService->checkPlanningAccess($project);

        $hrPlan = $project->humanResourcePlan;
        if (!$hrPlan) {
            abort(404, 'HR Plan tidak ditemukan.');
        }

        if ($hrPlan->status === 'finalized') {
            abort(403, 'HR Plan sudah difinalisasi dan tidak dapat diperbarui.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $hrPlan->notes = $request->notes;
        $hrPlan->updated_by = Auth::id();
        $hrPlan->save();

        return redirect()->route('projects.human-resource.edit', $project->id)
            ->with('success', 'Catatan HR Plan berhasil diperbarui.');
    }

    /**
     * Add an HR item to the plan.
     */
    public function addItem(Request $request, Project $project, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();

        if (!in_array($role, ['project management officer', 'pmo'])) {
            abort(403, 'Hanya PMO yang dapat menambahkan item perencanaan SDM.');
        }

        $hrService->checkPlanningAccess($project);

        $request->validate([
            'team_member_id' => 'nullable|exists:team_members,id',
            'wbs_item_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project) {
                    if ($value) {
                        $wbs = WbsItem::find($value);

                        if (!$wbs || $wbs->project_id !== $project->id) {
                            $fail('Item WBS harus berasal dari proyek yang sama.');
                        }
                    }
                }
            ],
            'workload_percentage' => 'nullable|numeric|min:0|max:100',
            'estimated_work_days' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ], [
            'quantity.integer' => 'Jumlah harus berupa angka bulat.',
            'quantity.min' => 'Jumlah minimal adalah 1.',
            'workload_percentage.min' => 'Beban kerja minimal 0%.',
            'workload_percentage.max' => 'Beban kerja maksimal 100%.',
            'estimated_work_days.min' => 'Estimasi hari kerja minimal 1 hari.',
        ]);

        $hrService->addItem($project, $request);

        return redirect()
            ->route('projects.human-resource.edit', $project->id)
            ->with('success', 'Item perencanaan SDM berhasil ditambahkan.');
    }

    /**
     * Update an existing HR item.
     */
    public function updateItem(Request $request, Project $project, HumanResourceItem $humanResourceItem, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();

        if (!in_array($role, ['project management officer', 'pmo'])) {
            abort(403, 'Hanya PMO yang dapat memperbarui item perencanaan SDM.');
        }

        $hrService->checkPlanningAccess($project);

        $request->validate([
            'team_member_id' => 'nullable|exists:team_members,id',
            'workload_percentage' => 'nullable|numeric|min:0|max:100',
            'estimated_work_days' => 'nullable|integer|min:1',
            'quantity' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
            'wbs_item_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project) {
                    if ($value) {
                        $wbs = WbsItem::find($value);

                        if (!$wbs || $wbs->project_id !== $project->id) {
                            $fail('Item WBS harus berasal dari proyek yang sama.');
                        }
                    }
                }
            ],
        ], [
            'quantity.integer' => 'Jumlah harus berupa angka bulat.',
            'quantity.min' => 'Jumlah minimal adalah 1.',
            'workload_percentage.min' => 'Beban kerja minimal 0%.',
            'workload_percentage.max' => 'Beban kerja maksimal 100%.',
            'estimated_work_days.min' => 'Estimasi hari kerja minimal 1 hari.',
        ]);

        $hrService->updateItem(
            $project,
            $humanResourceItem,
            $request
        );

        return redirect()
            ->route('projects.human-resource.edit', $project->id)
            ->with('success', 'Item perencanaan SDM berhasil diperbarui.');
    }

    /**
     * Delete an HR item (only draft allowed).
     */
    public function deleteItem(Project $project, HumanResourceItem $humanResourceItem, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat menghapus item perencanaan SDM.');
        }

        $hrService->checkPlanningAccess($project);

        $hrPlan = $project->humanResourcePlan;
        if (!$hrPlan || $humanResourceItem->human_resource_plan_id !== $hrPlan->id) {
            abort(404, 'Item perencanaan SDM tidak sesuai dengan proyek ini.');
        }

        if ($hrPlan->status === 'finalized') {
            abort(403, 'HR Plan sudah difinalisasi.');
        }

        $humanResourceItem->delete();

        return redirect()->route('projects.human-resource.edit', $project->id)
            ->with('success', 'Item perencanaan SDM berhasil dihapus.');
    }

    /**
     * Finalize the project HR plan.
     */
    public function finalize(Project $project, HumanResourceService $hrService)
    {
        $role = $hrService->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memfinalisasi Human Resource Plan.');
        }

        $hrService->checkPlanningAccess($project);

        $hrPlan = $project->humanResourcePlan;
        if (!$hrPlan) {
            abort(404, 'HR Plan tidak ditemukan.');
        }

        if ($hrPlan->status === 'finalized') {
            return redirect()->route('projects.human-resource.show', $project->id)
                ->with('info', 'HR Plan sudah berstatus finalized.');
        }

        // PMO tidak boleh finalize jika belum ada HR item
        if ($hrPlan->humanResourceItems()->count() === 0) {
            return redirect()->route('projects.human-resource.edit', $project->id)
                ->with('error', 'HR Plan tidak dapat difinalisasi karena belum memiliki item perencanaan SDM.');
        }

        $hrPlan->status = 'finalized';
        $hrPlan->updated_by = Auth::id();
        $hrPlan->save();

        return redirect()->route('projects.human-resource.show', $project->id)
            ->with('success', 'HR Plan berhasil difinalisasi.');
    }
}
