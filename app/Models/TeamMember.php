<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class TeamMember extends Model
{
    use HasFactory;

    protected $table = 'team_members';

    protected $fillable = [
        'name',
        'role_name',
        'skills',
        'default_capacity_percentage',
        'notes',
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'default_capacity_percentage' => 'integer',
        'is_active' => 'boolean',
        'user_id' => 'integer',
    ];

    /**
     * Get the user account for this team member.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the human resource assignments/items for this team member.
     */
    public function humanResourceItems(): HasMany
    {
        return $this->hasMany(HumanResourceItem::class, 'team_member_id');
    }

    /**
     * Accessor for remaining capacity percentage.
     */
    public function getRemainingCapacityPercentageAttribute(): int
    {
        return (int) ($this->default_capacity_percentage - $this->current_workload_percentage);
    }

    /**
     * Accessor for workload status.
     */
    public function getWorkloadStatusAttribute(): string
    {
        $workload = $this->current_workload_percentage;

        if ($workload <= 50) {
            return 'Available';
        } elseif ($workload <= 80) {
            return 'Partially Allocated';
        } elseif ($workload <= 99) {
            return 'Nearly Full';
        } else {
            return 'Full';
        }
    }

    public function getCurrentWorkloadPercentageAttribute(): int
    {
        if (!$this->user_id) {return 0;}

        return (int) DB::table('task_user')
            ->where('user_id', $this->user_id)
            ->sum('workload_percentage');
    }
}
