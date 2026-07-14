<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'background',
        'objectives',
        'initial_needs',
        'project_overview',
        'scope_overview',
        'estimated_budget',
        'status',
        'feedback_notes',
        'ai_suggestions',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the project that this proposal belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the user who created this proposal.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this proposal.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
