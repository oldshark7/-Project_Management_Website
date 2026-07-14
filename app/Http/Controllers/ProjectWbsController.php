<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\WbsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectWbsController extends Controller
{
    /**
     * Check if the authenticated user has access to WBS.
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
     * Display a listing of projects in planning status and their WBS status.
     */
    public function index()
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role === 'project manager') {
            // PM only sees their own planning projects
            $projects = Project::where('owner_id', $userId)
                ->where('status', 'planning')
                ->with(['scope', 'wbsItems'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Manager and PMO see all planning projects
            $projects = Project::where('status', 'planning')
                ->with(['scope', 'wbsItems'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('project-planning.wbs.index', compact('projects'));
    }

    /**
     * Display the specified project's WBS.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // Validate project status
        if ($project->status !== 'planning') {
            abort(403, 'WBS hanya dapat diakses jika status proyek adalah Planning.');
        }

        // Validate that project scope is created and finalized
        if (!$project->scope || $project->scope->status !== 'finalized') {
            abort(403, 'WBS hanya dapat diakses jika Project Scope proyek ini sudah finalized.');
        }

        // Authorization checks
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat WBS untuk proyek Anda sendiri.');
            }
        }

        // Get root WbsItems (parent_id is null)
        $wbsItems = $project->wbsItems()
            ->whereNull('parent_id')
            ->orderBy('order_number')
            ->orderBy('created_at')
            ->with('children')
            ->get();

        // Calculate if WBS is finalized (has items and all of them are finalized)
        $totalItems = $project->wbsItems()->count();
        $draftItemsCount = $project->wbsItems()->where('status', 'draft')->count();
        $isWbsFinalized = ($totalItems > 0 && $draftItemsCount === 0);

        return view('project-planning.wbs.show', compact('project', 'wbsItems', 'isWbsFinalized', 'totalItems'));
    }

    /**
     * Show the form for creating a new WbsItem.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat item WBS.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Item WBS hanya dapat dibuat jika status proyek adalah Planning.');
        }

        if (!$project->scope || $project->scope->status !== 'finalized') {
            abort(403, 'Item WBS hanya dapat dibuat jika Project Scope proyek ini sudah finalized.');
        }

        // Check if WBS is finalized
        $totalItems = $project->wbsItems()->count();
        $draftItemsCount = $project->wbsItems()->where('status', 'draft')->count();
        if ($totalItems > 0 && $draftItemsCount === 0) {
            abort(403, 'WBS sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        $parentItems = $project->wbsItems()->orderBy('title')->get();

        return view('project-planning.wbs.create', compact('project', 'parentItems'));
    }

    /**
     * Store a newly created WbsItem.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat item WBS.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Item WBS hanya dapat dibuat jika status proyek adalah Planning.');
        }

        if (!$project->scope || $project->scope->status !== 'finalized') {
            abort(403, 'Item WBS hanya dapat dibuat jika Project Scope proyek ini sudah finalized.');
        }

        // Check WBS finalization status
        $totalItems = $project->wbsItems()->count();
        $draftItemsCount = $project->wbsItems()->where('status', 'draft')->count();
        if ($totalItems > 0 && $draftItemsCount === 0) {
            abort(403, 'WBS sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deliverable' => 'nullable|string',
            'priority' => 'required|string|in:low,medium,high',
            'estimated_duration_days' => 'nullable|integer|min:1',
            'parent_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project) {
                    if ($value) {
                        $parent = WbsItem::find($value);
                        if (!$parent || $parent->project_id !== $project->id) {
                            $fail('Parent item harus berasal dari proyek yang sama.');
                        }
                    }
                }
            ],
        ], [
            'title.required' => 'Judul WBS wajib diisi.',
            'description.required' => 'Deskripsi WBS wajib diisi.',
            'priority.required' => 'Prioritas wajib dipilih.',
            'priority.in' => 'Prioritas yang dipilih tidak valid.',
            'estimated_duration_days.min' => 'Estimasi durasi minimal adalah 1 hari.',
            'estimated_duration_days.integer' => 'Estimasi durasi harus berupa angka.',
        ]);

        $orderNumber = $project->wbsItems()->where('parent_id', $request->parent_id)->count() + 1;

        $item = new WbsItem();
        $item->project_id = $project->id;
        $item->project_scope_id = $project->scope->id;
        $item->parent_id = $request->parent_id;
        $item->title = $request->title;
        $item->description = $request->description;
        $item->deliverable = $request->deliverable;
        $item->priority = $request->priority;
        $item->estimated_duration_days = $request->estimated_duration_days;
        $item->status = 'draft';
        $item->order_number = $orderNumber;
        $item->created_by = Auth::id();
        $item->updated_by = Auth::id();
        $item->save();

        return redirect()->route('projects.wbs.show', $project->id)
            ->with('success', 'Item WBS berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the WbsItem.
     */
    public function edit(Project $project, WbsItem $wbsItem)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat mengubah item WBS.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'WBS hanya dapat diubah jika status proyek adalah Planning.');
        }

        if ($wbsItem->project_id !== $project->id) {
            abort(404, 'Item WBS tidak ditemukan pada proyek ini.');
        }

        // Check if WBS is finalized
        $totalItems = $project->wbsItems()->count();
        $draftItemsCount = $project->wbsItems()->where('status', 'draft')->count();
        if ($totalItems > 0 && $draftItemsCount === 0) {
            abort(403, 'WBS sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        $parentItems = $project->wbsItems()->where('id', '!=', $wbsItem->id)->orderBy('title')->get();

        return view('project-planning.wbs.edit', compact('project', 'wbsItem', 'parentItems'));
    }

    /**
     * Update the WbsItem.
     */
    public function update(Request $request, Project $project, WbsItem $wbsItem)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat mengubah item WBS.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'WBS hanya dapat diubah jika status proyek adalah Planning.');
        }

        if ($wbsItem->project_id !== $project->id) {
            abort(404, 'Item WBS tidak ditemukan pada proyek ini.');
        }

        // Check if WBS is finalized
        $totalItems = $project->wbsItems()->count();
        $draftItemsCount = $project->wbsItems()->where('status', 'draft')->count();
        if ($totalItems > 0 && $draftItemsCount === 0) {
            abort(403, 'WBS sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'deliverable' => 'nullable|string',
            'priority' => 'required|string|in:low,medium,high',
            'estimated_duration_days' => 'nullable|integer|min:1',
            'parent_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project, $wbsItem) {
                    if ($value) {
                        if ($value == $wbsItem->id) {
                            $fail('Item WBS tidak boleh menjadi parent dari dirinya sendiri.');
                            return;
                        }
                        $parent = WbsItem::find($value);
                        if (!$parent || $parent->project_id !== $project->id) {
                            $fail('Parent item harus berasal dari proyek yang sama.');
                        }
                    }
                }
            ],
        ], [
            'title.required' => 'Judul WBS wajib diisi.',
            'description.required' => 'Deskripsi WBS wajib diisi.',
            'priority.required' => 'Prioritas wajib dipilih.',
            'priority.in' => 'Prioritas yang dipilih tidak valid.',
            'estimated_duration_days.min' => 'Estimasi durasi minimal adalah 1 hari.',
            'estimated_duration_days.integer' => 'Estimasi durasi harus berupa angka.',
        ]);

        $wbsItem->parent_id = $request->parent_id;
        $wbsItem->title = $request->title;
        $wbsItem->description = $request->description;
        $wbsItem->deliverable = $request->deliverable;
        $wbsItem->priority = $request->priority;
        $wbsItem->estimated_duration_days = $request->estimated_duration_days;
        $wbsItem->updated_by = Auth::id();
        $wbsItem->save();

        return redirect()->route('projects.wbs.show', $project->id)
            ->with('success', 'Item WBS berhasil diperbarui.');
    }

    /**
     * Delete the WbsItem (only allowed if still draft).
     */
    public function destroy(Project $project, WbsItem $wbsItem)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat menghapus item WBS.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'WBS hanya dapat diubah jika status proyek adalah Planning.');
        }

        if ($wbsItem->project_id !== $project->id) {
            abort(404, 'Item WBS tidak ditemukan pada proyek ini.');
        }

        // Delete only allowed for draft items
        if ($wbsItem->status !== 'draft') {
            abort(403, 'Anda hanya dapat menghapus item WBS yang masih berstatus draf.');
        }

        $wbsItem->delete();

        return redirect()->route('projects.wbs.show', $project->id)
            ->with('success', 'Item WBS berhasil dihapus.');
    }

    /**
     * Finalize the project's WBS.
     */
    public function finalize(Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memfinalisasi WBS.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'WBS hanya dapat diakses jika status proyek adalah Planning.');
        }

        // PMO tidak boleh finalize WBS jika belum ada WBS item sama sekali
        if ($project->wbsItems()->count() === 0) {
            return redirect()->route('projects.wbs.show', $project->id)
                ->with('error', 'WBS tidak dapat difinalisasi karena belum ada item WBS sama sekali.');
        }

        // Update all items to finalized status
        $project->wbsItems()->update([
            'status' => 'finalized',
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('projects.wbs.show', $project->id)
            ->with('success', 'WBS berhasil difinalisasi.');
    }
}
