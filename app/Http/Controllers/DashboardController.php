<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;
use App\Models\Project;
use App\Models\ProjectProposal;
use App\Http\Controllers\ProjectController;
use App\Models\TeamMember;


class DashboardController extends Controller
{
    public function index(DashboardService $dashboardService)
    {
        $projects = Project::select('id', 'title')->get();
        $members = TeamMember::where('is_active', true)
            ->get()
            ->sortByDesc('current_workload_percentage')
            ->take(5);

        $data = $dashboardService->getDashboardData(auth()->user());

        // return view('dashboard.index', $data, compact('members'));
        return view('dashboard.index', array_merge($data, [
            'members' => $members,
            'projects' => $projects
        ]));
    }

    public function detailDashboard($id, DashboardService $dashboardService)
    {
        $project = Project::findOrFail($id);
        $data = $dashboardService->getDetailProjectDashboard($id);
        $projects = Project::select('id', 'title')->get();
        $projectsProposal = ProjectProposal::select('id', 'background')->get();
        $members = TeamMember::where('is_active', true)
            ->get()
            ->sortByDesc('current_workload_percentage')
            ->take(5);

        return view('dashboard.components.detail-proyek-dashboard', array_merge($data, [
            'projects' => $projects,
            'members' => $members,
            'projectProposal' => $projectsProposal,
            // 'title' => $project -> title
        ]));
    }
}
