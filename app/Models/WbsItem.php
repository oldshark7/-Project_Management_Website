<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WbsItem extends Model
{
    use HasFactory;

    protected $table = 'wbs_items';

    protected $casts = ['task_status_finished_at' => 'datetime',];

    protected $fillable = [
        'project_id',
        'project_scope_id',
        'parent_id',
        'title',
        'description',
        'deliverable',
        'priority',
        'estimated_duration_days',
        'status',
        'kanban_status',
        'order_number',
        'created_by',
        'updated_by',
    ];

    public function statusUpdater()
    {
        return $this->belongsTo(User::class, 'status_updated_by');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withPivot(['project_id', 'role', 'workload_percentage',])->withTimestamps();
    }

    public function scopeFinished($query)
    {
        return $query->whereNotNull('task_status_finished_at');
    }

    public function scopeUnfinished($query)
    {
        return $query->whereNull('task_status_finished_at');
    }

    /**
     * Get the project that this WBS item belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the project scope that this WBS item belongs to.
     */
    public function scope(): BelongsTo
    {
        return $this->belongsTo(ProjectScope::class, 'project_scope_id');
    }

    /**
     * Get the parent WBS item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(WbsItem::class, 'parent_id');
    }

    /**
     * Get the child WBS items.
     */
    public function children(): HasMany
    {
        return $this->hasMany(WbsItem::class, 'parent_id')->orderBy('order_number')->orderBy('created_at');
    }

    /**
     * Get the user who created this WBS item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this WBS item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the timeline item associated with this WBS item.
     */
    public function timelineItem(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TimelineItem::class, 'wbs_item_id');
    }

    /**
     * Get the risk items associated with this WBS task.
     */
    public function riskItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RiskItem::class, 'related_wbs_item_id');
    }

    public function isFullyAssigned()
    {
        if ($this->children->isNotEmpty()) {
            return $this->children->every(
                fn($child) => $child->isFullyAssigned()
            );
        }

        // leaf node wajib punya user
        return $this->users->isNotEmpty();
    }
}
