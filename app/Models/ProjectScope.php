<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectScope extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'objective',
        'scope_description',
        'in_scope',
        'out_of_scope',
        'main_requirements',
        'deliverables',
        'acceptance_criteria',
        'assumptions',
        'constraints',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the project that this scope belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    /**
     * Get the user who created this scope.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this scope.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the WBS items associated with the project scope.
     */
    public function wbsItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(WbsItem::class, 'project_scope_id');
    }
}
