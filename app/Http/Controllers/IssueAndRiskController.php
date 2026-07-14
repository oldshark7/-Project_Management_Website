<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Issue;
use App\Models\User;
use App\Models\RiskManagementPlan;
use App\Models\RiskItem;
use App\Helpers\RiskSuggestionHelper;
use App\Models\HumanResourcePlan;
use App\Models\HumanResourceItem;


class IssueAndRiskController extends Controller
{
    public function index()
    {
        $projects = Project::with(['owner', 'projectManager'])->select('id', 'title', 'owner_id', 'manager_id', 'start_date', 'end_date')->get();

        return view('project-executing.issue-risk-management.index', compact('projects'));
    }

    public function show(Request $request,  Project $project)
    {
        $tab = $request->get('tab', 'issue');
        $plan = HumanResourcePlan::where('project_id', $project->id)->first();

        $users = collect();

        if ($plan) {
            $users = HumanResourceItem::with('teamMember.user')
                ->where('human_resource_plan_id', $plan->id)
                ->get()
                ->pluck('teamMember.user')
                ->filter()
                ->unique('id')
                ->values();
        }

        $issues = null;
        $query = Issue::with(['assignee', 'reporter'])->where('project_id', $project->id);

        if ($request->priority) {
            $query->where('priority', $request->priority);
        }
        if ($request->assigned === 'me') {
            $query->where('assignee_id', auth()->id());
        }
        if ($request->due === 'today') {
            $query->whereDate('due_date', today());
        }
        if ($request->due === 'overdue') {
            $query->whereDate('due_date', '<', today())->where('status', '!=', 'done');
        }
        if ($request->due === 'done') {
            $query->where('status', 'done');
        }
        if ($request->due === 'approved') {
            $query->where('status', 'approved');
        }

        $issues = $query->get();
        [$risks, $riskSuggestion] = $this->getRiskData($project, $tab);

        return view('project-executing.issue-risk-management.show', [
            'project' => $project,
            'issues' => $issues,
            'risks' => $risks,
            'tab' => $tab,
            'users' => $users,
            'riskSuggestion' => $riskSuggestion,
        ]);
    }

    private function getRiskData(Project $project, string $tab)
    {
        $risks = collect();
        $riskSuggestion = null;

        $riskPlan = RiskManagementPlan::where('project_id', $project->id)->first();

        if ($riskPlan) {
            $risks = RiskItem::where('risk_management_plan_id', $riskPlan->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($tab === 'risk') {
            $riskSuggestion = RiskSuggestionHelper::get($project->id);
        }

        return [$risks, $riskSuggestion];
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'assignee_id' => 'required|exists:users,id',
            'priority' => 'required|in:low,medium,high',
            'due_date' => 'required|date|after_or_equal:today',
        ]);

        Issue::create([
            'project_id' => $request->project_id,
            'title' => $request->title,
            'description' => $request->description,
            'assignee_id' => $request->assignee_id,
            'priority' => $request->priority,
            'due_date' => $request->due_date,
            'status' => 'open',
            'reported_by' => auth()->id(),
        ]);

        return redirect()->route('issue-risk.show', ['project' => $request->project_id])->with('success', 'Issue berhasil dibuat');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:' . implode(',', Issue::STATUSES),
        ]);

        $issue = Issue::findOrFail($id);

        $issue->update([
            'status' => $request->status,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status berhasil diupdate',
            'status' => $issue->status,
        ]);
    }

    public function riskSuggestionStatus(Request $request, int $projectId)
    {
        $status = RiskSuggestionHelper::status($projectId);

        return response()->json($status);
    }
}
