<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Issue extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'description',
        'status',
        'priority',
        'assignee_id',
        'reported_by',
        'due_date',
        'resolved_at',
    ];

    const STATUSES = [
        'open',
        'in_progress',
        'done',
        'closed'
    ];

    // relasi ke project
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    // relasi ke user (assignee)
    public function assignee()
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    // relasi ke user (reporter)
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }
}
