<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\BudgetExpense;

class BudgetItem extends Model
{
    use HasFactory;

    protected $table = 'budget_items';

    protected $fillable = [
        'budget_plan_id',
        'category',
        'description',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_cost' => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];
    
    public function expenses()
    {
        return $this->hasMany(BudgetExpense::class);
    }

    public function getActualCostAttribute()
    {
        return $this->expenses()->sum('amount');
    }

    public function getVarianceAttribute()
    {
        return $this->total_cost - $this->actual_cost;
    }

    public function getRemainingBudgetAttribute()
    {
        return max(0, $this->variance);
    }

    /**
     * Get the budget plan this item belongs to.
     */
    public function budgetPlan(): BelongsTo
    {
        return $this->belongsTo(BudgetPlan::class, 'budget_plan_id');
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
}
