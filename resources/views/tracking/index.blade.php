@extends('layouts.app')

@section('kicker', 'Period Explorer')
@section('page_title', 'Daily, Weekly, Monthly & Yearly Spending Intelligence')

@section('content')
<!-- HORIZON SELECTOR TABS & DYNAMIC DATE PICKER -->
<div class="section-block theme-ivory" style="padding: 14px 20px;">
    <div style="display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap;">
        <div style="display: flex; gap: 6px; flex-wrap: wrap;">
            <a href="{{ route('tracking.index', ['tab' => 'explorer', 'year' => $year, 'month' => $month, 'date' => $date]) }}"
               class="btn {{ $tab === 'explorer' ? 'btn-plum' : 'btn-ivory' }}">
                Breakdown Explorer
            </a>
            <a href="{{ route('tracking.index', ['tab' => 'daily', 'year' => $year, 'month' => $month, 'date' => $date]) }}"
               class="btn {{ $tab === 'daily' ? 'btn-plum' : 'btn-ivory' }}">
                Daily ({{ $date }})
            </a>
            <a href="{{ route('tracking.index', ['tab' => 'weekly', 'year' => $year, 'month' => $month, 'date' => $date]) }}"
               class="btn {{ $tab === 'weekly' ? 'btn-plum' : 'btn-ivory' }}">
                Weekly (Mon–Sun)
            </a>
            <a href="{{ route('tracking.index', ['tab' => 'monthly', 'year' => $year, 'month' => $month, 'date' => $date]) }}"
               class="btn {{ $tab === 'monthly' ? 'btn-plum' : 'btn-ivory' }}">
                Monthly ({{ $monthly['month_label'] }})
            </a>
            <a href="{{ route('tracking.index', ['tab' => 'yearly', 'year' => $year, 'month' => $month, 'date' => $date]) }}"
               class="btn {{ $tab === 'yearly' ? 'btn-plum' : 'btn-ivory' }}">
                Yearly ({{ $year }})
            </a>
        </div>

        <form method="GET" action="{{ route('tracking.index') }}" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <input type="date" name="date" value="{{ $date }}" class="form-control mono" style="padding: 6px 10px; font-size: 12px;">
            <select name="month" class="form-control" style="padding: 6px 10px; font-size: 12px;">
                @for($m = 1; $m <= 12; $m++)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::createFromDate($year, $m, 1)->format('F') }}
                    </option>
                @endfor
            </select>
            <select name="year" class="form-control mono" style="padding: 6px 10px; font-size: 12px;">
                <option value="2026" {{ $year === 2026 ? 'selected' : '' }}>2026</option>
                <option value="2025" {{ $year === 2025 ? 'selected' : '' }}>2025</option>
            </select>
            <button type="submit" class="btn btn-emerald btn-sm">Inspect Period</button>
        </form>
    </div>
</div>

@if($tab === 'explorer')
    <!-- SPENDING BREAKDOWN EXPLORER -->
    <section class="section-block theme-ivory">
        <div class="section-header">
            <div>
                <div style="font-size: 10.5px; color: var(--emerald); text-transform: uppercase; font-weight: 700;">Hierarchical Reconciliation Drill-Down</div>
                <h2 class="section-title">{{ $explorer['month_name'] }} — Weekly & Daily Spending Breakdown</h2>
            </div>
            <div class="mono" style="font-size: 18px; font-weight: 700; color: var(--text-dark);">
                Monthly Total: {{ $user->currency }}{{ number_format((float)$explorer['total_monthly_expenditure'], 2) }}
            </div>
        </div>
        <div class="section-body" style="display: flex; flex-direction: column; gap: 18px;">
            @foreach($explorer['weeks'] as $wNum => $wData)
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 14px; padding: 18px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span style="background-color: var(--emerald); color: #FFF; font-weight: 700; font-size: 11px; padding: 3px 10px; border-radius: 9999px;">
                                {{ $wData['title'] }}
                            </span>
                            <strong style="color: var(--text-dark); font-size: 14px;">{{ $wData['range_label'] }}</strong>
                            <span style="color: var(--text-muted); font-size: 12px;">({{ $wData['transaction_count'] }} transactions • {{ $wData['share_of_month_pct'] }}% of month)</span>
                        </div>
                        <div class="mono" style="font-size: 17px; font-weight: 700; color: var(--text-dark);">
                            {{ $user->currency }}{{ number_format((float)$wData['total'], 2) }}
                        </div>
                    </div>
                    <div class="progress-track" style="margin-bottom: 16px;">
                        <div class="progress-fill" style="width: {{ $wData['bar_width_pct'] }}%; background-color: var(--emerald);"></div>
                    </div>

                    <!-- Daily sequence inside this week -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(125px, 1fr)); gap: 10px; margin-bottom: 16px;">
                        @foreach($wData['days'] as $dItem)
                            <a href="{{ route('tracking.index', ['tab' => 'daily', 'date' => $dItem['date'], 'year' => $year, 'month' => $month]) }}"
                               style="padding: 10px 12px; border-radius: 10px; background-color: {{ (float)$dItem['total'] > 0 ? '#FFFFFF' : 'var(--ivory)' }}; border: 1px solid {{ (float)$dItem['total'] > 0 ? 'var(--mustard)' : 'var(--ivory-border)' }}; box-shadow: {{ (float)$dItem['total'] > 0 ? 'var(--shadow-xs)' : 'none' }};">
                                <div style="font-size: 11px; color: var(--text-muted); font-weight: 600;">{{ $dItem['day_name'] }}</div>
                                <div style="font-size: 10.5px; color: var(--text-muted); margin-bottom: 4px;">{{ $dItem['formatted_date'] }}</div>
                                <div class="mono" style="font-size: 13.5px; font-weight: 700; color: {{ (float)$dItem['total'] > 0 ? 'var(--text-dark)' : 'var(--text-muted)' }};">
                                    {{ $user->currency }}{{ number_format((float)$dItem['total'], 2) }}
                                </div>
                            </a>
                        @endforeach
                    </div>

                    @if($wData['transaction_count'] > 0)
                        @php
                            $flatExplorerWeekTxs = collect();
                            foreach ($wData['days'] as $dItem) {
                                foreach ($dItem['transactions'] as $tx) {
                                    $flatExplorerWeekTxs->push(array_merge($tx, [
                                        'day_short' => $dItem['day_short'],
                                        'formatted_date' => $dItem['formatted_date'],
                                    ]));
                                }
                            }
                            $compoundedExplorerWeekTxs = $flatExplorerWeekTxs->groupBy(fn($t) => preg_replace('/\s+/', ' ', strtolower(trim((string) $t['title']))));
                        @endphp
                        <div class="table-wrap" style="background-color: #FFFFFF; border-radius: 10px; border: 1px solid var(--ivory-border);">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Latest Date</th>
                                        <th>Title</th>
                                        <th>Category</th>
                                        <th>Payment Method</th>
                                        <th style="text-align: right;">Compounded Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($compoundedExplorerWeekTxs as $wGroup)
                                        @php
                                            $tx = $wGroup->last();
                                            $wCount = $wGroup->count();
                                            $wTotal = $wGroup->sum(fn($i) => (float) $i['amount']);
                                            $wDates = $wGroup->map(fn($i) => $i['day_short'] . ' ' . substr($i['formatted_date'], 0, 6) . ' (' . $i['formatted_amount'] . ')')->implode(' • ');
                                        @endphp
                                        <tr>
                                            <td class="mono" style="font-size: 11.5px; color: var(--text-muted); white-space: nowrap;">
                                                {{ $tx['day_short'] }}, {{ $tx['formatted_date'] }}
                                                @if($wCount > 1)
                                                    <div>
                                                        <span class="mono" style="font-size: 9.5px; font-weight: 800; padding: 1.5px 6px; border-radius: 9999px; background-color: var(--coral-soft); color: var(--coral-dark);">
                                                            COMPOUNDED ({{ $wCount }}x)
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <strong>{{ $tx['title'] }}</strong>
                                                @if($wCount > 1)
                                                    <div class="mono" style="font-size: 10px; color: var(--blue); margin-top: 2px;">
                                                        Merged: {{ $wDates }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                                    <span class="cat-swatch" style="background-color: {{ $tx['category_color'] }};"></span>
                                                    {{ $tx['category'] }}
                                                </span>
                                                @if($tx['subcategory'])
                                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px;">↳ {{ $tx['subcategory'] }}</div>
                                                @endif
                                            </td>
                                            <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">{{ $tx['payment_method'] }}</td>
                                            <td class="mono" style="text-align: right; font-weight: 700; color: var(--coral-dark); white-space: nowrap;">
                                                {{ $user->currency }}{{ number_format($wTotal, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </section>
@endif

@if($tab === 'daily')
    <!-- DAILY OVERVIEW -->
    <div class="kpi-grid">
        <div class="kpi-cell bg-coral">
            <div class="kpi-label">Total Daily Spending ({{ $daily['formatted_date'] }})</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$daily['total_spending'], 2) }}</div>
            <div class="kpi-footer">
                <span>vs Prev Day ({{ $daily['previous_date'] }})</span>
                <span class="mono">{{ $daily['comparison']['direction'] === 'up' ? '+' : '' }}{{ $daily['comparison']['percentage'] }}%</span>
            </div>
        </div>
        <div class="kpi-cell bg-plum">
            <div class="kpi-label">Expense Transactions</div>
            <div class="kpi-amount">{{ $daily['transaction_count'] }}</div>
            <div class="kpi-footer">
                <span>Previous Day Total</span>
                <span class="mono">{{ $user->currency }}{{ number_format((float)$daily['comparison']['previous'], 2) }}</span>
            </div>
        </div>
        <div class="kpi-cell bg-emerald">
            <div class="kpi-label">Daily Income Recorded</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$daily['total_income'], 2) }}</div>
            <div class="kpi-footer"><span>Recorded on {{ $daily['date'] }}</span></div>
        </div>
        <div class="kpi-cell bg-charcoal">
            <div class="kpi-label">Quick Day Navigation</div>
            <div style="display: flex; gap: 8px; margin-top: 6px;">
                <a href="{{ route('tracking.index', ['tab' => 'daily', 'date' => $daily['previous_date']]) }}" class="btn btn-ivory btn-sm">← Prev Day</a>
                <a href="{{ route('tracking.index', ['tab' => 'daily', 'date' => $daily['next_date']]) }}" class="btn btn-plum btn-sm">Next Day →</a>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Transactions on {{ $daily['formatted_date'] }}</h3>
                <button type="button" class="btn btn-coral btn-sm" onclick="openQuickModal('expense')">+ Add Expense</button>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Title & Notes</th>
                            <th>Category</th>
                            <th>Payment</th>
                            <th style="text-align: right;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($daily['all_transactions'] as $tx)
                            <tr>
                                <td class="mono">{{ $tx->transaction_time ? substr($tx->transaction_time, 0, 5) : '—' }}</td>
                                <td>
                                    <strong>{{ $tx->title }}</strong>
                                    @if($tx->subcategory)<div style="font-size:11px;color:var(--text-muted);">{{ $tx->subcategory->name }}</div>@endif
                                </td>
                                <td>{{ $tx->category?->name }}</td>
                                <td>{{ $tx->paymentMethod?->name }}</td>
                                <td class="mono" style="text-align: right; font-weight: 700; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                    {{ $user->currency }}{{ number_format((float)$tx->amount, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No transactions recorded on this date.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Daily Category Breakdown</h3>
            </div>
            <div class="section-body">
                @forelse($daily['category_breakdown'] as $cb)
                    <div style="margin-bottom: 14px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 5px;">
                            <span><strong>{{ $cb['name'] }}</strong> ({{ $cb['count'] }} items)</span>
                            <span class="mono">{{ $user->currency }}{{ number_format((float)$cb['amount'], 2) }} ({{ $cb['share_pct'] }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $cb['share_pct'] }}%; background-color: {{ $cb['color'] }};"></div>
                        </div>
                    </div>
                @empty
                    <p style="font-size: 12.5px; color: var(--text-muted);">No expenses recorded for this day.</p>
                @endforelse
            </div>
        </section>
    </div>
@endif

@if($tab === 'weekly')
    <!-- WEEKLY OVERVIEW -->
    <div class="kpi-grid">
        <div class="kpi-cell bg-emerald">
            <div class="kpi-label">Weekly Expenditure ({{ $weekly['week_label'] }})</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$weekly['total_expenditure'], 2) }}</div>
            <div class="kpi-footer">
                <span>vs Prev Week ({{ $user->currency }}{{ number_format((float)$weekly['comparison']['previous'], 2) }})</span>
                <span class="mono">{{ $weekly['comparison']['percentage'] }}%</span>
            </div>
        </div>
        <div class="kpi-cell bg-plum">
            <div class="kpi-label">Average Daily Spend</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$weekly['average_daily'], 2) }}</div>
            <div class="kpi-footer"><span>Weekly Limit: {{ $user->currency }}{{ number_format((float)$weekly['weekly_limit'], 2) }}</span></div>
        </div>
        <div class="kpi-cell bg-coral">
            <div class="kpi-label">Highest Spending Day</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$weekly['highest_day']['amount'], 2) }}</div>
            <div class="kpi-footer"><span>{{ $weekly['highest_day']['name'] }}</span></div>
        </div>
        <div class="kpi-cell bg-mustard">
            <div class="kpi-label">Lowest Spending Day</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)($weekly['lowest_day']['amount'] ?? 0), 2) }}</div>
            <div class="kpi-footer"><span>{{ $weekly['lowest_day']['name'] }}</span></div>
        </div>
    </div>

    <div class="grid-2">
        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Monday to Sunday Breakdown ({{ $weekly['week_label'] }})</h3>
                <div style="display: flex; gap: 6px;">
                    <a href="{{ route('tracking.index', ['tab' => 'weekly', 'date' => $weekly['prev_week_date']]) }}" class="btn btn-ivory btn-sm">← Prev Week</a>
                    <a href="{{ route('tracking.index', ['tab' => 'weekly', 'date' => $weekly['next_week_date']]) }}" class="btn btn-ivory btn-sm">Next Week →</a>
                </div>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Day of Week</th>
                            <th>Date</th>
                            <th>Transactions</th>
                            <th>Share</th>
                            <th style="text-align: right;">Expenditure</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($weekly['days'] as $wDay)
                            <tr>
                                <td><strong>{{ $wDay['day_name'] }}</strong></td>
                                <td class="mono">{{ $wDay['date'] }}</td>
                                <td class="mono">{{ $wDay['count'] }}</td>
                                <td class="mono">{{ $wDay['share_pct'] }}%</td>
                                <td class="mono" style="text-align: right; font-weight: 700;">
                                    <a href="{{ route('tracking.index', ['tab' => 'daily', 'date' => $wDay['date']]) }}" style="color: var(--blue); text-decoration: underline;">
                                        {{ $user->currency }}{{ number_format((float)$wDay['amount'], 2) }}
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Weekly Category Distribution</h3>
            </div>
            <div class="section-body">
                @foreach($weekly['category_distribution'] as $wCat)
                    <div style="margin-bottom: 13px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                            <strong>{{ $wCat['name'] }} ({{ $wCat['count'] }}x)</strong>
                            <span class="mono">{{ $user->currency }}{{ number_format((float)$wCat['amount'], 2) }} ({{ $wCat['share_pct'] }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $wCat['share_pct'] }}%; background-color: {{ $wCat['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endif

@if($tab === 'monthly')
    <!-- MONTHLY OVERVIEW -->
    <div class="kpi-grid">
        <div class="kpi-cell bg-coral">
            <div class="kpi-label">Monthly Expenditure ({{ $monthly['month_label'] }})</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$monthly['total_expenditure'], 2) }}</div>
            <div class="kpi-footer">
                <span>vs Prev Month ({{ $user->currency }}{{ number_format((float)$monthly['comparison']['previous'], 2) }})</span>
                <span class="mono">{{ $monthly['comparison']['percentage'] }}%</span>
            </div>
        </div>
        <div class="kpi-cell bg-emerald">
            <div class="kpi-label">Total Monthly Income</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$monthly['total_income'], 2) }}</div>
            <div class="kpi-footer"><span>Net Cash Flow: {{ $user->currency }}{{ number_format((float)$monthly['net_cash_flow'], 2) }}</span></div>
        </div>
        <div class="kpi-cell bg-mustard">
            <div class="kpi-label">Explicit Monthly Savings</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$monthly['monthly_savings'], 2) }}</div>
            <div class="kpi-footer"><span>Avg Daily Spend: {{ $user->currency }}{{ number_format((float)$monthly['average_daily'], 2) }}</span></div>
        </div>
        <div class="kpi-cell bg-plum">
            <div class="kpi-label">Budget Utilization & Peak Day</div>
            <div class="kpi-amount">{{ $monthly['budget_utilization_pct'] }}%</div>
            <div class="kpi-footer"><span>Peak: {{ $monthly['highest_spending_day']['date'] }} ({{ $user->currency }}{{ number_format($monthly['highest_spending_day']['amount'], 0) }})</span></div>
        </div>
    </div>

    <div class="grid-2">
        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Category-by-Category Expenditure ({{ $monthly['month_label'] }})</h3>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Transactions</th>
                            <th>Share</th>
                            <th style="text-align: right;">Total Spent</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($monthly['categories'] as $mCat)
                            <tr>
                                <td>
                                    <span class="cat-swatch" style="background-color: {{ $mCat['color'] }}; margin-right: 6px;"></span>
                                    <strong>{{ $mCat['name'] }}</strong>
                                </td>
                                <td class="mono">{{ $mCat['count'] }}</td>
                                <td class="mono">{{ $mCat['share_pct'] }}%</td>
                                <td class="mono" style="text-align: right; font-weight: 700;">{{ $user->currency }}{{ number_format((float)$mCat['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Weekly Summaries Within {{ $monthly['month_label'] }}</h3>
            </div>
            <div class="section-body" style="display: flex; flex-direction: column; gap: 12px;">
                @foreach($monthly['weekly_summaries'] as $wSum)
                    <div style="background-color: var(--ivory-alt); padding: 13px 15px; border-radius: 10px; border: 1px solid var(--ivory-border);">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 7px;">
                            <strong>{{ $wSum['title'] }} — {{ $wSum['range_label'] }}</strong>
                            <span class="mono" style="color: var(--text-dark); font-weight: 700;">{{ $user->currency }}{{ number_format((float)$wSum['total'], 2) }} ({{ $wSum['share_of_month_pct'] }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $wSum['bar_width_pct'] }}%; background-color: var(--emerald);"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endif

@if($tab === 'yearly')
    <!-- YEARLY OVERVIEW -->
    <div class="kpi-grid">
        <div class="kpi-cell bg-plum">
            <div class="kpi-label">Total Annual Expenses ({{ $yearly['year'] }})</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$yearly['total_expenses'], 2) }}</div>
            <div class="kpi-footer">
                <span>YoY vs {{ $yearly['year'] - 1 }}: {{ $user->currency }}{{ number_format((float)$yearly['yoy_comparison']['previous'], 2) }}</span>
                <span class="mono">{{ $yearly['yoy_comparison']['percentage'] }}%</span>
            </div>
        </div>
        <div class="kpi-cell bg-emerald">
            <div class="kpi-label">Total Annual Income ({{ $yearly['year'] }})</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$yearly['total_income'], 2) }}</div>
            <div class="kpi-footer"><span>Annual Net: {{ $user->currency }}{{ number_format((float)$yearly['net_cash_flow'], 2) }}</span></div>
        </div>
        <div class="kpi-cell bg-mustard">
            <div class="kpi-label">Annual Explicit Savings</div>
            <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$yearly['annual_savings'], 2) }}</div>
            <div class="kpi-footer"><span>Avg Monthly Spend: {{ $user->currency }}{{ number_format((float)$yearly['average_monthly_expense'], 2) }}</span></div>
        </div>
        <div class="kpi-cell bg-charcoal">
            <div class="kpi-label">Highest & Lowest Spending Months</div>
            <div class="kpi-amount" style="font-size: 16px;">
                High: {{ $yearly['highest_month']['name'] }} ({{ $user->currency }}{{ number_format($yearly['highest_month']['amount'], 0) }})
            </div>
            <div class="kpi-footer">
                <span>Low: {{ $yearly['lowest_month']['name'] }} ({{ $user->currency }}{{ number_format($yearly['lowest_month']['amount'] ?? 0, 0) }})</span>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">January to December Monthly Ledger Breakdown ({{ $yearly['year'] }})</h3>
            </div>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th style="text-align: right;">Income</th>
                            <th style="text-align: right;">Expenses</th>
                            <th style="text-align: right;">Explicit Savings</th>
                            <th style="text-align: right;">Net Cash Flow</th>
                            <th style="text-align: right;">Drill-Down</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($yearly['monthly_series'] as $mRow)
                            <tr>
                                <td><strong>{{ $mRow['name'] }} {{ $yearly['year'] }}</strong></td>
                                <td class="mono" style="text-align: right; color: var(--emerald-dark);">{{ $user->currency }}{{ number_format((float)$mRow['income'], 2) }}</td>
                                <td class="mono" style="text-align: right; color: var(--coral-dark); font-weight: 700;">{{ $user->currency }}{{ number_format((float)$mRow['expense'], 2) }}</td>
                                <td class="mono" style="text-align: right; color: var(--mustard-dark);">{{ $user->currency }}{{ number_format((float)$mRow['savings'], 2) }}</td>
                                <td class="mono" style="text-align: right; font-weight: 700;">{{ $user->currency }}{{ number_format((float)$mRow['net'], 2) }}</td>
                                <td style="text-align: right;">
                                    <a href="{{ route('tracking.index', ['tab' => 'explorer', 'year' => $yearly['year'], 'month' => $mRow['month']]) }}" class="btn btn-ivory btn-sm">Inspect →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>

        <section class="section-block theme-ivory">
            <div class="section-header">
                <h3 class="section-title">Annual Category Distribution ({{ $yearly['year'] }})</h3>
            </div>
            <div class="section-body">
                @foreach($yearly['category_distribution'] as $yCat)
                    <div style="margin-bottom: 13px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12.5px; margin-bottom: 4px;">
                            <strong>{{ $yCat['name'] }} ({{ $yCat['count'] }} transactions)</strong>
                            <span class="mono">{{ $user->currency }}{{ number_format((float)$yCat['amount'], 2) }} ({{ $yCat['share_pct'] }}%)</span>
                        </div>
                        <div class="progress-track">
                            <div class="progress-fill" style="width: {{ $yCat['share_pct'] }}%; background-color: {{ $yCat['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    </div>
@endif
@endsection
