@extends('layouts.app')

@section('kicker', 'Reports & Exports')
@section('page_title', 'Financial Statements & Multi-Format Exports (PDF, Excel, CSV)')

@section('content')
<!-- FILTER & EXPORT TOOLBAR (COLLAPSED ON MOBILE BY DEFAULT) -->
<section class="section-block theme-ivory mobile-collapsible-card" id="reportsFilterFormCard">
    <div class="section-header reports-card-header" onclick="toggleCollapsibleCard('reportsFilterFormCard', 'mobile')">
        <div class="reports-header-top-row" style="display: flex; align-items: center; justify-content: space-between; gap: 8px; min-width: 0; flex: 1;">
            <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                <span class="collapsible-chevron-badge mono mobile-only-collapse-el">▸</span>
                <h2 class="section-title reports-card-title" style="min-width: 0;">Generate Custom Date-Range Financial Report</h2>
            </div>
            <button type="button"
                    class="btn btn-ivory btn-sm collapsible-toggle-btn mobile-only-collapse-el"
                    data-expand-label="Configure ▾"
                    data-hide-label="Hide ▴"
                    style="padding: 3px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700; flex-shrink: 0;">
                Configure ▾
            </button>
        </div>
        <div class="reports-export-actions" style="display: flex; gap: 8px; flex-wrap: wrap;" onclick="event.stopPropagation()">
            <a href="{{ route('reports.export', ['format' => 'csv', 'start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId]) }}"
               class="btn btn-emerald btn-sm">⬇ Export CSV</a>
            <a href="{{ route('reports.export', ['format' => 'excel', 'start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId]) }}"
               class="btn btn-mustard btn-sm">⬇ Export Excel (.xls)</a>
            <a href="{{ route('reports.export', ['format' => 'pdf', 'start_date' => $startDate, 'end_date' => $endDate, 'category_id' => $categoryId]) }}"
               target="_blank" class="btn btn-coral btn-sm">🖨 Printable PDF Statement ↗</a>
        </div>
    </div>
    <div class="section-body collapsible-card-body">
        <form method="GET" action="{{ route('reports.index') }}" class="form-grid reports-filter-form" style="align-items: end;">
            <div class="form-group reports-field-full">
                <label class="form-label">Report Template</label>
                <select name="report_type" class="form-control">
                    <option value="monthly" {{ $reportType === 'monthly' ? 'selected' : '' }}>Monthly Financial Report</option>
                    <option value="daily" {{ $reportType === 'daily' ? 'selected' : '' }}>Daily Expense Report</option>
                    <option value="weekly" {{ $reportType === 'weekly' ? 'selected' : '' }}>Weekly Expense Report</option>
                    <option value="annual" {{ $reportType === 'annual' ? 'selected' : '' }}>Annual Financial Report</option>
                    <option value="category" {{ $reportType === 'category' ? 'selected' : '' }}>Category Expenditure Report</option>
                    <option value="income_vs_expense" {{ $reportType === 'income_vs_expense' ? 'selected' : '' }}>Income vs Expense Report</option>
                    <option value="budget" {{ $reportType === 'budget' ? 'selected' : '' }}>Budget Performance Report</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Start Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control mono">
            </div>
            <div class="form-group">
                <label class="form-label">End Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control mono">
            </div>
            <div class="form-group reports-field-full">
                <label class="form-label">Category Filter</label>
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group reports-field-full">
                <button type="submit" class="btn btn-plum" style="width: 100%; justify-content: center;">Generate Report</button>
            </div>
        </form>
    </div>
</section>

<!-- REPORT SUMMARY METRICS -->
<div class="kpi-grid">
    <div class="kpi-cell bg-emerald">
        <div class="kpi-label">Report Period Total Income</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$totalIncome, 2) }}</div>
        <div class="kpi-footer"><span>{{ $startDate }} → {{ $endDate }}</span></div>
    </div>
    <div class="kpi-cell bg-coral">
        <div class="kpi-label">Report Period Total Expenses</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$totalExpenses, 2) }}</div>
        <div class="kpi-footer"><span>{{ $transactions->where('type', 'expense')->count() }} expense items</span></div>
    </div>
    <div class="kpi-cell bg-blue">
        <div class="kpi-label">Net Cash Flow Position</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$netBalance, 2) }}</div>
        <div class="kpi-footer">
            <span>
                @if((float)($incomeFundedSavings ?? 0) > 0)
                    Gross before savings: {{ $user->currency }}{{ number_format((float)($grossBalance ?? $netBalance), 2) }}
                @else
                    Reconciled Ledger Balance
                @endif
            </span>
        </div>
    </div>
    <div class="kpi-cell bg-mustard">
        <div class="kpi-label">Explicit Savings Recorded</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$explicitSavings, 2) }}</div>
        <div class="kpi-footer">
            <span>From Income: {{ $user->currency }}{{ number_format((float)($incomeFundedSavings ?? 0), 2) }} • Free: {{ $user->currency }}{{ number_format((float)($freeCapitalSavings ?? 0), 2) }}</span>
        </div>
    </div>
</div>

<!-- FULL-WIDTH ITEMISED TRANSACTION STATEMENT CARD -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h3 class="section-title">Itemised Transaction Statement ({{ $startDate }} to {{ $endDate }})</h3>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">{{ $transactions->count() }} reconciled records in selected statement period</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-plum btn-sm" onclick="openCategorySummaryModal()">
                Category Expenditure Summary ({{ count($categorySummary) }})
            </button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Ref</th>
                    <th>Description</th>
                    <th>Category</th>
                    <th>Method</th>
                    <th>Type</th>
                    <th style="text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                    <tr>
                        <td class="mono" style="font-size: 11.5px; white-space: nowrap;">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                        <td class="mono" style="font-size: 11px; color: var(--text-muted); white-space: nowrap;">{{ $tx->reference_code }}</td>
                        <td><strong>{{ $tx->title }}</strong></td>
                        <td style="white-space: nowrap;">
                            <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                <span class="cat-swatch" style="background-color: {{ $tx->category?->color ?? '#2D9F78' }};"></span>
                                {{ $tx->category?->name ?? 'General' }}
                            </span>
                        </td>
                        <td style="color: var(--text-muted); white-space: nowrap;">{{ $tx->paymentMethod?->name ?? '—' }}</td>
                        <td style="white-space: nowrap;">
                            <span class="mono" style="font-size: 10.5px; font-weight: 700; padding: 3px 8px; border-radius: 9999px; background-color: {{ $tx->type === 'income' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                {{ $tx->type }}
                            </span>
                        </td>
                        <td class="mono" style="text-align: right; font-weight: 700; white-space: nowrap; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                            {{ $tx->type === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format((float)$tx->amount, 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">No transactions recorded in this date range.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL: CATEGORY EXPENDITURE SUMMARY -->
<div id="categorySummaryModal" class="modal-backdrop" onclick="if(event.target === this) closeCategorySummaryModal()">
    <div class="modal-box" style="max-width: 600px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Statement Breakdown ({{ $startDate }} → {{ $endDate }})</div>
                <h3 style="font-size: 16px;">Category Expenditure Summary</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeCategorySummaryModal()">✕ Close</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
            @forelse($categorySummary as $cs)
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px;">
                        <span>
                            <span class="cat-swatch" style="background-color: {{ $cs['color'] }}; margin-right: 6px;"></span>
                            <strong>{{ $cs['name'] }}</strong>
                            <span style="color: var(--text-muted); font-size: 12px;">({{ $cs['count'] }}x)</span>
                        </span>
                        <span class="mono" style="font-weight: 700;">
                            {{ $user->currency }}{{ number_format((float)$cs['amount'], 2) }}
                            <span style="color: var(--text-muted); font-weight: 500;">({{ $cs['share_pct'] }}%)</span>
                        </span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width: {{ min(100, $cs['share_pct']) }}%; background-color: {{ $cs['color'] }};"></div>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 20px; color: var(--text-muted);">No expense categories recorded in this report period.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCategorySummaryModal() {
        document.getElementById('categorySummaryModal')?.classList.add('open');
    }

    function closeCategorySummaryModal() {
        document.getElementById('categorySummaryModal')?.classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCategorySummaryModal();
        }
    });
</script>
@endpush
