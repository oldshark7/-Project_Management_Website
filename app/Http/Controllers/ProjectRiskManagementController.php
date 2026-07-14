<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\RiskManagementPlan;
use App\Models\RiskItem;
use App\Models\WbsItem;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class ProjectRiskManagementController extends Controller
{
    protected $openRouterService;

    public function __construct(OpenRouterService $openRouterService)
    {
        $this->openRouterService = $openRouterService;
    }

    /**
     * Check if the authenticated user has access to Risk Management.
     */
    protected function checkBaseAccess(): string
    {
        if (!Auth::check()) {
            abort(401);
        }

        $role = strtolower(Auth::user()->role);
        if (!in_array($role, ['manager', 'project management officer', 'pmo'])) {
            abort(403, 'Akses ditolak. Peran Anda tidak diizinkan mengakses Risk Management.');
        }

        return $role;
    }

    /**
     * Check if the project is in planning status and has finalized preceding tasks.
     */
    protected function checkPlanningAccess(Project $project): void
    {
        // 1. Project status must be planning
        if ($project->status !== 'planning') {
            abort(403, 'Risk Management hanya dapat diakses jika status proyek adalah Planning.');
        }

        // 2. Scope must be finalized
        if (!$project->scope || $project->scope->status !== 'finalized') {
            abort(403, 'Risk Management hanya dapat diakses jika Project Scope proyek ini sudah finalized.');
        }

        // 3. WBS must be finalized
        $wbsCount = $project->wbsItems()->count();
        $wbsDraftCount = $project->wbsItems()->where('status', 'draft')->count();
        $isWbsFinalized = ($wbsCount > 0 && $wbsDraftCount === 0);
        if (!$isWbsFinalized) {
            abort(403, 'Risk Management hanya dapat diakses jika WBS proyek ini sudah finalized.');
        }

        // 4. Timeline must be finalized
        $timelineCount = $project->timelineItems()->count();
        $timelineDraftCount = $project->timelineItems()->where('status', 'draft')->count();
        $isTimelineFinalized = ($timelineCount > 0 && $timelineDraftCount === 0 && $timelineCount === $wbsCount);
        if (!$isTimelineFinalized) {
            abort(403, 'Risk Management hanya dapat diakses jika Timeline proyek ini sudah finalized.');
        }

        // 5. Budget must be finalized
        if (!$project->budgetPlan || $project->budgetPlan->status !== 'finalized') {
            abort(403, 'Risk Management hanya dapat diakses jika Budget Planning proyek ini sudah finalized.');
        }

        // 6. HR must be finalized
        if (!$project->humanResourcePlan || $project->humanResourcePlan->status !== 'finalized') {
            abort(403, 'Risk Management hanya dapat diakses jika Human Resource Planning proyek ini sudah finalized.');
        }
    }

    /**
     * Display a listing of projects in planning status and their Risk Management status.
     */
    public function index()
    {
        $this->checkBaseAccess();

        // Managers and PMO see all planning projects
        $projects = Project::where('status', 'planning')
            ->with(['scope', 'wbsItems', 'timelineItems', 'budgetPlan', 'humanResourcePlan', 'riskPlan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-planning.risk-management.index', compact('projects'));
    }

    /**
     * Display the specified project's Risk Management plan and items.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;

        if (!$riskPlan) {
            if ($role === 'project management officer' || $role === 'pmo') {
                return redirect()->route('projects.risk-management.create', $project->id)
                    ->with('info', 'Risk Plan belum dibuat. Silakan inisialisasi terlebih dahulu.');
            }
        }

        $riskItems = $riskPlan ? $riskPlan->riskItems()->with('wbsItem')->orderBy('created_at', 'desc')->get() : collect();
        $isRiskFinalized = $riskPlan && $riskPlan->status === 'finalized';

        // Calculate stats aggregates
        $totalRisks = $riskItems->count();
        
        $probHigh = $riskItems->where('probability', 'high')->count();
        $probMed = $riskItems->where('probability', 'medium')->count();
        $probLow = $riskItems->where('probability', 'low')->count();

        $sevHigh = $riskItems->where('severity', 'high')->count();
        $sevMed = $riskItems->where('severity', 'medium')->count();
        $sevLow = $riskItems->where('severity', 'low')->count();

        $statusOpen = $riskItems->where('status', 'open')->count();
        $statusMitigated = $riskItems->where('status', 'mitigated')->count();
        $statusAccepted = $riskItems->where('status', 'accepted')->count();
        $statusClosed = $riskItems->where('status', 'closed')->count();

        return view('project-planning.risk-management.show', compact(
            'project', 'riskPlan', 'riskItems', 'isRiskFinalized', 'totalRisks',
            'probHigh', 'probMed', 'probLow',
            'sevHigh', 'sevMed', 'sevLow',
            'statusOpen', 'statusMitigated', 'statusAccepted', 'statusClosed'
        ));
    }

    /**
     * Show the form for creating a new Risk plan.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat Risk Management Plan.');
        }

        $this->checkPlanningAccess($project);

        if ($project->riskPlan) {
            return redirect()->route('projects.risk-management.edit', $project->id)
                ->with('info', 'Risk Plan sudah diinisialisasi. Anda dialihkan ke halaman edit.');
        }

        return view('project-planning.risk-management.create', compact('project'));
    }

    /**
     * Store a newly initialized Risk plan.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat Risk Management Plan.');
        }

        $this->checkPlanningAccess($project);

        if ($project->riskPlan) {
            return redirect()->route('projects.risk-management.edit', $project->id)
                ->with('info', 'Risk Plan sudah diinisialisasi.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $riskPlan = new RiskManagementPlan();
        $riskPlan->project_id = $project->id;
        $riskPlan->status = 'draft';
        $riskPlan->notes = $request->notes;
        $riskPlan->created_by = Auth::id();
        $riskPlan->updated_by = Auth::id();
        $riskPlan->save();

        return redirect()->route('projects.risk-management.edit', $project->id)
            ->with('success', 'Risk Management Plan berhasil diinisialisasi. Silakan kelola item risiko proyek.');
    }

    /**
     * Show the dashboard to manage Risk plan items.
     */
    public function edit(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat mengedit Risk Management Plan.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan) {
            return redirect()->route('projects.risk-management.create', $project->id);
        }

        if ($riskPlan->status === 'finalized') {
            abort(403, 'Risk Management Plan sudah difinalisasi dan tidak dapat diedit lagi.');
        }

        $riskItems = $riskPlan->riskItems()->with('wbsItem')->orderBy('created_at', 'desc')->get();
        $wbsItems = $project->wbsItems()->orderBy('title')->get();

        // Calculate stats aggregates
        $totalRisks = $riskItems->count();
        
        $probHigh = $riskItems->where('probability', 'high')->count();
        $probMed = $riskItems->where('probability', 'medium')->count();
        $probLow = $riskItems->where('probability', 'low')->count();

        $sevHigh = $riskItems->where('severity', 'high')->count();
        $sevMed = $riskItems->where('severity', 'medium')->count();
        $sevLow = $riskItems->where('severity', 'low')->count();

        $statusOpen = $riskItems->where('status', 'open')->count();
        $statusMitigated = $riskItems->where('status', 'mitigated')->count();
        $statusAccepted = $riskItems->where('status', 'accepted')->count();
        $statusClosed = $riskItems->where('status', 'closed')->count();

        // Safe JSON decode for AI Suggestions
        $aiSuggestions = [];
        if ($riskPlan->ai_suggestions) {
            try {
                $aiSuggestions = json_decode($riskPlan->ai_suggestions, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $aiSuggestions = [];
                }
            } catch (Exception $e) {
                Log::error('Gagal melakukan decode ai_suggestions: ' . $e->getMessage());
                $aiSuggestions = [];
            }
        }

        return view('project-planning.risk-management.edit', compact(
            'project', 'riskPlan', 'riskItems', 'wbsItems', 'totalRisks',
            'probHigh', 'probMed', 'probLow',
            'sevHigh', 'sevMed', 'sevLow',
            'statusOpen', 'statusMitigated', 'statusAccepted', 'statusClosed',
            'aiSuggestions'
        ));
    }

    /**
     * Update the Risk plan general notes.
     */
    public function update(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memperbarui Risk Management Plan.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan) {
            abort(404, 'Risk Management Plan tidak ditemukan.');
        }

        if ($riskPlan->status === 'finalized') {
            abort(403, 'Risk Management Plan sudah difinalisasi.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $riskPlan->notes = $request->notes;
        $riskPlan->updated_by = Auth::id();
        $riskPlan->save();

        return redirect()->route('projects.risk-management.edit', $project->id)
            ->with('success', 'Catatan Risk Management Plan berhasil diperbarui.');
    }

    /**
     * Add a risk item to the plan.
     */
    public function addItem(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat menambahkan item risiko.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan) {
            abort(404, 'Risk Management Plan tidak ditemukan.');
        }

        if ($riskPlan->status === 'finalized') {
            abort(403, 'Risk Management Plan sudah difinalisasi.');
        }

        $request->validate([
            'risk_title' => 'required|string|max:255',
            'risk_description' => 'required|string',
            'risk_cause' => 'nullable|string',
            'impact' => 'required|string',
            'probability' => 'required|string|in:low,medium,high',
            'severity' => 'required|string|in:low,medium,high',
            'mitigation_plan' => 'required|string',
            'contingency_plan' => 'nullable|string',
            'risk_owner' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:open,mitigated,accepted,closed',
            'notes' => 'nullable|string',
            'related_wbs_item_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project) {
                    if ($value) {
                        $wbs = WbsItem::find($value);
                        if (!$wbs || $wbs->project_id !== $project->id) {
                            $fail('Item WBS yang ditautkan harus berasal dari proyek yang sama.');
                        }
                    }
                }
            ],
        ], [
            'risk_title.required' => 'Judul risiko wajib diisi.',
            'risk_description.required' => 'Deskripsi risiko wajib diisi.',
            'impact.required' => 'Dampak risiko wajib diisi.',
            'probability.required' => 'Peluang (probability) wajib diisi.',
            'probability.in' => 'Peluang harus low, medium, atau high.',
            'severity.required' => 'Tingkat keparahan (severity) wajib diisi.',
            'severity.in' => 'Tingkat keparahan harus low, medium, atau high.',
            'mitigation_plan.required' => 'Rencana mitigasi wajib diisi.',
            'status.in' => 'Status tidak valid.',
        ]);

        $item = new RiskItem();
        $item->risk_management_plan_id = $riskPlan->id;
        $item->risk_title = $request->risk_title;
        $item->risk_description = $request->risk_description;
        $item->risk_cause = $request->risk_cause;
        $item->impact = $request->impact;
        $item->probability = $request->probability;
        $item->severity = $request->severity;
        $item->mitigation_plan = $request->mitigation_plan;
        $item->contingency_plan = $request->contingency_plan;
        $item->risk_owner = $request->risk_owner;
        $item->related_wbs_item_id = $request->related_wbs_item_id;
        $item->status = $request->status ?: 'open';
        $item->notes = $request->notes;
        $item->created_by = Auth::id();
        $item->updated_by = Auth::id();
        $item->save();

        return redirect()->route('projects.risk-management.edit', $project->id)
            ->with('success', 'Item risiko berhasil ditambahkan.');
    }

    /**
     * Update an existing risk item.
     */
    public function updateItem(Request $request, Project $project, RiskItem $riskItem)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memperbarui item risiko.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan || $riskItem->risk_management_plan_id !== $riskPlan->id) {
            abort(404, 'Item risiko tidak sesuai dengan proyek ini.');
        }

        if ($riskPlan->status === 'finalized') {
            abort(403, 'Risk Management Plan sudah difinalisasi.');
        }

        $request->validate([
            'risk_title' => 'required|string|max:255',
            'risk_description' => 'required|string',
            'risk_cause' => 'nullable|string',
            'impact' => 'required|string',
            'probability' => 'required|string|in:low,medium,high',
            'severity' => 'required|string|in:low,medium,high',
            'mitigation_plan' => 'required|string',
            'contingency_plan' => 'nullable|string',
            'risk_owner' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:open,mitigated,accepted,closed',
            'notes' => 'nullable|string',
            'related_wbs_item_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project) {
                    if ($value) {
                        $wbs = WbsItem::find($value);
                        if (!$wbs || $wbs->project_id !== $project->id) {
                            $fail('Item WBS yang ditautkan harus berasal dari proyek yang sama.');
                        }
                    }
                }
            ],
        ], [
            'risk_title.required' => 'Judul risiko wajib diisi.',
            'risk_description.required' => 'Deskripsi risiko wajib diisi.',
            'impact.required' => 'Dampak risiko wajib diisi.',
            'probability.required' => 'Peluang (probability) wajib diisi.',
            'probability.in' => 'Peluang harus low, medium, atau high.',
            'severity.required' => 'Tingkat keparahan (severity) wajib diisi.',
            'severity.in' => 'Tingkat keparahan harus low, medium, atau high.',
            'mitigation_plan.required' => 'Rencana mitigasi wajib diisi.',
            'status.in' => 'Status tidak valid.',
        ]);

        $riskItem->risk_title = $request->risk_title;
        $riskItem->risk_description = $request->risk_description;
        $riskItem->risk_cause = $request->risk_cause;
        $riskItem->impact = $request->impact;
        $riskItem->probability = $request->probability;
        $riskItem->severity = $request->severity;
        $riskItem->mitigation_plan = $request->mitigation_plan;
        $riskItem->contingency_plan = $request->contingency_plan;
        $riskItem->risk_owner = $request->risk_owner;
        $riskItem->related_wbs_item_id = $request->related_wbs_item_id;
        $riskItem->status = $request->status ?: 'open';
        $riskItem->notes = $request->notes;
        $riskItem->updated_by = Auth::id();
        $riskItem->save();

        return redirect()->route('projects.risk-management.edit', $project->id)
            ->with('success', 'Item risiko berhasil diperbarui.');
    }

    /**
     * Delete a risk item.
     */
    public function deleteItem(Project $project, RiskItem $riskItem)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat menghapus item risiko.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan || $riskItem->risk_management_plan_id !== $riskPlan->id) {
            abort(404, 'Item risiko tidak sesuai dengan proyek ini.');
        }

        // Delete only allowed if still draft
        if ($riskPlan->status === 'finalized') {
            abort(403, 'Risk Management Plan sudah difinalisasi.');
        }

        $riskItem->delete();

        return redirect()->route('projects.risk-management.edit', $project->id)
            ->with('success', 'Item risiko berhasil dihapus.');
    }

    /**
     * Trigger OpenRouter API call to generate suggestions and store raw JSON.
     */
    public function generateAi(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memicu AI suggestions.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan) {
            abort(404, 'Risk Management Plan belum diinisialisasi.');
        }

        if ($riskPlan->status === 'finalized') {
            abort(403, 'Risk Management Plan sudah difinalisasi.');
        }

        try {
            $suggestionsJson = $this->openRouterService->generateRiskManagementSuggestions($project);

            // Verify if suggestions is a valid JSON array or object
            $decoded = json_decode($suggestionsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                throw new Exception('AI mengembalikan data JSON yang tidak valid. Silakan coba kembali.');
            }

            // Save raw JSON to database
            $riskPlan->ai_suggestions = $suggestionsJson;
            $riskPlan->updated_by = Auth::id();
            $riskPlan->save();

            return redirect()->route('projects.risk-management.edit', $project->id)
                ->with('success', 'AI berhasil memberikan rekomendasi potensi risiko proyek.');

        } catch (Exception $e) {
            Log::error('AI Suggestion Error: ' . $e->getMessage());
            return redirect()->route('projects.risk-management.edit', $project->id)
                ->with('error', 'Gagal memicu AI Assistant: ' . $e->getMessage());
        }
    }

    /**
     * Finalize the Risk Management Plan.
     */
    public function finalize(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memfinalisasi Risk Management.');
        }

        $this->checkPlanningAccess($project);

        $riskPlan = $project->riskPlan;
        if (!$riskPlan) {
            abort(404, 'Risk Management Plan tidak ditemukan.');
        }

        if ($riskPlan->status === 'finalized') {
            return redirect()->route('projects.risk-management.show', $project->id)
                ->with('info', 'Risk Management Plan sudah finalized.');
        }

        // PMO tidak boleh finalize jika belum ada risk item
        if ($riskPlan->riskItems()->count() === 0) {
            return redirect()->route('projects.risk-management.edit', $project->id)
                ->with('error', 'Risk Management tidak dapat difinalisasi karena belum memiliki item risiko.');
        }

        $riskPlan->status = 'finalized';
        $riskPlan->updated_by = Auth::id();
        $riskPlan->save();

        return redirect()->route('projects.risk-management.show', $project->id)
            ->with('success', 'Risk Management Plan berhasil difinalisasi.');
    }
}
