<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:180'],
            'type' => ['required', 'in:expense,income'],
            'income_source' => ['nullable', 'string', 'max:60'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999.99'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_subcategory_id' => ['nullable', 'exists:expense_subcategories,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'transaction_date' => ['required', 'date'],
            'transaction_time' => ['nullable'],
            'merchant_or_source' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_recurring' => ['nullable', 'boolean'],
            'recurring_frequency' => ['nullable', 'in:daily,weekly,monthly,quarterly,yearly'],
            'receipt' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }
}
