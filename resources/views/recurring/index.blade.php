@extends('layouts.app')

@section('kicker', 'Recurring Bills')
@section('page_title', 'Scheduled Commitments, Bills & Controlled Execution')

@section('content')
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-cell bg-plum">
        <div class="kpi-label">Active Recurring Schedules</div>
        <div class="kpi-amount">{{ $recurringList->where('status', 'active')->count() }}</div>
        <div class="kpi-footer"><span>Daily, Weekly, Monthly, Quarterly & Annual</span></div>
    </div>
    <div class="kpi-cell bg-coral">
        <div class="kpi-label">Estimated Monthly Commitment Load</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$monthlyCommitmentTotal, 2) }}</div>
        <div class="kpi-footer"><span>Weekly × 4.33 wks/mo • Daily × 30d • Annual ÷ 12</span></div>
    </div>
    <div class="kpi-cell bg-emerald">
        <div class="kpi-label">Anti-Duplication Guard</div>
        <div class="kpi-amount" style="font-size: 16px;">Controlled Ledger Posting</div>
        <div class="kpi-footer"><span>Posts installment & advances next due date</span></div>
    </div>
</div>

<!-- FULL-WIDTH SCHEDULED RECURRING COMMITMENTS & PAYMENT REMINDERS CARD -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h2 class="section-title">Scheduled Recurring Commitments & Payment Reminders</h2>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Automated reminders & one-click ledger posting for recurring bills and inflows</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-plum btn-sm" onclick="openScheduleRecurringModal()">+ Schedule New Recurring Commitment</button>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Commitment Title</th>
                    <th>Category</th>
                    <th>Frequency</th>
                    <th>Last Processed</th>
                    <th>Next Due Date</th>
                    <th style="text-align: right;">Amount</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recurringList as $rec)
                    @php
                        $todayStart = now()->startOfDay();
                        $dueStart = $rec->next_due_date->copy()->startOfDay();
                        $daysUntilDue = (int) $todayStart->diffInDays($dueStart, false);
                        $reminderLeadDays = (int) ($rec->reminder_days_before ?? 3);
                        $reminderTriggerDate = $dueStart->copy()->subDays($reminderLeadDays);
                        $isInReminderWindow = $daysUntilDue <= $reminderLeadDays;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $rec->title }}</strong>
                            <div style="font-size: 11.5px; color: var(--text-muted); display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 2px;">
                                <span>{{ $rec->paymentMethod?->name ?? '—' }} • Reminder {{ $reminderLeadDays }}d before ({{ $reminderTriggerDate->format('d M Y') }})</span>
                                @if($daysUntilDue < 0)
                                    <span class="mono" style="font-size: 9.5px; font-weight: 800; padding: 1.5px 7px; border-radius: 9999px; background-color: var(--coral-soft); color: var(--coral-dark);">
                                        OVERDUE ({{ abs($daysUntilDue) }}d)
                                    </span>
                                @elseif($daysUntilDue === 0)
                                    <span class="mono" style="font-size: 9.5px; font-weight: 800; padding: 1.5px 7px; border-radius: 9999px; background-color: var(--coral-soft); color: var(--coral-dark);">
                                        🔔 DUE TODAY
                                    </span>
                                @elseif($isInReminderWindow)
                                    <span class="mono" style="font-size: 9.5px; font-weight: 800; padding: 1.5px 7px; border-radius: 9999px; background-color: rgba(229, 169, 60, 0.2); color: #9E6B12;">
                                        🔔 REMINDER ACTIVE (Due in {{ $daysUntilDue }}d)
                                    </span>
                                @else
                                    <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 7px; border-radius: 9999px; background-color: var(--ivory-alt); color: var(--text-muted); border: 1px solid var(--ivory-border);">
                                        In {{ $daysUntilDue }}d
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            <div>
                                <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                    <span class="cat-swatch" style="background-color: {{ $rec->category?->color ?? '#2D9F78' }};"></span>
                                    {{ $rec->category?->name ?? 'General' }}
                                </span>
                            </div>
                            @if($rec->subcategory)
                                <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px; padding-left: 4px;">↳ {{ $rec->subcategory->name }}</div>
                            @endif
                        </td>
                        <td class="mono" style="text-transform: uppercase; font-size: 11px; font-weight: 700; color: var(--text-muted); white-space: nowrap;">{{ $rec->frequency }}</td>
                        <td class="mono" style="font-size: 11.5px; color: var(--text-muted); white-space: nowrap;">{{ $rec->last_processed_date ? $rec->last_processed_date->format('d M Y') : '—' }}</td>
                        <td class="mono" style="font-size: 12px; font-weight: 700; color: var(--coral-dark); white-space: nowrap;">{{ $rec->next_due_date->format('d M Y') }}</td>
                        <td class="mono" style="text-align: right; font-weight: 700; white-space: nowrap;">
                            {{ $user->currency }}{{ number_format((float)$rec->amount, 2) }}
                            @php
                                $recAmt = (float) $rec->amount;
                                $recMonthlyEq = match ($rec->frequency) {
                                    'daily' => $recAmt * 30,
                                    'weekly' => $recAmt * 4.33,
                                    'quarterly' => $recAmt / 3,
                                    'yearly' => $recAmt / 12,
                                    default => $recAmt,
                                };
                            @endphp
                            @if($rec->frequency !== 'monthly')
                                <div style="font-size: 10px; color: var(--text-muted); font-weight: 500;">
                                    ≈ {{ $user->currency }}{{ number_format($recMonthlyEq, 2) }}/mo
                                </div>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <div style="display: inline-flex; align-items: center; gap: 5px; flex-wrap: wrap; justify-content: flex-end;">
                                <form action="{{ route('recurring.process', $rec) }}" method="POST" style="display:inline; margin: 0;">
                                    @csrf
                                    <input type="hidden" name="post_mode" value="today">
                                    <button type="submit"
                                            class="btn btn-emerald btn-sm"
                                            style="padding: 3px 8px; font-size: 11px;"
                                            title="Record this installment in the ledger on today's date ({{ now()->format('d M Y') }}) and advance Next Due Date">
                                        Post Today ({{ now()->format('d M') }})
                                    </button>
                                </form>
                                @if($rec->next_due_date->toDateString() !== now()->toDateString())
                                    <form action="{{ route('recurring.process', $rec) }}" method="POST" style="display:inline; margin: 0;">
                                        @csrf
                                        <input type="hidden" name="post_mode" value="due_date">
                                        <button type="submit"
                                                class="btn btn-plum btn-sm"
                                                style="padding: 3px 8px; font-size: 11px;"
                                                title="Record this installment on its scheduled due date ({{ $rec->next_due_date->format('d M Y') }}) and advance Next Due Date">
                                            Post on Due ({{ $rec->next_due_date->format('d M') }})
                                        </button>
                                    </form>
                                @endif
                                <button type="button"
                                        class="btn btn-ivory btn-sm"
                                        style="padding: 3px 8px; font-size: 11px;"
                                        title="Edit recurring schedule"
                                        onclick='openEditRecurringModal(@json($rec))'>
                                    ✎ Edit
                                </button>
                                <form action="{{ route('recurring.destroy', $rec) }}" method="POST" style="display:inline; margin: 0;" onsubmit="return confirm('Remove schedule?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 8px; font-size: 11px;" title="Remove schedule">Del</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">No recurring schedules configured yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- MODAL: SCHEDULE NEW RECURRING COMMITMENT -->
<div id="scheduleRecurringModal" class="modal-backdrop" onclick="if(event.target === this) closeScheduleRecurringModal()">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Recurring Commitments</div>
                <h3 style="font-size: 16px;">Schedule New Recurring Commitment</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeScheduleRecurringModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('recurring.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="rec_modal_title" class="form-control" placeholder="e.g., GWCL Monthly Water Bill" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount ({{ $user->currency }})</label>
                        <input type="number" step="0.01" min="0.01" name="amount" class="form-control mono" placeholder="0.00" required>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="expense_category_id" id="rec_modal_category" class="form-control" onchange="syncRecurringSubcategories()" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" data-subcategories='@json($cat->subcategories)'>
                                    {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subcategory (Optional)</label>
                        <select name="expense_subcategory_id" id="rec_modal_subcategory" class="form-control">
                            <option value="">General / Unspecified</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method_id" class="form-control">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" class="form-control">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly" selected>Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Annual</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="form-group">
                        <label class="form-label">Next Due Date</label>
                        <input type="date" name="next_due_date" value="{{ now()->addMonth()->startOfMonth()->toDateString() }}" class="form-control mono" required>
                        <input type="hidden" name="start_date" value="{{ now()->toDateString() }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remind Me (Days Before Due)</label>
                        <input type="number" min="0" max="30" name="reminder_days_before" value="3" class="form-control mono" required>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeScheduleRecurringModal()">Cancel</button>
                    <button type="submit" class="btn btn-plum">Save Recurring Schedule</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: EDIT EXISTING RECURRING COMMITMENT -->
<div id="editRecurringModal" class="modal-backdrop" onclick="if(event.target === this) closeEditRecurringModal()">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Recurring Commitments</div>
                <h3 style="font-size: 16px;">Edit Recurring Commitment</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeEditRecurringModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form id="editRecurringForm" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Title</label>
                    <input type="text" name="title" id="edit_rec_title" class="form-control" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_rec_type" class="form-control">
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount ({{ $user->currency }})</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="edit_rec_amount" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="expense_category_id" id="edit_rec_category" class="form-control" onchange="syncEditRecurringSubcategories()" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" data-subcategories='@json($cat->subcategories)'>
                                    {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subcategory (Optional)</label>
                        <select name="expense_subcategory_id" id="edit_rec_subcategory" class="form-control">
                            <option value="">General / Unspecified</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method_id" id="edit_rec_pm" class="form-control">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Frequency</label>
                        <select name="frequency" id="edit_rec_frequency" class="form-control">
                            <option value="daily">Daily</option>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="yearly">Annual</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr);">
                    <div class="form-group">
                        <label class="form-label">Next Due Date</label>
                        <input type="date" name="next_due_date" id="edit_rec_next_due_date" class="form-control mono" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Remind Me (Days Before Due)</label>
                        <input type="number" min="0" max="30" name="reminder_days_before" id="edit_rec_reminder_days" value="3" class="form-control mono" required>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeEditRecurringModal()">Cancel</button>
                    <button type="submit" class="btn btn-emerald">Save Schedule Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function syncRecurringSubcategories(preselectedSubcategoryId = null) {
        const catSelect = document.getElementById('rec_modal_category');
        const subSelect = document.getElementById('rec_modal_subcategory');
        if (!catSelect || !subSelect) return;

        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        subSelect.innerHTML = '<option value="">General / Unspecified</option>';
        if (!selectedOpt) return;

        try {
            const subs = JSON.parse(selectedOpt.getAttribute('data-subcategories') || '[]');
            subs.forEach(function (sub) {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                if (preselectedSubcategoryId && String(sub.id) === String(preselectedSubcategoryId)) {
                    opt.selected = true;
                }
                subSelect.appendChild(opt);
            });
        } catch (e) {}
    }

    function syncEditRecurringSubcategories(preselectedSubcategoryId = null) {
        const catSelect = document.getElementById('edit_rec_category');
        const subSelect = document.getElementById('edit_rec_subcategory');
        if (!catSelect || !subSelect) return;

        const selectedOpt = catSelect.options[catSelect.selectedIndex];
        subSelect.innerHTML = '<option value="">General / Unspecified</option>';
        if (!selectedOpt) return;

        try {
            const subs = JSON.parse(selectedOpt.getAttribute('data-subcategories') || '[]');
            subs.forEach(function (sub) {
                const opt = document.createElement('option');
                opt.value = sub.id;
                opt.textContent = sub.name;
                if (preselectedSubcategoryId && String(sub.id) === String(preselectedSubcategoryId)) {
                    opt.selected = true;
                }
                subSelect.appendChild(opt);
            });
        } catch (e) {}
    }

    function openScheduleRecurringModal() {
        syncRecurringSubcategories();
        document.getElementById('scheduleRecurringModal')?.classList.add('open');
        setTimeout(() => document.getElementById('rec_modal_title')?.focus(), 50);
    }

    function closeScheduleRecurringModal() {
        document.getElementById('scheduleRecurringModal')?.classList.remove('open');
    }

    function openEditRecurringModal(rec) {
        const form = document.getElementById('editRecurringForm');
        if (form) {
            form.action = '/recurring/' + rec.id;
        }
        document.getElementById('edit_rec_title').value = rec.title || '';
        document.getElementById('edit_rec_type').value = rec.type || 'expense';
        document.getElementById('edit_rec_amount').value = rec.amount || '';
        document.getElementById('edit_rec_category').value = rec.expense_category_id || '';
        syncEditRecurringSubcategories(rec.expense_subcategory_id || '');
        document.getElementById('edit_rec_pm').value = rec.payment_method_id || '';
        document.getElementById('edit_rec_frequency').value = rec.frequency || 'monthly';
        document.getElementById('edit_rec_next_due_date').value = (rec.next_due_date || '').substring(0, 10);
        document.getElementById('edit_rec_reminder_days').value = rec.reminder_days_before ?? 3;
        document.getElementById('editRecurringModal')?.classList.add('open');
        setTimeout(() => document.getElementById('edit_rec_title')?.focus(), 50);
    }

    function closeEditRecurringModal() {
        document.getElementById('editRecurringModal')?.classList.remove('open');
    }

    document.addEventListener('DOMContentLoaded', function () {
        syncRecurringSubcategories();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeScheduleRecurringModal();
            closeEditRecurringModal();
        }
    });
</script>
@endpush
