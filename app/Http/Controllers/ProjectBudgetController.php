<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\BudgetPlan;
use App\Models\BudgetItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectBudgetController extends Controller
{
    /**
     * Check if the authenticated user has access to Budget Planning.
     */
    protected function checkBaseAccess(): string
    {
        if (!Auth::check()) {
            abort(401);
        }

        $role = strtolower(Auth::user()->role);
        if (!in_array($role, ['manager', 'project management officer', 'pmo'])) {
            abort(403, 'Akses ditolak. Peran Anda tidak diizinkan mengakses Budget Planning.');
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
            abort(403, 'Budget Planning hanya dapat diakses jika status proyek adalah Planning.');
        }

        // 2. Scope must be finalized
        if (!$project->scope || $project->scope->status !== 'finalized') {
            abort(403, 'Budget Planning hanya dapat diakses jika Project Scope proyek ini sudah finalized.');
        }

        // 3. WBS must be finalized
        $wbsCount = $project->wbsItems()->count();
        $wbsDraftCount = $project->wbsItems()->where('status', 'draft')->count();
        $isWbsFinalized = ($wbsCount > 0 && $wbsDraftCount === 0);
        if (!$isWbsFinalized) {
            abort(403, 'Budget Planning hanya dapat diakses jika WBS proyek ini sudah finalized.');
        }

        // 4. Timeline must be finalized
        $timelineCount = $project->timelineItems()->count();
        $timelineDraftCount = $project->timelineItems()->where('status', 'draft')->count();
        $isTimelineFinalized = ($timelineCount > 0 && $timelineDraftCount === 0 && $timelineCount === $wbsCount);
        if (!$isTimelineFinalized) {
            abort(403, 'Budget Planning hanya dapat diakses jika Timeline proyek ini sudah finalized.');
        }
    }

    /**
     * Display a listing of projects in planning status and their Budget status.
     */
    public function index()
    {
        $this->checkBaseAccess();

        // Managers and PMO see all planning projects
        $projects = Project::where('status', 'planning')
            ->with(['scope', 'wbsItems', 'timelineItems', 'budgetPlan'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('project-planning.budget.index', compact('projects'));
    }

    /**
     * Parse Rupiah or currency string to integer safely.
     */
    public static function parseBudgetNumeric($value): ?int
    {
        if (is_null($value) || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int) round((float) $value);
        }
        
        $str = trim((string)$value);
        
        // Remove currency symbols/words like "Rp", "IDR", "rupiah" (case-insensitive)
        $cleaned = preg_replace('/(?i)\b(rp|idr|rupiah)\b/', '', $str);
        $cleaned = str_ireplace(['rp.', 'rp', 'idr'], '', $cleaned);
        
        // Check if there are any alphabetical characters left (e.g. "juta", "ribu", "untuk...")
        if (preg_match('/[a-zA-Z]/', $cleaned)) {
            return null;
        }
        
        $cleaned = str_replace(' ', '', $cleaned);
        
        $dotCount = substr_count($cleaned, '.');
        $commaCount = substr_count($cleaned, ',');
        
        if ($dotCount > 1 && $commaCount <= 1) {
            $cleaned = str_replace('.', '', $cleaned);
            if ($commaCount === 1) {
                $cleaned = str_replace(',', '.', $cleaned);
            }
        } elseif ($commaCount > 1 && $dotCount <= 1) {
            $cleaned = str_replace(',', '', $cleaned);
        } elseif ($dotCount === 1 && $commaCount === 1) {
            $dotPos = strpos($cleaned, '.');
            $commaPos = strpos($cleaned, ',');
            if ($dotPos < $commaPos) {
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                $cleaned = str_replace(',', '', $cleaned);
            }
        } elseif ($dotCount === 1 && $commaCount === 0) {
            if (preg_match('/\.\d{3}$/', $cleaned)) {
                $cleaned = str_replace('.', '', $cleaned);
            }
        } elseif ($commaCount === 1 && $dotCount === 0) {
            if (preg_match('/,\d{3}$/', $cleaned)) {
                $cleaned = str_replace(',', '', $cleaned);
            } else {
                $cleaned = str_replace(',', '.', $cleaned);
            }
        }
        
        $cleaned = preg_replace('/[^0-9\.-]/', '', $cleaned);
        
        if (is_numeric($cleaned)) {
            return (int) round((float) $cleaned);
        }
        
        return null;
    }

    /**
     * Get baseline budget from Project Charter or Project Proposal.
     */
    public static function getBaselineBudget(Project $project): array
    {
        $baseline = null;
        $source = null;

        if ($project->charter) {
            $charterVal = self::parseBudgetNumeric($project->charter->budget_summary);
            if (!is_null($charterVal) && $charterVal > 0) {
                $baseline = $charterVal;
                $source = 'Project Charter';
            }
        }

        if (is_null($baseline) && $project->proposal) {
            $proposalVal = self::parseBudgetNumeric($project->proposal->estimated_budget);
            if (!is_null($proposalVal) && $proposalVal > 0) {
                $baseline = $proposalVal;
                $source = 'Project Proposal';
            }
        }

        return [
            'value' => $baseline,
            'source' => $source,
        ];
    }

    /**
     * Display the specified project's budget plan and items.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;

        // If no budget plan exists, redirect to create for Manager or show empty page for PMO
        if (!$budgetPlan) {
            if ($role === 'manager') {
                return redirect()->route('projects.budget.create', $project->id)
                    ->with('info', 'Budget Plan belum dibuat. Silakan inisialisasi terlebih dahulu.');
            }
            // For PMO, we can render a show view showing that the plan has not been created yet
        }

        $budgetItems = $budgetPlan ? $budgetPlan->budgetItems()->orderBy('category')->get() : collect();
        $isBudgetFinalized = $budgetPlan && $budgetPlan->status === 'finalized';

        $baselineInfo = self::getBaselineBudget($project);
        $baselineBudget = $baselineInfo['value'];
        $baselineSource = $baselineInfo['source'];
        
        $totalRab = $budgetPlan ? (int) $budgetPlan->total_budget : 0;
        $remainingBudget = !is_null($baselineBudget) ? ($baselineBudget - $totalRab) : null;
        $usagePercentage = !is_null($baselineBudget) && $baselineBudget > 0 ? round(($totalRab / $baselineBudget) * 100) : 0;
        
        if (is_null($baselineBudget)) {
            $budgetStatus = 'Baseline belum tersedia';
        } else {
            if ($usagePercentage <= 80) {
                $budgetStatus = 'Aman';
            } elseif ($usagePercentage <= 100) {
                $budgetStatus = 'Mendekati Batas';
            } else {
                $budgetStatus = 'Melebihi Estimasi';
            }
        }

        return view('project-planning.budget.show', compact(
            'project', 
            'budgetPlan', 
            'budgetItems', 
            'isBudgetFinalized',
            'baselineBudget',
            'baselineSource',
            'totalRab',
            'remainingBudget',
            'usagePercentage',
            'budgetStatus'
        ));
    }

    /**
     * Show the form for creating a new budget plan.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat Budget Plan.');
        }

        $this->checkPlanningAccess($project);

        if ($project->budgetPlan) {
            return redirect()->route('projects.budget.edit', $project->id)
                ->with('info', 'Budget Plan sudah diinisialisasi. Anda dialihkan ke halaman edit.');
        }

        return view('project-planning.budget.create', compact('project'));
    }

    /**
     * Store a newly initialized budget plan.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat Budget Plan.');
        }

        $this->checkPlanningAccess($project);

        if ($project->budgetPlan) {
            return redirect()->route('projects.budget.edit', $project->id)
                ->with('info', 'Budget Plan sudah diinisialisasi.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $budgetPlan = new BudgetPlan();
        $budgetPlan->project_id = $project->id;
        $budgetPlan->status = 'draft';
        $budgetPlan->total_budget = 0.00;
        $budgetPlan->notes = $request->notes;
        $budgetPlan->created_by = Auth::id();
        $budgetPlan->updated_by = Auth::id();
        $budgetPlan->save();

        return redirect()->route('projects.budget.edit', $project->id)
            ->with('success', 'Budget Plan berhasil diinisialisasi. Silakan tambahkan item anggaran.');
    }

    /**
     * Show the dashboard to manage budget plan items.
     */
    public function edit(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengedit Budget Plan.');
        }

        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;
        if (!$budgetPlan) {
            return redirect()->route('projects.budget.create', $project->id);
        }

        if ($budgetPlan->status === 'finalized') {
            abort(403, 'Budget Plan sudah difinalisasi dan tidak dapat diedit lagi.');
        }

        $budgetItems = $budgetPlan->budgetItems()->orderBy('created_at', 'desc')->get();

        $baselineInfo = self::getBaselineBudget($project);
        $baselineBudget = $baselineInfo['value'];
        $baselineSource = $baselineInfo['source'];
        
        $totalRab = (int) $budgetPlan->total_budget;
        $remainingBudget = !is_null($baselineBudget) ? ($baselineBudget - $totalRab) : null;
        $usagePercentage = !is_null($baselineBudget) && $baselineBudget > 0 ? round(($totalRab / $baselineBudget) * 100) : 0;
        
        if (is_null($baselineBudget)) {
            $budgetStatus = 'Baseline belum tersedia';
        } else {
            if ($usagePercentage <= 80) {
                $budgetStatus = 'Aman';
            } elseif ($usagePercentage <= 100) {
                $budgetStatus = 'Mendekati Batas';
            } else {
                $budgetStatus = 'Melebihi Estimasi';
            }
        }

        return view('project-planning.budget.edit', compact(
            'project', 
            'budgetPlan', 
            'budgetItems',
            'baselineBudget',
            'baselineSource',
            'totalRab',
            'remainingBudget',
            'usagePercentage',
            'budgetStatus'
        ));
    }

    /**
     * Update the budget plan general metadata.
     */
    public function update(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat memperbarui Budget Plan.');
        }

        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;
        if (!$budgetPlan) {
            abort(404, 'Budget Plan tidak ditemukan.');
        }

        if ($budgetPlan->status === 'finalized') {
            abort(403, 'Budget Plan sudah difinalisasi dan tidak dapat diperbarui.');
        }

        $request->validate([
            'notes' => 'nullable|string',
        ]);

        $budgetPlan->notes = $request->notes;
        $budgetPlan->updated_by = Auth::id();
        $budgetPlan->save();

        return redirect()->route('projects.budget.edit', $project->id)
            ->with('success', 'Catatan Budget Plan berhasil diperbarui.');
    }

    /**
     * Add a budget item to the plan.
     */
    public function addItem(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat menambahkan item anggaran.');
        }

        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;
        if (!$budgetPlan) {
            abort(404, 'Budget Plan tidak ditemukan.');
        }

        if ($budgetPlan->status === 'finalized') {
            abort(403, 'Budget Plan sudah difinalisasi.');
        }

        $request->validate([
            'category' => 'required|string|in:human_resource,infrastructure,tools,operational,contingency,other',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'description.required' => 'Deskripsi wajib diisi.',
            'quantity.required' => 'Jumlah (quantity) wajib diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka bulat.',
            'quantity.min' => 'Jumlah minimal adalah 1.',
            'unit.required' => 'Satuan (unit) wajib diisi.',
            'unit_cost.required' => 'Harga satuan wajib diisi.',
            'unit_cost.numeric' => 'Harga satuan harus berupa angka.',
            'unit_cost.min' => 'Harga satuan minimal 0.',
        ]);

        $totalCost = $request->quantity * $request->unit_cost;

        $item = new BudgetItem();
        $item->budget_plan_id = $budgetPlan->id;
        $item->category = $request->category;
        $item->description = $request->description;
        $item->quantity = $request->quantity;
        $item->unit = $request->unit;
        $item->unit_cost = $request->unit_cost;
        $item->total_cost = $totalCost;
        $item->notes = $request->notes;
        $item->created_by = Auth::id();
        $item->updated_by = Auth::id();
        $item->save();

        // Recalculate plan total
        $budgetPlan->total_budget = $budgetPlan->budgetItems()->sum('total_cost');
        $budgetPlan->save();

        return redirect()->route('projects.budget.edit', $project->id)
            ->with('success', 'Item anggaran berhasil ditambahkan.');
    }

    /**
     * Update an existing budget item.
     */
    public function updateItem(Request $request, Project $project, BudgetItem $budgetItem)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat memperbarui item anggaran.');
        }

        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;
        if (!$budgetPlan || $budgetItem->budget_plan_id !== $budgetPlan->id) {
            abort(404, 'Item anggaran tidak sesuai dengan proyek ini.');
        }

        if ($budgetPlan->status === 'finalized') {
            abort(403, 'Budget Plan sudah difinalisasi.');
        }

        $request->validate([
            'category' => 'required|string|in:human_resource,infrastructure,tools,operational,contingency,other',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit' => 'required|string|max:50',
            'unit_cost' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ], [
            'category.required' => 'Kategori wajib dipilih.',
            'category.in' => 'Kategori tidak valid.',
            'description.required' => 'Deskripsi wajib diisi.',
            'quantity.required' => 'Jumlah (quantity) wajib diisi.',
            'quantity.integer' => 'Jumlah harus berupa angka bulat.',
            'quantity.min' => 'Jumlah minimal adalah 1.',
            'unit.required' => 'Satuan (unit) wajib diisi.',
            'unit_cost.required' => 'Harga satuan wajib diisi.',
            'unit_cost.numeric' => 'Harga satuan harus berupa angka.',
            'unit_cost.min' => 'Harga satuan minimal 0.',
        ]);

        $totalCost = $request->quantity * $request->unit_cost;

        $budgetItem->category = $request->category;
        $budgetItem->description = $request->description;
        $budgetItem->quantity = $request->quantity;
        $budgetItem->unit = $request->unit;
        $budgetItem->unit_cost = $request->unit_cost;
        $budgetItem->total_cost = $totalCost;
        $budgetItem->notes = $request->notes;
        $budgetItem->updated_by = Auth::id();
        $budgetItem->save();

        // Recalculate plan total
        $budgetPlan->total_budget = $budgetPlan->budgetItems()->sum('total_cost');
        $budgetPlan->save();

        return redirect()->route('projects.budget.edit', $project->id)
            ->with('success', 'Item anggaran berhasil diperbarui.');
    }

    /**
     * Delete a budget item (only draft allowed).
     */
    public function deleteItem(Project $project, BudgetItem $budgetItem)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat menghapus item anggaran.');
        }

        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;
        if (!$budgetPlan || $budgetItem->budget_plan_id !== $budgetPlan->id) {
            abort(404, 'Item anggaran tidak sesuai dengan proyek ini.');
        }

        if ($budgetPlan->status === 'finalized') {
            abort(403, 'Budget Plan sudah difinalisasi.');
        }

        $budgetItem->delete();

        // Recalculate plan total
        $budgetPlan->total_budget = $budgetPlan->budgetItems()->sum('total_cost');
        $budgetPlan->save();

        return redirect()->route('projects.budget.edit', $project->id)
            ->with('success', 'Item anggaran berhasil dihapus.');
    }

    /**
     * Finalize the project budget.
     */
    public function finalize(Project $project)
    {
        $role = $this->checkBaseAccess();
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat memfinalisasi Budget Plan.');
        }

        $this->checkPlanningAccess($project);

        $budgetPlan = $project->budgetPlan;
        if (!$budgetPlan) {
            abort(404, 'Budget Plan tidak ditemukan.');
        }

        if ($budgetPlan->status === 'finalized') {
            return redirect()->route('projects.budget.show', $project->id)
                ->with('info', 'Budget Plan sudah berstatus finalized.');
        }

        // Manager tidak boleh finalize jika belum ada budget item
        if ($budgetPlan->budgetItems()->count() === 0) {
            return redirect()->route('projects.budget.edit', $project->id)
                ->with('error', 'Budget Plan tidak dapat difinalisasi karena belum memiliki item anggaran.');
        }

        // Validate against baseline budget
        $baselineInfo = self::getBaselineBudget($project);
        $baselineBudget = $baselineInfo['value'];

        if (is_null($baselineBudget)) {
            return redirect()->route('projects.budget.edit', $project->id)
                ->with('error', 'Budget Planning belum dapat difinalisasi karena baseline anggaran dari Proposal/Charter belum tersedia.');
        }

        $totalRab = (int) $budgetPlan->total_budget;
        if ($totalRab > $baselineBudget) {
            return redirect()->route('projects.budget.edit', $project->id)
                ->with('error', 'Budget Planning tidak dapat difinalisasi karena total RAB melebihi baseline anggaran awal.');
        }

        $budgetPlan->status = 'finalized';
        $budgetPlan->updated_by = Auth::id();
        $budgetPlan->save();

        return redirect()->route('projects.budget.show', $project->id)
            ->with('success', 'Budget Plan berhasil difinalisasi.');
    }
}

