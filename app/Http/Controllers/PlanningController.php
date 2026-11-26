<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\ExpenseCategory;
use App\Models\FinancialGoal;
use App\Models\PaymentMethod;
use App\Models\RecurringTransaction;
use App\Models\SavingsRecord;
use App\Models\Transaction;
use App\Services\UnifiedFinancialEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PlanningController extends Controller
{
    public function __construct(
        private readonly UnifiedFinancialEngine $engine
    ) {}

    /**
     * 6. BUDGET MANAGEMENT
     */
    public function budgets(Request $request)
    {
        $user = $this->currentUser();
        $now = now();
        $year = (int) $request->query('year', $now->year);
        $month = (int) $request->query('month', $now->month);

        $monthlyOverview = $this->engine->getMonthlyOverview($user, $year, $month);
        $refWeekDate = ($year === (int) $now->year && $month === (int) $now->month)
            ? $now->toDateString()
            : Carbon::createFromDate($year, $month, min($now->day, Carbon::createFromDate($year, $month, 1)->daysInMonth))->toDateString();
        $weeklyOverview = $this->engine->getWeeklyOverview($user, $refWeekDate);
        $yearlyOverview = $this->engine->getYearlyOverview($user, $year);

        $activeBudget = Budget::with('categoryAllocations.category')
            ->where('user_id', $user->id)
            ->where('period_type', 'monthly')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $catSpendMap = collect($monthlyOverview['categories'])->keyBy('category_id');

        $categoryBudgetRows = [];
        if ($activeBudget) {
            foreach ($activeBudget->categoryAllocations as $alloc) {
                $spent = (float) ($catSpendMap[$alloc->expense_category_id]['amount'] ?? 0.0);
                $limit = (float) $alloc->allocated_amount;
                $remaining = $limit - $spent;
                $pct = $limit > 0 ? round(($spent / $limit) * 100, 1) : 0.0;

                $categoryBudgetRows[] = [
                    'allocation_id' => $alloc->id,
                    'category_id' => $alloc->expense_category_id,
                    'category_name' => $alloc->category?->name ?? 'Category',
                    'color' => $alloc->category?->color ?? '#319B79',
                    'planned' => number_format($limit, 2, '.', ''),
                    'actual' => number_format($spent, 2, '.', ''),
                    'remaining' => number_format(max(0, $remaining), 2, '.', ''),
                    'exceeded' => $remaining < 0 ? number_format(abs($remaining), 2, '.', '') : '0.00',
                    'is_exceeded' => $remaining < 0,
                    ' is_warning' => $pct >= (float) $alloc->alert_threshold_percent && $remaining >= 0,
                    'utilization_pct' => $pct,
                ];
            }
        }

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('budgets.index', compact(
            'user',
            'year',
            'month',
            'monthlyOverview',
            'weeklyOverview',
            'yearlyOverview',
            'activeBudget',
            'categoryBudgetRows',
            'categories',
            'paymentMethods'
        ));
    }

    public function updateTargets(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'overall_monthly_budget' => ['required', 'numeric', 'min:0'],
            'weekly_spending_limit' => ['required', 'numeric', 'min:0'],
            'yearly_spending_target' => ['required', 'numeric', 'min:0'],
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
        ]);

        $user->update([
            'overall_monthly_budget' => number_format((float) $validated['overall_monthly_budget'], 2, '.', ''),
            'weekly_spending_limit' => number_format((float) $validated['weekly_spending_limit'], 2, '.', ''),
            'yearly_spending_target' => number_format((float) $validated['yearly_spending_target'], 2, '.', ''),
        ]);

        $monthStart = Carbon::createFromDate($validated['year'], $validated['month'], 1);
        Budget::updateOrCreate(
            [
                'user_id' => $user->id,
                'period_type' => 'monthly',
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'name' => $monthStart->format('F Y') . ' Master Spending Plan',
                'start_date' => $monthStart->startOfMonth()->toDateString(),
                'end_date' => $monthStart->endOfMonth()->toDateString(),
                'total_limit' => number_format((float) $validated['overall_monthly_budget'], 2, '.', ''),
                'alert_threshold_percent' => 80.00,
                'is_active' => true,
            ]
        );

        return back()->with('success', 'Monthly, weekly, and yearly spending targets updated.');
    }

    public function storeCategoryAllocation(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'year' => ['required', 'integer'],
            'month' => ['required', 'integer', 'between:1,12'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'allocated_amount' => ['required', 'numeric', 'min:1'],
            'alert_threshold_percent' => ['nullable', 'numeric', 'between:10,100'],
        ]);

        $monthStart = Carbon::createFromDate($validated['year'], $validated['month'], 1);
        $budget = Budget::firstOrCreate(
            [
                'user_id' => $user->id,
                'period_type' => 'monthly',
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            [
                'name' => $monthStart->format('F Y') . ' Master Spending Plan',
                'start_date' => $monthStart->startOfMonth()->toDateString(),
                'end_date' => $monthStart->endOfMonth()->toDateString(),
                'total_limit' => $user->overall_monthly_budget,
                'alert_threshold_percent' => 80.00,
                'is_active' => true,
            ]
        );

        BudgetCategory::updateOrCreate(
            [
                'budget_id' => $budget->id,
                'expense_category_id' => $validated['expense_category_id'],
            ],
            [
                'allocated_amount' => number_format((float) $validated['allocated_amount'], 2, '.', ''),
                'alert_threshold_percent' => $validated['alert_threshold_percent'] ?? 80.00,
            ]
        );

        return back()->with('success', 'Category budget limit saved.');
    }

    public function destroyCategoryAllocation(BudgetCategory $allocation)
    {
        $user = $this->currentUser();
        abort_if($allocation->budget?->user_id !== $user->id, 403);

        $allocation->delete();
        return back()->with('success', 'Category budget allocation removed.');
    }

    /**
     * 9. RECURRING EXPENSES & REMINDERS
     */
    public function recurring()
    {
        $user = $this->currentUser();

        $recurringList = RecurringTransaction::with(['category', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->orderBy('next_due_date')
            ->get();

        // Calculate monthly equivalent commitment across frequencies
        $monthlyCommitmentTotal = '0.00';
        foreach ($recurringList->where('status', 'active')->where('type', 'expense') as $item) {
            $amt = (float) $item->amount;
            $monthlyEq = match ($item->frequency) {
                'daily' => $amt * 30,
                'weekly' => $amt * 4.33,
                'quarterly' => $amt / 3,
                'yearly' => $amt / 12,
                default => $amt,
            };
            $monthlyCommitmentTotal = UnifiedFinancialEngine::add($monthlyCommitmentTotal, $monthlyEq);
        }

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('recurring.index', compact(
            'user',
            'recurringList',
            'monthlyCommitmentTotal',
            'categories',
            'paymentMethods'
        ));
    }

    public function storeRecurring(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_subcategory_id' => ['nullable', 'exists:expense_subcategories,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'start_date' => ['required', 'date'],
            'next_due_date' => ['required', 'date'],
            'reminder_days_before' => ['nullable', 'integer', 'min:0', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        RecurringTransaction::create([
            'user_id' => $user->id,
            'expense_category_id' => $validated['expense_category_id'],
            'expense_subcategory_id' => $validated['expense_subcategory_id'] ?? null,
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'amount' => number_format((float) $validated['amount'], 2, '.', ''),
            'frequency' => $validated['frequency'],
            'start_date' => $validated['start_date'],
            'next_due_date' => $validated['next_due_date'],
            'reminder_days_before' => $validated['reminder_days_before'] ?? 3,
            'auto_record' => false,
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Recurring financial commitment scheduled.');
    }

    public function updateRecurring(Request $request, RecurringTransaction $recurring)
    {
        $user = $this->currentUser();
        abort_if($recurring->user_id !== $user->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'type' => ['required', 'in:expense,income'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_category_id' => ['required', 'exists:expense_categories,id'],
            'expense_subcategory_id' => ['nullable', 'exists:expense_subcategories,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'frequency' => ['required', 'in:daily,weekly,monthly,quarterly,yearly'],
            'next_due_date' => ['required', 'date'],
            'reminder_days_before' => ['nullable', 'integer', 'min:0', 'max:30'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $recurring->update([
            'expense_category_id' => $validated['expense_category_id'],
            'expense_subcategory_id' => $validated['expense_subcategory_id'] ?? null,
            'payment_method_id' => $validated['payment_method_id'] ?? null,
            'title' => $validated['title'],
            'type' => $validated['type'],
            'amount' => number_format((float) $validated['amount'], 2, '.', ''),
            'frequency' => $validated['frequency'],
            'next_due_date' => $validated['next_due_date'],
            'reminder_days_before' => $validated['reminder_days_before'] ?? $recurring->reminder_days_before,
            'notes' => $validated['notes'] ?? $recurring->notes,
        ]);

        return back()->with('success', "Recurring schedule '{$recurring->title}' updated.");
    }

    /**
     * Controlled Recurring Execution Mechanism:
     * Explicitly posts the installment into `transactions` (either on its scheduled due date or today's date)
     * and advances `next_due_date` without duplicate firing.
     */
    public function processRecurringNow(Request $request, RecurringTransaction $recurring)
    {
        $user = $this->currentUser();
        abort_if($recurring->user_id !== $user->id, 403);

        $postMode = $request->input('post_mode', 'due_date'); // 'due_date' or 'today'

        DB::transaction(function () use ($recurring, $user, $postMode) {
            $dueDate = $recurring->next_due_date->copy();
            $effectiveDate = $postMode === 'today' ? now() : $dueDate->copy();

            // Prevent duplicate execution for the same effective date
            $alreadyRecorded = Transaction::where('user_id', $user->id)
                ->where('recurring_transaction_id', $recurring->id)
                ->whereDate('transaction_date', $effectiveDate->toDateString())
                ->exists();

            if (!$alreadyRecorded) {
                Transaction::create([
                    'user_id' => $user->id,
                    'expense_category_id' => $recurring->expense_category_id,
                    'expense_subcategory_id' => $recurring->expense_subcategory_id,
                    'payment_method_id' => $recurring->payment_method_id,
                    'recurring_transaction_id' => $recurring->id,
                    'title' => $recurring->title,
                    'type' => $recurring->type,
                    'income_source' => $recurring->type === 'income' ? ($recurring->category?->name ?? $recurring->title) : null,
                    'amount' => $recurring->amount,
                    'currency' => $user->currency,
                    'transaction_date' => $effectiveDate->toDateString(),
                    'transaction_time' => now()->format('H:i:s'),
                    'merchant_or_source' => $recurring->category?->name ?? 'Scheduled Commitment',
                    'reference_code' => 'RC-' . strtoupper(Str::random(7)),
                    'is_recurring' => true,
                    'recurring_frequency' => $recurring->frequency,
                    'notes' => 'Processed from recurring schedule #' . $recurring->id . ($postMode === 'today' ? ' (Posted Today)' : ''),
                ]);
            }

            $baseDateForNext = $postMode === 'today' && $dueDate->isPast() ? $effectiveDate : $dueDate;
            $nextDue = match ($recurring->frequency) {
                'daily' => $baseDateForNext->copy()->addDay(),
                'weekly' => $baseDateForNext->copy()->addWeek(),
                'quarterly' => $baseDateForNext->copy()->addMonthsNoOverflow(3),
                'yearly' => $baseDateForNext->copy()->addYear(),
                default => $baseDateForNext->copy()->addMonthNoOverflow(),
            };

            $recurring->update([
                'last_processed_date' => $effectiveDate->toDateString(),
                'next_due_date' => $nextDue->toDateString(),
            ]);
        });

        $dateLabel = $postMode === 'today' ? now()->format('d M Y') . ' (Today)' : 'its scheduled due date';
        return back()->with('success', "Recurring installment recorded into transaction ledger for {$dateLabel} and next due date advanced.");
    }

    public function destroyRecurring(RecurringTransaction $recurring)
    {
        $user = $this->currentUser();
        abort_if($recurring->user_id !== $user->id, 403);

        $recurring->delete();
        return back()->with('success', 'Recurring schedule removed.');
    }

    /**
     * 10. SAVINGS & FINANCIAL GOALS
     */
    public function savings()
    {
        $user = $this->currentUser();

        $goals = FinancialGoal::with('savingsRecords')
            ->where('user_id', $user->id)
            ->orderBy('priority')
            ->get();

        $savingsRecords = SavingsRecord::with(['financialGoal', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->get();

        $monthStartStr = now()->startOfMonth()->toDateString();
        $monthEndStr = now()->endOfMonth()->toDateString();
        $totalExplicitSavings = $this->engine->sumExplicitSavings($user->id);
        $thisMonthSavings = $this->engine->sumExplicitSavings($user->id, $monthStartStr, $monthEndStr);
        $thisMonthIncomeFundedSavings = $this->engine->sumIncomeFundedSavings($user->id, $monthStartStr, $monthEndStr);
        $thisMonthFreeCapitalSavings = $this->engine->sumFreeCapitalSavings($user->id, $monthStartStr, $monthEndStr);

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('savings.index', compact(
            'user',
            'goals',
            'savingsRecords',
            'totalExplicitSavings',
            'thisMonthSavings',
            'thisMonthIncomeFundedSavings',
            'thisMonthFreeCapitalSavings',
            'categories',
            'paymentMethods'
        ));
    }

    public function storeGoal(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'target_date' => ['nullable', 'date'],
            'color' => ['required', 'string', 'max:20'],
            'priority' => ['required', 'in:low,medium,high'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        FinancialGoal::create([
            'user_id' => $user->id,
            'title' => $validated['title'],
            'category' => $validated['category'],
            'target_amount' => number_format((float) $validated['target_amount'], 2, '.', ''),
            'current_amount' => '0.00',
            'start_date' => now()->toDateString(),
            'target_date' => $validated['target_date'] ?? null,
            'color' => $validated['color'],
            'priority' => $validated['priority'],
            'status' => 'active',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('success', 'Financial goal created.');
    }

    public function updateGoal(Request $request, FinancialGoal $goal)
    {
        $user = $this->currentUser();
        abort_if($goal->user_id !== $user->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'category' => ['required', 'string', 'max:80'],
            'target_amount' => ['required', 'numeric', 'min:1'],
            'target_date' => ['nullable', 'date'],
            'color' => ['required', 'string', 'max:20'],
            'priority' => ['nullable', 'in:low,medium,high'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $newTarget = (float) $validated['target_amount'];
        $currentAmt = (float) $goal->current_amount;

        $goal->update([
            'title' => $validated['title'],
            'category' => $validated['category'],
            'target_amount' => number_format($newTarget, 2, '.', ''),
            'target_date' => $validated['target_date'] ?? null,
            'color' => $validated['color'],
            'priority' => $validated['priority'] ?? $goal->priority,
            'status' => $currentAmt >= $newTarget ? 'achieved' : 'active',
            'notes' => $validated['notes'] ?? $goal->notes,
        ]);

        return back()->with('success', "Financial goal '{$goal->title}' updated.");
    }

    public function toggleArchiveGoal(FinancialGoal $goal)
    {
        $user = $this->currentUser();
        abort_if($goal->user_id !== $user->id, 403);

        if ($goal->status === 'archived') {
            $restoredStatus = (float) $goal->current_amount >= (float) $goal->target_amount ? 'achieved' : 'active';
            $goal->update(['status' => $restoredStatus]);
            return back()->with('success', "Financial goal '{$goal->title}' restored to Active Capital & Savings Goals.");
        }

        abort_if((float) $goal->current_amount < (float) $goal->target_amount, 422, 'Only reached/completed savings goals can be archived.');

        $goal->update(['status' => 'archived']);

        return back()->with('success', "Completed goal '{$goal->title}' has been archived.");
    }

    public function destroyGoal(FinancialGoal $goal)
    {
        $user = $this->currentUser();
        abort_if($goal->user_id !== $user->id, 403);

        DB::transaction(function () use ($goal, $user) {
            SavingsRecord::where('user_id', $user->id)
                ->where('financial_goal_id', $goal->id)
                ->delete();
            $goal->delete();
        });

        return back()->with('success', 'Financial goal and its linked savings entries removed.');
    }

    public function updateSavingsRecord(Request $request, SavingsRecord $record)
    {
        $user = $this->currentUser();
        abort_if($record->user_id !== $user->id, 403);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'financial_goal_id' => ['nullable', 'exists:financial_goals,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'action_type' => ['required', 'in:deposit,withdrawal'],
            'funding_source' => ['nullable', 'in:monthly_income,free_capital'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'record_date' => ['required', 'date'],
            'vault_or_account' => ['nullable', 'string', 'max:120'],
        ]);

        DB::transaction(function () use ($user, $record, $validated) {
            $oldGoalId = $record->financial_goal_id;
            $newGoalId = !empty($validated['financial_goal_id']) ? (int) $validated['financial_goal_id'] : null;

            $record->update([
                'title' => $validated['title'],
                'financial_goal_id' => $newGoalId,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'action_type' => $validated['action_type'],
                'funding_source' => $validated['funding_source'] ?? $record->funding_source ?? 'monthly_income',
                'amount' => number_format((float) $validated['amount'], 2, '.', ''),
                'record_date' => $validated['record_date'],
                'vault_or_account' => $validated['vault_or_account'] ?? 'Primary Savings Vault',
            ]);

            if ($oldGoalId) {
                $this->recalculateGoalBalance($user, (int) $oldGoalId);
            }
            if ($newGoalId && $newGoalId !== (int) $oldGoalId) {
                $this->recalculateGoalBalance($user, $newGoalId);
            }
        });

        return back()->with('success', 'Savings transfer updated and goal balance recalculated.');
    }

    public function destroySavingsRecord(SavingsRecord $record)
    {
        $user = $this->currentUser();
        abort_if($record->user_id !== $user->id, 403);

        DB::transaction(function () use ($user, $record) {
            $goalId = $record->financial_goal_id;
            $record->delete();

            if ($goalId) {
                $this->recalculateGoalBalance($user, (int) $goalId);
            }
        });

        return back()->with('success', 'Savings transfer deleted and goal balance recalculated.');
    }

    private function recalculateGoalBalance($user, int $goalId): void
    {
        $goal = FinancialGoal::where('user_id', $user->id)->find($goalId);
        if (!$goal) {
            return;
        }

        $records = SavingsRecord::where('user_id', $user->id)
            ->where('financial_goal_id', $goal->id)
            ->get();

        $deposits = $records->where('action_type', 'deposit')->sum(fn ($r) => (float) $r->amount);
        $withdrawals = $records->where('action_type', 'withdrawal')->sum(fn ($r) => (float) $r->amount);
        $netCurrent = max(0, $deposits - $withdrawals);
        $targetAmt = (float) $goal->target_amount;

        $goal->update([
            'current_amount' => number_format($netCurrent, 2, '.', ''),
            'status' => $netCurrent >= $targetAmt ? 'achieved' : 'active',
        ]);
    }

    public function storeSavingsRecord(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'financial_goal_id' => ['nullable', 'exists:financial_goals,id'],
            'payment_method_id' => ['nullable', 'exists:payment_methods,id'],
            'action_type' => ['required', 'in:deposit,withdrawal'],
            'funding_source' => ['nullable', 'in:monthly_income,free_capital'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'record_date' => ['required', 'date'],
            'vault_or_account' => ['nullable', 'string', 'max:120'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $overflowMessage = null;
        $fundingSource = $validated['funding_source'] ?? 'monthly_income';

        DB::transaction(function () use ($user, $validated, $fundingSource, &$overflowMessage) {
            $amountStr = number_format((float) $validated['amount'], 2, '.', '');

            if (!empty($validated['financial_goal_id'])) {
                $goal = FinancialGoal::where('user_id', $user->id)->find($validated['financial_goal_id']);
                if ($goal) {
                    $prevCurrent = (float) $goal->current_amount;
                    $targetAmt = (float) $goal->target_amount;
                    $remainingBefore = max(0, $targetAmt - $prevCurrent);

                    $newCurrent = $validated['action_type'] === 'deposit'
                        ? (float) UnifiedFinancialEngine::add($goal->current_amount, $amountStr)
                        : max(0, (float) UnifiedFinancialEngine::sub($goal->current_amount, $amountStr));

                    $overflowAmt = max(0, $newCurrent - $targetAmt);

                    $notesToSave = $validated['notes'] ?? null;
                    if ($validated['action_type'] === 'deposit' && $overflowAmt > 0) {
                        $overflowTag = "Target Overflow: +{$user->currency}" . number_format($overflowAmt, 2) . " surplus above {$user->currency}" . number_format($targetAmt, 2) . " target";
                        $notesToSave = $notesToSave ? ($notesToSave . ' • ' . $overflowTag) : $overflowTag;
                        $overflowMessage = "Explicit savings recorded! '{$goal->title}' reached 100% of its {$user->currency}" . number_format($targetAmt, 2) . " target with a +{$user->currency}" . number_format($overflowAmt, 2) . " Target Overflow surplus.";
                    }

                    SavingsRecord::create([
                        'user_id' => $user->id,
                        'financial_goal_id' => $goal->id,
                        'payment_method_id' => $validated['payment_method_id'] ?? null,
                        'title' => $validated['title'],
                        'action_type' => $validated['action_type'],
                        'funding_source' => $fundingSource,
                        'amount' => $amountStr,
                        'record_date' => $validated['record_date'],
                        'vault_or_account' => $validated['vault_or_account'] ?? 'Primary Savings Vault',
                        'notes' => $notesToSave,
                    ]);

                    $goal->update([
                        'current_amount' => number_format($newCurrent, 2, '.', ''),
                        'status' => $newCurrent >= $targetAmt ? 'achieved' : 'active',
                    ]);

                    return;
                }
            }

            SavingsRecord::create([
                'user_id' => $user->id,
                'financial_goal_id' => null,
                'payment_method_id' => $validated['payment_method_id'] ?? null,
                'title' => $validated['title'],
                'action_type' => $validated['action_type'],
                'funding_source' => $fundingSource,
                'amount' => $amountStr,
                'record_date' => $validated['record_date'],
                'vault_or_account' => $validated['vault_or_account'] ?? 'Primary Savings Vault',
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return back()->with('success', $overflowMessage ?? 'Explicit savings entry recorded and goal balance updated.');
    }
}
