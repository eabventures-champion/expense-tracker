@extends('layouts.app')

@section('kicker', 'Financial Insights')
@section('page_title', 'Automated Behavioral Diagnostics & Cost Reduction Opportunities')

@section('content')
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <div style="font-size: 10.5px; color: var(--emerald); text-transform: uppercase; font-weight: 700;">Calculated From Actual Recorded Transactions</div>
            <h2 class="section-title">Live Spending Intelligence Briefings ({{ $monthly['month_label'] }})</h2>
        </div>
        <span class="mono" style="background-color: var(--mustard-soft); color: var(--mustard-dark); padding: 4px 12px; border-radius: 9999px; font-weight: 700; font-size: 11.5px;">
            {{ count($insights) }} Active Findings
        </span>
    </div>
    <div class="section-body">
        <div class="grid-2">
            @foreach($insights as $insight)
                <div style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); border-top: 4px solid {{ $insight['accent'] }}; border-radius: 14px; padding: 18px 20px; display: flex; flex-direction: column; justify-content: space-between; gap: 14px; box-shadow: var(--shadow-xs);">
                    <div>
                        <!-- Header Row: Category Pill + Title on left, Status Badge on right -->
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 10px;">
                            <div>
                                <span style="display: inline-block; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.06em; color: var(--text-muted); background-color: var(--ivory); padding: 2px 8px; border-radius: 6px; margin-bottom: 4px;">
                                    {{ $insight['category'] }}
                                </span>
                                <div style="font-size: 13px; font-weight: 700; color: var(--text-dark);">
                                    {{ $insight['title'] }}
                                </div>
                            </div>
                            <span class="mono" style="background-color: {{ $insight['accent'] }}; color: #FFFFFF; font-weight: 700; font-size: 11px; padding: 4px 10px; border-radius: 9999px; white-space: nowrap; flex-shrink: 0;">
                                {{ $insight['badge'] }}
                            </span>
                        </div>

                        <!-- Main Headline & Explanation -->
                        <h3 style="font-size: 15px; font-weight: 700; color: var(--text-dark); line-height: 1.35; margin-bottom: 4px;">
                            {{ $insight['headline'] }}
                        </h3>
                        <p style="font-size: 12px; color: var(--text-muted); line-height: 1.45;">
                            {{ $insight['detail'] }}
                        </p>
                    </div>

                    <!-- Structured 3-Column Metrics Strip -->
                    @if(!empty($insight['metrics']))
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 10px; padding: 10px 12px;">
                            @foreach($insight['metrics'] as $m)
                                <div>
                                    <div style="font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.04em; color: var(--text-muted);">
                                        {{ $m['label'] }}
                                    </div>
                                    <div class="mono" style="font-size: 12.5px; font-weight: 700; color: var(--text-dark); margin-top: 2px;">
                                        {{ $m['value'] }}
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</section>

<!-- FULL-WIDTH HIGH-EXPENDITURE CATEGORY RANKING CARD -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h3 class="section-title">High-Expenditure Category Ranking ({{ $monthly['month_label'] }})</h3>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Ranked breakdown of category outflow velocity and share of total monthly spend</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-plum btn-sm" onclick="openTrajectoryModal()">
                Month-to-Month Financial Trajectory ({{ $yearly['year'] }})
            </button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Category & Subcategories</th>
                    <th>Frequency</th>
                    <th>Share of Spend</th>
                    <th style="text-align: right;">Total Expenditure</th>
                </tr>
            </thead>
            <tbody>
                @foreach($monthly['categories'] as $idx => $cat)
                    <tr>
                        <td class="mono" style="color: var(--text-muted); white-space: nowrap;">#{{ $idx + 1 }}</td>
                        <td>
                            <div style="display: flex; align-items: center;">
                                <span class="cat-swatch" style="background-color: {{ $cat['color'] }}; margin-right: 7px;"></span>
                                <strong>{{ $cat['name'] }}</strong>
                            </div>
                            @if(!empty($cat['subcategories']))
                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-top: 6px; padding-left: 14px;">
                                    @foreach($cat['subcategories'] as $subItem)
                                        <span style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 9999px; padding: 2px 8px; font-size: 10.5px; color: var(--text-muted); display: inline-flex; align-items: center; gap: 4px;">
                                            <span>↳ {{ $subItem['name'] }}</span>
                                            @if(($subItem['amount'] ?? 0) > 0)
                                                <strong class="mono" style="color: var(--text-dark); font-size: 10px;">
                                                    ({{ $user->currency }}{{ number_format((float)$subItem['amount'], 2) }})
                                                </strong>
                                            @endif
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </td>
                        <td class="mono" style="white-space: nowrap;">{{ $cat['count'] }} tx</td>
                        <td style="min-width: 160px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="mono" style="font-weight: 600; min-width: 44px;">{{ $cat['share_pct'] }}%</span>
                                <div class="progress-track" style="flex: 1;">
                                    <div class="progress-fill" style="width: {{ min(100, $cat['share_pct']) }}%; background-color: {{ $cat['color'] }};"></div>
                                </div>
                            </div>
                        </td>
                        <td class="mono" style="text-align: right; font-weight: 700; white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$cat['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL: MONTH-TO-MONTH FINANCIAL TRAJECTORY -->
<div id="trajectoryModal" class="modal-backdrop" onclick="if(event.target === this) closeTrajectoryModal()">
    <div class="modal-box" style="max-width: 680px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Annual Cash Flow Trajectory</div>
                <h3 style="font-size: 16px;">Month-to-Month Financial Trajectory ({{ $yearly['year'] }})</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeTrajectoryModal()">✕ Close</button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th style="text-align: right;">Income</th>
                            <th style="text-align: right;">Expenditure</th>
                            <th style="text-align: right;">Net Surplus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($yearly['monthly_series'] as $mRow)
                            <tr>
                                <td><strong>{{ $mRow['name'] }} {{ $yearly['year'] }}</strong></td>
                                <td class="mono" style="text-align: right; color: var(--emerald-dark); white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$mRow['income'], 2) }}</td>
                                <td class="mono" style="text-align: right; color: var(--coral-dark); white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$mRow['expense'], 2) }}</td>
                                <td class="mono" style="text-align: right; color: var(--blue); font-weight: 700; white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$mRow['net'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openTrajectoryModal() {
        document.getElementById('trajectoryModal')?.classList.add('open');
    }

    function closeTrajectoryModal() {
        document.getElementById('trajectoryModal')?.classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeTrajectoryModal();
        }
    });
</script>
@endpush
