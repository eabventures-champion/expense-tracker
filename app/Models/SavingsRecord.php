<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavingsRecord extends Model
{
    protected $fillable = [
        'user_id',
        'financial_goal_id',
        'payment_method_id',
        'title',
        'action_type',
        'funding_source',
        'amount',
        'record_date',
        'vault_or_account',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'record_date' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function financialGoal(): BelongsTo
    {
        return $this->belongsTo(FinancialGoal::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }
}
