<?php

namespace App\Http\Controllers;

use App\Models\AppNotification;
use App\Models\Budget;
use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use App\Services\SpendingInsightsService;
use App\Services\UnifiedFinancialEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly UnifiedFinancialEngine $engine,
        private readonly SpendingInsightsService $insightsService
    ) {}

    public function index(Request $request)
    {
        $user = $this->currentUser();

        $now = now();
        $selectedDate = $request->query('date', $now->toDateString());
        $referenceCarbon = Carbon::parse($selectedDate);
        $selectedYear = (int) $request->query('year', $referenceCarbon->year);
        $selectedMonth = (int) $request->query('month', $referenceCarbon->month);
        $activePeriod = $request->query('period', 'monthly'); // daily, weekly, monthly, quarterly, yearly

        $summary = $this->engine->getExecutiveSummary($user, $referenceCarbon);
        $explorer = $this->engine->getSpendingBreakdownExplorer($user, $selectedYear, $selectedMonth);
        $monthlyOverview = $this->engine->getMonthlyOverview($user, $selectedYear, $selectedMonth);
        $weeklyOverview = $this->engine->getWeeklyOverview($user, $selectedDate);
        $yearlyOverview = $this->engine->getYearlyOverview($user, $selectedYear);
        $insights = $this->insightsService->generateInsights($user, $referenceCarbon);

        $allUserRecentTransactions = Transaction::with(['category', 'subcategory', 'paymentMethod', 'receipts'])
            ->where('user_id', $user->id)
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->get();

        $recentTransactions = $allUserRecentTransactions
            ->groupBy(fn (Transaction $t) => preg_replace('/\s+/', ' ', strtolower(trim((string) $t->title))) . '|' . $t->type)
            ->take(10)
            ->flatten(1)
            ->values();

        // Period-specific transaction lists for the clickable KPI card modals (Today, This Week, This Month, This Year, Monthly Income)
        $todayDateStr = $referenceCarbon->toDateString();
        $weekStartStr = $referenceCarbon->copy()->startOfWeek(Carbon::MONDAY)->toDateString();
        $weekEndStr = $referenceCarbon->copy()->endOfWeek(Carbon::SUNDAY)->toDateString();
        $monthStartStr = $referenceCarbon->copy()->startOfMonth()->toDateString();
        $monthEndStr = $referenceCarbon->copy()->endOfMonth()->toDateString();
        $yearStartStr = $referenceCarbon->copy()->startOfYear()->toDateString();
        $yearEndStr = $referenceCarbon->copy()->endOfYear()->toDateString();

        $kpiExpensesYear = Transaction::with(['category', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$yearStartStr, $yearEndStr])
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->get();

        $kpiExpensesMonth = $kpiExpensesYear->filter(function (Transaction $tx) use ($monthStartStr, $monthEndStr) {
            $d = $tx->transaction_date->toDateString();
            return $d >= $monthStartStr && $d <= $monthEndStr;
        })->values();

        $kpiExpensesWeek = $kpiExpensesYear->filter(function (Transaction $tx) use ($weekStartStr, $weekEndStr) {
            $d = $tx->transaction_date->toDateString();
            return $d >= $weekStartStr && $d <= $weekEndStr;
        })->values();

        $kpiExpensesToday = $kpiExpensesYear->filter(function (Transaction $tx) use ($todayDateStr) {
            return $tx->transaction_date->toDateString() === $todayDateStr;
        })->values();

        $kpiIncomeMonth = Transaction::with(['category', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$monthStartStr, $monthEndStr])
            ->orderByDesc('transaction_date')
            ->orderByDesc('transaction_time')
            ->orderByDesc('id')
            ->get();

        $activeBudget = Budget::with('categoryAllocations.category')
            ->where('user_id', $user->id)
            ->where('period_type', 'monthly')
            ->where('year', $selectedYear)
            ->where('month', $selectedMonth)
            ->first();

        $upcomingRecurring = RecurringTransaction::with(['category', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->orderBy('next_due_date')
            ->limit(5)
            ->get();

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();

        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();
        $notifications = AppNotification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(6)
            ->get();

        return view('dashboard', compact(
            'user',
            'selectedYear',
            'selectedMonth',
            'selectedDate',
            'activePeriod',
            'summary',
            'explorer',
            'monthlyOverview',
            'weeklyOverview',
            'yearlyOverview',
            'insights',
            'recentTransactions',
            'kpiExpensesToday',
            'kpiExpensesWeek',
            'kpiExpensesMonth',
            'kpiExpensesYear',
            'kpiIncomeMonth',
            'activeBudget',
            'upcomingRecurring',
            'categories',
            'paymentMethods',
            'notifications'
        ));
    }
}
