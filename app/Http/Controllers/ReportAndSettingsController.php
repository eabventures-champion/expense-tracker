<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Models\PaymentMethod;
use App\Models\Transaction;
use App\Services\UnifiedFinancialEngine;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportAndSettingsController extends Controller
{
    public function __construct(
        private readonly UnifiedFinancialEngine $engine
    ) {}

    /**
     * 10. REPORTS & EXPORTS
     */
    public function reports(Request $request)
    {
        $user = $this->currentUser();
        $now = now();
        $reportType = $request->query('report_type', 'monthly'); // daily, weekly, monthly, annual, category, income_vs_expense, budget
        $startDate = $request->query('start_date', $now->copy()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', $now->copy()->endOfMonth()->toDateString());
        $categoryId = $request->query('category_id');
        $year = (int) $request->query('year', $now->year);
        $month = (int) $request->query('month', $now->month);

        $query = Transaction::with(['category', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $transactions = $query->orderBy('transaction_date')->orderBy('transaction_time')->get();

        $totalExpenses = '0.00';
        $totalIncome = '0.00';
        foreach ($transactions as $tx) {
            if ($tx->type === 'expense') {
                $totalExpenses = UnifiedFinancialEngine::add($totalExpenses, (string) $tx->amount);
            } else {
                $totalIncome = UnifiedFinancialEngine::add($totalIncome, (string) $tx->amount);
            }
        }

        $netBalance = UnifiedFinancialEngine::sub($totalIncome, $totalExpenses);
        $explicitSavings = $this->engine->sumExplicitSavings($user->id, $startDate, $endDate);

        $categorySummary = $transactions->where('type', 'expense')
            ->groupBy('expense_category_id')
            ->map(function ($group) use ($totalExpenses) {
                $cat = $group->first()->category;
                $sum = '0.00';
                foreach ($group as $item) {
                    $sum = UnifiedFinancialEngine::add($sum, (string) $item->amount);
                }
                return [
                    'name' => $cat?->name ?? 'Uncategorised',
                    'color' => $cat?->color ?? '#319B79',
                    'count' => $group->count(),
                    'amount' => $sum,
                    'share_pct' => (float) $totalExpenses > 0 ? round(((float) $sum / (float) $totalExpenses) * 100, 1) : 0.0,
                ];
            })->sortByDesc(fn ($c) => (float) $c['amount'])->values()->all();

        $monthlyOverview = $this->engine->getMonthlyOverview($user, $year, $month);
        $yearlyOverview = $this->engine->getYearlyOverview($user, $year);

        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('reports.index', compact(
            'user',
            'reportType',
            'startDate',
            'endDate',
            'categoryId',
            'year',
            'month',
            'transactions',
            'totalExpenses',
            'totalIncome',
            'netBalance',
            'explicitSavings',
            'categorySummary',
            'monthlyOverview',
            'yearlyOverview',
            'categories',
            'paymentMethods'
        ));
    }

    /**
     * Export filtered transactions & summary to CSV, Excel (.xls SpreadsheetML), or printable PDF report view.
     */
    public function export(Request $request)
    {
        $user = $this->currentUser();
        $format = $request->query('format', 'csv'); // csv, excel, pdf
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->endOfMonth()->toDateString());
        $categoryId = $request->query('category_id');

        $query = Transaction::with(['category', 'subcategory', 'paymentMethod'])
            ->where('user_id', $user->id)
            ->whereBetween('transaction_date', [$startDate, $endDate]);

        if ($categoryId) {
            $query->where('expense_category_id', $categoryId);
        }

        $transactions = $query->orderBy('transaction_date')->orderBy('transaction_time')->get();

        $totalExpenses = '0.00';
        $totalIncome = '0.00';
        foreach ($transactions as $tx) {
            if ($tx->type === 'expense') {
                $totalExpenses = UnifiedFinancialEngine::add($totalExpenses, (string) $tx->amount);
            } else {
                $totalIncome = UnifiedFinancialEngine::add($totalIncome, (string) $tx->amount);
            }
        }
        $netBalance = UnifiedFinancialEngine::sub($totalIncome, $totalExpenses);

        if ($format === 'pdf') {
            return view('reports.pdf', compact(
                'user',
                'startDate',
                'endDate',
                'transactions',
                'totalExpenses',
                'totalIncome',
                'netBalance'
            ));
        }

        if ($format === 'excel') {
            $fileName = "CediLedger_Financial_Report_{$startDate}_to_{$endDate}.xls";
            $content = view('reports.excel_xml', compact(
                'user',
                'startDate',
                'endDate',
                'transactions',
                'totalExpenses',
                'totalIncome',
                'netBalance'
            ))->render();

            return response($content, 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        // Default CSV Stream
        $fileName = "CediLedger_Transactions_{$startDate}_to_{$endDate}.csv";
        return new StreamedResponse(function () use ($transactions, $user, $totalIncome, $totalExpenses, $netBalance, $startDate, $endDate) {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['CEDILEDGER PERSONAL FINANCIAL INTELLIGENCE REPORT']);
            fputcsv($handle, ['Reporting Period', "{$startDate} to {$endDate}", 'Currency', $user->currency]);
            fputcsv($handle, ['Total Income', $totalIncome, 'Total Expenses', $totalExpenses, 'Net Cash Flow', $netBalance]);
            fputcsv($handle, []);
            fputcsv($handle, ['Date', 'Time', 'Reference', 'Title', 'Type', 'Category', 'Subcategory', 'Payment Method', 'Merchant / Source', 'Amount (' . $user->currency . ')']);

            foreach ($transactions as $tx) {
                fputcsv($handle, [
                    $tx->transaction_date->format('Y-m-d'),
                    $tx->transaction_time ?? '',
                    $tx->reference_code,
                    $tx->title,
                    strtoupper($tx->type),
                    $tx->category?->name ?? 'Uncategorised',
                    $tx->subcategory?->name ?? '',
                    $tx->paymentMethod?->name ?? '',
                    $tx->merchant_or_source ?? '',
                    number_format((float) $tx->amount, 2, '.', ''),
                ]);
            }
            fclose($handle);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }

    /**
     * 11. SETTINGS & PAYMENT METHODS
     */
    public function settings()
    {
        $user = $this->currentUser();
        $categories = ExpenseCategory::with('subcategories')
            ->where('user_id', $user->id)
            ->orderBy('sort_order')
            ->get();
        $paymentMethods = PaymentMethod::where('user_id', $user->id)->get();

        return view('settings.index', compact('user', 'categories', 'paymentMethods'));
    }

    public function updateSettings(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150'],
            'currency' => ['required', 'string', 'max:10'],
            'monthly_income_target' => ['required', 'numeric', 'min:0'],
            'overall_monthly_budget' => ['required', 'numeric', 'min:0'],
            'weekly_spending_limit' => ['required', 'numeric', 'min:0'],
            'yearly_spending_target' => ['required', 'numeric', 'min:0'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'currency' => $validated['currency'],
            'monthly_income_target' => number_format((float) $validated['monthly_income_target'], 2, '.', ''),
            'overall_monthly_budget' => number_format((float) $validated['overall_monthly_budget'], 2, '.', ''),
            'weekly_spending_limit' => number_format((float) $validated['weekly_spending_limit'], 2, '.', ''),
            'yearly_spending_target' => number_format((float) $validated['yearly_spending_target'], 2, '.', ''),
        ]);

        return back()->with('success', 'Profile preferences, default currency, and financial thresholds updated.');
    }

    public function storePaymentMethod(Request $request)
    {
        $user = $this->currentUser();
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'type' => ['required', 'in:mobile_money,bank,card,cash,wallet'],
            'provider' => ['nullable', 'string', 'max:100'],
            'account_reference' => ['nullable', 'string', 'max:80'],
            'color' => ['required', 'string', 'max:20'],
        ]);

        PaymentMethod::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'type' => $validated['type'],
            'provider' => $validated['provider'] ?? null,
            'account_reference' => $validated['account_reference'] ?? null,
            'color' => $validated['color'],
            'is_default' => false,
        ]);

        return back()->with('success', 'Payment method added.');
    }

    public function destroyPaymentMethod(PaymentMethod $paymentMethod)
    {
        $user = $this->currentUser();
        abort_if($paymentMethod->user_id !== $user->id, 403);

        $paymentMethod->delete();
        return back()->with('success', 'Payment method removed.');
    }
}
