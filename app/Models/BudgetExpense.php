<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BudgetExpense extends Model
{
    protected $fillable = [
        'budget_item_id',
        'title',
        'description',
        'amount',
        'expense_date',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function budgetItem()
    {
        return $this->belongsTo(BudgetItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
