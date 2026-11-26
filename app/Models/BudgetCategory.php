<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetCategory extends Model
{
    protected $fillable = [
        'budget_id',
        'expense_category_id',
        'allocated_amount',
        'alert_threshold_percent',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'alert_threshold_percent' => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(Budget::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }
}
