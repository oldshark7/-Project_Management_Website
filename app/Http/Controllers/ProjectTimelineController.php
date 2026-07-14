<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\TimelineItem;
use App\Models\WbsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectTimelineController extends Controller
{
    /**
     * Check if the authenticated user has access to Timeline.
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
     * Display a listing of projects in planning status and their Timeline status.
     */
    public function index()
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        if ($role === 'project manager') {
            // PM only sees their own planning projects
            $projects = Project::where('owner_id', $userId)
                ->where('status', 'planning')
                ->with(['scope', 'wbsItems', 'timelineItems'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Manager and PMO see all planning projects
            $projects = Project::where('status', 'planning')
                ->with(['scope', 'wbsItems', 'timelineItems'])
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('project-planning.timeline.index', compact('projects'));
    }

    /**
     * Display the specified project's Timeline and Gantt Chart.
     */
    public function show(Project $project)
    {
        $role = $this->checkBaseAccess();
        $userId = Auth::id();

        // Validate project status
        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diakses jika status proyek adalah Planning.');
        }

        // Validate scope
        if (!$project->scope || $project->scope->status !== 'finalized') {
            abort(403, 'Timeline hanya dapat diakses jika Project Scope proyek ini sudah finalized.');
        }

        // Validate WBS is finalized
        $totalWbs = $project->wbsItems()->count();
        $draftWbs = $project->wbsItems()->where('status', 'draft')->count();
        $isWbsFinalized = ($totalWbs > 0 && $draftWbs === 0);
        if (!$isWbsFinalized) {
            abort(403, 'Timeline hanya dapat diakses jika WBS proyek ini sudah finalized.');
        }

        // Authorization checks
        if ($role === 'project manager') {
            if ($project->owner_id !== $userId) {
                abort(403, 'Anda hanya dapat melihat Timeline untuk proyek Anda sendiri.');
            }
        }

        // Get hierarchical WBS items with timelineItem relation loaded
        $wbsItems = $project->wbsItems()
            ->whereNull('parent_id')
            ->orderBy('order_number')
            ->orderBy('created_at')
            ->with(['children', 'timelineItem'])
            ->get();

        $timelineItems = $project->timelineItems()->with('wbsItem')->get();

        // Calculate timeline boundaries for the Gantt Chart
        $minDate = null;
        $maxDate = null;
        $timelineItemsWithDates = $project->timelineItems()->whereNotNull('start_date')->get();
        if ($timelineItemsWithDates->isNotEmpty()) {
            $minDate = Carbon::parse($timelineItemsWithDates->min('start_date'));
            $maxDate = Carbon::parse($timelineItemsWithDates->max('end_date'));
        }

        $wbsItemsCount = $project->wbsItems()->count();
        $timelineItemsCount = $project->timelineItems()->count();
        $isTimelineFinalized = ($timelineItemsCount > 0 && $project->timelineItems()->where('status', 'draft')->count() === 0);

        return view('project-planning.timeline.show', compact(
            'project', 
            'wbsItems', 
            'timelineItems', 
            'minDate', 
            'maxDate', 
            'isTimelineFinalized', 
            'wbsItemsCount', 
            'timelineItemsCount'
        ));
    }

    /**
     * Show the form for creating a new timeline item.
     */
    public function create(Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat item timeline.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diatur jika status proyek adalah Planning.');
        }

        // Check WBS status
        $totalWbs = $project->wbsItems()->count();
        $draftWbs = $project->wbsItems()->where('status', 'draft')->count();
        $isWbsFinalized = ($totalWbs > 0 && $draftWbs === 0);
        if (!$isWbsFinalized) {
            abort(403, 'Timeline hanya dapat dibuat jika WBS sudah finalized.');
        }

        // Check if Timeline is finalized
        $timelineItemsCount = $project->timelineItems()->count();
        $draftTimelineCount = $project->timelineItems()->where('status', 'draft')->count();
        if ($timelineItemsCount > 0 && $draftTimelineCount === 0) {
            abort(403, 'Timeline sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        // Get WBS items of this project that do not have a timeline item yet
        $wbsItems = $project->wbsItems()->whereDoesntHave('timelineItem')->orderBy('title')->get();

        // Get dependency targets (WBS items that already have a timeline item)
        // Exclude parent/summary tasks (WBS items with children)
        $dependencyItems = $project->wbsItems()
            ->whereHas('timelineItem')
            ->whereDoesntHave('children')
            ->orderBy('title')
            ->get();

        $invalidPredecessorsMap = $this->getInvalidPredecessorsMap($project);

        return view('project-planning.timeline.create', compact('project', 'wbsItems', 'dependencyItems', 'invalidPredecessorsMap'));
    }

    /**
     * Store a newly created timeline item.
     */
    public function store(Request $request, Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat membuat item timeline.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diatur jika status proyek adalah Planning.');
        }

        // Check WBS status
        $totalWbs = $project->wbsItems()->count();
        $draftWbs = $project->wbsItems()->where('status', 'draft')->count();
        $isWbsFinalized = ($totalWbs > 0 && $draftWbs === 0);
        if (!$isWbsFinalized) {
            abort(403, 'Timeline hanya dapat dibuat jika WBS sudah finalized.');
        }

        // Check if Timeline is finalized
        $timelineItemsCount = $project->timelineItems()->count();
        $draftTimelineCount = $project->timelineItems()->where('status', 'draft')->count();
        if ($timelineItemsCount > 0 && $draftTimelineCount === 0) {
            abort(403, 'Timeline sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'wbs_item_id' => [
                'required',
                'exists:wbs_items,id',
                'unique:timeline_items,wbs_item_id',
                function ($attribute, $value, $fail) use ($project) {
                    $wbs = WbsItem::find($value);
                    if (!$wbs || $wbs->project_id !== $project->id) {
                        $fail('Item WBS harus berasal dari proyek yang sama.');
                    }
                }
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_milestone' => 'required|boolean',
            'milestone_name' => 'required_if:is_milestone,true|nullable|string|max:255',
            'notes' => 'nullable|string',
            'dependency_wbs_item_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project, $request) {
                    if ($value) {
                        $wbsId = $request->wbs_item_id;
                        if ($value == $wbsId) {
                            $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                            return;
                        }
                        $dep = WbsItem::find($value);
                        if (!$dep || $dep->project_id !== $project->id) {
                            $fail('Dependency WBS harus berasal dari proyek yang sama.');
                            return;
                        }
                        // Check if predecessor has children (is parent summary task)
                        if ($dep->children()->exists()) {
                            $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                            return;
                        }
                        // Check if predecessor is a descendant of the selected WBS item
                        $targetWbs = WbsItem::find($wbsId);
                        if ($targetWbs) {
                            $descendantIds = $this->getDescendantIds($targetWbs);
                            if (in_array($value, $descendantIds)) {
                                $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                                return;
                            }
                        }
                        // Check for circular dependency
                        if ($this->causesCircularDependency($wbsId, $value)) {
                            $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                            return;
                        }
                    }
                },
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->start_date) {
                        $depTimeline = TimelineItem::where('wbs_item_id', $value)->first();
                        if ($depTimeline) {
                            $depEndDate = Carbon::parse($depTimeline->end_date);
                            $startDate = Carbon::parse($request->start_date);
                            if ($startDate->lt($depEndDate)) {
                                $fail('Tanggal mulai tugas tidak boleh mendahului tanggal selesai tugas prasyarat (dependency): ' . $depEndDate->format('d-m-Y'));
                            }
                        }
                    }
                }
            ],
        ], [
            'wbs_item_id.required' => 'WBS Item wajib dipilih.',
            'wbs_item_id.unique' => 'WBS Item ini sudah memiliki timeline item.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'milestone_name.required_if' => 'Nama milestone wajib diisi jika tipe item adalah milestone.',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $duration = $start->diffInDays($end) + 1; // inclusive duration

        $item = new TimelineItem();
        $item->project_id = $project->id;
        $item->wbs_item_id = $request->wbs_item_id;
        $item->start_date = $request->start_date;
        $item->end_date = $request->end_date;
        $item->duration_days = $duration;
        $item->dependency_wbs_item_id = $request->dependency_wbs_item_id;
        $item->is_milestone = $request->is_milestone;
        $item->milestone_name = $request->milestone_name;
        $item->notes = $request->notes;
        $item->status = 'draft';
        $item->created_by = Auth::id();
        $item->updated_by = Auth::id();
        $item->save();

        return redirect()->route('projects.timeline.show', $project->id)
            ->with('success', 'Item timeline berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the timeline item.
     */
    public function edit(Project $project, TimelineItem $timelineItem)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat mengubah item timeline.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diubah jika status proyek adalah Planning.');
        }

        if ($timelineItem->project_id !== $project->id) {
            abort(404, 'Item timeline tidak ditemukan pada proyek ini.');
        }

        // Check if Timeline is finalized
        $timelineItemsCount = $project->timelineItems()->count();
        $draftTimelineCount = $project->timelineItems()->where('status', 'draft')->count();
        if ($timelineItemsCount > 0 && $draftTimelineCount === 0) {
            abort(403, 'Timeline sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        // WBS items: select unmapped ones + the current one
        $wbsItems = $project->wbsItems()
            ->where(function($query) use ($timelineItem) {
                $query->whereDoesntHave('timelineItem')
                      ->orWhere('id', $timelineItem->wbs_item_id);
            })
            ->orderBy('title')
            ->get();

        // Dependency: WBS items that have timeline items, excluding the current WBS item
        // Exclude parent/summary tasks (WBS items with children)
        $dependencyItems = $project->wbsItems()
            ->whereHas('timelineItem')
            ->where('id', '!=', $timelineItem->wbs_item_id)
            ->whereDoesntHave('children')
            ->orderBy('title')
            ->get();

        $invalidPredecessorsMap = $this->getInvalidPredecessorsMap($project);

        return view('project-planning.timeline.edit', compact('project', 'timelineItem', 'wbsItems', 'dependencyItems', 'invalidPredecessorsMap'));
    }

    /**
     * Update the timeline item.
     */
    public function update(Request $request, Project $project, TimelineItem $timelineItem)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat mengubah item timeline.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diubah jika status proyek adalah Planning.');
        }

        if ($timelineItem->project_id !== $project->id) {
            abort(404, 'Item timeline tidak ditemukan pada proyek ini.');
        }

        // Check if Timeline is finalized
        $timelineItemsCount = $project->timelineItems()->count();
        $draftTimelineCount = $project->timelineItems()->where('status', 'draft')->count();
        if ($timelineItemsCount > 0 && $draftTimelineCount === 0) {
            abort(403, 'Timeline sudah difinalisasi dan tidak dapat diubah lagi.');
        }

        $request->validate([
            'wbs_item_id' => [
                'required',
                'exists:wbs_items,id',
                'unique:timeline_items,wbs_item_id,' . $timelineItem->id,
                function ($attribute, $value, $fail) use ($project) {
                    $wbs = WbsItem::find($value);
                    if (!$wbs || $wbs->project_id !== $project->id) {
                        $fail('Item WBS harus berasal dari proyek yang sama.');
                    }
                }
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'is_milestone' => 'required|boolean',
            'milestone_name' => 'required_if:is_milestone,true|nullable|string|max:255',
            'notes' => 'nullable|string',
            'dependency_wbs_item_id' => [
                'nullable',
                'exists:wbs_items,id',
                function ($attribute, $value, $fail) use ($project, $request) {
                    if ($value) {
                        $wbsId = $request->wbs_item_id;
                        if ($value == $wbsId) {
                            $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                            return;
                        }
                        $dep = WbsItem::find($value);
                        if (!$dep || $dep->project_id !== $project->id) {
                            $fail('Dependency WBS harus berasal dari proyek yang sama.');
                            return;
                        }
                        // Check if predecessor has children (is parent summary task)
                        if ($dep->children()->exists()) {
                            $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                            return;
                        }
                        // Check if predecessor is a descendant of the selected WBS item
                        $targetWbs = WbsItem::find($wbsId);
                        if ($targetWbs) {
                            $descendantIds = $this->getDescendantIds($targetWbs);
                            if (in_array($value, $descendantIds)) {
                                $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                                return;
                            }
                        }
                        // Check for circular dependency
                        if ($this->causesCircularDependency($wbsId, $value)) {
                            $fail('Predecessor tidak valid karena task tidak boleh memilih dirinya sendiri, parent summary yang tidak sesuai, atau menyebabkan dependency berulang.');
                            return;
                        }
                    }
                },
                function ($attribute, $value, $fail) use ($request) {
                    if ($value && $request->start_date) {
                        $depTimeline = TimelineItem::where('wbs_item_id', $value)->first();
                        if ($depTimeline) {
                            $depEndDate = Carbon::parse($depTimeline->end_date);
                            $startDate = Carbon::parse($request->start_date);
                            if ($startDate->lt($depEndDate)) {
                                $fail('Tanggal mulai tugas tidak boleh mendahului tanggal selesai tugas prasyarat (dependency): ' . $depEndDate->format('d-m-Y'));
                            }
                        }
                    }
                }
            ],
        ], [
            'wbs_item_id.required' => 'WBS Item wajib dipilih.',
            'wbs_item_id.unique' => 'WBS Item ini sudah memiliki timeline item.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'milestone_name.required_if' => 'Nama milestone wajib diisi jika tipe item adalah milestone.',
        ]);

        $start = Carbon::parse($request->start_date);
        $end = Carbon::parse($request->end_date);
        $duration = $start->diffInDays($end) + 1; // inclusive duration

        $timelineItem->wbs_item_id = $request->wbs_item_id;
        $timelineItem->start_date = $request->start_date;
        $timelineItem->end_date = $request->end_date;
        $timelineItem->duration_days = $duration;
        $timelineItem->dependency_wbs_item_id = $request->dependency_wbs_item_id;
        $timelineItem->is_milestone = $request->is_milestone;
        $timelineItem->milestone_name = $request->milestone_name;
        $timelineItem->notes = $request->notes;
        $timelineItem->updated_by = Auth::id();
        $timelineItem->save();

        return redirect()->route('projects.timeline.show', $project->id)
            ->with('success', 'Item timeline berhasil diperbarui.');
    }

    /**
     * Delete the timeline item (only draft status allowed).
     */
    public function destroy(Project $project, TimelineItem $timelineItem)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat menghapus item timeline.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diubah jika status proyek adalah Planning.');
        }

        if ($timelineItem->project_id !== $project->id) {
            abort(404, 'Item timeline tidak ditemukan pada proyek ini.');
        }

        // Delete only allowed for draft items
        if ($timelineItem->status !== 'draft') {
            abort(403, 'Anda hanya dapat menghapus item timeline yang masih berstatus draf.');
        }

        $timelineItem->delete();

        return redirect()->route('projects.timeline.show', $project->id)
            ->with('success', 'Item timeline berhasil dihapus.');
    }

    /**
     * Finalize the timeline.
     */
    public function finalize(Project $project)
    {
        $role = $this->checkBaseAccess();

        if ($role !== 'project management officer' && $role !== 'pmo') {
            abort(403, 'Hanya PMO yang dapat memfinalisasi timeline.');
        }

        if ($project->status !== 'planning') {
            abort(403, 'Timeline hanya dapat diakses jika status proyek adalah Planning.');
        }

        // PMO tidak boleh finalize timeline jika belum ada timeline item
        if ($project->timelineItems()->count() === 0) {
            return redirect()->route('projects.timeline.show', $project->id)
                ->with('error', 'Timeline tidak dapat difinalisasi karena belum ada timeline item sama sekali.');
        }

        // PMO tidak boleh finalize timeline jika masih ada WBS item yang belum memiliki timeline item
        $wbsCount = $project->wbsItems()->count();
        $timelineCount = $project->timelineItems()->count();
        if ($wbsCount !== $timelineCount) {
            return redirect()->route('projects.timeline.show', $project->id)
                ->with('error', 'Timeline tidak dapat difinalisasi karena masih ada item WBS yang belum memiliki jadwal timeline.');
        }

        // Update status of all timeline items to finalized
        $project->timelineItems()->update([
            'status' => 'finalized',
            'updated_by' => Auth::id()
        ]);

        return redirect()->route('projects.timeline.show', $project->id)
            ->with('success', 'Timeline berhasil difinalisasi.');
    }

    /**
     * Check recursively if setting dependency causes circular dependency.
     */
    private function causesCircularDependency(int $wbsItemId, int $dependencyWbsItemId): bool
    {
        if ($wbsItemId === $dependencyWbsItemId) {
            return true;
        }

        $visited = [];
        $currentId = $dependencyWbsItemId;

        while ($currentId) {
            if ($currentId === $wbsItemId) {
                return true;
            }

            if (in_array($currentId, $visited, true)) {
                break;
            }
            $visited[] = $currentId;

            $timelineItem = TimelineItem::where('wbs_item_id', $currentId)->first();
            $currentId = $timelineItem ? $timelineItem->dependency_wbs_item_id : null;
        }

        return false;
    }

    /**
     * Get all descendant IDs of a given WBS item recursively.
     */
    private function getDescendantIds($wbsItem): array
    {
        if (!$wbsItem) {
            return [];
        }

        $ids = [];
        foreach ($wbsItem->children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getDescendantIds($child));
        }

        return $ids;
    }

    /**
     * Build map of invalid predecessor IDs for each WBS item in the project.
     */
    private function getInvalidPredecessorsMap(Project $project): array
    {
        $wbsItems = $project->wbsItems;
        $map = [];

        foreach ($wbsItems as $wbs) {
            $invalidIds = [];

            // 1. Self is invalid
            $invalidIds[] = $wbs->id;

            // 2. Descendants are invalid
            $descendants = $this->getDescendantIds($wbs);
            $invalidIds = array_merge($invalidIds, $descendants);

            // 3. Circular dependency check
            foreach ($wbsItems as $other) {
                if ($other->id !== $wbs->id) {
                    if ($this->causesCircularDependency($wbs->id, $other->id)) {
                        $invalidIds[] = $other->id;
                    }
                }
            }

            // 4. Any WBS item with children (parent summary task) is also invalid as predecessor
            foreach ($wbsItems as $other) {
                if ($other->children()->exists()) {
                    $invalidIds[] = $other->id;
                }
            }

            $map[$wbs->id] = array_values(array_unique($invalidIds));
        }

        return $map;
    }
}
