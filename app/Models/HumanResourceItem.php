<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HumanResourceItem extends Model
{
    use HasFactory;

    protected $table = 'human_resource_items';

    protected $fillable = [
        'human_resource_plan_id',
        'wbs_item_id',
        'team_member_id',
        'role_name',
        'required_skill',
        'job_description',
        'person_in_charge',
        'workload_percentage',
        'estimated_work_days',
        'quantity',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'workload_percentage' => 'integer',
        'estimated_work_days' => 'integer',
        'quantity' => 'integer',
        'team_member_id' => 'integer',
    ];

    /**
     * Get the team member associated with this resource item.
     */
    public function teamMember(): BelongsTo
    {
        return $this->belongsTo(TeamMember::class, 'team_member_id');
    }

    public function getSkillsAttribute()
    {
        return array_filter(array_map('trim', explode(',', $this->required_skill ?? '')));
    }

    /**
     * Get the plan that this item belongs to.
     */
    public function humanResourcePlan(): BelongsTo
    {
        return $this->belongsTo(HumanResourcePlan::class, 'human_resource_plan_id');
    }

    /**
     * Get the WBS item associated with this resource item.
     */
    public function wbsItem(): BelongsTo
    {
        return $this->belongsTo(WbsItem::class, 'wbs_item_id');
    }

    /**
     * Get the user who created this item.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this item.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get required skills as array.
     */
    public function getSkillsArrayAttribute(): array
    {
        return array_map('trim', explode(',', $this->required_skill));
    }

    /**
     * Get workload UI metadata (color, label, etc).
     */
    public function getWorkloadMetaAttribute(): array
    {
        $loadPercent = $this->workload_percentage ?? 0;

        if ($loadPercent > 85) {
            return [
                'barColor' => 'bg-rose-500',
                'label' => 'OVERLOAD',
                'labelClass' => 'text-rose-500',
            ];
        } elseif ($loadPercent >= 60) {
            return [
                'barColor' => 'bg-slate-700',
                'label' => 'OPTIMAL',
                'labelClass' => 'text-slate-700',
            ];
        }

        return [
            'barColor' => 'bg-slate-400',
            'label' => 'UNDERLOAD',
            'labelClass' => 'text-slate-500',
        ];
    }
}
