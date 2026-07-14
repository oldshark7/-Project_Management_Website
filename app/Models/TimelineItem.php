<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimelineItem extends Model
{
    use HasFactory;

    protected $table = 'timeline_items';

    protected $fillable = [
        'project_id',
        'wbs_item_id',
        'start_date',
        'end_date',
        'duration_days',
        'dependency_wbs_item_id',
        'is_milestone',
        'milestone_name',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_milestone' => 'boolean',
    ];

    /**
     * Get the project that this timeline item belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the WBS item associated with this timeline item.
     */
    public function wbsItem(): BelongsTo
    {
        return $this->belongsTo(WbsItem::class, 'wbs_item_id');
    }

    /**
     * Get the dependency WBS item.
     */
    public function dependencyWbsItem(): BelongsTo
    {
        return $this->belongsTo(WbsItem::class, 'dependency_wbs_item_id');
    }

    /**
     * Get the user who created this timeline item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this timeline item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
