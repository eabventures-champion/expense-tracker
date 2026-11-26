<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class SpendingInsightsService
{
    public function __construct(
        private readonly UnifiedFinancialEngine $engine
    ) {}

    /**
     * Generate data-backed financial insights strictly from actual database transactions.
     */
    public function generateInsights(User $user, ?Carbon $referenceDate = null): array
    {
        $now = ($referenceDate ?? Carbon::now())->copy();
        $currency = $user->currency ?: 'GH¢';

        $monthly = $this->engine->getMonthlyOverview($user, (int) $now->year, (int) $now->month);
        $weekly = $this->engine->getWeeklyOverview($user, $now->toDateString());

        $insights = [];

        // 1. Month-over-Month spending movement
        $momPct = $monthly['comparison']['percentage'];
        $momDir = $monthly['comparison']['direction'];
        if ($momDir === 'up') {
            $insights[] = [
                'title' => 'Month-on-Month Spending Increase',
                'headline' => "Your spending increased by {$momPct}% compared to last month.",
                'detail' => "Recorded {$currency}" . number_format((float) $monthly['total_expenditure'], 2) . " in {$now->format('F')} vs {$currency}" . number_format((float) $monthly['comparison']['previous'], 2) . " last month.",
                'badge' => "+{$momPct}% MoM",
                'accent' => '#E66D55', // Coral
                'bg' => '#29243D',
                'category' => 'Month-to-Month Change',
                'metrics' => [
                    ['label' => 'This Month', 'value' => $currency . number_format((float) $monthly['total_expenditure'], 2)],
                    ['label' => 'Last Month', 'value' => $currency . number_format((float) $monthly['comparison']['previous'], 2)],
                    ['label' => 'Variance', 'value' => '+' . $momPct . '%'],
                ],
            ];
        } elseif ($momDir === 'down') {
            $absPct = abs($momPct);
            $insights[] = [
                'title' => 'Month-on-Month Spending Reduction',
                'headline' => "Your spending decreased by {$absPct}% compared to last month.",
                'detail' => "Total expenditure this month stands at {$currency}" . number_format((float) $monthly['total_expenditure'], 2) . ", down from {$currency}" . number_format((float) $monthly['comparison']['previous'], 2) . ".",
                'badge' => "-{$absPct}% Saved",
                'accent' => '#319B79', // Emerald
                'bg' => '#29243D',
                'category' => 'Month-to-Month Change',
                'metrics' => [
                    ['label' => 'This Month', 'value' => $currency . number_format((float) $monthly['total_expenditure'], 2)],
                    ['label' => 'Last Month', 'value' => $currency . number_format((float) $monthly['comparison']['previous'], 2)],
                    ['label' => 'Saved', 'value' => '-' . $absPct . '%'],
                ],
            ];
        }

        // 2. Highest Spending Category This Month
        if (!empty($monthly['categories'])) {
            $topCat = $monthly['categories'][0];
            $insights[] = [
                'title' => 'Primary Cost Driver',
                'headline' => "{$topCat['name']} is your highest spending category this month.",
                'detail' => "Accounting for {$topCat['share_pct']}% of total monthly expenditure across {$topCat['count']} transactions.",
                'badge' => "{$topCat['share_pct']}% Share",
                'accent' => '#E9B94E', // Mustard
                'bg' => '#292B30',
                'category' => 'High-Expenditure Category',
                'metrics' => [
                    ['label' => 'Category Spend', 'value' => $currency . number_format((float) $topCat['amount'], 2)],
                    ['label' => 'Monthly Share', 'value' => $topCat['share_pct'] . '%'],
                    ['label' => 'Frequency', 'value' => $topCat['count'] . ' tx'],
                ],
            ];
        }

        // 3. Weekly Food & Groceries or Top Weekly Category Insight
        $foodThisWeek = collect($weekly['category_distribution'])->firstWhere('name', 'Food & Groceries');
        if ($foodThisWeek) {
            $insights[] = [
                'title' => 'Weekly Food & Groceries Velocity',
                'headline' => "You spent {$currency}" . number_format((float) $foodThisWeek['amount'], 2) . " on Food & Groceries this week.",
                'detail' => "Active week horizon: {$weekly['week_label']}.",
                'badge' => 'Weekly Habit',
                'accent' => '#319B79',
                'bg' => '#29243D',
                'category' => 'Spending Pattern',
                'metrics' => [
                    ['label' => 'Weekly Spend', 'value' => $currency . number_format((float) $foodThisWeek['amount'], 2)],
                    ['label' => 'Share of Week', 'value' => $foodThisWeek['share_pct'] . '%'],
                    ['label' => 'Items', 'value' => $foodThisWeek['count'] . ' tx'],
                ],
            ];
        }

        // 4. Average Daily Expenditure
        $insights[] = [
            'title' => 'Daily Burn Rate',
            'headline' => "Your average daily expenditure this month is {$currency}" . number_format((float) $monthly['average_daily'], 2) . ".",
            'detail' => "Peak daily spend was recorded on {$monthly['highest_spending_day']['date']}.",
            'badge' => 'Daily Average',
            'accent' => '#5377B8', // Muted Blue
            'bg' => '#292B30',
            'category' => 'Spending Pattern',
            'metrics' => [
                ['label' => 'Daily Avg', 'value' => $currency . number_format((float) $monthly['average_daily'], 2)],
                ['label' => 'Peak Day', 'value' => $currency . number_format((float) $monthly['highest_spending_day']['amount'], 2)],
                ['label' => 'Peak Date', 'value' => $monthly['highest_spending_day']['date']],
            ],
        ];

        // 5. Exceeded or Approaching Category Budgets
        $activeBudget = Budget::with('categoryAllocations.category')
            ->where('user_id', $user->id)
            ->where('period_type', 'monthly')
            ->where('year', (int) $now->year)
            ->where('month', (int) $now->month)
            ->first();

        if ($activeBudget) {
            $catSpendMap = collect($monthly['categories'])->keyBy('category_id');
            foreach ($activeBudget->categoryAllocations as $alloc) {
                $spent = (float) ($catSpendMap[$alloc->expense_category_id]['amount'] ?? 0);
                $limit = (float) $alloc->allocated_amount;
                if ($limit > 0 && $spent > $limit) {
                    $exceededBy = $spent - $limit;
                    $utilPct = round(($spent / $limit) * 100, 1);
                    $catName = $alloc->category?->name ?? 'Category';
                    $insights[] = [
                        'title' => "{$catName} Limit Exceeded",
                        'headline' => "Exceeded monthly {$catName} budget by {$currency}" . number_format($exceededBy, 2) . ".",
                        'detail' => "Utilization is at {$utilPct}% of your monthly allocation.",
                        'badge' => 'Over Budget',
                        'accent' => '#E66D55',
                        'bg' => '#29243D',
                        'category' => 'Budget Alert',
                        'metrics' => [
                            ['label' => 'Planned Limit', 'value' => $currency . number_format($limit, 2)],
                            ['label' => 'Actual Spend', 'value' => $currency . number_format($spent, 2)],
                            ['label' => 'Exceeded By', 'value' => '+' . $currency . number_format($exceededBy, 2)],
                        ],
                    ];
                }
            }
        }

        // 6. 3-Month Subscription / Category Trend Detection
        $threeMonthsSubscriptions = [];
        for ($i = 2; $i >= 0; $i--) {
            $mDate = $now->copy()->subMonthsNoOverflow($i);
            $mStart = $mDate->copy()->startOfMonth()->toDateString();
            $mEnd = $mDate->copy()->endOfMonth()->toDateString();

            $subAmount = Transaction::where('user_id', $user->id)
                ->where('type', 'expense')
                ->whereHas('category', fn ($q) => $q->where('name', 'Subscriptions'))
                ->whereBetween('transaction_date', [$mStart, $mEnd])
                ->sum('amount');

            $threeMonthsSubscriptions[] = [
                'month' => $mDate->format('M'),
                'amount' => (float) $subAmount,
            ];
        }

        if (
            count($threeMonthsSubscriptions) === 3 &&
            $threeMonthsSubscriptions[2]['amount'] > $threeMonthsSubscriptions[1]['amount'] &&
            $threeMonthsSubscriptions[1]['amount'] >= $threeMonthsSubscriptions[0]['amount']
        ) {
            $insights[] = [
                'title' => '3-Month Subscription Creep',
                'headline' => 'Subscriptions spending has risen steadily over the past 3 months.',
                'detail' => 'Consider auditing recurring digital tools and unused memberships.',
                'badge' => '3-Month Trend',
                'accent' => '#D96D9B', // Rose
                'bg' => '#292B30',
                'category' => 'Opportunity to Reduce',
                'metrics' => [
                    ['label' => $threeMonthsSubscriptions[0]['month'], 'value' => $currency . number_format($threeMonthsSubscriptions[0]['amount'], 2)],
                    ['label' => $threeMonthsSubscriptions[1]['month'], 'value' => $currency . number_format($threeMonthsSubscriptions[1]['amount'], 2)],
                    ['label' => $threeMonthsSubscriptions[2]['month'], 'value' => $currency . number_format($threeMonthsSubscriptions[2]['amount'], 2)],
                ],
            ];
        }

        // 7. Repeated Merchant / Habit Detection
        $repeatedCategories = Transaction::with('category')
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString()])
            ->get()
            ->groupBy('expense_category_id')
            ->filter(fn ($grp) => $grp->count() >= 2)
            ->sortByDesc(fn ($grp) => $grp->count())
            ->first();

        if ($repeatedCategories) {
            $cName = $repeatedCategories->first()->category?->name ?? 'General';
            $cnt = $repeatedCategories->count();
            $sum = (float) $repeatedCategories->sum('amount');
            $avgTx = $cnt > 0 ? $sum / $cnt : 0;
            $insights[] = [
                'title' => 'High-Frequency Category Activity',
                'headline' => "Recorded {$cnt} separate transactions in {$cName} this month.",
                'detail' => "Consolidating frequent purchases into planned bulk orders can reduce impulse spending.",
                'badge' => "{$cnt}x Frequency",
                'accent' => '#E9B94E',
                'bg' => '#29243D',
                'category' => 'Repeated Expenses',
                'metrics' => [
                    ['label' => 'Transactions', 'value' => $cnt . ' orders'],
                    ['label' => 'Total Spend', 'value' => $currency . number_format($sum, 2)],
                    ['label' => 'Avg / Order', 'value' => $currency . number_format($avgTx, 2)],
                ],
            ];
        }

        return $insights;
    }
}
