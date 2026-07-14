<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectProposal;
use App\Services\OpenRouterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectProposalController extends Controller
{
    /**
     * Check if the authenticated user has access to project proposals.
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
     * Display the proposal for the specified project.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // PM authorization: must be the project owner
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat proposal untuk proyek Anda sendiri.');
            }
        } 
        // PMO authorization: only allowed if project status is planning
        elseif (in_array($role, ['pmo', 'project management officer'])) {
            if ($project->status !== 'planning') {
                abort(403, 'PMO hanya dapat melihat proposal jika status proyek sudah planning.');
            }
        }
        // Manager: has access to view all.

        $proposal = $project->proposal;

        return view('projects.proposal.show', compact('project', 'proposal'));
    }

    /**
     * Download the proposal as PDF.
     */
    public function downloadPdf(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // PM authorization: must be the project owner
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat proposal untuk proyek Anda sendiri.');
            }
        } 
        // PMO authorization: only allowed if project status is planning
        elseif (in_array($role, ['pmo', 'project management officer'])) {
            if ($project->status !== 'planning') {
                abort(403, 'PMO hanya dapat melihat proposal jika status proyek sudah planning.');
            }
        }
        // Manager: has access to view all.

        $proposal = $project->proposal;
        if (!$proposal) {
            return redirect()->route('projects.show', $project->id)->with('error', 'Project Proposal belum dibuat.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('projects.proposal.pdf', compact('project', 'proposal'));
        $filename = 'project-proposal-' . \Illuminate\Support\Str::slug($project->title) . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Show the form for creating a new proposal.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat proposal.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Proposal hanya dapat dibuat jika status proyek sudah disetujui (Approved).');
        }
 
        if ($project->proposal) {
            return redirect()->route('projects.proposal.edit', $project->id)
                ->with('info', 'Proposal sudah ada. Anda dialihkan ke halaman edit.');
        }
 
        return view('projects.proposal.create', compact('project'));
    }

    /**
     * Store a newly created proposal in storage.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat proposal.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Proposal hanya dapat dibuat jika status proyek sudah disetujui (Approved).');
        }
 
        if ($project->proposal) {
            abort(403, 'Proposal untuk proyek ini sudah dibuat.');
        }
 
        $validated = $request->validate([
            'background' => 'nullable|string',
            'objectives' => 'nullable|string',
            'initial_needs' => 'nullable|string',
            'project_overview' => 'nullable|string',
            'scope_overview' => 'nullable|string',
            'estimated_budget' => 'nullable|numeric|min:0',
            'action' => 'required|string|in:save,submit,generate_ai',
        ], [
            'estimated_budget.numeric' => 'Anggaran perkiraan harus berupa angka.',
            'estimated_budget.min' => 'Anggaran perkiraan tidak boleh bernilai negatif.',
        ]);
 
        $status = ($validated['action'] === 'submit') ? 'submitted' : 'draft';
 
        $proposal = new ProjectProposal();
        $proposal->project_id = $project->id;
        $proposal->background = $validated['background'] ?? null;
        $proposal->objectives = $validated['objectives'] ?? null;
        $proposal->initial_needs = $validated['initial_needs'] ?? null;
        $proposal->project_overview = $validated['project_overview'] ?? null;
        $proposal->scope_overview = $validated['scope_overview'] ?? null;
        $proposal->estimated_budget = $validated['estimated_budget'] ?? null;
        $proposal->status = $status;
        $proposal->created_by = $userId;
        $proposal->updated_by = $userId;
 
        if ($validated['action'] === 'generate_ai') {
            if (function_exists('set_time_limit')) {
                @set_time_limit(180);
            }
            if (empty(config('services.openrouter.api_key'))) {
                $proposal->save();
                return redirect()->route('projects.proposal.edit', $project->id)
                    ->with('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda. Namun draf berhasil disimpan.');
            }
 
            try {
                $openRouter = app(OpenRouterService::class);
                $suggestions = $openRouter->generateProposalSuggestions($project);
                $proposal->ai_suggestions = $suggestions;
                $proposal->save();
                return redirect()->route('projects.proposal.edit', $project->id)
                    ->with('success', 'Rekomendasi AI berhasil dibuat dan draf disimpan.');
            } catch (\Exception $e) {
                $proposal->save();
                return redirect()->route('projects.proposal.edit', $project->id)
                    ->with('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
            }
        }

        $proposal->save();
        $msg = ($status === 'submitted') ? 'Proposal berhasil difinalisasi.' : 'Draf proposal berhasil disimpan.';
        return redirect()->route('projects.proposal.show', $project->id)->with('success', $msg);
    }

    /**
     * Show the form for editing the proposal.
     */
    public function edit(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengedit proposal.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Anda tidak dapat mengedit proposal karena status proyek bukan Approved.');
        }
 
        $proposal = $project->proposal;
        if (!$proposal) {
            return redirect()->route('projects.proposal.create', $project->id);
        }
 
        if ($proposal->status === 'submitted') {
            abort(403, 'Anda tidak dapat mengedit proposal yang sudah difinalisasi.');
        }
 
        return view('projects.proposal.edit', compact('project', 'proposal'));
    }

    /**
     * Update the proposal in storage.
     */
    public function update(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();
 
        $proposal = $project->proposal;
        if (!$proposal) {
            abort(404, 'Proposal tidak ditemukan.');
        }
 
        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengedit proposal.');
        }
 
        if ($project->status !== 'approved') {
            abort(403, 'Anda tidak dapat mengedit proposal karena status proyek bukan Approved.');
        }
 
        if ($proposal->status === 'submitted') {
            abort(403, 'Anda tidak dapat mengedit proposal yang sudah difinalisasi.');
        }
 
        $validated = $request->validate([
            'background' => 'nullable|string',
            'objectives' => 'nullable|string',
            'initial_needs' => 'nullable|string',
            'project_overview' => 'nullable|string',
            'scope_overview' => 'nullable|string',
            'estimated_budget' => 'nullable|numeric|min:0',
            'action' => 'required|string|in:save,submit,generate_ai',
        ], [
            'estimated_budget.numeric' => 'Anggaran perkiraan harus berupa angka.',
            'estimated_budget.min' => 'Anggaran perkiraan tidak boleh bernilai negatif.',
        ]);
 
        if ($request->has('background')) {
            $proposal->background = $validated['background'] ?? null;
        }
        if ($request->has('objectives')) {
            $proposal->objectives = $validated['objectives'] ?? null;
        }
        if ($request->has('initial_needs')) {
            $proposal->initial_needs = $validated['initial_needs'] ?? null;
        }
        if ($request->has('project_overview')) {
            $proposal->project_overview = $validated['project_overview'] ?? null;
        }
        if ($request->has('scope_overview')) {
            $proposal->scope_overview = $validated['scope_overview'] ?? null;
        }
        if ($request->has('estimated_budget')) {
            $proposal->estimated_budget = $validated['estimated_budget'] ?? null;
        }
        $proposal->updated_by = $userId;
 
        if ($validated['action'] === 'submit') {
            $proposal->status = 'submitted';
        }
 
        if ($validated['action'] === 'generate_ai') {
            if (function_exists('set_time_limit')) {
                @set_time_limit(180);
            }
            if (empty(config('services.openrouter.api_key'))) {
                $proposal->save();
                return redirect()->route('projects.proposal.edit', $project->id)
                    ->with('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda. Namun draf berhasil disimpan.');
            }
 
            try {
                $openRouter = app(OpenRouterService::class);
                $suggestions = $openRouter->generateProposalSuggestions($project);
                $proposal->ai_suggestions = $suggestions;
                $proposal->save();
                return redirect()->route('projects.proposal.edit', $project->id)
                    ->with('success', 'Rekomendasi AI berhasil diperbarui.');
            } catch (\Exception $e) {
                $proposal->save();
                return redirect()->route('projects.proposal.edit', $project->id)
                    ->with('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
            }
        }

        $proposal->save();
 
        $msg = ($validated['action'] === 'submit') ? 'Proposal berhasil difinalisasi.' : 'Perubahan proposal berhasil disimpan.';
        return redirect()->route('projects.proposal.show', $project->id)->with('success', $msg);
    }

    /**
     * Standalone action to generate AI suggestion.
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
 
        $proposal = $project->proposal;
        if (!$proposal) {
            abort(404, 'Project Proposal belum dibuat. Silakan buat draf terlebih dahulu.');
        }
 
        if ($proposal->status !== 'draft') {
            abort(403, 'Rekomendasi AI hanya dapat dibuat jika status Project Proposal masih draft.');
        }
 
        if (function_exists('set_time_limit')) {
            @set_time_limit(180);
        }
 
        if (empty(config('services.openrouter.api_key'))) {
            return redirect()->back()->with('error', 'API Key OpenRouter belum dikonfigurasi. Silakan periksa file .env Anda.');
        }
 
        try {
            $openRouter = app(OpenRouterService::class);
            $suggestions = $openRouter->generateProposalSuggestions($project);
            $proposal->ai_suggestions = $suggestions;
            $proposal->updated_by = $userId;
            $proposal->save();
 
            return redirect()->back()->with('success', 'Rekomendasi AI berhasil digenerate.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal mendapatkan rekomendasi AI. Silakan coba lagi atau periksa konfigurasi API.');
        }
    }
}
