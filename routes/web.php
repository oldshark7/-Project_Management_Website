<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TeamManagementController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectProposalController;
use App\Http\Controllers\ProjectCharterController;
use App\Http\Controllers\ProjectScopeController;
use App\Http\Controllers\ProjectWbsController;
use App\Http\Controllers\ProjectTimelineController;
use App\Http\Controllers\ProjectBudgetController;
use App\Http\Controllers\ProjectHumanResourceController;
use App\Http\Controllers\ProjectRiskManagementController;
use App\Http\Controllers\TaskManagementController;
use App\Http\Controllers\CostController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IssueAndRiskController;
use App\Http\Controllers\ChangeRequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MeetingController;
use Illuminate\Support\Facades\Route;

Route::get('/risk-suggestion/{projectId}/status', [IssueAndRiskController::class, 'riskSuggestionStatus'])->name('risk-suggestion.status');
Route::get('/', function () {return view('auth/login');});
Route::post('/change-requests', [ChangeRequestController::class, 'store'])->name('change-requests.store');


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{id}', [DashboardController::class, 'detailDashboard'])->name('proyek.dashboard');
    Route::get('/report/weekly/{projectId}', [ReportController::class, 'weekly'])->name('report.weekly');
    Route::resource('meeting-schedules', MeetingController::class);

    // Project Initiation (Project Manager & Manager)
    Route::middleware('role:project_manager,manager')->group(function () {
        Route::get('/project-initiation', function () {
            return view('project-initiation.index');
        })->name('project-initiation');
    });

    // Project Planning (Manager & PMO)
    Route::middleware('role:manager,pmo')->group(function () {
        Route::get('/project-planning', function () {
            return view('project-planning.index');
        })->name('project-planning');
    });

    Route::get('/team-management', [TeamManagementController::class, 'index'])->name('teamManagement');

    Route::middleware('role:pmo')->group(function () {
        Route::post('/team-management', [TeamManagementController::class, 'store'])->name('teamManagement.store');
        Route::put('/team-management/{teamMember}', [TeamManagementController::class, 'update'])->name('teamManagement.update');
        Route::patch('/team-management/{teamMember}/toggle-status', [TeamManagementController::class, 'toggleStatus'])->name('teamManagement.toggleStatus');
        Route::delete('/team-management/{teamMember}', [TeamManagementController::class, 'destroy'])->name('teamManagement.destroy');
        Route::post('/projects/{project}/assign/{wbs}', [ProjectHumanResourceController::class, 'assignMembers']);
    });

    Route::middleware('role:project_manager,pmo,project management officer,it')->group(function () {
        // task-management section
        Route::get('/task-management', [TaskManagementController::class, 'index'])->name('task-management.index');
        Route::get('/task-management/{project}', [TaskManagementController::class, 'show'])->name('task-management.show');
        Route::post('/tasks/{id}/update-status', [TaskManagementController::class, 'updateStatus']);
        Route::get('/task-insight/{project}', [TaskManagementController::class, 'getTaskInsight'])->name('task-insight');

        Route::get('/issue', [IssueAndRiskController::class, 'index'])->name('issue-risk.index');
        Route::get('/issue-risk/{project}', [IssueAndRiskController::class, 'show'])->name('issue-risk.show');
        Route::post('/issues', [IssueAndRiskController::class, 'store'])->name('issues.store');
        Route::patch('/issues/{id}/status', [IssueAndRiskController::class, 'updateStatus'])->name('issues.updateStatus');

        Route::get('/change-requests', [ChangeRequestController::class, 'index'])->name('change-requests.index');
        Route::get('/change-requests/{project}', [ChangeRequestController::class, 'show'])->name('change-requests.show');
        Route::patch('/change-requests/{changeRequest}/approve', [ChangeRequestController::class, 'approve'])->name('change-request.approve');
        Route::patch('/change-requests/{changeRequest}/reject',[ChangeRequestController::class, 'reject'])->name('change-request.reject');
        // Route::resource('change-requests', ChangeRequestController::class);
    });

    Route::middleware('role:project_manager,manager,pmo')->group(function () {
        Route::resource('projects', ProjectController::class);

        // Project Proposal nested routes
        Route::get('/projects/{project}/proposal', [ProjectProposalController::class, 'show'])->name('projects.proposal.show');
        Route::get('/projects/{project}/proposal/download', [ProjectProposalController::class, 'downloadPdf'])->name('projects.proposal.download');
        Route::get('/projects/{project}/proposal/create', [ProjectProposalController::class, 'create'])->name('projects.proposal.create');
        Route::post('/projects/{project}/proposal', [ProjectProposalController::class, 'store'])->name('projects.proposal.store');
        Route::get('/projects/{project}/proposal/edit', [ProjectProposalController::class, 'edit'])->name('projects.proposal.edit');
        Route::put('/projects/{project}/proposal', [ProjectProposalController::class, 'update'])->name('projects.proposal.update');
        Route::post('/projects/{project}/proposal/generate-ai', [ProjectProposalController::class, 'generateAi'])->name('projects.proposal.generate_ai');

        // Project Charter nested routes
        Route::get('/projects/{project}/charter', [ProjectCharterController::class, 'show'])->name('projects.charter.show');
        Route::get('/projects/{project}/charter/download', [ProjectCharterController::class, 'downloadPdf'])->name('projects.charter.download');
        Route::get('/projects/{project}/charter/create', [ProjectCharterController::class, 'create'])->name('projects.charter.create');
        Route::post('/projects/{project}/charter', [ProjectCharterController::class, 'store'])->name('projects.charter.store');
        Route::get('/projects/{project}/charter/edit', [ProjectCharterController::class, 'edit'])->name('projects.charter.edit');
        Route::put('/projects/{project}/charter', [ProjectCharterController::class, 'update'])->name('projects.charter.update');
        Route::post('/projects/{project}/charter/generate-ai', [ProjectCharterController::class, 'generateAi'])->name('projects.charter.generate_ai');

        // Project Scope routes
        Route::get('/project-planning/scope', [ProjectScopeController::class, 'index'])->name('project-planning.scope.index');
        Route::get('/projects/{project}/scope', [ProjectScopeController::class, 'show'])->name('projects.scope.show');
        Route::get('/projects/{project}/scope/create', [ProjectScopeController::class, 'create'])->name('projects.scope.create');
        Route::post('/projects/{project}/scope', [ProjectScopeController::class, 'store'])->name('projects.scope.store');
        Route::get('/projects/{project}/scope/edit', [ProjectScopeController::class, 'edit'])->name('projects.scope.edit');
        Route::put('/projects/{project}/scope', [ProjectScopeController::class, 'update'])->name('projects.scope.update');
        Route::post('/projects/{project}/scope/finalize', [ProjectScopeController::class, 'finalize'])->name('projects.scope.finalize');

        // Project WBS routes
        Route::get('/project-planning/wbs', [ProjectWbsController::class, 'index'])->name('project-planning.wbs.index');
        Route::get('/projects/{project}/wbs', [ProjectWbsController::class, 'show'])->name('projects.wbs.show');
        Route::get('/projects/{project}/wbs/create', [ProjectWbsController::class, 'create'])->name('projects.wbs.create');
        Route::post('/projects/{project}/wbs', [ProjectWbsController::class, 'store'])->name('projects.wbs.store');
        Route::get('/projects/{project}/wbs/{wbsItem}/edit', [ProjectWbsController::class, 'edit'])->name('projects.wbs.edit');
        Route::put('/projects/{project}/wbs/{wbsItem}', [ProjectWbsController::class, 'update'])->name('projects.wbs.update');
        Route::delete('/projects/{project}/wbs/{wbsItem}', [ProjectWbsController::class, 'destroy'])->name('projects.wbs.destroy');
        Route::post('/projects/{project}/wbs/finalize', [ProjectWbsController::class, 'finalize'])->name('projects.wbs.finalize');

        // Project Timeline routes
        Route::get('/project-planning/timeline', [ProjectTimelineController::class, 'index'])->name('project-planning.timeline.index');
        Route::get('/projects/{project}/timeline', [ProjectTimelineController::class, 'show'])->name('projects.timeline.show');
        Route::get('/projects/{project}/timeline/create', [ProjectTimelineController::class, 'create'])->name('projects.timeline.create');
        Route::post('/projects/{project}/timeline', [ProjectTimelineController::class, 'store'])->name('projects.timeline.store');
        Route::get('/projects/{project}/timeline/{timelineItem}/edit', [ProjectTimelineController::class, 'edit'])->name('projects.timeline.edit');
        Route::put('/projects/{project}/timeline/{timelineItem}', [ProjectTimelineController::class, 'update'])->name('projects.timeline.update');
        Route::delete('/projects/{project}/timeline/{timelineItem}', [ProjectTimelineController::class, 'destroy'])->name('projects.timeline.destroy');
        Route::post('/projects/{project}/timeline/finalize', [ProjectTimelineController::class, 'finalize'])->name('projects.timeline.finalize');

        // Project Budget routes
        Route::get('/project-planning/budget', [ProjectBudgetController::class, 'index'])->name('project-planning.budget.index');
        Route::get('/projects/{project}/budget', [ProjectBudgetController::class, 'show'])->name('projects.budget.show');
        Route::get('/projects/{project}/budget/create', [ProjectBudgetController::class, 'create'])->name('projects.budget.create');
        Route::post('/projects/{project}/budget', [ProjectBudgetController::class, 'store'])->name('projects.budget.store');
        Route::get('/projects/{project}/budget/edit', [ProjectBudgetController::class, 'edit'])->name('projects.budget.edit');
        Route::put('/projects/{project}/budget', [ProjectBudgetController::class, 'update'])->name('projects.budget.update');
        Route::post('/projects/{project}/budget/items', [ProjectBudgetController::class, 'addItem'])->name('projects.budget.items.add');
        Route::put('/projects/{project}/budget/items/{budgetItem}', [ProjectBudgetController::class, 'updateItem'])->name('projects.budget.items.update');
        Route::delete('/projects/{project}/budget/items/{budgetItem}', [ProjectBudgetController::class, 'deleteItem'])->name('projects.budget.items.delete');
        Route::post('/projects/{project}/budget/finalize', [ProjectBudgetController::class, 'finalize'])->name('projects.budget.finalize');

        // Project Human Resource routes
        Route::get('/project-planning/human-resource', [ProjectHumanResourceController::class, 'index'])->name('project-planning.human-resource.index');
        Route::get('/projects/{project}/human-resource', [ProjectHumanResourceController::class, 'show'])->name('projects.human-resource.show');
        Route::get('/projects/{project}/human-resource/create', [ProjectHumanResourceController::class, 'create'])->name('projects.human-resource.create');
        Route::post('/projects/{project}/human-resource', [ProjectHumanResourceController::class, 'store'])->name('projects.human-resource.store');
        Route::get('/projects/{project}/human-resource/edit', [ProjectHumanResourceController::class, 'edit'])->name('projects.human-resource.edit');
        Route::put('/projects/{project}/human-resource', [ProjectHumanResourceController::class, 'update'])->name('projects.human-resource.update');
        Route::post('/projects/{project}/human-resource/items', [ProjectHumanResourceController::class, 'addItem'])->name('projects.human-resource.items.add');
        Route::put('/projects/{project}/human-resource/items/{humanResourceItem}', [ProjectHumanResourceController::class, 'updateItem'])->name('projects.human-resource.items.update');
        Route::delete('/projects/{project}/human-resource/items/{humanResourceItem}', [ProjectHumanResourceController::class, 'deleteItem'])->name('projects.human-resource.items.delete');
        Route::post('/projects/{project}/human-resource/finalize', [ProjectHumanResourceController::class, 'finalize'])->name('projects.human-resource.finalize');

        // Project Risk Management routes
        Route::get('/project-planning/risk-management', [ProjectRiskManagementController::class, 'index'])->name('project-planning.risk-management.index');
        Route::get('/projects/{project}/risk-management', [ProjectRiskManagementController::class, 'show'])->name('projects.risk-management.show');
        Route::get('/projects/{project}/risk-management/create', [ProjectRiskManagementController::class, 'create'])->name('projects.risk-management.create');
        Route::post('/projects/{project}/risk-management', [ProjectRiskManagementController::class, 'store'])->name('projects.risk-management.store');
        Route::get('/projects/{project}/risk-management/edit', [ProjectRiskManagementController::class, 'edit'])->name('projects.risk-management.edit');
        Route::put('/projects/{project}/risk-management', [ProjectRiskManagementController::class, 'update'])->name('projects.risk-management.update');
        Route::post('/projects/{project}/risk-management/items', [ProjectRiskManagementController::class, 'addItem'])->name('projects.risk-management.items.add');
        Route::put('/projects/{project}/risk-management/items/{riskItem}', [ProjectRiskManagementController::class, 'updateItem'])->name('projects.risk-management.items.update');
        Route::delete('/projects/{project}/risk-management/items/{riskItem}', [ProjectRiskManagementController::class, 'deleteItem'])->name('projects.risk-management.items.delete');
        Route::post('/projects/{project}/risk-management/generate-ai', [ProjectRiskManagementController::class, 'generateAi'])->name('projects.risk-management.generate_ai');
        Route::post('/projects/{project}/risk-management/finalize', [ProjectRiskManagementController::class, 'finalize'])->name('projects.risk-management.finalize');

        // control-budget section
        Route::get('/cost-control', [CostController::class, 'index'])->name('cost-control.index');
        Route::get('/cost-control/{project}', [CostController::class, 'show'])->name('cost-control.show');
        Route::post('/cost-control/{project}/expense',[CostController::class, 'storeExpense'])->name('cost-control.expense.store');
        Route::post('/cost-control/{project}/generate-insight',[CostController::class, 'generateInsight'])->name('cost-control.generate-insight');
        Route::post('/projects/{project}/budget-items',[CostController::class, 'storeItem'])->name('projects.budget.items.store');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
