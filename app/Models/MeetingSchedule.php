<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MeetingSchedule extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'meeting_type',
        'meeting_date',
        'start_time',
        'end_time',
        'location',
        'meeting_link',
        'reminder_before',
        'description',
        'status',
        'created_by',
    ];

    protected $casts = [
        'meeting_date' => 'date',
    ];

    /**
     * Project yang memiliki meeting ini.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * User yang membuat meeting.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}