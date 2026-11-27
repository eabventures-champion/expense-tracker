@extends('layouts.app')

@section('kicker', 'Dashboard')
@section('page_title', 'Personal Financial Intelligence & Spending Overview')

@section('content')
<!-- DATE & PERIOD FILTER BAR (COLLAPSIBLE IN MOBILE VIEW) -->
<div class="section-block theme-ivory horizon-bar-card" id="activeHorizonCard" style="padding: 14px 20px;">
    <div class="horizon-mobile-toggle-row" onclick="toggleMobileHorizonBar()">
        <div style="display: flex; align-items: center; gap: 7px; min-width: 0;">
            <span id="horizonMobileChevron" class="mono" style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background-color: var(--ivory); border: 1px solid var(--ivory-border); font-size: 11px; font-weight: 800; color: var(--plum); flex-shrink: 0;">
                ▸
            </span>
            <span style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted); font-weight: 800; white-space: nowrap;">
                Active Horizon
            </span>
        </div>
        <button type="button" id="horizonMobileToggleBtn" class="btn btn-ivory btn-sm" style="padding: 3px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700; flex-shrink: 0;">
            Change Date ▾
        </button>
    </div>

    <form method="GET" action="{{ route('dashboard') }}" id="horizonFilterForm" class="horizon-filter-form" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
        <div class="horizon-desktop-info" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <span class="horizon-desktop-label" style="font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); font-weight: 700;">Active Ledger Horizon</span>
            <span class="mono horizon-desktop-date" style="background-color: var(--ivory); color: var(--plum); padding: 5px 12px; border-radius: 9999px; border: 1px solid var(--ivory-border); font-size: 12px; font-weight: 600;">{{ $summary['reference_label'] }}</span>
            <a href="{{ route('tracking.index', ['tab' => 'explorer', 'year' => $selectedYear, 'month' => $selectedMonth]) }}" class="btn btn-emerald btn-sm">Open Period Explorer →</a>
        </div>
        <div class="horizon-controls-group" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <label style="font-size: 12px; font-weight: 600; color: var(--text-muted);">Reference Date:</label>
            <input type="date" name="date" value="{{ $selectedDate }}" class="form-control mono" style="padding: 6px 10px; font-size: 12px;">
            <select name="month" class="form-control" style="padding: 6px 10px; font-size: 12px;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $selectedMonth === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate($selectedYear, $m, 1)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-control mono" style="padding: 6px 10px; font-size: 12px;">
                <option value="2026" {{ $selectedYear === 2026 ? 'selected' : '' }}>2026</option>
                <option value="2025" {{ $selectedYear === 2025 ? 'selected' : '' }}>2025</option>
            </select>
            <button type="submit" class="btn btn-plum btn-sm">Apply Horizon</button>
        </div>
    </form>
</div>

<!-- 8 KEY FINANCIAL INDICATORS WITH PERIOD COMPARISONS -->
<div class="kpi-grid kpi-grid-dashboard">
    <!-- 1. Total Expenses Today (CLICKABLE TO VIEW ITEMS MODAL) -->
    <div class="kpi-cell bg-plum" onclick="openKpiBreakdownModal('today')" style="cursor: pointer;" title="Click to view Expenses Today items">
        <div class="kpi-top">
            <span class="kpi-label">Expenses Today</span>
            <span class="delta-tag {{ $summary['expenses_today']['direction'] === 'up' ? 'up-bad' : 'down-good' }}">
                {{ $summary['expenses_today']['direction'] === 'up' ? '▲' : '▼' }} {{ abs($summary['expenses_today']['percentage']) }}%
            </span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['expenses_today']['current'], 2) }}</div>
        <div class="kpi-footer">
            <span>{{ $summary['expenses_today']['comparison_label'] }}</span>
            <span class="mono" style="color: var(--emerald-dark); font-weight: 700;">View ({{ $kpiExpensesToday->count() }}) ↗</span>
        </div>
    </div>

    <!-- 2. Total Expenses This Week (CLICKABLE TO VIEW ITEMS MODAL) -->
    <div class="kpi-cell bg-charcoal" onclick="openKpiBreakdownModal('week')" style="cursor: pointer;" title="Click to view Expenses This Week items">
        <div class="kpi-top">
            <span class="kpi-label">Expenses This Week</span>
            <span class="delta-tag {{ $summary['expenses_week']['direction'] === 'up' ? 'up-bad' : 'down-good' }}">
                {{ $summary['expenses_week']['direction'] === 'up' ? '▲' : '▼' }} {{ abs($summary['expenses_week']['percentage']) }}%
            </span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['expenses_week']['current'], 2) }}</div>
        <div class="kpi-footer">
            <span>{{ $summary['expenses_week']['comparison_label'] }}</span>
            <span class="mono" style="color: var(--mustard-dark); font-weight: 700;">View ({{ $kpiExpensesWeek->count() }}) ↗</span>
        </div>
    </div>

    <!-- 3. Total Expenses This Month (CLICKABLE TO VIEW ITEMS MODAL) -->
    <div class="kpi-cell bg-coral" onclick="openKpiBreakdownModal('month')" style="cursor: pointer;" title="Click to view Expenses This Month items">
        <div class="kpi-top">
            <span class="kpi-label">Expenses This Month</span>
            <span class="delta-tag {{ $summary['expenses_month']['direction'] === 'up' ? 'up-bad' : 'down-good' }}">
                {{ $summary['expenses_month']['direction'] === 'up' ? '▲' : '▼' }} {{ abs($summary['expenses_month']['percentage']) }}%
            </span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['expenses_month']['current'], 2) }}</div>
        <div class="kpi-footer">
            <span>{{ $summary['expenses_month']['comparison_label'] }}</span>
            <span class="mono" style="color: var(--coral-dark); font-weight: 700;">View ({{ $kpiExpensesMonth->count() }}) ↗</span>
        </div>
    </div>

    <!-- 4. Total Expenses This Year (CLICKABLE TO VIEW ITEMS MODAL) -->
    <div class="kpi-cell bg-plum" onclick="openKpiBreakdownModal('year')" style="cursor: pointer;" title="Click to view Expenses This Year items">
        <div class="kpi-top">
            <span class="kpi-label">Expenses This Year ({{ $summary['year_label'] }})</span>
            <span class="delta-tag {{ $summary['expenses_year']['direction'] === 'up' ? 'up-bad' : 'down-good' }}">
                {{ $summary['expenses_year']['direction'] === 'up' ? '▲' : '▼' }} {{ abs($summary['expenses_year']['percentage']) }}%
            </span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['expenses_year']['current'], 2) }}</div>
        <div class="kpi-footer">
            <span>{{ $summary['expenses_year']['comparison_label'] }}</span>
            <span class="mono" style="color: var(--plum); font-weight: 700;">View ({{ $kpiExpensesYear->count() }}) ↗</span>
        </div>
    </div>

    <!-- 5. Total Income (CLICKABLE TO VIEW ITEMS MODAL) -->
    <div class="kpi-cell bg-emerald" onclick="openKpiBreakdownModal('income')" style="cursor: pointer;" title="Click to view Monthly Income items">
        <div class="kpi-top">
            <span class="kpi-label">{{ $summary['income_month']['label'] }}</span>
            <span class="delta-tag up-good">
                {{ $summary['income_month']['direction'] === 'up' ? '▲' : '▼' }} {{ abs($summary['income_month']['percentage']) }}%
            </span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['income_month']['current'], 2) }}</div>
        <div class="kpi-footer">
            <span>YTD: {{ $user->currency }}{{ number_format((float)$summary['income_month']['annual_income'], 2) }}</span>
            <span class="mono" style="color: var(--emerald-dark); font-weight: 700;">View ({{ $kpiIncomeMonth->count() }}) ↗</span>
        </div>
    </div>

    <!-- 6. Current Balance (Net Cash Flow) -->
    <div class="kpi-cell bg-blue">
        <div class="kpi-top">
            <span class="kpi-label">Monthly Net Balance</span>
            <span class="delta-tag neutral">
                {{ $summary['current_balance']['direction'] === 'up' ? '▲' : '▼' }} {{ abs($summary['current_balance']['percentage']) }}%
            </span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['current_balance']['current'], 2) }}</div>
        <div class="kpi-footer" style="flex-direction: column; align-items: flex-start; gap: 3px;">
            <span>{{ $summary['current_balance']['comparison_label'] }}</span>
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                @if((float)($summary['current_balance']['income_funded_savings'] ?? 0) > 0)
                    <span class="mono" style="font-size: 10.5px; color: var(--text-muted);">
                        Gross before savings: {{ $user->currency }}{{ number_format((float)$summary['current_balance']['gross_before_savings'], 2) }}
                    </span>
                @endif
                <span class="mono" style="color: {{ (float)$summary['current_balance']['current'] >= 0 ? 'var(--blue)' : 'var(--coral-dark)' }}; font-weight: 700;">
                    {{ (float)$summary['current_balance']['current'] >= 0 ? 'Surplus' : 'Deficit' }}
                </span>
            </div>
        </div>
    </div>

    <!-- 7. Total Savings (Explicitly Recorded) -->
    <div class="kpi-cell bg-mustard">
        <div class="kpi-top">
            <span class="kpi-label">Explicit Savings</span>
            <span class="delta-tag neutral">+{{ $user->currency }}{{ number_format((float)$summary['total_savings']['monthly_added'], 0) }} {{ substr($summary['month_label'], 0, 3) }}</span>
        </div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$summary['total_savings']['cumulative'], 2) }}</div>
        <div class="kpi-footer" style="flex-direction: column; align-items: flex-start; gap: 3px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; font-size: 11px;">
                <span>From Income: <strong class="mono">{{ $user->currency }}{{ number_format((float)($summary['total_savings']['income_funded_month'] ?? 0), 2) }}</strong></span>
                <span>Free Capital: <strong class="mono">{{ $user->currency }}{{ number_format((float)($summary['total_savings']['free_capital_month'] ?? 0), 2) }}</strong></span>
            </div>
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <span>Recorded Vaults</span>
                <a href="{{ route('savings.index') }}">Manage →</a>
            </div>
        </div>
    </div>

    <!-- 8. Monthly Budget Remaining -->
    <div class="kpi-cell bg-ivory">
        <div class="kpi-top">
            <span class="kpi-label">Monthly Budget Left</span>
            <span class="delta-tag {{ $summary['budget_remaining']['is_exceeded'] ? 'up-bad' : 'down-good' }}">
                {{ $summary['budget_remaining']['utilization_pct'] }}% Used
            </span>
        </div>
        <div class="kpi-amount" style="color: {{ $summary['budget_remaining']['is_exceeded'] ? '#E66D55' : '#2D9F78' }} !important;">
            {{ $user->currency }}{{ number_format((float)$summary['budget_remaining']['remaining'], 2) }}
        </div>
        <div class="kpi-footer">
            <span>Limit: {{ $user->currency }}{{ number_format((float)$summary['budget_remaining']['limit'], 2) }}</span>
            <a href="{{ route('budgets.index') }}">Adjust →</a>
        </div>
    </div>
</div>

<!-- SPENDING BREAKDOWN EXPLORER (MONTH -> WEEKS 1..4 -> DAILY DRILL-DOWN) -->
<div class="grid-sidebar-right">
    <section class="section-block theme-charcoal">
        <div class="section-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.07em;">Interactive Drill-Down</div>
                <h2 class="section-title">Spending Breakdown Explorer — {{ $explorer['month_name'] }}</h2>
            </div>
            <span style="background-color: rgba(45, 159, 120, 0.2); color: #5CE0B5; padding: 4px 12px; border-radius: 9999px; font-size: 11px; font-weight: 600;">
                {{ $explorer['transaction_count'] }} Reconciled Transactions
            </span>
        </div>
        <div class="section-body">
            <!-- Top Monthly Expenditure Header -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); flex-wrap: wrap; gap: 12px;">
                <div>
                    <div style="font-size: 12px; color: #A3A7BA; margin-bottom: 2px;">{{ $explorer['month_name'] }} Total Outflow</div>
                    <div class="mono" style="font-size: 28px; font-weight: 700; color: #FFFFFF;">
                        {{ $user->currency }}{{ number_format((float)$explorer['total_monthly_expenditure'], 2) }}
                    </div>
                </div>
                <div style="display: flex; gap: 6px; background-color: rgba(0,0,0,0.25); padding: 4px; border-radius: 10px;">
                    @foreach($explorer['weeks'] as $wNum => $wItem)
                        <button type="button"
                                class="btn btn-sm week-selector-btn {{ $wNum === 4 ? 'btn-emerald' : 'btn-plum' }}"
                                id="weekBtn_{{ $wNum }}"
                                onclick="selectExplorerWeek({{ $wNum }})">
                            Week {{ $wNum }}
                        </button>
                    @endforeach
                </div>
            </div>

            <!-- Weekly Breakdown Progress Bars -->
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <span style="font-size: 13px; font-weight: 600; color: #F7F8FA;">Weekly distribution</span>
                <span style="font-size: 11.5px; color: #A3A7BA;">Select a week to inspect Monday → Sunday ledger</span>
            </div>

            <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                @foreach($explorer['weeks'] as $wNum => $wData)
                    <div onclick="selectExplorerWeek({{ $wNum }})"
                         style="cursor: pointer; padding: 12px 14px; border-radius: 10px; background-color: rgba(255,255,255,0.03); border: 1px solid {{ $wNum === 4 ? '#2D9F78' : 'rgba(255,255,255,0.08)' }}; transition: all 0.15s ease;"
                         id="weekRow_{{ $wNum }}">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                            <div>
                                <strong style="color: #FFFFFF; font-size: 13px;">{{ $wData['title'] }}</strong>
                                <span style="font-size: 11.5px; color: #A3A7BA; margin-left: 8px;">{{ $wData['range_label'] }} • {{ $wData['transaction_count'] }} items ({{ $wData['share_of_month_pct'] }}%)</span>
                            </div>
                            <span class="mono" style="font-size: 14px; font-weight: 700; color: #FFFFFF;">
                                {{ $user->currency }}{{ number_format((float)$wData['total'], 2) }}
                            </span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $wData['bar_width_pct'] }}%; background-color: #2D9F78;"></div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Drill-down Container: Week -> Monday..Sunday -> Exact Transactions -->
            @foreach($explorer['weeks'] as $wNum => $wData)
                <div id="weekDrilldown_{{ $wNum }}" class="week-drilldown-panel" style="display: {{ $wNum === 4 ? 'block' : 'none' }}; background-color: rgba(0, 0, 0, 0.22); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 12px; padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; color: #E5A93C; font-weight: 700;">Active Week View</div>
                            <div style="font-size: 14px; font-weight: 700; color: #FFFFFF;">
                                {{ $wData['title'] }} ({{ $wData['range_label'] }})
                            </div>
                        </div>
                        <span class="mono" style="background-color: rgba(45, 159, 120, 0.2); color: #5CE0B5; padding: 4px 10px; border-radius: 9999px; font-size: 12px; font-weight: 700;">
                            Week Total: {{ $user->currency }}{{ number_format((float)$wData['total'], 2) }}
                        </span>
                    </div>

                    <!-- Monday..Sunday Pill Strip -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(105px, 1fr)); gap: 8px; margin-bottom: 16px;">
                        @foreach($wData['days'] as $dayItem)
                            <a href="{{ route('tracking.index', ['tab' => 'daily', 'date' => $dayItem['date']]) }}"
                               style="padding: 9px 10px; border-radius: 9px; background-color: {{ (float)$dayItem['total'] > 0 ? 'rgba(229, 169, 60, 0.1)' : 'rgba(255, 255, 255, 0.03)' }}; border: 1px solid {{ (float)$dayItem['total'] > 0 ? 'rgba(229, 169, 60, 0.4)' : 'rgba(255, 255, 255, 0.07)' }}; display: flex; flex-direction: column; gap: 3px;">
                                <span style="font-size: 10.5px; color: #A3A7BA; font-weight: 600;">{{ substr($dayItem['day_name'], 0, 3) }} • {{ substr($dayItem['formatted_date'], 0, 6) }}</span>
                                <span class="mono" style="font-size: 12.5px; font-weight: 700; color: {{ (float)$dayItem['total'] > 0 ? '#F5C469' : '#646A7A' }};">
                                    {{ $user->currency }}{{ number_format((float)$dayItem['total'], 2) }}
                                </span>
                            </a>
                        @endforeach
                    </div>

                    <!-- Exact Transactions inside the selected week (Compounded by Title) -->
                    <div class="table-wrap">
                        @php
                            $flatWeekTxs = collect();
                            foreach ($wData['days'] as $dayItem) {
                                foreach ($dayItem['transactions'] as $tx) {
                                    $flatWeekTxs->push(array_merge($tx, [
                                        'day_short' => $dayItem['day_short'],
                                        'formatted_date' => $dayItem['formatted_date'],
                                    ]));
                                }
                            }
                            $compoundedWeekTxs = $flatWeekTxs->groupBy(fn($t) => preg_replace('/\s+/', ' ', strtolower(trim((string) $t['title']))));
                        @endphp
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Day &amp; Date</th>
                                    <th>Transaction</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($compoundedWeekTxs as $wTxGroup)
                                    @php
                                        $tx = $wTxGroup->last();
                                        $wCount = $wTxGroup->count();
                                        $wTotal = $wTxGroup->sum(fn($i) => (float) $i['amount']);
                                        $wMin = $wTxGroup->min(fn($i) => (float) $i['amount']);
                                        $wMax = $wTxGroup->max(fn($i) => (float) $i['amount']);
                                        $wDates = $wTxGroup->map(fn($i) => $i['day_short'] . ' ' . substr($i['formatted_date'], 0, 6) . ' (' . $i['formatted_amount'] . ')')->implode(' • ');
                                    @endphp
                                    <tr>
                                        <td>
                                            <div class="mono" style="font-size: 11.5px; color: #F5C469; margin-bottom: 4px; white-space: nowrap;">
                                                {{ $tx['day_short'] }}, {{ $tx['formatted_date'] }}
                                                @if($wCount > 1)
                                                    <span style="background-color: rgba(230,109,85,0.22); color: #FF8A73; font-size: 9.5px; font-weight: 800; padding: 1.5px 6px; border-radius: 9999px; margin-left: 4px;">
                                                        {{ $wCount }}x
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="mono" style="font-weight: 800; font-size: 13px; color: #FF8A73; margin-bottom: 5px; white-space: nowrap;">
                                                {{ $user->currency }}{{ number_format($wTotal, 2) }}
                                                @if($wCount > 1)
                                                    <span style="font-size: 10px; color: #A3A7BA; font-weight: 600; margin-left: 4px;">
                                                        @if(abs($wMax - $wMin) > 0.005)
                                                            ({{ $wCount }}x • {{ $user->currency }}{{ number_format($wMin, 2) }}–{{ $user->currency }}{{ number_format($wMax, 2) }})
                                                        @else
                                                            ({{ $wCount }}x • {{ $user->currency }}{{ number_format($wMin, 2) }} each)
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                            <div>
                                                <span class="cat-badge" style="background-color: rgba(255,255,255,0.06); color: #FFFFFF; border-color: rgba(255,255,255,0.12); padding: 2px 8px; font-size: 10.5px;">
                                                    <span class="cat-swatch" style="background-color: {{ $tx['category_color'] }};"></span>
                                                    {{ $tx['category'] }}
                                                </span>
                                            </div>
                                            <div style="font-size: 11px; color: #A3A7BA; margin-top: 4px;">
                                                {{ $tx['payment_method'] }}
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="color: #FFFFFF;">{{ $tx['title'] }}</strong>
                                            @if($tx['subcategory'])
                                                <div style="font-size: 11px; color: #A3A7BA;">{{ $tx['subcategory'] }}</div>
                                            @endif
                                            @if($wCount > 1)
                                                <div class="mono" style="font-size: 10px; color: #5CE0B5; margin-top: 3px;">
                                                    Merged: {{ $wDates }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" style="text-align: center; padding: 16px; color: #A3A7BA;">No expenses recorded in this week block.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <!-- FINANCIAL INSIGHTS & SPENDING INTELLIGENCE PANEL -->
    <section class="section-block theme-ivory">
        <div class="section-header">
            <div>
                <div style="font-size: 10.5px; color: var(--coral); text-transform: uppercase; font-weight: 700; letter-spacing: 0.07em;">Automated Diagnostics</div>
                <h2 class="section-title">Spending Intelligence & Alerts</h2>
            </div>
            <a href="{{ route('insights.index') }}" class="btn btn-ivory btn-sm">All Insights →</a>
        </div>
        <div class="section-body" style="display: flex; flex-direction: column; gap: 12px;">
            @foreach(array_slice($insights, 0, 5) as $insight)
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-left: 4px solid {{ $insight['accent'] }}; border-radius: 10px; padding: 13px 15px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 5px;">
                        <span style="font-size: 10.5px; font-weight: 700; text-transform: uppercase; color: var(--text-muted);">
                            {{ $insight['category'] }}
                        </span>
                        <span class="mono" style="background-color: {{ $insight['accent'] }}; color: #FFFFFF; font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 9999px;">
                            {{ $insight['badge'] }}
                        </span>
                    </div>
                    <div style="font-weight: 700; font-size: 13px; color: var(--text-dark); margin-bottom: 3px;">
                        {{ $insight['headline'] }}
                    </div>
                    <div style="font-size: 12px; color: var(--text-muted);">
                        {{ $insight['detail'] }}
                    </div>
                </div>
            @endforeach
        </div>
    </section>
</div>

<!-- INTERACTIVE SPENDING ANALYTICS SUITE -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <div style="font-size: 10.5px; color: var(--blue); text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Analytics Suite</div>
            <h2 class="section-title">Interactive Spending Visualizations ({{ $monthlyOverview['month_label'] }} & {{ $selectedYear }})</h2>
        </div>
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <button type="button" class="btn btn-sm btn-plum" onclick="switchChartHorizon('daily')" id="chartTab_daily">Daily Trend</button>
            <button type="button" class="btn btn-sm btn-ivory" onclick="switchChartHorizon('weekly')" id="chartTab_weekly">Weekly Breakdown</button>
            <button type="button" class="btn btn-sm btn-ivory" onclick="switchChartHorizon('yearly')" id="chartTab_yearly">Yearly Overview</button>
            <button type="button" class="btn btn-sm btn-emerald" onclick="openChartPresentationModal('primary')" title="Open Fullscreen Presentation View">
                ⛶ Present Charts
            </button>
        </div>
    </div>
    <div class="section-body">
        <div class="grid-2">
            <!-- Chart Panel A: Switchable Daily / Weekly / Yearly Trend -->
            <div style="background-color: var(--ivory-alt); padding: 18px; border-radius: 12px; border: 1px solid var(--ivory-border); min-width: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; flex-wrap: wrap;">
                    <h3 id="primaryChartHeading" style="font-size: 14px; color: var(--text-dark);">Daily Spending Trend — {{ $monthlyOverview['month_label'] }}</h3>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="mono" style="font-size: 11px; color: var(--text-muted);">Live Telemetry</span>
                        <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 8px; font-size: 11px;" onclick="openChartPresentationModal('primary')" title="Expand Chart">
                            ⛶ Expand
                        </button>
                    </div>
                </div>
                <div class="dashboard-chart-box" style="position: relative; width: 100%; height: 250px; min-height: 250px; cursor: pointer;" onclick="openChartPresentationModal('primary')" title="Click to expand in fullscreen presentation mode">
                    <canvas id="primaryHorizonChart" style="display: block; width: 100%; height: 100%;"></canvas>
                </div>
            </div>

            <!-- Chart Panel B: Spending by Category Doughnut + Table -->
            <div style="background-color: var(--ivory-alt); padding: 18px; border-radius: 12px; border: 1px solid var(--ivory-border); min-width: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; flex-wrap: wrap;">
                    <h3 style="font-size: 14px; color: var(--text-dark);">Spending by Category — {{ $monthlyOverview['month_label'] }}</h3>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="mono" style="font-size: 11.5px; font-weight: 700; color: var(--emerald);">Total: {{ $user->currency }}{{ number_format((float)$monthlyOverview['total_expenditure'], 2) }}</span>
                        <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 8px; font-size: 11px;" onclick="openChartPresentationModal('category')" title="Expand Chart">
                            ⛶ Expand
                        </button>
                    </div>
                </div>
                <div class="category-chart-split-grid" style="display: grid; grid-template-columns: 165px minmax(0, 1fr); gap: 12px; align-items: center;">
                    <div class="dashboard-chart-box" style="position: relative; width: 100%; height: 210px; min-height: 210px; cursor: pointer;" onclick="openChartPresentationModal('category')" title="Click to expand in fullscreen presentation mode">
                        <canvas id="categoryDistributionChart" style="display: block; width: 100%; height: 100%;"></canvas>
                    </div>
                    <div style="max-height: 230px; overflow-y: auto; overflow-x: hidden; min-width: 0;">
                        <table class="data-table" style="font-size: 12px; width: 100%;">
                            <thead>
                                <tr>
                                    <th style="padding: 8px 8px;">Category</th>
                                    <th style="text-align: right; padding: 8px 6px;">Amount</th>
                                    <th style="text-align: right; padding: 8px 8px;">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyOverview['categories'] as $catItem)
                                    <tr>
                                        <td style="padding: 8px 8px;">
                                            <div style="display: flex; align-items: center; gap: 7px; min-width: 0;">
                                                <span class="cat-swatch" style="background-color: {{ $catItem['color'] }}; width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; box-shadow: 0 0 0 2px rgba(20, 22, 31, 0.06);"></span>
                                                <span style="font-weight: 600; color: var(--text-dark); line-height: 1.25; word-break: break-word;">{{ $catItem['name'] }}</span>
                                            </div>
                                        </td>
                                        <td class="mono" style="text-align: right; white-space: nowrap; padding: 8px 6px; font-weight: 600;">{{ $user->currency }}{{ number_format((float)$catItem['amount'], 2) }}</td>
                                        <td class="mono" style="text-align: right; font-weight: 700; color: {{ $catItem['color'] }}; white-space: nowrap; padding: 8px 8px;">{{ $catItem['share_pct'] }}%</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 18px 10px; color: var(--text-muted);">
                                            No category spending recorded for {{ $monthlyOverview['month_label'] }} yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Second Analytics Row: Annual Income vs Expenditure + Monthly Budget Utilisation -->
        <div class="grid-2" style="margin-top: 20px;">
            <div style="background-color: var(--ivory-alt); padding: 18px; border-radius: 12px; border: 1px solid var(--ivory-border); min-width: 0;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px; flex-wrap: wrap;">
                    <h3 style="font-size: 14px; color: var(--text-dark);">{{ $selectedYear }} Monthly Income vs Expenditure (Jan – Dec)</h3>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="mono" style="font-size: 11.5px; color: var(--emerald); font-weight: 700;">
                            Annual Net: {{ $user->currency }}{{ number_format((float)$yearlyOverview['net_cash_flow'], 2) }}
                        </span>
                        <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 8px; font-size: 11px;" onclick="openChartPresentationModal('income_vs_expense')" title="Expand Chart">
                            ⛶ Expand
                        </button>
                    </div>
                </div>
                <div class="dashboard-chart-box" style="position: relative; width: 100%; height: 220px; min-height: 220px; cursor: pointer;" onclick="openChartPresentationModal('income_vs_expense')" title="Click to expand in fullscreen presentation mode">
                    <canvas id="incomeVsExpenseChart" style="display: block; width: 100%; height: 100%;"></canvas>
                </div>
            </div>

            <!-- Budget Utilisation Live Progress Matrix -->
            <div style="background-color: var(--ivory-alt); padding: 18px; border-radius: 12px; border: 1px solid var(--ivory-border);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; gap: 8px;">
                    <h3 style="font-size: 14px; color: var(--text-dark);">Category Budget Utilization ({{ $monthlyOverview['month_label'] }})</h3>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <a href="{{ route('budgets.index') }}" class="btn btn-ivory btn-sm">Configure Budgets</a>
                        <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 8px; font-size: 11px;" onclick="openChartPresentationModal('budget')" title="Expand Chart">
                            ⛶ Expand
                        </button>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 11px; max-height: 220px; overflow-y: auto; padding-right: 4px;">
                    @if($activeBudget)
                        @php
                            $catMap = collect($monthlyOverview['categories'])->keyBy('category_id');
                        @endphp
                        @foreach($activeBudget->categoryAllocations as $alloc)
                            @php
                                $actual = (float) ($catMap[$alloc->expense_category_id]['amount'] ?? 0);
                                $limit = (float) $alloc->allocated_amount;
                                $pct = $limit > 0 ? round(($actual / $limit) * 100, 1) : 0;
                                $barColor = $pct > 100 ? '#E66D55' : ($pct >= 80 ? '#E5A93C' : '#2D9F78');
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 4px;">
                                    <strong>{{ $alloc->category?->name }}</strong>
                                    <span class="mono">
                                        {{ $user->currency }}{{ number_format($actual, 2) }} / {{ $user->currency }}{{ number_format($limit, 2) }}
                                        <strong style="color: {{ $barColor }};">({{ $pct }}%)</strong>
                                    </span>
                                </div>
                                <div class="progress-track">
                                    <div class="progress-fill" style="width: {{ min(100, $pct) }}%; background-color: {{ $barColor }};"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p style="font-size: 12.5px; color: var(--text-muted);">No active budget configured for this month.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

<!-- RECENT TRANSACTIONS (FULL WIDTH) & UPCOMING RECURRING COMMITMENTS MODAL -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <div style="font-size: 10.5px; color: var(--text-muted); text-transform: uppercase; font-weight: 700;">Real-Time Ledger</div>
            <h2 class="section-title">Recent Recorded Transactions</h2>
        </div>
        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn btn-mustard btn-sm" onclick="openUpcomingBillsModal()">
                Upcoming Bills & Reminders ({{ $upcomingRecurring->count() }})
            </button>
            <button type="button" class="btn btn-coral btn-sm" onclick="openQuickModal('expense')">+ Record Expense</button>
            <a href="{{ route('transactions.index') }}" class="btn btn-ivory btn-sm">View All →</a>
        </div>
    </div>
    <div class="table-wrap">
        @php
            $compoundedRecentTxs = $recentTransactions->groupBy(function ($tx) {
                return strtolower(trim($tx->title)) . '|' . $tx->type;
            });
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>Latest Date & Status</th>
                    <th>Description & Merchant</th>
                    <th>Category</th>
                    <th style="text-align: right;">Compounded Amount</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($compoundedRecentTxs as $rKey => $rGroup)
                    @php
                        $tx = $rGroup->first();
                        $rCount = $rGroup->count();
                        $rTotal = $rGroup->sum(fn($i) => (float) $i->amount);
                        $rMethods = $rGroup->map(fn($i) => $i->paymentMethod?->name)->filter()->unique()->implode(', ') ?: '—';
                        $rSubcategories = $rGroup->map(fn($i) => $i->subcategory?->name)->filter()->unique()->values();
                    @endphp
                    <tr>
                        <td class="mono" style="font-size: 11.5px; white-space: nowrap;">
                            <strong>{{ $tx->transaction_date->format('d M Y') }}</strong>
                            @if($rCount > 1)
                                <div style="margin-top: 3px;">
                                    <span class="mono" style="font-size: 9.5px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; background-color: {{ $tx->type === 'income' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                        COMPOUNDED ({{ $rCount }}x)
                                    </span>
                                </div>
                            @else
                                <div style="font-size: 10.5px; color: var(--text-muted);">{{ $tx->transaction_time ? substr($tx->transaction_time, 0, 5) : '—' }}</div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $tx->title }}</strong>
                            <div style="font-size: 11.5px; color: var(--text-muted);">
                                {{ $tx->merchant_or_source }}
                                @if($tx->subcategory) • {{ $tx->subcategory->name }} @endif
                                @if($rCount > 1) • {{ $rCount }} entries compounded @endif
                            </div>
                        </td>
                        <td>
                            <div>
                                <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                    <span class="cat-swatch" style="background-color: {{ $tx->category?->color ?? '#2D9F78' }};"></span>
                                    {{ $tx->category?->name ?? 'General' }}
                                </span>
                            </div>
                            @if($rSubcategories->isNotEmpty())
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px; padding-left: 4px;">
                                    ↳ {{ $rSubcategories->implode(', ') }}
                                </div>
                            @endif
                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 5px; padding-left: 4px;">
                                <span style="font-size: 11.5px; color: var(--text-muted);">
                                    Method: <strong style="color: var(--text-dark); font-weight: 600;">{{ $rMethods }}</strong>
                                </span>
                                <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 7px; border-radius: 9999px; background-color: {{ $tx->type === 'income' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                    {{ $tx->type }}
                                </span>
                            </div>
                        </td>
                        <td class="mono" style="text-align: right; font-weight: 800; white-space: nowrap; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                            {{ $tx->type === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format($rTotal, 2) }}
                            @if($rCount > 1)
                                <div style="font-size: 10px; color: var(--text-muted); font-weight: 600;">{{ $rCount }}x compounded</div>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 5px;">
                                <button type="button"
                                        class="btn btn-emerald btn-sm"
                                        style="padding: 3px 8px; font-size: 11px;"
                                        title="Record '{{ $tx->title }}' again"
                                        onclick='repeatQuickTransaction(@json($tx))'>
                                    ↻ Repeat
                                </button>
                                @if($rCount === 1)
                                    <form action="{{ route('transactions.destroy', $tx) }}" method="POST" style="display:inline; margin: 0;" onsubmit="return confirm('Delete this transaction?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ivory btn-sm" title="Delete">✕</button>
                                    </form>
                                @else
                                    <a href="{{ route('transactions.index', ['q' => $tx->title]) }}" class="btn btn-ivory btn-sm" style="padding: 3px 8px; font-size: 11px;">
                                        Manage ({{ $rCount }})
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- UPCOMING BILLS & REMINDERS MODAL -->
<div id="upcomingBillsModal" class="modal-backdrop" onclick="if(event.target === this) closeUpcomingBillsModal()">
    <div class="modal-box" style="max-width: 560px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Scheduled Commitments</div>
                <h3 style="font-size: 16px;">Upcoming Bills & Reminders</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <a href="{{ route('recurring.index') }}" class="btn btn-ivory btn-sm">Manage All</a>
                <button type="button" class="btn btn-coral btn-sm" onclick="closeUpcomingBillsModal()">✕ Close</button>
            </div>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($upcomingRecurring as $rec)
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-left: 4px solid {{ $rec->category?->color ?? '#E5A93C' }}; border-radius: 10px; padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px;">
                    <div>
                        <div style="font-weight: 700; color: var(--text-dark); font-size: 13px;">{{ $rec->title }}</div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">
                            {{ ucfirst($rec->frequency) }} • Due <strong style="color: var(--text-dark);">{{ $rec->next_due_date->format('d M Y') }}</strong>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div class="mono" style="font-weight: 700; color: var(--coral-dark); font-size: 13.5px;">
                            {{ $user->currency }}{{ number_format((float)$rec->amount, 2) }}
                        </div>
                        <form action="{{ route('recurring.process', $rec) }}" method="POST" style="margin-top: 4px;">
                            @csrf
                            <button type="submit" class="btn btn-emerald btn-sm" style="padding: 3px 9px; font-size: 10.5px;">Post Now</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 20px; color: var(--text-muted);">No upcoming recurring commitments scheduled.</div>
            @endforelse
        </div>
    </div>
</div>

<!-- FULLSCREEN CHART PRESENTATION MODAL -->
<div id="chartPresentationModal" class="modal-backdrop" onclick="if(event.target === this) closeChartPresentationModal()">
    <div class="modal-box pres-modal-box" style="max-width: 96vw; width: 1380px; height: 90vh; max-height: 90vh; display: flex; flex-direction: column;">
        <div class="modal-header pres-modal-header" style="flex-shrink: 0; flex-direction: column; align-items: stretch; gap: 10px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px;">
                <div style="min-width: 0; flex: 1;">
                    <div style="font-size: 10px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.07em;">Executive Presentation Mode</div>
                    <h3 id="presentationModalTitle" style="font-size: 17px; margin-top: 2px;">Spending Analytics Presentation</h3>
                </div>
                <button type="button" class="btn btn-coral btn-sm" onclick="closeChartPresentationModal()" style="flex-shrink: 0;">✕ Exit</button>
            </div>
            <div class="pres-tabs-grid" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="btn btn-sm pres-tab-btn" id="presTab_primary" onclick="switchPresentationView('primary')">1. Spending Trend</button>
                <button type="button" class="btn btn-sm pres-tab-btn" id="presTab_category" onclick="switchPresentationView('category')">2. Category Breakdown</button>
                <button type="button" class="btn btn-sm pres-tab-btn" id="presTab_income_vs_expense" onclick="switchPresentationView('income_vs_expense')">3. Income vs Expense</button>
                <button type="button" class="btn btn-sm pres-tab-btn" id="presTab_budget" onclick="switchPresentationView('budget')">4. Budget Utilization</button>
            </div>
        </div>
        <div class="modal-body pres-modal-body" style="flex: 1; display: flex; flex-direction: column; overflow-y: auto; padding: 20px;">
            <!-- Sub-toolbar for Spending Trend Horizon inside Presentation Modal -->
            <div id="presTrendHorizonBar" class="pres-horizon-bar" style="display: none; align-items: center; justify-content: space-between; margin-bottom: 14px; padding-bottom: 10px; border-bottom: 1px solid var(--ivory-border); flex-wrap: wrap; gap: 8px; flex-shrink: 0;">
                <div style="font-size: 12px; color: var(--text-muted); font-weight: 600;">
                    Switch Time Horizon:
                </div>
                <div class="pres-horizon-btns" style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button type="button" class="btn btn-sm btn-plum" id="presHorizon_daily" onclick="switchPresentationTrendHorizon('daily')">Daily ({{ $monthlyOverview['month_label'] }})</button>
                    <button type="button" class="btn btn-sm btn-ivory" id="presHorizon_weekly" onclick="switchPresentationTrendHorizon('weekly')">Weekly ({{ $weeklyOverview['week_label'] }})</button>
                    <button type="button" class="btn btn-sm btn-ivory" id="presHorizon_yearly" onclick="switchPresentationTrendHorizon('yearly')">Yearly ({{ $selectedYear }})</button>
                </div>
            </div>

            <!-- View 1 & 3: Single Large Canvas Container -->
            <div id="presSingleChartWrap" class="pres-single-chart-wrap" style="flex: 1; height: 420px; min-height: 290px; position: relative; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 14px; padding: 16px;">
                <canvas id="presentationSingleCanvas" style="display: block; width: 100%; height: 100%;"></canvas>
            </div>

            <!-- View 2: Category Doughnut + Full Table Side-by-Side -->
            <div id="presCategoryWrap" class="pres-split-grid" style="display: none; flex: 1; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); gap: 18px; align-items: stretch;">
                <div class="pres-split-canvas-card" style="position: relative; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 14px; padding: 18px; height: 380px; min-height: 260px;">
                    <canvas id="presentationCategoryCanvas" style="display: block; width: 100%; height: 100%;"></canvas>
                </div>
                <div style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); border-radius: 14px; overflow-y: auto; min-width: 0;">
                    <div style="padding: 14px 16px; border-bottom: 1px solid var(--ivory-border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 6px;">
                        <strong style="font-size: 13.5px;">Category Expenditure Ranking ({{ $monthlyOverview['month_label'] }})</strong>
                        <span class="mono" style="font-weight: 700; color: var(--emerald); font-size: 12.5px;">Total: {{ $user->currency }}{{ number_format((float)$monthlyOverview['total_expenditure'], 2) }}</span>
                    </div>
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Category</th>
                                <th style="text-align: right;">Tx</th>
                                <th style="text-align: right;">Amount</th>
                                <th style="text-align: right;">Share</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($monthlyOverview['categories'] as $catItem)
                                <tr>
                                    <td>
                                        <span class="cat-swatch" style="background-color: {{ $catItem['color'] }}; margin-right: 6px;"></span>
                                        <strong>{{ $catItem['name'] }}</strong>
                                    </td>
                                    <td class="mono" style="text-align: right;">{{ $catItem['count'] }}</td>
                                    <td class="mono" style="text-align: right; font-weight: 700; white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$catItem['amount'], 2) }}</td>
                                    <td class="mono" style="text-align: right; font-weight: 600; color: var(--blue); white-space: nowrap;">{{ $catItem['share_pct'] }}%</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align: center; padding: 20px; color: var(--text-muted);">No category expenditure recorded for this period yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- View 4: Budget Utilization Full Presentation Grid -->
            <div id="presBudgetWrap" class="pres-split-grid" style="display: none; flex: 1; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap: 18px; align-items: stretch;">
                <div class="pres-split-canvas-card" style="position: relative; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 14px; padding: 18px; height: 380px; min-height: 260px;">
                    <canvas id="presentationBudgetCanvas" style="display: block; width: 100%; height: 100%;"></canvas>
                </div>
                <div style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); border-radius: 14px; padding: 20px; overflow-y: auto; display: flex; flex-direction: column; gap: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; padding-bottom: 10px; border-bottom: 1px solid var(--ivory-border);">
                        <strong style="font-size: 14.5px;">Planned vs Actual Category Limits ({{ $monthlyOverview['month_label'] }})</strong>
                        <a href="{{ route('budgets.index') }}" class="btn btn-plum btn-sm">Manage Budgets →</a>
                    </div>
                    @if($activeBudget)
                        @php
                            $presCatMap = collect($monthlyOverview['categories'])->keyBy('category_id');
                        @endphp
                        @foreach($activeBudget->categoryAllocations as $alloc)
                            @php
                                $actual = (float) ($presCatMap[$alloc->expense_category_id]['amount'] ?? 0);
                                $limit = (float) $alloc->allocated_amount;
                                $pct = $limit > 0 ? round(($actual / $limit) * 100, 1) : 0;
                                $barColor = $pct > 100 ? '#E66D55' : ($pct >= 80 ? '#E5A93C' : '#2D9F78');
                            @endphp
                            <div>
                                <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px;">
                                    <strong>{{ $alloc->category?->name }}</strong>
                                    <span class="mono">
                                        {{ $user->currency }}{{ number_format($actual, 2) }} / {{ $user->currency }}{{ number_format($limit, 2) }}
                                        <strong style="color: {{ $barColor }};">({{ $pct }}%)</strong>
                                    </span>
                                </div>
                                <div class="progress-track" style="height: 10px;">
                                    <div class="progress-fill" style="width: {{ min(100, $pct) }}%; background-color: {{ $barColor }};"></div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CLICKABLE KPI CARD ITEMS BREAKDOWN MODAL (EXPENSES TODAY, THIS WEEK, THIS MONTH, THIS YEAR, & MONTHLY INCOME) -->
@php
    $kpiModalPeriods = [
        'today' => [
            'title' => 'Expenses Today — ' . $summary['reference_label'],
            'badge' => 'Daily Ledger',
            'total' => $summary['expenses_today']['current'],
            'previous' => $summary['expenses_today']['previous'],
            'comp_label' => $summary['expenses_today']['comparison_label'],
            'items' => $kpiExpensesToday,
            'type' => 'expense',
        ],
        'week' => [
            'title' => 'Expenses This Week — ' . $weeklyOverview['week_label'],
            'badge' => 'Weekly Ledger',
            'total' => $summary['expenses_week']['current'],
            'previous' => $summary['expenses_week']['previous'],
            'comp_label' => $summary['expenses_week']['comparison_label'],
            'items' => $kpiExpensesWeek,
            'type' => 'expense',
        ],
        'month' => [
            'title' => 'Expenses This Month — ' . $summary['month_label'],
            'badge' => 'Monthly Ledger',
            'total' => $summary['expenses_month']['current'],
            'previous' => $summary['expenses_month']['previous'],
            'comp_label' => $summary['expenses_month']['comparison_label'],
            'items' => $kpiExpensesMonth,
            'type' => 'expense',
        ],
        'year' => [
            'title' => 'Expenses This Year — ' . $summary['year_label'],
            'badge' => 'Annual Ledger',
            'total' => $summary['expenses_year']['current'],
            'previous' => $summary['expenses_year']['previous'],
            'comp_label' => $summary['expenses_year']['comparison_label'],
            'items' => $kpiExpensesYear,
            'type' => 'expense',
        ],
        'income' => [
            'title' => $summary['income_month']['label'] . ' — ' . $summary['month_label'],
            'badge' => 'Monthly Income Inflows',
            'total' => $summary['income_month']['current'],
            'previous' => $summary['income_month']['previous'],
            'comp_label' => $summary['income_month']['comparison_label'],
            'items' => $kpiIncomeMonth,
            'type' => 'income',
        ],
    ];
@endphp
<div id="kpiItemsBreakdownModal" class="modal-backdrop" onclick="if(event.target === this) closeKpiBreakdownModal()">
    <div class="modal-box" style="max-width: 780px;">
        <div class="modal-header" style="flex-wrap: wrap; gap: 10px;">
            <div>
                <div id="kpiModalBadge" style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">
                    Period Items Breakdown
                </div>
                <h3 id="kpiModalHeading" style="font-size: 17px; margin-top: 2px;">Expenses Breakdown</h3>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <button type="button" id="kpiModalQuickAddBtn" class="btn btn-emerald btn-sm" onclick="closeKpiBreakdownModal(); openQuickModal('expense');">+ Record Expense</button>
                <button type="button" class="btn btn-coral btn-sm" onclick="closeKpiBreakdownModal()">✕ Close</button>
            </div>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 14px;">
            <!-- Period Tabs Bar inside Modal -->
            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; background-color: var(--ivory); padding: 6px; border-radius: 11px; border: 1px solid var(--ivory-border);">
                <button type="button" id="kpiTabBtn_today" class="btn btn-sm kpi-period-tab-btn btn-plum" onclick="openKpiBreakdownModal('today')">
                    Expenses Today ({{ $kpiExpensesToday->count() }})
                </button>
                <button type="button" id="kpiTabBtn_week" class="btn btn-sm kpi-period-tab-btn btn-ivory" onclick="openKpiBreakdownModal('week')">
                    Expenses This Week ({{ $kpiExpensesWeek->count() }})
                </button>
                <button type="button" id="kpiTabBtn_month" class="btn btn-sm kpi-period-tab-btn btn-ivory" onclick="openKpiBreakdownModal('month')">
                    Expenses This Month ({{ $kpiExpensesMonth->count() }})
                </button>
                <button type="button" id="kpiTabBtn_year" class="btn btn-sm kpi-period-tab-btn btn-ivory" onclick="openKpiBreakdownModal('year')">
                    Expenses This Year ({{ $kpiExpensesYear->count() }})
                </button>
                <button type="button" id="kpiTabBtn_income" class="btn btn-sm kpi-period-tab-btn btn-ivory" onclick="openKpiBreakdownModal('income')">
                    Monthly Income ({{ $kpiIncomeMonth->count() }})
                </button>
            </div>

            @foreach($kpiModalPeriods as $pKey => $pData)
                <div id="kpiPane_{{ $pKey }}" class="kpi-period-pane" style="display: none; flex-direction: column; gap: 14px;">
                    <div class="kpi-modal-summary-grid" style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 12px 15px;">
                        <div style="min-width: 0;">
                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Recorded Items</div>
                            <div class="mono" style="font-size: 14.5px; font-weight: 800; color: var(--text-dark); margin-top: 2px;">
                                {{ $pData['items']->count() }} {{ \Illuminate\Support\Str::plural('Transaction', $pData['items']->count()) }}
                            </div>
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Period Total</div>
                            <div class="mono" style="font-size: 14.5px; font-weight: 800; color: {{ $pData['type'] === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; margin-top: 2px;">
                                {{ $user->currency }}{{ number_format((float)$pData['total'], 2) }}
                            </div>
                        </div>
                        <div style="min-width: 0;">
                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Previous Period ({{ $pData['comp_label'] }})</div>
                            <div class="mono" style="font-size: 14px; font-weight: 700; color: var(--text-muted); margin-top: 2px;">
                                {{ $user->currency }}{{ number_format((float)$pData['previous'], 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="table-wrap kpi-modal-table-wrap" style="border: 1px solid var(--ivory-border); border-radius: 12px; max-height: 410px; overflow-y: auto; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        @php
                            $compoundedKpiItems = $pData['items']->groupBy(function ($itemTx) {
                                return $itemTx->type . '_title|' . ($itemTx->expense_category_id ?: 0) . '|' . preg_replace('/\s+/', ' ', strtolower(trim((string) $itemTx->title)));
                            });
                        @endphp
                        <table class="data-table kpi-modal-table">
                            <thead>
                                <tr>
                                    <th>Date &amp; Status</th>
                                    <th>Item, Amount &amp; Merchant</th>
                                    <th>Category &amp; Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($compoundedKpiItems as $cKey => $cGroup)
                                    @php
                                        $itemTx = $cGroup->first();
                                        $cCount = $cGroup->count();
                                        $cTotal = $cGroup->sum(fn($x) => (float) $x->amount);
                                        $cMethods = $cGroup->map(fn($x) => $x->paymentMethod?->name)->filter()->unique()->implode(', ') ?: '—';
                                        $cDatesList = $cGroup->map(fn($x) => $x->transaction_date->format('d M') . ' (' . $user->currency . number_format((float)$x->amount, 2) . ')')->implode(' • ');
                                        $cMin = $cGroup->min(fn($x) => (float) $x->amount);
                                        $cMax = $cGroup->max(fn($x) => (float) $x->amount);
                                    @endphp
                                    <tr>
                                        <td class="mono" style="white-space: nowrap; font-size: 11px;">
                                            <strong>{{ $itemTx->transaction_date->format('d M Y') }}</strong>
                                            @if($cCount > 1)
                                                <div style="margin-top: 3px;">
                                                    <span class="mono" style="font-size: 9px; font-weight: 800; padding: 2px 6px; border-radius: 9999px; background-color: {{ $itemTx->type === 'income' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $itemTx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                                        {{ $cCount }}x
                                                    </span>
                                                </div>
                                            @else
                                                <div style="font-size: 10px; color: var(--text-muted);">
                                                    {{ $itemTx->transaction_time ? substr($itemTx->transaction_time, 0, 5) : '—' }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                                <strong style="font-size: 12.5px; color: var(--text-dark);">{{ $itemTx->title }}</strong>
                                                <button type="button"
                                                        class="btn btn-ivory btn-sm"
                                                        style="padding: 1.5px 7px; font-size: 10px; border-radius: 9999px;"
                                                        title="Record '{{ $itemTx->title }}' again"
                                                        onclick='closeKpiBreakdownModal(); repeatQuickTransaction(@json($itemTx));'>
                                                    ↻ Repeat
                                                </button>
                                            </div>
                                            @if($itemTx->merchant_or_source)
                                                <div style="font-size: 11px; color: var(--text-muted);">{{ $itemTx->merchant_or_source }}</div>
                                            @endif
                                            <div style="display: flex; align-items: baseline; gap: 6px; flex-wrap: wrap; margin-top: 3px;">
                                                <span class="mono" style="font-weight: 800; font-size: 12.5px; color: {{ $itemTx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                                    {{ $itemTx->type === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format($cTotal, 2) }}
                                                </span>
                                                @if($cCount > 1)
                                                    <span class="mono" style="font-size: 10px; color: var(--text-muted); font-weight: 600;">
                                                        @if(abs($cMax - $cMin) > 0.005)
                                                            ({{ $cCount }}x • {{ $user->currency }}{{ number_format($cMin, 2) }} – {{ $user->currency }}{{ number_format($cMax, 2) }})
                                                        @else
                                                            ({{ $cCount }}x • {{ $user->currency }}{{ number_format($cMin, 2) }} each)
                                                        @endif
                                                    </span>
                                                @endif
                                            </div>
                                            @if($cCount > 1)
                                                <div class="mono" style="font-size: 9.5px; color: var(--blue); margin-top: 2px;">
                                                    {{ $cDatesList }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <span class="cat-badge" style="background-color: var(--ivory-alt); white-space: normal; line-height: 1.25;">
                                                    <span class="cat-swatch" style="background-color: {{ $itemTx->category?->color ?? '#2D9F78' }};"></span>
                                                    <span>{{ $itemTx->category?->name ?? 'General' }}</span>
                                                </span>
                                            </div>
                                            @if($itemTx->subcategory)
                                                <div style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px; padding-left: 4px;">
                                                    ↳ {{ $itemTx->subcategory->name }}
                                                </div>
                                            @endif
                                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px; padding-left: 4px;">
                                                Method: <strong style="color: var(--text-dark); font-weight: 600;">{{ $cMethods }}</strong>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" style="text-align: center; padding: 28px; color: var(--text-muted);">
                                            <strong>No items recorded for this period yet.</strong>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleMobileHorizonBar() {
        const card = document.getElementById('activeHorizonCard');
        const chevron = document.getElementById('horizonMobileChevron');
        const btn = document.getElementById('horizonMobileToggleBtn');
        if (!card) return;

        const isExpanded = card.classList.toggle('mobile-expanded');
        if (chevron) {
            chevron.textContent = isExpanded ? '▾' : '▸';
            chevron.style.backgroundColor = isExpanded ? 'var(--emerald-soft)' : 'var(--ivory)';
            chevron.style.color = isExpanded ? 'var(--emerald-dark)' : 'var(--plum)';
        }
        if (btn) {
            btn.textContent = isExpanded ? 'Hide ▴' : 'Change Date ▾';
        }
    }

    function openUpcomingBillsModal() {
        document.getElementById('upcomingBillsModal')?.classList.add('open');
    }

    function closeUpcomingBillsModal() {
        document.getElementById('upcomingBillsModal')?.classList.remove('open');
    }

    const kpiPeriodMeta = {
        today: { title: @json($kpiModalPeriods['today']['title']), badge: @json($kpiModalPeriods['today']['badge']) },
        week: { title: @json($kpiModalPeriods['week']['title']), badge: @json($kpiModalPeriods['week']['badge']) },
        month: { title: @json($kpiModalPeriods['month']['title']), badge: @json($kpiModalPeriods['month']['badge']) },
        year: { title: @json($kpiModalPeriods['year']['title']), badge: @json($kpiModalPeriods['year']['badge']) },
        income: { title: @json($kpiModalPeriods['income']['title']), badge: @json($kpiModalPeriods['income']['badge']) }
    };

    function openKpiBreakdownModal(periodKey = 'today') {
        const modal = document.getElementById('kpiItemsBreakdownModal');
        if (!modal) return;

        document.querySelectorAll('.kpi-period-pane').forEach(function (pane) {
            pane.style.display = 'none';
        });
        const activePane = document.getElementById('kpiPane_' + periodKey);
        if (activePane) {
            activePane.style.display = 'flex';
        }

        document.querySelectorAll('.kpi-period-tab-btn').forEach(function (btn) {
            btn.className = 'btn btn-sm kpi-period-tab-btn btn-ivory';
        });
        const activeBtn = document.getElementById('kpiTabBtn_' + periodKey);
        if (activeBtn) {
            activeBtn.className = 'btn btn-sm kpi-period-tab-btn btn-plum';
        }

        const meta = kpiPeriodMeta[periodKey] || kpiPeriodMeta.today;
        const headingEl = document.getElementById('kpiModalHeading');
        const badgeEl = document.getElementById('kpiModalBadge');
        const quickAddBtn = document.getElementById('kpiModalQuickAddBtn');
        if (headingEl) headingEl.textContent = meta.title;
        if (badgeEl) badgeEl.textContent = meta.badge;
        if (quickAddBtn) {
            if (periodKey === 'income') {
                quickAddBtn.textContent = '+ Record Income';
                quickAddBtn.setAttribute('onclick', "closeKpiBreakdownModal(); openQuickModal('income');");
            } else {
                quickAddBtn.textContent = '+ Record Expense';
                quickAddBtn.setAttribute('onclick', "closeKpiBreakdownModal(); openQuickModal('expense');");
            }
        }

        modal.classList.add('open');
    }

    function closeKpiBreakdownModal() {
        document.getElementById('kpiItemsBreakdownModal')?.classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeUpcomingBillsModal();
            closeChartPresentationModal();
            closeKpiBreakdownModal();
        }
    });
    function selectExplorerWeek(weekNum) {
        document.querySelectorAll('.week-drilldown-panel').forEach(el => el.style.display = 'none');
        const target = document.getElementById('weekDrilldown_' + weekNum);
        if (target) target.style.display = 'block';

        for (let i = 1; i <= 4; i++) {
            const btn = document.getElementById('weekBtn_' + i);
            const row = document.getElementById('weekRow_' + i);
            if (btn) {
                btn.className = 'btn btn-sm week-selector-btn ' + (i === weekNum ? 'btn-emerald' : 'btn-plum');
            }
            if (row) {
                row.style.borderColor = (i === weekNum) ? '#2D9F78' : 'rgba(255,255,255,0.08)';
            }
        }
    }

    const dailyTrendData = @json($monthlyOverview['daily_trend']);
    const weeklyDaysData = @json($weeklyOverview['days']);
    const yearlyMonthlyData = @json($yearlyOverview['monthly_series']);
    const categoryData = @json($monthlyOverview['categories']);
    @php
        $budgetChartAllocations = [];
        if ($activeBudget) {
            $bCatMap = collect($monthlyOverview['categories'])->keyBy('category_id');
            foreach ($activeBudget->categoryAllocations as $alloc) {
                $budgetChartAllocations[] = [
                    'name' => $alloc->category?->name ?? 'Category',
                    'planned' => (float) $alloc->allocated_amount,
                    'actual' => (float) ($bCatMap[$alloc->expense_category_id]['amount'] ?? 0),
                ];
            }
        }
    @endphp
    const budgetComparisonData = @json($budgetChartAllocations);

    let primaryChartInstance = null;
    let activePrimaryHorizonMode = 'daily';
    let presSingleChartInstance = null;
    let presCategoryChartInstance = null;
    let presBudgetChartInstance = null;

    function initPrimaryHorizonChart(mode = 'daily') {
        const ctx = document.getElementById('primaryHorizonChart');
        if (!ctx) return;
        if (primaryChartInstance) primaryChartInstance.destroy();

        let labels = [];
        let values = [];
        let color = '#E66D55';

        if (mode === 'daily') {
            labels = dailyTrendData.map(d => d.label);
            values = dailyTrendData.map(d => d.amount);
            color = '#E66D55';
            document.getElementById('primaryChartHeading').textContent = 'Daily Spending Trend — {{ $monthlyOverview["month_label"] }}';
        } else if (mode === 'weekly') {
            labels = weeklyDaysData.map(d => d.day_short + ' ' + d.formatted_date);
            values = weeklyDaysData.map(d => parseFloat(d.amount));
            color = '#E5A93C';
            document.getElementById('primaryChartHeading').textContent = 'Weekly Spending Breakdown ({{ $weeklyOverview["week_label"] }})';
        } else {
            labels = yearlyMonthlyData.map(m => m.short_name);
            values = yearlyMonthlyData.map(m => parseFloat(m.expense));
            color = '#2D9F78';
            document.getElementById('primaryChartHeading').textContent = 'Yearly Monthly Expenditure Overview ({{ $selectedYear }})';
        }

        primaryChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Expenditure ({{ $user->currency }})',
                    data: values,
                    backgroundColor: color,
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#14161F' } }
                },
                scales: {
                    x: { ticks: { color: '#646A7A', font: { size: 10 } }, grid: { display: false } },
                    y: { ticks: { color: '#646A7A', font: { size: 10 } }, grid: { color: '#E5E7EF' } }
                }
            }
        });
    }

    function switchChartHorizon(mode) {
        activePrimaryHorizonMode = mode;
        ['daily', 'weekly', 'yearly'].forEach(m => {
            const btn = document.getElementById('chartTab_' + m);
            if (btn) btn.className = 'btn btn-sm ' + (m === mode ? 'btn-plum' : 'btn-ivory');
        });
        initPrimaryHorizonChart(mode);
    }

    // FULLSCREEN CHART PRESENTATION MODAL ENGINE
    function openChartPresentationModal(viewType = 'primary') {
        const modal = document.getElementById('chartPresentationModal');
        if (!modal) return;
        modal.classList.add('open');
        switchPresentationView(viewType);
    }

    function closeChartPresentationModal() {
        document.getElementById('chartPresentationModal')?.classList.remove('open');
    }

    function switchPresentationView(viewType) {
        ['primary', 'category', 'income_vs_expense', 'budget'].forEach(v => {
            const tab = document.getElementById('presTab_' + v);
            if (tab) {
                tab.className = 'btn btn-sm pres-tab-btn ' + (v === viewType ? 'btn-emerald' : 'btn-ivory');
            }
        });

        const horizonBar = document.getElementById('presTrendHorizonBar');
        const singleWrap = document.getElementById('presSingleChartWrap');
        const catWrap = document.getElementById('presCategoryWrap');
        const budgetWrap = document.getElementById('presBudgetWrap');
        const titleEl = document.getElementById('presentationModalTitle');

        horizonBar.style.display = 'none';
        singleWrap.style.display = 'none';
        catWrap.style.display = 'none';
        budgetWrap.style.display = 'none';

        if (viewType === 'primary') {
            horizonBar.style.display = 'flex';
            singleWrap.style.display = 'block';
            switchPresentationTrendHorizon(activePrimaryHorizonMode);
        } else if (viewType === 'category') {
            catWrap.style.display = 'grid';
            titleEl.textContent = 'Spending by Category — {{ $monthlyOverview["month_label"] }}';
            renderPresentationCategoryChart();
        } else if (viewType === 'income_vs_expense') {
            singleWrap.style.display = 'block';
            titleEl.textContent = '{{ $selectedYear }} Monthly Income vs Expenditure (Jan – Dec)';
            renderPresentationIncomeVsExpenseChart();
        } else if (viewType === 'budget') {
            budgetWrap.style.display = 'grid';
            titleEl.textContent = 'Category Budget Utilization — {{ $monthlyOverview["month_label"] }}';
            renderPresentationBudgetChart();
        }
    }

    function switchPresentationTrendHorizon(mode) {
        activePrimaryHorizonMode = mode;
        ['daily', 'weekly', 'yearly'].forEach(m => {
            const btn = document.getElementById('presHorizon_' + m);
            if (btn) btn.className = 'btn btn-sm ' + (m === mode ? 'btn-plum' : 'btn-ivory');
        });
        switchChartHorizon(mode);

        const ctx = document.getElementById('presentationSingleCanvas');
        if (!ctx) return;
        if (presSingleChartInstance) presSingleChartInstance.destroy();

        let labels = [];
        let values = [];
        let color = '#E66D55';
        const titleEl = document.getElementById('presentationModalTitle');

        if (mode === 'daily') {
            labels = dailyTrendData.map(d => d.label);
            values = dailyTrendData.map(d => d.amount);
            color = '#E66D55';
            titleEl.textContent = 'Daily Spending Trend — {{ $monthlyOverview["month_label"] }}';
        } else if (mode === 'weekly') {
            labels = weeklyDaysData.map(d => d.day_short + ' ' + d.formatted_date);
            values = weeklyDaysData.map(d => parseFloat(d.amount));
            color = '#E5A93C';
            titleEl.textContent = 'Weekly Spending Breakdown ({{ $weeklyOverview["week_label"] }})';
        } else {
            labels = yearlyMonthlyData.map(m => m.short_name);
            values = yearlyMonthlyData.map(m => parseFloat(m.expense));
            color = '#2D9F78';
            titleEl.textContent = 'Yearly Monthly Expenditure Overview ({{ $selectedYear }})';
        }

        presSingleChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Expenditure ({{ $user->currency }})',
                    data: values,
                    backgroundColor: color,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#14161F', font: { size: 13, weight: '600' } } }
                },
                scales: {
                    x: { ticks: { color: '#14161F', font: { size: 12 } }, grid: { display: false } },
                    y: { ticks: { color: '#14161F', font: { size: 12 } }, grid: { color: '#E5E7EF' } }
                }
            }
        });
    }

    function renderPresentationCategoryChart() {
        const ctx = document.getElementById('presentationCategoryCanvas');
        if (!ctx) return;
        if (presCategoryChartInstance) presCategoryChartInstance.destroy();

        const defaultCategoryPalette = ['#2D9F78', '#E66D55', '#E5A93C', '#4E73DF', '#D96D9B', '#646A7A', '#1E1B2E', '#0EA5E9', '#8B5CF6', '#F97316', '#14B8A6', '#EC4899', '#6366F1', '#84CC16', '#A855F7', '#F43F5E'];
        const hasCategoryData = Array.isArray(categoryData) && categoryData.length > 0;
        const chartLabels = hasCategoryData ? categoryData.map(c => c.name) : ['No Spending Recorded Yet'];
        const chartValues = hasCategoryData ? categoryData.map(c => parseFloat(c.amount) || 0) : [1];
        const chartColors = hasCategoryData
            ? categoryData.map((c, idx) => c.color || defaultCategoryPalette[idx % defaultCategoryPalette.length])
            : ['#E5E7EF'];

        presCategoryChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: chartLabels,
                datasets: [{
                    data: chartValues,
                    backgroundColor: chartColors,
                    borderColor: '#FFFFFF',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { color: '#14161F', padding: 12, font: { size: 11.5 } }
                    }
                }
            }
        });
    }

    function renderPresentationIncomeVsExpenseChart() {
        const ctx = document.getElementById('presentationSingleCanvas');
        if (!ctx) return;
        if (presSingleChartInstance) presSingleChartInstance.destroy();

        presSingleChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: yearlyMonthlyData.map(m => m.short_name),
                datasets: [
                    {
                        label: 'Income ({{ $user->currency }})',
                        data: yearlyMonthlyData.map(m => parseFloat(m.income)),
                        backgroundColor: '#2D9F78',
                        borderRadius: 7
                    },
                    {
                        label: 'Expenditure ({{ $user->currency }})',
                        data: yearlyMonthlyData.map(m => parseFloat(m.expense)),
                        backgroundColor: '#E66D55',
                        borderRadius: 7
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#14161F', font: { size: 13, weight: '600' } } }
                },
                scales: {
                    x: { ticks: { color: '#14161F', font: { size: 12 } }, grid: { display: false } },
                    y: { beginAtZero: true, ticks: { color: '#14161F', font: { size: 12 } }, grid: { color: '#E5E7EF' } }
                }
            }
        });
    }

    function renderPresentationBudgetChart() {
        const ctx = document.getElementById('presentationBudgetCanvas');
        if (!ctx) return;
        if (presBudgetChartInstance) presBudgetChartInstance.destroy();

        presBudgetChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: budgetComparisonData.map(b => b.name),
                datasets: [
                    {
                        label: 'Planned Limit ({{ $user->currency }})',
                        data: budgetComparisonData.map(b => b.planned),
                        backgroundColor: '#4E73DF',
                        borderRadius: 6
                    },
                    {
                        label: 'Actual Spent ({{ $user->currency }})',
                        data: budgetComparisonData.map(b => b.actual),
                        backgroundColor: '#E66D55',
                        borderRadius: 6
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: '#14161F', font: { size: 12, weight: '600' } } }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: '#E5E7EF' } },
                    y: { ticks: { color: '#14161F', font: { size: 11.5 } }, grid: { display: false } }
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initPrimaryHorizonChart('daily');

        const catCtx = document.getElementById('categoryDistributionChart');
        if (catCtx) {
            const defaultCategoryPalette = ['#2D9F78', '#E66D55', '#E5A93C', '#4E73DF', '#D96D9B', '#646A7A', '#1E1B2E', '#0EA5E9', '#8B5CF6', '#F97316', '#14B8A6', '#EC4899', '#6366F1', '#84CC16', '#A855F7', '#F43F5E'];
            const hasCat = Array.isArray(categoryData) && categoryData.length > 0;
            new Chart(catCtx, {
                type: 'doughnut',
                data: {
                    labels: hasCat ? categoryData.map(c => c.name) : ['No Spending Recorded Yet'],
                    datasets: [{
                        data: hasCat ? categoryData.map(c => parseFloat(c.amount) || 0) : [1],
                        backgroundColor: hasCat ? categoryData.map((c, idx) => c.color || defaultCategoryPalette[idx % defaultCategoryPalette.length]) : ['#E5E7EF'],
                        borderColor: '#FFFFFF',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '65%',
                    plugins: {
                        legend: { display: false }
                    }
                }
            });
        }

        const incExpCtx = document.getElementById('incomeVsExpenseChart');
        if (incExpCtx) {
            new Chart(incExpCtx, {
                type: 'bar',
                data: {
                    labels: yearlyMonthlyData.map(m => m.short_name),
                    datasets: [
                        {
                            label: 'Income ({{ $user->currency }})',
                            data: yearlyMonthlyData.map(m => parseFloat(m.income)),
                            backgroundColor: '#2D9F78',
                            borderRadius: 5
                        },
                        {
                            label: 'Expenditure ({{ $user->currency }})',
                            data: yearlyMonthlyData.map(m => parseFloat(m.expense)),
                            backgroundColor: '#E66D55',
                            borderRadius: 5
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: { grid: { display: false } },
                        y: { beginAtZero: true, grid: { color: '#E5E7EF' } }
                    }
                }
            });
        }
    });
</script>
@endpush
