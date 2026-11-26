<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancialGoal extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'category',
        'target_amount',
        'current_amount',
        'start_date',
        'target_date',
        'color',
        'icon',
        'priority',
        'status',
        'notes',
    ];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'start_date' => 'date',
        'target_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function savingsRecords(): HasMany
    {
        return $this->hasMany(SavingsRecord::class);
    }
}
