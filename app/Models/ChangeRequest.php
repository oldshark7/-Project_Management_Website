<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChangeRequest extends Model
{
    protected $fillable = [
        'project_id',
        'wbs_item_id',
        'field_changed',
        'old_value',
        'new_value',
        'requested_deadline',
        'reason',
        'status',
        'requested_by',
    ];

    public function wbsItem()
    {
        return $this->belongsTo(WbsItem::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
