<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\SavingsRecord;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class UnifiedFinancialEngine
{
    /**
     * Exact decimal addition using BCMath (2 decimal places).
     */
    public static function add(string|float|int $a, string|float|int $b): string
    {
        $aStr = number_format((float) $a, 2, '.', '');
        $bStr = number_format((float) $b, 2, '.', '');
        return function_exists('bcadd') ? bcadd($aStr, $bStr, 2) : number_format((float) $aStr + (float) $bStr, 2, '.', '');
    }

    /**
     * Exact decimal subtraction using BCMath.
     */
    public static function sub(string|float|int $a, string|float|int $b): string
    {
        $aStr = number_format((float) $a, 2, '.', '');
        $bStr = number_format((float) $b, 2, '.', '');
        return function_exists('bcsub') ? bcsub($aStr, $bStr, 2) : number_format((float) $aStr - (float) $bStr, 2, '.', '');
    }

    /**
     * Percentage change from previous to current.
     */
    public static function percentageChange(string|float|int $current, string|float|int $previous): array
    {
        $curr = (float) $current;
        $prev = (float) $previous;
        $diff = self::sub($curr, $prev);

        if ($prev <= 0.0) {
            $pct = $curr > 0 ? 100.0 : 0.0;
        } else {
            $pct = round((($curr - $prev) / $prev) * 100, 1);
        }

        return [
            'current' => number_format($curr, 2, '.', ''),
            'previous' => number_format($prev, 2, '.', ''),
            'difference' => $diff,
            'percentage' => $pct,
            'direction' => $curr > $prev ? 'up' : ($curr < $prev ? 'down' : 'flat'),
        ];
    }

    /**
     * Sum transactions by type and inclusive date range directly from MySQL DECIMAL(15,2).
     */
    public function sumTransactions(int $userId, string $type, string $startDate, string $endDate, ?int $categoryId = null): string
    {
        $query = Transaction::where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $sum = $query->sum('amount');
        return number_format((float) $sum, 2, '.', '');
    }

    /**
     * Explicitly recorded savings (Deposits minus Withdrawals).
     * Never automatically classifies unspent income as savings.
     */
    public function sumExplicitSavings(int $userId, ?string $startDate = null, ?string $endDate = null, ?string $fundingSource = null): string
    {
        $depositsQuery = SavingsRecord::where('user_id', $userId)->where('action_type', 'deposit');
        $withdrawalsQuery = SavingsRecord::where('user_id', $userId)->where('action_type', 'withdrawal');

        if ($fundingSource !== null) {
            $depositsQuery->where('funding_source', $fundingSource);
            $withdrawalsQuery->where('funding_source', $fundingSource);
        }

        if ($startDate && $endDate) {
            $depositsQuery->whereBetween('record_date', [$startDate, $endDate]);
            $withdrawalsQuery->whereBetween('record_date', [$startDate, $endDate]);
        }

        $deposits = number_format((float) $depositsQuery->sum('amount'), 2, '.', '');
        $withdrawals = number_format((float) $withdrawalsQuery->sum('amount'), 2, '.', '');

        return self::sub($deposits, $withdrawals);
    }

    /**
     * Sum explicit savings funded directly from the period's total income
     * (which deducts from the period's spendable Net Balance).
     */
    public function sumIncomeFundedSavings(int $userId, ?string $startDate = null, ?string $endDate = null): string
    {
        return $this->sumExplicitSavings($userId, $startDate, $endDate, 'monthly_income');
    }

    /**
     * Sum explicit savings funded from external / free capital
     * (which does not deduct from the period's Monthly Income Net Balance).
     */
    public function sumFreeCapitalSavings(int $userId, ?string $startDate = null, ?string $endDate = null): string
    {
        return $this->sumExplicitSavings($userId, $startDate, $endDate, 'free_capital');
    }

    /**
     * Build the 8 primary Dashboard Financial Summary indicators with previous-period comparisons.
     */
    public function getExecutiveSummary(User $user, ?Carbon $referenceDate = null): array
    {
        $now = ($referenceDate ?? Carbon::now())->copy();
        $todayStr = $now->toDateString();
        $yesterdayStr = $now->copy()->subDay()->toDateString();

        // Week ranges (Monday to Sunday)
        $weekStart = $now->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEnd = $now->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $prevWeekStart = $now->copy()->subWeek()->startOfWeek(Carbon::MONDAY)->toDateString();
        $prevWeekEnd = $now->copy()->subWeek()->endOfWeek(Carbon::SUNDAY)->toDateString();

        // Month ranges
        $monthStart = $now->copy()->startOfMonth()->toDateString();
        $monthEnd = $now->copy()->endOfMonth()->toDateString();
        $prevMonthStart = $now->copy()->subMonthNoOverflow()->startOfMonth()->toDateString();
        $prevMonthEnd = $now->copy()->subMonthNoOverflow()->endOfMonth()->toDateString();

        // Year ranges
        $yearStart = $now->copy()->startOfYear()->toDateString();
        $yearEnd = $now->copy()->endOfYear()->toDateString();
        $prevYearStart = $now->copy()->subYear()->startOfYear()->toDateString();
        $prevYearEnd = $now->copy()->subYear()->endOfYear()->toDateString();

        // 1. Total Expenses Today
        $expensesToday = $this->sumTransactions($user->id, 'expense', $todayStr, $todayStr);
        $expensesYesterday = $this->sumTransactions($user->id, 'expense', $yesterdayStr, $yesterdayStr);

        // 2. Total Expenses This Week
        $expensesThisWeek = $this->sumTransactions($user->id, 'expense', $weekStart, $weekEnd);
        $expensesLastWeek = $this->sumTransactions($user->id, 'expense', $prevWeekStart, $prevWeekEnd);

        // 3. Total Expenses This Month
        $expensesThisMonth = $this->sumTransactions($user->id, 'expense', $monthStart, $monthEnd);
        $expensesLastMonth = $this->sumTransactions($user->id, 'expense', $prevMonthStart, $prevMonthEnd);

        // 4. Total Expenses This Year
        $expensesThisYear = $this->sumTransactions($user->id, 'expense', $yearStart, $yearEnd);
        $expensesLastYear = $this->sumTransactions($user->id, 'expense', $prevYearStart, $prevYearEnd);

        // 5. Total Income (Current Month & Year)
        $incomeThisMonth = $this->sumTransactions($user->id, 'income', $monthStart, $monthEnd);
        $incomeLastMonth = $this->sumTransactions($user->id, 'income', $prevMonthStart, $prevMonthEnd);
        $incomeThisYear = $this->sumTransactions($user->id, 'income', $yearStart, $yearEnd);

        // 6.7. Total Savings (Explicitly recorded in savings_records, split by Funding Source)
        $totalSavingsAllTime = $this->sumExplicitSavings($user->id);
        $savingsThisMonth = $this->sumExplicitSavings($user->id, $monthStart, $monthEnd);
        $savingsLastMonth = $this->sumExplicitSavings($user->id, $prevMonthStart, $prevMonthEnd);

        $incomeFundedSavingsThisMonth = $this->sumIncomeFundedSavings($user->id, $monthStart, $monthEnd);
        $incomeFundedSavingsLastMonth = $this->sumIncomeFundedSavings($user->id, $prevMonthStart, $prevMonthEnd);
        $freeCapitalSavingsThisMonth = $this->sumFreeCapitalSavings($user->id, $monthStart, $monthEnd);

        // 6. Current Balance (Net Cash Flow This Month = Monthly Income - Monthly Expenses - Income-Funded Savings)
        $grossBalanceThisMonth = self::sub($incomeThisMonth, $expensesThisMonth);
        $grossBalancePrevMonth = self::sub($incomeLastMonth, $expensesLastMonth);
        $netCashFlowMonth = self::sub($grossBalanceThisMonth, $incomeFundedSavingsThisMonth);
        $netCashFlowPrevMonth = self::sub($grossBalancePrevMonth, $incomeFundedSavingsLastMonth);

        // 8. Monthly Budget Remaining
        $activeBudget = Budget::where('user_id', $user->id)
            ->where('period_type', 'monthly')
            ->where('year', (int) $now->year)
            ->where('month', (int) $now->month)
            ->first();

        $monthlyBudgetLimit = $activeBudget
            ? number_format((float) $activeBudget->total_limit, 2, '.', '')
            : number_format((float) $user->overall_monthly_budget, 2, '.', '');

        $budgetRemaining = self::sub($monthlyBudgetLimit, $expensesThisMonth);
        $budgetUtilizationPct = (float) $monthlyBudgetLimit > 0
            ? round(((float) $expensesThisMonth / (float) $monthlyBudgetLimit) * 100, 1)
            : 0.0;

        return [
            'reference_date' => $todayStr,
            'reference_label' => $now->format('l, j F Y'),
            'month_label' => $now->format('F Y'),
            'year_label' => $now->format('Y'),
            'expenses_today' => array_merge(
                self::percentageChange($expensesToday, $expensesYesterday),
                ['label' => 'Total Expenses Today', 'comparison_label' => 'vs yesterday (' . $now->copy()->subDay()->format('d M') . ')']
            ),
            'expenses_week' => array_merge(
                self::percentageChange($expensesThisWeek, $expensesLastWeek),
                ['label' => 'Total Expenses This Week', 'comparison_label' => 'vs previous week']
            ),
            'expenses_month' => array_merge(
                self::percentageChange($expensesThisMonth, $expensesLastMonth),
                ['label' => 'Total Expenses This Month', 'comparison_label' => 'vs ' . $now->copy()->subMonthNoOverflow()->format('F')]
            ),
            'expenses_year' => array_merge(
                self::percentageChange($expensesThisYear, $expensesLastYear),
                ['label' => 'Total Expenses This Year', 'comparison_label' => 'vs ' . $now->copy()->subYear()->format('Y')]
            ),
            'income_month' => array_merge(
                self::percentageChange($incomeThisMonth, $incomeLastMonth),
                [
                    'label' => 'Total Income (' . $now->format('M') . ')',
                    'annual_income' => $incomeThisYear,
                    'comparison_label' => 'vs ' . $now->copy()->subMonthNoOverflow()->format('F'),
                ]
            ),
            'current_balance' => array_merge(
                self::percentageChange($netCashFlowMonth, $netCashFlowPrevMonth),
                [
                    'gross_before_savings' => $grossBalanceThisMonth,
                    'income_funded_savings' => $incomeFundedSavingsThisMonth,
                    'free_capital_savings' => $freeCapitalSavingsThisMonth,
                    'label' => 'Current Monthly Net Balance',
                    'comparison_label' => (float) $incomeFundedSavingsThisMonth > 0
                        ? 'Income - Expenses - ' . ($user->currency ?: 'GH¢') . number_format((float) $incomeFundedSavingsThisMonth, 2) . ' Saved from Income'
                        : 'Income minus expenses this month',
                ]
            ),
            'total_savings' => array_merge(
                self::percentageChange($savingsThisMonth, $savingsLastMonth),
                [
                    'cumulative' => $totalSavingsAllTime,
                    'monthly_added' => $savingsThisMonth,
                    'income_funded_month' => $incomeFundedSavingsThisMonth,
                    'free_capital_month' => $freeCapitalSavingsThisMonth,
                    'label' => 'Total Explicit Savings',
                    'comparison_label' => ($user->currency ?: 'GH¢') . number_format((float) $savingsThisMonth, 2) . ' added in ' . $now->format('M'),
                ]
            ),
            'budget_remaining' => [
                'limit' => $monthlyBudgetLimit,
                'spent' => $expensesThisMonth,
                'remaining' => $budgetRemaining,
                'utilization_pct' => $budgetUtilizationPct,
                'is_exceeded' => (float) $budgetRemaining < 0,
                'label' => 'Monthly Budget Remaining',
                'comparison_label' => $budgetUtilizationPct . '% of GH¢' . number_format((float) $monthlyBudgetLimit, 2) . ' utilised',
            ],
        ];
    }

    /**
     * Build the interactive Spending Breakdown Explorer for any selected Year & Month.
     * Reconciles Month Total -> Week 1..5 Totals -> Monday..Sunday Daily Breakdowns -> Exact Transactions.
     */
    public function getSpendingBreakdownExplorer(User $user, int $year, int $month): array
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();

        $transactions = Transaction::with(['category', 'subcategory', 'paymentMethod', 'receipts'])
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('transaction_date')
            ->orderBy('transaction_time')
            ->get();

        $totalMonthlyExpense = '0.00';
        foreach ($transactions as $tx) {
            $totalMonthlyExpense = self::add($totalMonthlyExpense, (string) $tx->amount);
        }

        // Partition the month into 4 or 5 structured week blocks (Day 1-7, 8-14, 15-21, 22-End or 22-28 & 29-End)
        // To match the user's reference (Week 1, Week 2, Week 3, Week 4 summing to total monthly expenditure):
        $daysInMonth = $monthStart->daysInMonth;
        $weekRanges = [
            1 => ['start' => 1, 'end' => 7, 'label' => 'Week 1 (1–7 ' . $monthStart->format('M') . ')'],
            2 => ['start' => 8, 'end' => 14, 'label' => 'Week 2 (8–14 ' . $monthStart->format('M') . ')'],
            3 => ['start' => 15, 'end' => 21, 'label' => 'Week 3 (15–21 ' . $monthStart->format('M') . ')'],
            4 => ['start' => 22, 'end' => $daysInMonth, 'label' => 'Week 4 (22–' . $daysInMonth . ' ' . $monthStart->format('M') . ')'],
        ];

        $weeks = [];
        $maxWeekAmount = 0.0;

        foreach ($weekRanges as $weekNum => $range) {
            $wStart = Carbon::createFromDate($year, $month, $range['start'])->startOfDay();
            $wEnd = Carbon::createFromDate($year, $month, $range['end'])->endOfDay();

            $weekTxs = $transactions->filter(function (Transaction $tx) use ($wStart, $wEnd) {
                return $tx->transaction_date->betweenIncluded($wStart, $wEnd);
            });

            $weekSum = '0.00';
            foreach ($weekTxs as $wTx) {
                $weekSum = self::add($weekSum, (string) $wTx->amount);
            }

            if ((float) $weekSum > $maxWeekAmount) {
                $maxWeekAmount = (float) $weekSum;
            }

            // Group daily inside this week
            $days = [];
            $cursor = $wStart->copy();
            while ($cursor->lte($wEnd)) {
                $dStr = $cursor->toDateString();
                $dayTxs = $weekTxs->filter(fn (Transaction $t) => $t->transaction_date->toDateString() === $dStr)->values();
                $daySum = '0.00';
                foreach ($dayTxs as $dTx) {
                    $daySum = self::add($daySum, (string) $dTx->amount);
                }

                $days[] = [
                    'date' => $dStr,
                    'day_name' => $cursor->format('l'),
                    'day_short' => $cursor->format('D'),
                    'formatted_date' => $cursor->format('d M Y'),
                    'total' => $daySum,
                    'transaction_count' => $dayTxs->count(),
                    'transactions' => $dayTxs->map(fn (Transaction $t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'amount' => number_format((float) $t->amount, 2, '.', ''),
                        'formatted_amount' => $user->currency . number_format((float) $t->amount, 2),
                        'category' => $t->category?->name ?? 'Uncategorised',
                        'category_color' => $t->category?->color ?? '#319B79',
                        'subcategory' => $t->subcategory?->name,
                        'payment_method' => $t->paymentMethod?->name ?? 'Default',
                        'time' => $t->transaction_time ? substr($t->transaction_time, 0, 5) : '—',
                        'merchant' => $t->merchant_or_source,
                        'has_receipt' => $t->receipts->isNotEmpty(),
                    ])->all(),
                ];
                $cursor->addDay();
            }

            $weeks[$weekNum] = [
                'week_number' => $weekNum,
                'title' => 'Week ' . $weekNum,
                'range_label' => $range['label'],
                'start_date' => $wStart->toDateString(),
                'end_date' => $wEnd->toDateString(),
                'total' => $weekSum,
                'share_of_month_pct' => (float) $totalMonthlyExpense > 0
                    ? round(((float) $weekSum / (float) $totalMonthlyExpense) * 100, 1)
                    : 0.0,
                'transaction_count' => $weekTxs->count(),
                'days' => $days,
            ];
        }

        // Compute relative bar width percentage for the visual progress bars
        foreach ($weeks as $weekNum => &$wData) {
            $wData['bar_width_pct'] = $maxWeekAmount > 0
                ? max(8, round(((float) $wData['total'] / $maxWeekAmount) * 100, 1))
                : 0;
        }

        return [
            'year' => $year,
            'month' => $month,
            'month_name' => $monthStart->format('F Y'),
            'total_monthly_expenditure' => $totalMonthlyExpense,
            'transaction_count' => $transactions->count(),
            'weeks' => $weeks,
        ];
    }

    /**
     * Daily Overview Report for a specific date.
     */
    public function getDailyOverview(User $user, string $dateStr): array
    {
        $date = Carbon::parse($dateStr);
        $prevDate = $date->copy()->subDay();

        $transactions = Transaction::with(['category', 'subcategory', 'paymentMethod', 'receipts'])
            ->where('user_id', $user->id)
            ->whereDate('transaction_date', $date->toDateString())
            ->orderBy('transaction_time')
            ->get();

        $expenses = $transactions->where('type', 'expense');
        $incomes = $transactions->where('type', 'income');

        $totalExpense = '0.00';
        foreach ($expenses as $tx) {
            $totalExpense = self::add($totalExpense, (string) $tx->amount);
        }

        $totalIncome = '0.00';
        foreach ($incomes as $tx) {
            $totalIncome = self::add($totalIncome, (string) $tx->amount);
        }

        $prevDayExpense = $this->sumTransactions($user->id, 'expense', $prevDate->toDateString(), $prevDate->toDateString());

        $categoryBreakdown = $expenses->groupBy('expense_category_id')->map(function (Collection $group) use ($totalExpense) {
            $cat = $group->first()->category;
            $sum = '0.00';
            foreach ($group as $item) {
                $sum = self::add($sum, (string) $item->amount);
            }
            return [
                'category_id' => $cat?->id,
                'name' => $cat?->name ?? 'Uncategorised',
                'color' => $cat?->color ?? '#319B79',
                'icon' => $cat?->icon ?? 'tag',
                'amount' => $sum,
                'count' => $group->count(),
                'share_pct' => (float) $totalExpense > 0 ? round(((float) $sum / (float) $totalExpense) * 100, 1) : 0.0,
            ];
        })->sortByDesc(fn ($c) => (float) $c['amount'])->values()->all();

        // 7-day trailing daily trend ending on selected date
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $date->copy()->subDays($i);
            $dStr = $d->toDateString();
            $trend[] = [
                'date' => $dStr,
                'label' => $d->format('D d M'),
                'amount' => (float) $this->sumTransactions($user->id, 'expense', $dStr, $dStr),
            ];
        }

        return [
            'date' => $date->toDateString(),
            'formatted_date' => $date->format('l, j F Y'),
            'total_spending' => $totalExpense,
            'total_income' => $totalIncome,
            'transaction_count' => $expenses->count(),
            'all_transactions' => $transactions,
            'comparison' => self::percentageChange($totalExpense, $prevDayExpense),
            'previous_date' => $prevDate->toDateString(),
            'next_date' => $date->copy()->addDay()->toDateString(),
            'category_breakdown' => $categoryBreakdown,
            'daily_trend' => $trend,
        ];
    }

    /**
     * Weekly Overview Report (Monday to Sunday around selected date).
     */
    public function getWeeklyOverview(User $user, string $referenceDateStr): array
    {
        $ref = Carbon::parse($referenceDateStr);
        $weekStart = $ref->copy()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $ref->copy()->endOfWeek(Carbon::SUNDAY);

        $prevWeekStart = $weekStart->copy()->subWeek();
        $prevWeekEnd = $weekEnd->copy()->subWeek();

        $expenses = Transaction::with(['category', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
            ->orderBy('transaction_date')
            ->get();

        $totalWeekly = '0.00';
        foreach ($expenses as $tx) {
            $totalWeekly = self::add($totalWeekly, (string) $tx->amount);
        }

        $prevWeeklyTotal = $this->sumTransactions($user->id, 'expense', $prevWeekStart->toDateString(), $prevWeekEnd->toDateString());

        $daysOfWeek = [];
        $highestDay = ['name' => '—', 'date' => '', 'amount' => -1.0];
        $lowestDay = ['name' => '—', 'date' => '', 'amount' => null];

        for ($i = 0; $i < 7; $i++) {
            $day = $weekStart->copy()->addDays($i);
            $dStr = $day->toDateString();
            $dayTxs = $expenses->filter(fn (Transaction $t) => $t->transaction_date->toDateString() === $dStr);

            $dSum = '0.00';
            foreach ($dayTxs as $tx) {
                $dSum = self::add($dSum, (string) $tx->amount);
            }

            $floatSum = (float) $dSum;
            if ($floatSum > $highestDay['amount']) {
                $highestDay = ['name' => $day->format('l (d M)'), 'date' => $dStr, 'amount' => $floatSum];
            }
            if ($lowestDay['amount'] === null || $floatSum < $lowestDay['amount']) {
                $lowestDay = ['name' => $day->format('l (d M)'), 'date' => $dStr, 'amount' => $floatSum];
            }

            $daysOfWeek[] = [
                'day_name' => $day->format('l'),
                'day_short' => $day->format('D'),
                'date' => $dStr,
                'formatted_date' => $day->format('d M'),
                'amount' => $dSum,
                'count' => $dayTxs->count(),
                'share_pct' => (float) $totalWeekly > 0 ? round(($floatSum / (float) $totalWeekly) * 100, 1) : 0.0,
            ];
        }

        $avgDaily = number_format((float) $totalWeekly / 7, 2, '.', '');

        $categoryDistribution = $expenses->groupBy('expense_category_id')->map(function (Collection $group) use ($totalWeekly) {
            $cat = $group->first()->category;
            $sum = '0.00';
            foreach ($group as $item) {
                $sum = self::add($sum, (string) $item->amount);
            }
            return [
                'name' => $cat?->name ?? 'Uncategorised',
                'color' => $cat?->color ?? '#319B79',
                'amount' => $sum,
                'count' => $group->count(),
                'share_pct' => (float) $totalWeekly > 0 ? round(((float) $sum / (float) $totalWeekly) * 100, 1) : 0.0,
            ];
        })->sortByDesc(fn ($c) => (float) $c['amount'])->values()->all();

        return [
            'week_start' => $weekStart->toDateString(),
            'week_end' => $weekEnd->toDateString(),
            'week_label' => $weekStart->format('d M') . ' – ' . $weekEnd->format('d M Y'),
            'prev_week_date' => $prevWeekStart->toDateString(),
            'next_week_date' => $weekStart->copy()->addWeek()->toDateString(),
            'total_expenditure' => $totalWeekly,
            'average_daily' => $avgDaily,
            'highest_day' => $highestDay,
            'lowest_day' => $lowestDay,
            'days' => $daysOfWeek,
            'category_distribution' => $categoryDistribution,
            'comparison' => self::percentageChange($totalWeekly, $prevWeeklyTotal),
            'weekly_limit' => number_format((float) $user->weekly_spending_limit, 2, '.', ''),
            'transactions' => $expenses,
        ];
    }

    /**
     * Monthly Overview Report with category breakdown, daily trends, weekly summaries, and budget utilization.
     */
    public function getMonthlyOverview(User $user, int $year, int $month): array
    {
        $monthStart = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $monthEnd = $monthStart->copy()->endOfMonth();
        $prevMonth = $monthStart->copy()->subMonthNoOverflow();

        $totalExpense = $this->sumTransactions($user->id, 'expense', $monthStart->toDateString(), $monthEnd->toDateString());
        $totalIncome = $this->sumTransactions($user->id, 'income', $monthStart->toDateString(), $monthEnd->toDateString());
        $monthlySavings = $this->sumExplicitSavings($user->id, $monthStart->toDateString(), $monthEnd->toDateString());
        $incomeFundedSavings = $this->sumIncomeFundedSavings($user->id, $monthStart->toDateString(), $monthEnd->toDateString());
        $freeCapitalSavings = $this->sumFreeCapitalSavings($user->id, $monthStart->toDateString(), $monthEnd->toDateString());
        $prevMonthExpense = $this->sumTransactions($user->id, 'expense', $prevMonth->startOfMonth()->toDateString(), $prevMonth->endOfMonth()->toDateString());

        $expenses = Transaction::with(['category.subcategories', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$monthStart->toDateString(), $monthEnd->toDateString()])
            ->orderBy('transaction_date')
            ->get();

        // Category-by-category expenditure (including subcategory breakdown sorted by highest spend first)
        $categories = $expenses->groupBy('expense_category_id')->map(function (Collection $group) use ($totalExpense) {
            $cat = $group->first()->category;
            $sum = '0.00';
            foreach ($group as $item) {
                $sum = self::add($sum, (string) $item->amount);
            }

            // Compute spend per subcategory (and include any configured subcategories on the category)
            $subSpendMap = [];
            foreach ($group as $txItem) {
                if ($txItem->subcategory) {
                    $sId = $txItem->subcategory->id;
                    if (!isset($subSpendMap[$sId])) {
                        $subSpendMap[$sId] = [
                            'id' => $sId,
                            'name' => $txItem->subcategory->name,
                            'amount' => 0.0,
                            'count' => 0,
                        ];
                    }
                    $subSpendMap[$sId]['amount'] += (float) $txItem->amount;
                    $subSpendMap[$sId]['count']++;
                }
            }

            if ($cat && $cat->relationLoaded('subcategories')) {
                foreach ($cat->subcategories as $cfgSub) {
                    if (!isset($subSpendMap[$cfgSub->id])) {
                        $subSpendMap[$cfgSub->id] = [
                            'id' => $cfgSub->id,
                            'name' => $cfgSub->name,
                            'amount' => 0.0,
                            'count' => 0,
                        ];
                    }
                }
            }

            $subcategoriesList = collect($subSpendMap)
                ->sortByDesc('amount')
                ->values()
                ->all();

            return [
                'category_id' => $cat?->id,
                'name' => $cat?->name ?? 'Uncategorised',
                'color' => $cat?->color ?? '#319B79',
                'icon' => $cat?->icon ?? 'tag',
                'amount' => $sum,
                'count' => $group->count(),
                'share_pct' => (float) $totalExpense > 0 ? round(((float) $sum / (float) $totalExpense) * 100, 1) : 0.0,
                'subcategories' => $subcategoriesList,
            ];
        })->sortByDesc(fn ($c) => (float) $c['amount'])->values()->all();

        // Daily spending trend across the month
        $dailyTrend = [];
        $highestDay = ['date' => '—', 'amount' => 0.0];
        $daysInMonth = $monthStart->daysInMonth;

        for ($d = 1; $d <= $daysInMonth; $d++) {
            $curr = Carbon::createFromDate($year, $month, $d);
            $dStr = $curr->toDateString();
            $dayTxs = $expenses->filter(fn (Transaction $t) => $t->transaction_date->toDateString() === $dStr);
            $dSum = '0.00';
            foreach ($dayTxs as $t) {
                $dSum = self::add($dSum, (string) $t->amount);
            }
            if ((float) $dSum > $highestDay['amount']) {
                $highestDay = ['date' => $curr->format('D, d M Y'), 'amount' => (float) $dSum];
            }
            $dailyTrend[] = [
                'day' => $d,
                'date' => $dStr,
                'label' => $curr->format('d M'),
                'amount' => (float) $dSum,
            ];
        }

        $explorer = $this->getSpendingBreakdownExplorer($user, $year, $month);

        $budget = Budget::where('user_id', $user->id)
            ->where('period_type', 'monthly')
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        $budgetLimit = $budget ? (float) $budget->total_limit : (float) $user->overall_monthly_budget;
        $budgetUtilization = $budgetLimit > 0 ? round(((float) $totalExpense / $budgetLimit) * 100, 1) : 0.0;

        return [
            'year' => $year,
            'month' => $month,
            'month_label' => $monthStart->format('F Y'),
            'total_expenditure' => $totalExpense,
            'total_income' => $totalIncome,
            'gross_cash_flow' => self::sub($totalIncome, $totalExpense),
            'net_cash_flow' => self::sub(self::sub($totalIncome, $totalExpense), $incomeFundedSavings),
            'monthly_savings' => $monthlySavings,
            'income_funded_savings' => $incomeFundedSavings,
            'free_capital_savings' => $freeCapitalSavings,
            'average_daily' => number_format((float) $totalExpense / $daysInMonth, 2, '.', ''),
            'highest_spending_day' => $highestDay,
            'budget_limit' => number_format($budgetLimit, 2, '.', ''),
            'budget_utilization_pct' => $budgetUtilization,
            'comparison' => self::percentageChange($totalExpense, $prevMonthExpense),
            'categories' => $categories,
            'daily_trend' => $dailyTrend,
            'weekly_summaries' => $explorer['weeks'],
        ];
    }

    /**
     * Yearly Overview Report (January to December) calculated strictly from recorded transactions.
     */
    public function getYearlyOverview(User $user, int $year): array
    {
        $yearStart = Carbon::createFromDate($year, 1, 1)->startOfYear()->toDateString();
        $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfYear()->toDateString();

        $prevYearStart = Carbon::createFromDate($year - 1, 1, 1)->startOfYear()->toDateString();
        $prevYearEnd = Carbon::createFromDate($year - 1, 12, 31)->endOfYear()->toDateString();

        $totalAnnualExpense = $this->sumTransactions($user->id, 'expense', $yearStart, $yearEnd);
        $totalAnnualIncome = $this->sumTransactions($user->id, 'income', $yearStart, $yearEnd);
        $annualSavings = $this->sumExplicitSavings($user->id, $yearStart, $yearEnd);
        $prevAnnualExpense = $this->sumTransactions($user->id, 'expense', $prevYearStart, $prevYearEnd);

        $monthlySeries = [];
        $highestMonth = ['name' => '—', 'month' => 1, 'amount' => -1.0];
        $lowestMonth = ['name' => '—', 'month' => 1, 'amount' => null];
        $activeMonthsCount = 0;

        for ($m = 1; $m <= 12; $m++) {
            $mStart = Carbon::createFromDate($year, $m, 1)->startOfMonth();
            $mEnd = $mStart->copy()->endOfMonth();

            $mExp = $this->sumTransactions($user->id, 'expense', $mStart->toDateString(), $mEnd->toDateString());
            $mInc = $this->sumTransactions($user->id, 'income', $mStart->toDateString(), $mEnd->toDateString());
            $mSav = $this->sumExplicitSavings($user->id, $mStart->toDateString(), $mEnd->toDateString());

            $expFloat = (float) $mExp;
            if ($expFloat > 0) {
                $activeMonthsCount++;
                if ($expFloat > $highestMonth['amount']) {
                    $highestMonth = ['name' => $mStart->format('F'), 'month' => $m, 'amount' => $expFloat];
                }
                if ($lowestMonth['amount'] === null || $expFloat < $lowestMonth['amount']) {
                    $lowestMonth = ['name' => $mStart->format('F'), 'month' => $m, 'amount' => $expFloat];
                }
            }

            $monthlySeries[] = [
                'month' => $m,
                'name' => $mStart->format('F'),
                'short_name' => $mStart->format('M'),
                'expense' => $mExp,
                'income' => $mInc,
                'savings' => $mSav,
                'net' => self::sub($mInc, $mExp),
            ];
        }

        $divisor = max(1, $activeMonthsCount);
        $avgMonthlyExpense = number_format((float) $totalAnnualExpense / $divisor, 2, '.', '');

        $yearExpenses = Transaction::with('category')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$yearStart, $yearEnd])
            ->get();

        $categoryDistribution = $yearExpenses->groupBy('expense_category_id')->map(function (Collection $group) use ($totalAnnualExpense) {
            $cat = $group->first()->category;
            $sum = '0.00';
            foreach ($group as $item) {
                $sum = self::add($sum, (string) $item->amount);
            }
            return [
                'category_id' => $cat?->id,
                'name' => $cat?->name ?? 'Uncategorised',
                'color' => $cat?->color ?? '#319B79',
                'amount' => $sum,
                'count' => $group->count(),
                'share_pct' => (float) $totalAnnualExpense > 0 ? round(((float) $sum / (float) $totalAnnualExpense) * 100, 1) : 0.0,
            ];
        })->sortByDesc(fn ($c) => (float) $c['amount'])->values()->all();

        return [
            'year' => $year,
            'total_expenses' => $totalAnnualExpense,
            'total_income' => $totalAnnualIncome,
            'net_cash_flow' => self::sub($totalAnnualIncome, $totalAnnualExpense),
            'annual_savings' => $annualSavings,
            'average_monthly_expense' => $avgMonthlyExpense,
            'highest_month' => $highestMonth,
            'lowest_month' => $lowestMonth ?? ['name' => '—', 'month' => 1, 'amount' => 0.0],
            'monthly_series' => $monthlySeries,
            'category_distribution' => $categoryDistribution,
            'yoy_comparison' => self::percentageChange($totalAnnualExpense, $prevAnnualExpense),
        ];
    }
}
