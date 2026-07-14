<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RiskManagementPlan extends Model
{
    use HasFactory;

    protected $table = 'risk_management_plans';

    protected $fillable = [
        'project_id',
        'status',
        'ai_suggestions',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the project that this plan belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the items under this plan.
     */
    public function riskItems(): HasMany
    {
        return $this->hasMany(RiskItem::class, 'risk_management_plan_id');
    }

    /**
     * Get the user who created this plan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this plan.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
