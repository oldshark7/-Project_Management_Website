<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectScopeController extends Controller
{
    /**
     * Check if the authenticated user has access to project planning scope.
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
     * Display a listing of projects in planning status and their scope.
     */
    public function index()
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role === 'project manager') {
            // PM only sees their own planning projects
            $projects = Project::where('owner_id', $userId)
                ->where('status', 'planning')
                ->with('scope')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Manager and PMO see all planning projects
            $projects = Project::where('status', 'planning')
                ->with('scope')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('project-planning.scope.index', compact('projects'));
    }

    /**
     * Display the specified project scope.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // Validate project status
        if ($project->status !== 'planning') {
            abort(403, 'Project Scope hanya dapat diakses jika status proyek adalah Planning.');
        }

        // Authorization checks
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat Project Scope untuk proyek Anda sendiri.');
            }
        }

        $scope = $project->scope;

        return view('project-planning.scope.show', compact('project', 'scope'));
    }

    /**
     * Show the form for creating a new project scope.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat Project Scope.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Project Scope hanya dapat dibuat jika status proyek adalah Planning.');
        }

        if ($project->scope) {
            return redirect()->route('projects.scope.edit', $project->id)
                ->with('info', 'Project Scope sudah ada. Anda dialihkan ke halaman edit.');
        }

        return view('project-planning.scope.create', compact('project'));
    }

    /**
     * Store a newly created project scope in storage.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat membuat Project Scope.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Project Scope hanya dapat dibuat jika status proyek adalah Planning.');
        }

        if ($project->scope) {
            abort(403, 'Project Scope untuk proyek ini sudah dibuat.');
        }

        $validated = $request->validate([
            'objective' => 'required|string',
            'scope_description' => 'required|string',
            'in_scope' => 'required|string',
            'out_of_scope' => 'required|string',
            'main_requirements' => 'nullable|string',
            'deliverables' => 'required|string',
            'acceptance_criteria' => 'required|string',
            'assumptions' => 'nullable|string',
            'constraints' => 'nullable|string',
            'notes' => 'nullable|string',
            'action' => 'required|string|in:save,submit',
        ], [
            'objective.required' => 'Tujuan Proyek (Objective) wajib diisi.',
            'scope_description.required' => 'Deskripsi Ruang Lingkup wajib diisi.',
            'in_scope.required' => 'Pekerjaan yang Termasuk (In-Scope) wajib diisi.',
            'out_of_scope.required' => 'Pekerjaan yang Tidak Termasuk (Out-of-Scope) wajib diisi.',
            'deliverables.required' => 'Hasil Kerja (Deliverables) wajib diisi.',
            'acceptance_criteria.required' => 'Kriteria Penerimaan wajib diisi.',
        ]);

        $status = ($validated['action'] === 'submit') ? 'finalized' : 'draft';

        $scope = new ProjectScope();
        $scope->project_id = $project->id;
        $scope->objective = $validated['objective'];
        $scope->scope_description = $validated['scope_description'];
        $scope->in_scope = $validated['in_scope'];
        $scope->out_of_scope = $validated['out_of_scope'];
        $scope->main_requirements = $validated['main_requirements'] ?? null;
        $scope->deliverables = $validated['deliverables'];
        $scope->acceptance_criteria = $validated['acceptance_criteria'];
        $scope->assumptions = $validated['assumptions'] ?? null;
        $scope->constraints = $validated['constraints'] ?? null;
        $scope->notes = $validated['notes'] ?? null;
        $scope->status = $status;
        $scope->created_by = $userId;
        $scope->updated_by = $userId;
        $scope->save();

        $msg = ($status === 'finalized') ? 'Project Scope berhasil difinalisasi.' : 'Draf Project Scope berhasil disimpan.';
        return redirect()->route('projects.scope.show', $project->id)->with('success', $msg);
    }

    /**
     * Show the form for editing the project scope.
     */
    public function edit(Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengubah Project Scope.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Project Scope hanya dapat diakses jika status proyek adalah Planning.');
        }

        $scope = $project->scope;
        if (!$scope) {
            return redirect()->route('projects.scope.create', $project->id);
        }

        if ($scope->status === 'finalized') {
            abort(403, 'Anda tidak dapat mengubah Project Scope yang sudah finalized.');
        }

        return view('project-planning.scope.edit', compact('project', 'scope'));
    }

    /**
     * Update the project scope in storage.
     */
    public function update(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat mengubah Project Scope.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Project Scope hanya dapat diubah jika status proyek adalah Planning.');
        }

        $scope = $project->scope;
        if (!$scope) {
            abort(404, 'Project Scope tidak ditemukan.');
        }

        if ($scope->status === 'finalized') {
            abort(403, 'Anda tidak dapat mengubah Project Scope yang sudah finalized.');
        }

        $validated = $request->validate([
            'objective' => 'required|string',
            'scope_description' => 'required|string',
            'in_scope' => 'required|string',
            'out_of_scope' => 'required|string',
            'main_requirements' => 'nullable|string',
            'deliverables' => 'required|string',
            'acceptance_criteria' => 'required|string',
            'assumptions' => 'nullable|string',
            'constraints' => 'nullable|string',
            'notes' => 'nullable|string',
            'action' => 'required|string|in:save,submit',
        ], [
            'objective.required' => 'Tujuan Proyek (Objective) wajib diisi.',
            'scope_description.required' => 'Deskripsi Ruang Lingkup wajib diisi.',
            'in_scope.required' => 'Pekerjaan yang Termasuk (In-Scope) wajib diisi.',
            'out_of_scope.required' => 'Pekerjaan yang Tidak Termasuk (Out-of-Scope) wajib diisi.',
            'deliverables.required' => 'Hasil Kerja (Deliverables) wajib diisi.',
            'acceptance_criteria.required' => 'Kriteria Penerimaan wajib diisi.',
        ]);

        $status = ($validated['action'] === 'submit') ? 'finalized' : 'draft';

        // Bug protection: only update fields if present in request, though in edit form they always are
        if ($request->has('objective')) {
            $scope->objective = $validated['objective'];
        }
        if ($request->has('scope_description')) {
            $scope->scope_description = $validated['scope_description'];
        }
        if ($request->has('in_scope')) {
            $scope->in_scope = $validated['in_scope'];
        }
        if ($request->has('out_of_scope')) {
            $scope->out_of_scope = $validated['out_of_scope'];
        }
        if ($request->has('main_requirements')) {
            $scope->main_requirements = $validated['main_requirements'] ?? null;
        }
        if ($request->has('deliverables')) {
            $scope->deliverables = $validated['deliverables'];
        }
        if ($request->has('acceptance_criteria')) {
            $scope->acceptance_criteria = $validated['acceptance_criteria'];
        }
        if ($request->has('assumptions')) {
            $scope->assumptions = $validated['assumptions'] ?? null;
        }
        if ($request->has('constraints')) {
            $scope->constraints = $validated['constraints'] ?? null;
        }
        if ($request->has('notes')) {
            $scope->notes = $validated['notes'] ?? null;
        }

        if ($status === 'finalized') {
            $scope->status = 'finalized';
        }

        $scope->updated_by = $userId;
        $scope->save();

        $msg = ($status === 'finalized') ? 'Project Scope berhasil difinalisasi.' : 'Perubahan Project Scope berhasil disimpan.';
        return redirect()->route('projects.scope.show', $project->id)->with('success', $msg);
    }

    /**
     * Finalize the project scope.
     */
    public function finalize(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role !== 'manager') {
            abort(403, 'Hanya Manager yang dapat memfinalisasi Project Scope.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Project Scope hanya dapat diakses jika status proyek adalah Planning.');
        }

        $scope = $project->scope;
        if (!$scope) {
            abort(404, 'Project Scope belum dibuat.');
        }

        if ($scope->status === 'finalized') {
            abort(403, 'Project Scope sudah finalized.');
        }

        $scope->status = 'finalized';
        $scope->updated_by = $userId;
        $scope->save();

        return redirect()->route('projects.scope.show', $project->id)->with('success', 'Project Scope berhasil difinalisasi.');
    }
}
