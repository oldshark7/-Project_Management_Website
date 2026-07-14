<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectCharter;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectCharterController extends Controller
{
    /**
     * Check if the authenticated user has access to project charters.
     */
    protected function checkBaseAccess(): string
    {
        if (!Auth::check()) {
            abort(401);
        }

        $role = strtolower(Auth::user()->role);
        if (!in_array($role, ['project manager', 'manager', 'project management officer', 'pmo'])) {
            abort(403, 'Akses ditolak. Peran Anda tidak diizinkan mengakses halaman ini.');
        }

        return $role;
    }

    /**
     * Display the charter for the specified project.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // PM authorization: must be the project owner
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat Project Charter untuk proyek Anda sendiri.');
            }
        } 
        // PMO authorization: only allowed if project status is planning
        elseif (in_array($role, ['pmo', 'project management officer'])) {
            if ($project->status !== 'planning') {
                abort(403, 'PMO hanya dapat melihat Project Charter jika status proyek sudah planning.');
            }
        }
        // Manager: has access to view all.

        $charter = $project->charter;

        $actualMilestones = $project->timelineItems()
            ->where('is_milestone', true)
            ->with(['wbsItem', 'dependencyWbsItem'])
            ->get();

        return view('projects.charter.show', compact('project', 'charter', 'actualMilestones'));
    }

    /**
     * Download the Project Charter as PDF.
     */
    public function downloadPdf(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // PM authorization: must be the project owner
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat Project Charter untuk proyek Anda sendiri.');
            }
        } 
        // PMO authorization: only allowed if project status is planning
        elseif (in_array($role, ['pmo', 'project management officer'])) {
            if ($project->status !== 'planning') {
                abort(403, 'PMO hanya dapat melihat Project Charter jika status proyek sudah planning.');
            }
        }
        // Manager: has access to view all.

        $charter = $project->charter;
        if (!$charter) {
            return redirect()->route('projects.show', $project->id)->with('error', 'Project Charter belum dibuat.');
        }

        $actualMilestones = $project->timelineItems()
            ->where('is_milestone', true)
            ->with(['wbsItem', 'dependencyWbsItem'])
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('projects.charter.pdf', compact('project', 'charter', 'actualMilestones'));
        $filename = 'project-charter-' . \Illuminate\Support\Str::slug($project->title) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show the form for creating a new charter.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat Project Charter.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Project Charter hanya dapat dibuat jika status proyek sudah disetujui (Approved).');
        }
 
        if ($project->charter) {
            return redirect()->route('projects.charter.edit', $project->id)
                ->with('info', 'Project Charter sudah ada. Anda dialihkan ke halaman edit.');
        }
 
        return view('projects.charter.create', compact('project'));
    }

    /**
     * Store a newly created charter in storage.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat Project Charter.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Project Charter hanya dapat dibuat jika status proyek sudah disetujui (Approved).');
        }
 
        if ($project->charter) {
            abort(403, 'Project Charter untuk proyek ini sudah dibuat.');
        }
 
        $validated = $request->validate([
            'project_purpose' => 'nullable|string',
            'business_case' => 'nullable|string',
            'project_objectives' => 'nullable|string',
            'scope_summary' => 'nullable|string',
            'success_criteria' => 'nullable|string',
            'assumptions' => 'nullable|string',
            'constraints' => 'nullable|string',
            'stakeholder_summary' => 'nullable|string',
            'milestone_summary' => 'nullable|string',
            'budget_summary' => 'nullable|numeric|min:0',
            'action' => 'required|string|in:save,submit,generate_ai',
        ], [
            'budget_summary.numeric' => 'Anggaran perkiraan harus berupa angka.',
            'budget_summary.min' => 'Anggaran perkiraan tidak boleh bernilai negatif.',
        ]);
 
        $status = ($validated['action'] === 'submit') ? 'submitted' : 'draft';
 
        $charter = new ProjectCharter();
        $charter->project_id = $project->id;
        $charter->project_purpose = $validated['project_purpose'] ?? null;
        $charter->business_case = $validated['business_case'] ?? null;
        $charter->project_objectives = $validated['project_objectives'] ?? null;
        $charter->scope_summary = $validated['scope_summary'] ?? null;
        $charter->success_criteria = $validated['success_criteria'] ?? null;
        $charter->assumptions = $validated['assumptions'] ?? null;
        $charter->constraints = $validated['constraints'] ?? null;
        $charter->stakeholder_summary = $validated['stakeholder_summary'] ?? null;
        $charter->milestone_summary = $validated['milestone_summary'] ?? null;
        $charter->budget_summary = $validated['budget_summary'] ?? null;
        $charter->status = $status;
        $charter->created_by = $userId;
        $charter->updated_by = $userId;
 
        if ($validated['action'] === 'generate_ai') {
            if (function_exists('set_time_limit')) {
                @set_time_limit(180);
            }
            if (empty(config('services.openrouter.api_key'))) {
                $charter->save();
                return redirect()->route('projects.charter.edit', $project->id)
                    ->with('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda. Namun draf berhasil disimpan.');
            }
 
            try {
                $openRouter = app(OpenRouterService::class);
                $suggestions = $openRouter->generateCharterSuggestions($project);
                $charter->ai_suggestions = $suggestions;
                $charter->save();
                return redirect()->route('projects.charter.edit', $project->id)
                    ->with('success', 'Rekomendasi AI berhasil dibuat dan draf disimpan.');
            } catch (\Exception $e) {
                $charter->save();
                return redirect()->route('projects.charter.edit', $project->id)
                    ->with('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
            }
        }
 
        $charter->save();
        $msg = ($status === 'submitted') ? 'Project Charter berhasil difinalisasi.' : 'Draf Project Charter berhasil disimpan.';
        return redirect()->route('projects.charter.show', $project->id)->with('success', $msg);
    }

    /**
     * Show the form for editing the charter.
     */
    public function edit(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengedit Project Charter.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Anda tidak dapat mengedit Project Charter karena status proyek bukan Approved.');
        }
 
        $charter = $project->charter;
        if (!$charter) {
            return redirect()->route('projects.charter.create', $project->id);
        }
 
        if ($charter->status === 'submitted') {
            abort(403, 'Anda tidak dapat mengedit Project Charter yang sudah difinalisasi.');
        }
 
        return view('projects.charter.edit', compact('project', 'charter'));
    }

    /**
     * Update the charter in storage.
     */
    public function update(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        $charter = $project->charter;
        if (!$charter) {
            abort(404, 'Project Charter tidak ditemukan.');
        }
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengedit Project Charter.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Anda tidak dapat mengedit Project Charter karena status proyek bukan Approved.');
        }
 
        if ($charter->status === 'submitted') {
            abort(403, 'Anda tidak dapat mengedit Project Charter yang sudah difinalisasi.');
        }
 
        $validated = $request->validate([
            'project_purpose' => 'nullable|string',
            'business_case' => 'nullable|string',
            'project_objectives' => 'nullable|string',
            'scope_summary' => 'nullable|string',
            'success_criteria' => 'nullable|string',
            'assumptions' => 'nullable|string',
            'constraints' => 'nullable|string',
            'stakeholder_summary' => 'nullable|string',
            'milestone_summary' => 'nullable|string',
            'budget_summary' => 'nullable|numeric|min:0',
            'action' => 'required|string|in:save,submit,generate_ai',
        ], [
            'budget_summary.numeric' => 'Anggaran perkiraan harus berupa angka.',
            'budget_summary.min' => 'Anggaran perkiraan tidak boleh bernilai negatif.',
        ]);
 
        if ($request->has('project_purpose')) {
            $charter->project_purpose = $validated['project_purpose'] ?? null;
        }
        if ($request->has('business_case')) {
            $charter->business_case = $validated['business_case'] ?? null;
        }
        if ($request->has('project_objectives')) {
            $charter->project_objectives = $validated['project_objectives'] ?? null;
        }
        if ($request->has('scope_summary')) {
            $charter->scope_summary = $validated['scope_summary'] ?? null;
        }
        if ($request->has('success_criteria')) {
            $charter->success_criteria = $validated['success_criteria'] ?? null;
        }
        if ($request->has('assumptions')) {
            $charter->assumptions = $validated['assumptions'] ?? null;
        }
        if ($request->has('constraints')) {
            $charter->constraints = $validated['constraints'] ?? null;
        }
        if ($request->has('stakeholder_summary')) {
            $charter->stakeholder_summary = $validated['stakeholder_summary'] ?? null;
        }
        if ($request->has('milestone_summary')) {
            $charter->milestone_summary = $validated['milestone_summary'] ?? null;
        }
        if ($request->has('budget_summary')) {
            $charter->budget_summary = $validated['budget_summary'] ?? null;
        }
        $charter->updated_by = $userId;
 
        if ($validated['action'] === 'submit') {
            $charter->status = 'submitted';
        }
 
        if ($validated['action'] === 'generate_ai') {
            if (function_exists('set_time_limit')) {
                @set_time_limit(180);
            }
            if (empty(config('services.openrouter.api_key'))) {
                $charter->save();
                return redirect()->route('projects.charter.edit', $project->id)
                    ->with('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda. Namun draf berhasil disimpan.');
            }
 
            try {
                $openRouter = app(OpenRouterService::class);
                $suggestions = $openRouter->generateCharterSuggestions($project);
                $charter->ai_suggestions = $suggestions;
                $charter->save();
                return redirect()->route('projects.charter.edit', $project->id)
                    ->with('success', 'Rekomendasi AI berhasil diperbarui.');
            } catch (\Exception $e) {
                $charter->save();
                return redirect()->route('projects.charter.edit', $project->id)
                    ->with('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
            }
        }
 
        $charter->save();
 
        $msg = ($validated['action'] === 'submit') ? 'Project Charter berhasil difinalisasi.' : 'Perubahan Project Charter berhasil disimpan.';
        return redirect()->route('projects.charter.show', $project->id)->with('success', $msg);
    }

    /**
     * Standalone action to generate AI suggestion from show or edit page.
     */
    public function generateAi(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat generate rekomendasi AI.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Rekomendasi AI hanya dapat dibuat jika status proyek sudah disetujui (Approved).');
        }
 
        $charter = $project->charter;
        if (!$charter) {
            abort(404, 'Project Charter belum dibuat. Silakan buat draf terlebih dahulu.');
        }
 
        if ($charter->status !== 'draft') {
            abort(403, 'Rekomendasi AI hanya dapat dibuat jika status Project Charter masih draft.');
        }
 
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
 
        if (empty(config('services.openrouter.api_key'))) {
            return redirect()->back()->with('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        }
 
        try {
            $openRouter = app(OpenRouterService::class);
            $suggestions = $openRouter->generateCharterSuggestions($project);
            $charter->ai_suggestions = $suggestions;
            $charter->updated_by = $userId;
            $charter->save();
 
            return redirect()->back()->with('success', 'Rekomendasi AI berhasil digenerate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
        }
    }
}
