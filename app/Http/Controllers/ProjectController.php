<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    /**
     * Check if the authenticated user has access to projects.
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
     * Display a listing of the resource.
     */
    public function index()
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role === 'project manager') {
            // PM only sees projects they created
            $projects = Project::where('owner_id', $userId)
                ->with(['owner', 'manager'])
                ->orderBy('created_at', 'desc')
                ->get();
        } elseif (in_array($role, ['pmo', 'project management officer'])) {
            // PMO only sees projects in planning stage
            $projects = Project::where('status', 'planning')
                ->with(['owner', 'manager'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Manager sees all projects
            $projects = Project::with(['owner', 'manager'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project manager') {
            abort(403, 'Hanya Project Manager yang dapat membuat project baru.');
        }

        $managers = User::whereRaw('LOWER(role) = ?', ['manager'])->get();

        return view('projects.create', compact('managers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project manager') {
            abort(403, 'Hanya Project Manager yang dapat membuat project baru.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'manager_id' => 'nullable|exists:users,id',
        ], [
            'title.required' => 'Judul proyek wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
        ]);

        $project = new Project();
        $project->title = $validated['title'];
        $project->description = $validated['description'] ?? null;
        $project->start_date = $validated['start_date'] ?? null;
        $project->end_date = $validated['end_date'] ?? null;
        $project->manager_id = $validated['manager_id'] ?? null;
        $project->owner_id = Auth::id();
        $project->status = 'draft'; // default status
        $project->save();

        return redirect()->route('projects.index')->with('success', 'Project berhasil dibuat sebagai draft.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // Check view access
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat project yang Anda buat sendiri.');
            }
        } elseif (in_array($role, ['pmo', 'project management officer'])) {
            if ($project->status !== 'planning') {
                abort(403, 'PMO hanya dapat melihat project dengan status planning.');
            }
        }

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if (in_array($role, ['pmo', 'project management officer'])) {
            abort(403, 'PMO tidak memiliki akses untuk mengubah project.');
        }

        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat mengubah project yang Anda buat sendiri.');
            }
            if (!in_array($project->status, ['draft', 'rejected'])) {
                abort(403, 'Project Manager hanya dapat mengubah project dengan status draft atau rejected.');
            }
        }

        $managers = User::whereRaw('LOWER(role) = ?', ['manager'])->get();

        return view('projects.edit', compact('project', 'managers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if (in_array($role, ['pmo', 'project management officer'])) {
            abort(403, 'PMO tidak memiliki akses untuk mengubah project.');
        }

        // Validate basic inputs if user is PM, or just status if Manager
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat mengubah project yang Anda buat sendiri.');
            }
            if (!in_array($project->status, ['draft', 'rejected'])) {
                abort(403, 'Project Manager hanya dapat mengubah project dengan status draft atau rejected.');
            }

            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'manager_id' => 'nullable|exists:users,id',
                'action' => 'nullable|string|in:save,submit',
            ], [
                'title.required' => 'Judul proyek wajib diisi.',
                'end_date.after_or_equal' => 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.',
            ]);

            $project->title = $validated['title'];
            $project->description = $validated['description'] ?? null;
            $project->start_date = $validated['start_date'] ?? null;
            $project->end_date = $validated['end_date'] ?? null;
            $project->manager_id = $validated['manager_id'] ?? null;

            if ($request->input('action') === 'submit') {
                $project->status = 'submitted';
            }

            $project->save();

            $msg = $request->input('action') === 'submit' ? 'Project berhasil diajukan.' : 'Project berhasil diperbarui.';
            return redirect()->route('projects.index')->with('success', $msg);

        } elseif ($role === 'manager') {
            $validated = $request->validate([
                'status' => 'required|string|in:draft,submitted,approved,rejected,planning',
            ]);

            $oldStatus = $project->status;
            $newStatus = $validated['status'];

            // Enforce transitions:
            // - draft -> submitted (handled by PM, but if Manager does it, fine, though usually:
            // - submitted -> approved or rejected (Manager only)
            // - approved -> planning (Manager only)
            // - rejected -> submitted (handled by PM)
            
            $valid = false;
            if ($oldStatus === $newStatus) {
                $valid = true;
            } elseif ($oldStatus === 'draft' && $newStatus === 'submitted') {
                $valid = true;
            } elseif ($oldStatus === 'submitted' && in_array($newStatus, ['approved', 'rejected'])) {
                $valid = true;
            } elseif ($oldStatus === 'approved' && $newStatus === 'planning') {
                // Ensure Proposal and Charter exist and are status = submitted (finalized)
                $proposal = $project->proposal;
                $charter = $project->charter;
                if (!$proposal || $proposal->status !== 'submitted' || !$charter || $charter->status !== 'submitted') {
                    return back()->withErrors(['status' => 'Project Proposal dan Project Charter harus dibuat dan difinalisasi (Submitted) terlebih dahulu sebelum status proyek dapat diubah ke Planning.'])
                                 ->withInput();
                }
                $valid = true;
            } elseif ($oldStatus === 'rejected' && $newStatus === 'submitted') {
                $valid = true;
            }

            if (!$valid) {
                return back()->withErrors(['status' => "Perubahan status dari {$oldStatus} ke {$newStatus} tidak valid."])
                             ->withInput();
            }

            $project->status = $newStatus;
            $project->save();

            return redirect()->route('projects.index')->with('success', "Status project berhasil diubah menjadi {$newStatus}.");
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role !== 'project manager') {
            abort(403, 'Hanya Project Manager yang dapat menghapus project.');
        }

        if ($project->owner_id !== $userId) {
            abort(403, 'Anda hanya dapat menghapus project yang Anda buat sendiri.');
        }

        if ($project->status !== 'draft') {
            abort(403, 'Project hanya dapat dihapus jika status masih draft.');
        }

        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project berhasil dihapus.');
    }
}
