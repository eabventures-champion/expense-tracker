<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Budget extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'period_type',
        'year',
        'month',
        'week_number',
        'start_date',
        'end_date',
        'total_limit',
        'alert_threshold_percent',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'total_limit' => 'decimal:2',
        'alert_threshold_percent' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function categoryAllocations(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }
}
