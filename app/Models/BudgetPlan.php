<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BudgetPlan extends Model
{
    use HasFactory;

    protected $table = 'budget_plans';

    protected $fillable = [
        'project_id',
        'status',
        'total_budget',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'total_budget' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(BudgetItem::class);
    }

    public function getActualCostAttribute()
    {
        return $this->items->sum('actual_cost');
    }

    public function getUsageAttribute()
    {
        return $this->total_budget > 0
            ? ($this->actual_cost / $this->total_budget) * 100
            : 0;
    }

    /**
     * Get the project that this budget plan belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the items under this budget plan.
     */
    public function budgetItems(): HasMany
    {
        return $this->hasMany(BudgetItem::class, 'budget_plan_id');
    }

    /**
     * Get the user who created this budget plan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this budget plan.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
