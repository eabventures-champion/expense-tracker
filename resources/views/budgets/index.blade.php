@extends('layouts.app')

@section('kicker', 'Budgets & Limits')
@section('page_title', 'Weekly, Monthly & Yearly Spending Targets vs Actuals')

@section('content')
<div class="kpi-grid">
    <div class="kpi-cell bg-plum">
        <div class="kpi-label">Overall Monthly Budget ({{ $monthlyOverview['month_label'] }})</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$monthlyOverview['budget_limit'], 2) }}</div>
        <div class="kpi-footer">
            <span>Actual Spent: {{ $user->currency }}{{ number_format((float)$monthlyOverview['total_expenditure'], 2) }}</span>
            <span class="mono">{{ $monthlyOverview['budget_utilization_pct'] }}%</span>
        </div>
    </div>
    <div class="kpi-cell bg-emerald">
        <div class="kpi-label">Monthly Budget Remaining</div>
        @php
            $rem = (float)$monthlyOverview['budget_limit'] - (float)$monthlyOverview['total_expenditure'];
        @endphp
        <div class="kpi-amount">{{ $user->currency }}{{ number_format($rem, 2) }}</div>
        <div class="kpi-footer"><span>{{ $rem >= 0 ? 'Within monthly ceiling' : 'Monthly budget exceeded' }}</span></div>
    </div>
    <div class="kpi-cell bg-charcoal">
        <div class="kpi-label">Weekly Spending Limit</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$user->weekly_spending_limit, 2) }}</div>
        <div class="kpi-footer">
            <span>Current Week: {{ $user->currency }}{{ number_format((float)$weeklyOverview['total_expenditure'], 2) }}</span>
        </div>
    </div>
    <div class="kpi-cell bg-blue">
        <div class="kpi-label">Yearly Spending Target ({{ $year }})</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$user->yearly_spending_target, 2) }}</div>
        <div class="kpi-footer">
            <span>YTD Spent: {{ $user->currency }}{{ number_format((float)$yearlyOverview['total_expenses'], 2) }}</span>
        </div>
    </div>
</div>

<!-- FULL-WIDTH CATEGORY PLANNED VS ACTUAL EXPENDITURE CARD -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h2 class="section-title">Category Planned vs Actual Expenditure ({{ $monthlyOverview['month_label'] }})</h2>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Track category spending ceilings against live reconciled expenditure</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-emerald btn-sm" onclick="openPeriodCeilingsModal()">Set Overall Period Ceilings</button>
            <button type="button" class="btn btn-plum btn-sm" onclick="openCategoryAllocationModal()">+ Assign Category Budget Limit</button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Category</th>
                    <th style="text-align: right;">Planned Budget</th>
                    <th style="text-align: right;">Actual Spent</th>
                    <th style="text-align: right;">Remaining</th>
                    <th style="text-align: right;">Exceeded</th>
                    <th>Utilization</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categoryBudgetRows as $row)
                    @php
                        $barColor = $row['is_exceeded'] ? '#E66D55' : ($row['utilization_pct'] >= 80 ? '#E5A93C' : '#2D9F78');
                    @endphp
                    <tr>
                        <td style="white-space: nowrap;">
                            <span class="cat-swatch" style="background-color: {{ $row['color'] }}; margin-right: 6px;"></span>
                            <strong>{{ $row['category_name'] }}</strong>
                        </td>
                        <td class="mono" style="text-align: right; white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$row['planned'], 2) }}</td>
                        <td class="mono" style="text-align: right; font-weight: 700; white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$row['actual'], 2) }}</td>
                        <td class="mono" style="text-align: right; color: var(--emerald-dark); white-space: nowrap;">{{ $user->currency }}{{ number_format((float)$row['remaining'], 2) }}</td>
                        <td class="mono" style="text-align: right; color: {{ $row['is_exceeded'] ? 'var(--coral-dark)' : 'var(--text-muted)' }}; font-weight: 700; white-space: nowrap;">
                            {{ (float)$row['exceeded'] > 0 ? $user->currency . number_format((float)$row['exceeded'], 2) : '—' }}
                        </td>
                        <td style="min-width: 180px;">
                            <div style="display: flex; justify-content: space-between; font-size: 10.5px; margin-bottom: 3px;">
                                <span style="font-weight: 700; color: {{ $barColor }};">{{ $row['utilization_pct'] }}%</span>
                                <span style="color: var(--text-muted);">{{ $row['is_exceeded'] ? 'EXCEEDED' : ($row['utilization_pct'] >= 80 ? 'NEAR LIMIT' : 'ON TRACK') }}</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width: {{ min(100, $row['utilization_pct']) }}%; background-color: {{ $barColor }};"></div>
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <form action="{{ route('budgets.allocations.destroy', $row['allocation_id']) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-ivory btn-sm" title="Remove category limit">✕</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; padding: 22px; color: var(--text-muted);">No category budgets allocated yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL 1: SET OVERALL PERIOD CEILINGS -->
<div id="periodCeilingsModal" class="modal-backdrop" onclick="if(event.target === this) closePeriodCeilingsModal()">
    <div class="modal-box" style="max-width: 480px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Master Spending Thresholds</div>
                <h3 style="font-size: 16px;">Set Overall Period Ceilings</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closePeriodCeilingsModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('budgets.targets') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <div class="form-group">
                    <label class="form-label">Overall Monthly Budget ({{ $user->currency }})</label>
                    <input type="number" step="0.01" min="0" name="overall_monthly_budget" id="ceiling_monthly_input" value="{{ $monthlyOverview['budget_limit'] }}" class="form-control mono" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Weekly Spending Limit ({{ $user->currency }})</label>
                    <input type="number" step="0.01" min="0" name="weekly_spending_limit" value="{{ $user->weekly_spending_limit }}" class="form-control mono" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Yearly Spending Target ({{ $user->currency }})</label>
                    <input type="number" step="0.01" min="0" name="yearly_spending_target" value="{{ $user->yearly_spending_target }}" class="form-control mono" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closePeriodCeilingsModal()">Cancel</button>
                    <button type="submit" class="btn btn-emerald">Update Master Targets</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: ASSIGN CATEGORY BUDGET LIMIT -->
<div id="categoryAllocationModal" class="modal-backdrop" onclick="if(event.target === this) closeCategoryAllocationModal()">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Category Allocation</div>
                <h3 style="font-size: 16px;">Assign Category Budget Limit</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeCategoryAllocationModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('budgets.allocations.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <input type="hidden" name="year" value="{{ $year }}">
                <input type="hidden" name="month" value="{{ $month }}">
                <div class="form-group">
                    <label class="form-label">Expense Category</label>
                    <select name="expense_category_id" class="form-control" required>
                        @foreach($categories->where('type', 'expense') as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Monthly Allocated Limit ({{ $user->currency }})</label>
                    <input type="number" step="0.01" min="0.01" name="allocated_amount" id="alloc_amount_input" class="form-control mono" placeholder="e.g., 850.00" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeCategoryAllocationModal()">Cancel</button>
                    <button type="submit" class="btn btn-plum">Save Category Limit</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openPeriodCeilingsModal() {
        document.getElementById('periodCeilingsModal')?.classList.add('open');
        setTimeout(() => document.getElementById('ceiling_monthly_input')?.focus(), 50);
    }

    function closePeriodCeilingsModal() {
        document.getElementById('periodCeilingsModal')?.classList.remove('open');
    }

    function openCategoryAllocationModal() {
        document.getElementById('categoryAllocationModal')?.classList.add('open');
        setTimeout(() => document.getElementById('alloc_amount_input')?.focus(), 50);
    }

    function closeCategoryAllocationModal() {
        document.getElementById('categoryAllocationModal')?.classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closePeriodCeilingsModal();
            closeCategoryAllocationModal();
        }
    });
</script>
@endpush
