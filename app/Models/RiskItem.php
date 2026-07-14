<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskItem extends Model
{
    use HasFactory;

    protected $table = 'risk_items';

    protected $fillable = [
        'risk_management_plan_id',
        'risk_title',
        'risk_description',
        'risk_cause',
        'impact',
        'probability',
        'severity',
        'mitigation_plan',
        'contingency_plan',
        'risk_owner',
        'related_wbs_item_id',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the plan that this item belongs to.
     */
    public function riskPlan(): BelongsTo
    {
        return $this->belongsTo(RiskManagementPlan::class, 'risk_management_plan_id');
    }

    /**
     * Get the WbsItem associated with this risk.
     */
    public function wbsItem(): BelongsTo
    {
        return $this->belongsTo(WbsItem::class, 'related_wbs_item_id');
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
