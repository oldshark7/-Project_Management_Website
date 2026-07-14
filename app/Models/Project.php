<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'owner_id',
        'manager_id',
        'status',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function projectManager()
    {
        return $this->belongsTo(User::class, 'project_manager_id');
    }

    /**
     * Get the user who owns/created the project.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get the manager assigned to the project.
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * Get the proposal associated with the project.
     */
    public function proposal(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectProposal::class, 'project_id');
    }

    /**
     * Get the charter associated with the project.
     */
    public function charter(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectCharter::class, 'project_id');
    }

    /**
     * Get the scope associated with the project.
     */
    public function scope(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ProjectScope::class, 'project_id');
    }

    /**
     * Get the WBS items associated with the project.
     */
    public function wbsItems(): HasMany
    {
        return $this->hasMany(WbsItem::class, 'project_id');
    }

    /**
     * Get the timeline items associated with the project.
     */
    public function timelineItems(): HasMany
    {
        return $this->hasMany(TimelineItem::class, 'project_id');
    }

    /**
     * Get the budget plan associated with the project.
     */
    public function budgetPlan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(BudgetPlan::class, 'project_id');
    }

    /**
     * Get the human resource plan associated with the project.
     */
    public function humanResourcePlan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(HumanResourcePlan::class, 'project_id');
    }

    /**
     * Get the risk plan associated with the project.
     */
    public function riskPlan(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(RiskManagementPlan::class, 'project_id');
    }

    public function meetingSchedules(): HasMany
    {
        return $this->hasMany(MeetingSchedule::class);
    }
}
