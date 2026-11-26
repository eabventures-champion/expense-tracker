<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTransactionRequest;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Receipt;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Services\UnifiedFinancialEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function __construct(
        private readonly UnifiedFinancialEngine $engine
    ) {}

    public function index(Request $request)
    {
        return $this->renderTransactionList($request, null, 'Unified Transactions Ledger');
    }

    public function expenses(Request $request)
    {
        return $this->renderTransactionList($request, 'expense', 'Expense Management & Receipts');
    }

    public function income(Request $request)
    {
        return $this->renderTransactionList($request, 'income', 'Income & Cash Inflow Records');
    }

    private function renderTransactionList(Request $request, ?string $forcedType, string $pageTitle)
    {
        $user = $this->currentUser();
        $type = $forcedType ?? $request->query('type');
        $categoryId = $request->query('category_id');
        $paymentMethodId = $request->query('payment_method_id');
        $search = trim((string) $request->query('q', ''));
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());

        $query = Transaction::with(['category', 'subcategory', 'paymentMethod', 'receipts'])
            ->where('user_id', $user->id);

        if ($type && in_array($type, ['expense', 'income'], true)) {
            $query->where('type', $type);
        }

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        if ($paymentMethodId) {
            $query->where('payment_method_id', $paymentMethodId);
        }

        if ($startDate && $endDate) {
            $query->whereBetween('transaction_date', [$startDate, $endDate]);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('merchant_or_source', 'like', "%{$search}%")
                    ->orWhere('notes', 'like', "%{$search}%")
                    ->orWhere('reference_code', 'like', "%{$search}%");
            });
        }

        $filteredExpenseTotal = (clone $query)->where('type', 'expense')->sum('amount');
        $filteredIncomeTotal = (clone $query)->where('type', 'income')->sum('amount');
        $filteredIncomeFundedSavings = $this->engine->sumIncomeFundedSavings($user->id, $startDate, $endDate);
        $filteredNet = UnifiedFinancialEngine::sub(
            UnifiedFinancialEngine::sub($filteredIncomeTotal, $filteredExpenseTotal),
            $filteredIncomeFundedSavings
        );

        $allMatchingTransactions = $query->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->get();

        // Compound / merge transactions BEFORE pagination by normalized Title + Type
        // (e.g., "Waakye", "From King David", "Assistance to someone"), while the table
        // groups all rows under their respective Category & Subcategory headers.
        $rawCompoundedGroups = $allMatchingTransactions->groupBy(function (Transaction $tx) {
            $normalizedTitle = preg_replace('/\s+/', ' ', strtolower(trim((string) $tx->title)));
            return $tx->type . '_title|' . ($tx->expense_category_id ?: 0) . '|' . $normalizedTitle;
        });

        // Group rows together by Category (ordered by Category total spend/inflow descending),
        // and arrange the rows beneath each Category by Latest Date descending (most recent first).
        $categoryTotals = $allMatchingTransactions->groupBy(function (Transaction $tx) {
            return $tx->type . '|' . ($tx->expense_category_id ?: 0);
        })->map(fn ($items) => (float) $items->sum('amount'));

        $allCompoundedGroups = $rawCompoundedGroups->sort(function ($groupA, $groupB) use ($categoryTotals) {
            /** @var Transaction $txA */
            $txA = $groupA->first();
            /** @var Transaction $txB */
            $txB = $groupB->first();

            $catKeyA = $txA->type . '|' . ($txA->expense_category_id ?: 0);
            $catKeyB = $txB->type . '|' . ($txB->expense_category_id ?: 0);

            if ($catKeyA !== $catKeyB) {
                $catTotalA = $categoryTotals[$catKeyA] ?? 0.0;
                $catTotalB = $categoryTotals[$catKeyB] ?? 0.0;
                if (abs($catTotalB - $catTotalA) > 0.005) {
                    return $catTotalB <=> $catTotalA;
                }
                return strcmp((string) ($txA->category?->name ?? 'ZZZ'), (string) ($txB->category?->name ?? 'ZZZ'));
            }

            $dateCompare = $txB->transaction_date <=> $txA->transaction_date;
            if ($dateCompare !== 0) {
                return $dateCompare;
            }

            $timeCompare = strcmp((string) ($txB->transaction_time ?? ''), (string) ($txA->transaction_time ?? ''));
            if ($timeCompare !== 0) {
                return $timeCompare;
            }

            return $txB->id <=> $txA->id;
        });

        $perPage = 20;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
        $currentPageGroups = $allCompoundedGroups->slice(($currentPage - 1) * $perPage, $perPage);

        $transactions = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageGroups,
            $allCompoundedGroups->count(),
            $perPage,
            $currentPage,
            [
                'path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(),
                'query' => $request->query(),
            ]
        );

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();

        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        // Income source breakdown when on income page
        $incomeBreakdown = [];
        if ($forcedType === 'income') {
            $incomeBreakdown = Transaction::where('user_id', $user->id)
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$startDate, $endDate])
                ->select('income_source', DB::raw('SUM(amount) as total_amount'), DB::raw('COUNT(*) as tx_count'))
                ->groupBy('income_source')
                ->orderByDesc('total_amount')
                ->get();
        }

        return view('transactions.index', compact(
            'user',
            'pageTitle',
            'forcedType',
            'type',
            'categoryId',
            'paymentMethodId',
            'search',
            'startDate',
            'endDate',
            'transactions',
            'filteredExpenseTotal',
            'filteredIncomeTotal',
            'filteredNet',
            'categories',
            'paymentMethods',
            'incomeBreakdown'
        ));
    }

    public function store(StoreTransactionRequest $request)
    {
        $user = $this->currentUser();
        $data = $request->validated();

        DB::transaction(function () use ($user, $data, $request) {
            $category = ExpenseCategory::find($data['expense_category_id']);
            $isRecurring = (bool) ($data['is_recurring'] ?? false);
            $frequency = $data['recurring_frequency'] ?? 'monthly';

            $recurringId = null;
            if ($isRecurring) {
                $txDate = Carbon::parse($data['transaction_date']);
                $nextDue = match ($frequency) {
                    'daily' => $txDate->copy()->addDay(),
                    'weekly' => $txDate->copy()->addWeek(),
                    'quarterly' => $txDate->copy()->addMonthsNoOverflow(3),
                    'yearly' => $txDate->copy()->addYear(),
                    default => $txDate->copy()->addMonthNoOverflow(),
                };

                $rec = RecurringTransaction::create([
                    'user_id' => $user->id,
                    'expense_category_id' => $data['expense_category_id'],
                    'expense_subcategory_id' => $data['expense_subcategory_id'] ?? null,
                    'payment_method_id' => $data['payment_method_id'] ?? null,
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'amount' => number_format((float) $data['amount'], 2, '.', ''),
                    'frequency' => $frequency,
                    'start_date' => $txDate->toDateString(),
                    'next_due_date' => $nextDue->toDateString(),
                    'last_processed_date' => $txDate->toDateString(),
                    'reminder_days_before' => 3,
                    'auto_record' => false,
                    'status' => 'active',
                    'notes' => $data['notes'] ?? null,
                ]);
                $recurringId = $rec->id;
            }

            $transaction = Transaction::create([
                'user_id' => $user->id,
                'expense_category_id' => $data['expense_category_id'],
                'expense_subcategory_id' => $data['expense_subcategory_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'recurring_transaction_id' => $recurringId,
                'title' => $data['title'],
                'type' => $data['type'],
                'income_source' => $data['type'] === 'income' ? ($data['income_source'] ?? $category?->name ?? 'Salary') : null,
                'amount' => number_format((float) $data['amount'], 2, '.', ''),
                'currency' => $user->currency,
                'transaction_date' => $data['transaction_date'],
                'transaction_time' => $data['transaction_time'] ?? now()->format('H:i:s'),
                'merchant_or_source' => $data['merchant_or_source'] ?? $category?->name,
                'reference_code' => 'CL-' . strtoupper(Str::random(7)),
                'is_recurring' => $isRecurring,
                'recurring_frequency' => $isRecurring ? $frequency : null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $storedPath = $file->store('receipts', 'public');
                Receipt::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'merchant_name' => $data['merchant_or_source'] ?? $data['title'],
                    'receipt_number' => $transaction->reference_code,
                    'notes' => 'Uploaded supporting receipt',
                ]);
            }
        });

        return back()->with('success', 'Transaction recorded and reconciled across daily, weekly, monthly, and yearly ledgers.');
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        $user = $this->currentUser();
        abort_if($transaction->user_id !== $user->id, 403);

        $data = $request->validated();
        $category = ExpenseCategory::find($data['expense_category_id']);

        DB::transaction(function () use ($transaction, $user, $data, $category, $request) {
            $transaction->update([
                'expense_category_id' => $data['expense_category_id'],
                'expense_subcategory_id' => $data['expense_subcategory_id'] ?? null,
                'payment_method_id' => $data['payment_method_id'] ?? null,
                'title' => $data['title'],
                'type' => $data['type'],
                'income_source' => $data['type'] === 'income' ? ($data['income_source'] ?? $category?->name) : null,
                'amount' => number_format((float) $data['amount'], 2, '.', ''),
                'transaction_date' => $data['transaction_date'],
                'transaction_time' => $data['transaction_time'] ?? $transaction->transaction_time,
                'merchant_or_source' => $data['merchant_or_source'] ?? $transaction->merchant_or_source,
                'is_recurring' => (bool) ($data['is_recurring'] ?? false),
                'recurring_frequency' => !empty($data['is_recurring']) ? ($data['recurring_frequency'] ?? 'monthly') : null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($request->hasFile('receipt')) {
                $file = $request->file('receipt');
                $storedPath = $file->store('receipts', 'public');
                Receipt::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => $user->id,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $storedPath,
                    'mime_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'merchant_name' => $data['merchant_or_source'] ?? $data['title'],
                    'receipt_number' => $transaction->reference_code,
                ]);
            }
        });

        return back()->with('success', 'Transaction updated and period totals recalculated.');
    }

    public function destroy(Transaction $transaction)
    {
        $user = $this->currentUser();
        abort_if($transaction->user_id !== $user->id, 403);

        foreach ($transaction->receipts as $receipt) {
            Storage::disk('public')->delete($receipt->file_path);
        }

        $transaction->delete();

        return back()->with('success', 'Transaction removed from ledger.');
    }
}
