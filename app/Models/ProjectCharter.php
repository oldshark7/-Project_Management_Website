<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectCharter extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_purpose',
        'business_case',
        'project_objectives',
        'scope_summary',
        'success_criteria',
        'assumptions',
        'constraints',
        'stakeholder_summary',
        'milestone_summary',
        'budget_summary',
        'status',
        'feedback_notes',
        'ai_suggestions',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the project that this charter belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the user who created this charter.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this charter.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
