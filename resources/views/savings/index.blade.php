@extends('layouts.app')

@section('kicker', 'Savings & Goals')
@section('page_title', 'Dedicated Savings Vaults & Capital Accumulation Targets')

@section('content')
@php
    $activeGoals = $goals->where('status', '!=', 'archived')->values();
    $archivedGoals = $goals->where('status', 'archived')->values();
@endphp
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-cell bg-emerald">
        <div class="kpi-label">Cumulative Explicit Savings</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$totalExplicitSavings, 2) }}</div>
        <div class="kpi-footer"><span>Strictly recorded deposits minus withdrawals</span></div>
    </div>
    <div class="kpi-cell bg-mustard">
        <div class="kpi-label">Savings Added This Month (Nov 2026)</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$thisMonthSavings, 2) }}</div>
        <div class="kpi-footer" style="flex-direction: column; align-items: flex-start; gap: 3px;">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%; font-size: 11px;">
                <span>From Monthly Income: <strong class="mono">{{ $user->currency }}{{ number_format((float)($thisMonthIncomeFundedSavings ?? 0), 2) }}</strong></span>
                <span>Free Capital: <strong class="mono">{{ $user->currency }}{{ number_format((float)($thisMonthFreeCapitalSavings ?? 0), 2) }}</strong></span>
            </div>
        </div>
    </div>
    <div class="kpi-cell bg-plum">
        <div class="kpi-label">Active Financial Goals</div>
        <div class="kpi-amount">{{ $activeGoals->count() }}</div>
        <div class="kpi-footer">
            <span>
                @if($archivedGoals->count() > 0)
                    {{ $archivedGoals->count() }} completed goal(s) archived
                @else
                    Treasury Bills, Real Estate & Equipment
                @endif
            </span>
        </div>
    </div>
</div>

<!-- FINANCIAL GOALS CARDS -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h2 class="section-title">Active Capital & Savings Goals ({{ $activeGoals->count() }})</h2>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Once a savings goal reaches 100% of its target, it can be archived or deleted
            </div>
        </div>
        <button type="button" class="btn btn-plum btn-sm" onclick="openGoalModal()">+ Create New Financial Goal</button>
    </div>
    <div class="section-body">
        <div class="grid-3">
            @forelse($activeGoals as $goal)
                @php
                    $targetAmt = (float) $goal->target_amount;
                    $currentAmt = (float) $goal->current_amount;
                    $pct = $targetAmt > 0 ? round(($currentAmt / $targetAmt) * 100, 1) : 0;
                    $remainingAmt = max(0, $targetAmt - $currentAmt);
                    $overflowAmt = max(0, $currentAmt - $targetAmt);
                    $isOverflowing = $overflowAmt > 0;
                    $isReached = $targetAmt > 0 && $currentAmt >= $targetAmt;
                @endphp
                <div style="background-color: {{ $isOverflowing ? '#FDFBF5' : 'var(--ivory-alt)' }}; border: 1.5px solid {{ $isOverflowing ? 'rgba(229, 169, 60, 0.55)' : ($isReached ? 'rgba(45, 159, 120, 0.45)' : 'var(--ivory-border)') }}; border-top: 4px solid {{ $goal->color }}; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; gap: 10px; box-shadow: {{ $isOverflowing ? '0 8px 22px -8px rgba(229, 169, 60, 0.25)' : 'none' }};">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap;">
                        <span style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">{{ $goal->category }}</span>
                        <div style="display: inline-flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                            @if($isOverflowing)
                                <span class="mono" style="background: linear-gradient(135deg, #1E1B2E 0%, #2D2747 100%); color: #F5C469; border: 1px solid rgba(229, 169, 60, 0.45); font-weight: 800; font-size: 10.5px; padding: 2px 9px; border-radius: 9999px;">
                                    ✦ +{{ $user->currency }}{{ number_format($overflowAmt, 2) }} OVERFLOW
                                </span>
                            @endif
                            <span class="mono" style="background-color: {{ $goal->color }}; color: #FFFFFF; font-weight: 700; font-size: 11px; padding: 2px 9px; border-radius: 9999px;">{{ $pct }}%</span>
                        </div>
                    </div>
                    <h3 style="font-size: 15.5px; color: var(--text-dark);">{{ $goal->title }}</h3>
                    <div class="mono" style="font-size: 18px; font-weight: 700; color: var(--text-dark);">
                        {{ $user->currency }}{{ number_format($currentAmt, 2) }}
                        <span style="font-size: 12px; color: var(--text-muted); font-weight: 500;">/ {{ $user->currency }}{{ number_format($targetAmt, 2) }}</span>
                    </div>
                    <div class="progress-track" style="position: relative; height: 9px;">
                        <div class="progress-fill" style="width: {{ min(100, $pct) }}%; background: {{ $isOverflowing ? 'linear-gradient(90deg, ' . $goal->color . ' 0%, #E5A93C 100%)' : $goal->color }};"></div>
                    </div>

                    @if($isOverflowing)
                        <div style="background: linear-gradient(135deg, #1E1B2E 0%, #27233A 100%); color: #FFFFFF; border-radius: 10px; padding: 9px 12px; display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; border: 1px solid rgba(229, 169, 60, 0.35);">
                            <div style="display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 700; color: #5CE0B5;">
                                <span>✓ Target Achieved</span>
                            </div>
                            <div class="mono" style="font-size: 11.5px; font-weight: 800; color: #F5C469;">
                                Surplus: +{{ $user->currency }}{{ number_format($overflowAmt, 2) }}
                            </div>
                        </div>
                    @elseif($isReached)
                        <div style="background-color: #F4FBF8; border: 1px solid rgba(45, 159, 120, 0.3); border-radius: 9px; padding: 7px 11px; display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="font-weight: 700; color: var(--emerald-dark);">✓ 100% Target Reached</span>
                            <span class="mono" style="font-weight: 700; color: var(--emerald-dark);">Ready to Archive</span>
                        </div>
                    @else
                        <div style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); border-radius: 9px; padding: 7px 11px; display: flex; align-items: center; justify-content: space-between; font-size: 11.5px;">
                            <span style="color: var(--text-muted); font-weight: 600;">Remaining to Hit Target:</span>
                            <span class="mono" style="font-weight: 700; color: var(--text-dark);">{{ $user->currency }}{{ number_format($remainingAmt, 2) }}</span>
                        </div>
                    @endif

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap; font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">
                        <span>Target Date: {{ $goal->target_date ? $goal->target_date->format('d M Y') : 'Open' }}</span>
                        <div style="display: inline-flex; align-items: center; gap: 5px; flex-wrap: wrap;">
                            <button type="button" class="btn btn-emerald btn-sm" style="padding: 3px 9px; font-size: 11px;" onclick="openSavingsTransferModal({{ $goal->id }})">+ Deposit</button>
                            <button type="button"
                                    class="btn btn-ivory btn-sm"
                                    style="padding: 3px 8px; font-size: 11px;"
                                    onclick="openEditGoalModal({{ $goal->id }}, @js($goal->title), @js($goal->category), '{{ number_format((float)$goal->target_amount, 2, '.', '') }}', '{{ $goal->target_date ? $goal->target_date->format('Y-m-d') : '' }}', @js($goal->color))"
                                    title="Edit financial goal '{{ $goal->title }}'">
                                ✎ Edit
                            </button>
                            @if($isReached)
                                <form action="{{ route('savings.goals.archive', $goal) }}" method="POST" style="display: inline; margin: 0;">
                                    @csrf
                                    <button type="submit" class="btn btn-plum btn-sm" style="padding: 3px 9px; font-size: 11px;" title="Archive this completed goal">
                                        📦 Archive
                                    </button>
                                </form>
                            @endif
                            <form action="{{ route('savings.goals.destroy', $goal) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete financial goal \'{{ addslashes($goal->title) }}\' and its linked savings entries?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 8px; font-size: 11px;" title="Delete financial goal">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1 / -1; text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px;">
                    <strong>No active financial goals right now.</strong> Click <strong>+ Create New Financial Goal</strong> above to start a new savings target.
                </div>
            @endforelse
        </div>
    </div>
</section>

@if($archivedGoals->isNotEmpty())
<section class="section-block theme-ivory" style="border: 1px dashed var(--ivory-border);">
    <div class="section-header" style="background-color: var(--ivory-alt);">
        <div>
            <h3 class="section-title" style="font-size: 14.5px;">📦 Archived & Completed Financial Goals ({{ $archivedGoals->count() }})</h3>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Reached savings goals that have been archived</div>
        </div>
    </div>
    <div class="section-body">
        <div class="grid-3">
            @foreach($archivedGoals as $aGoal)
                @php
                    $aTarget = (float) $aGoal->target_amount;
                    $aCurrent = (float) $aGoal->current_amount;
                    $aPct = $aTarget > 0 ? round(($aCurrent / $aTarget) * 100, 1) : 100;
                    $aOverflow = max(0, $aCurrent - $aTarget);
                @endphp
                <div style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); border-left: 4px solid {{ $aGoal->color }}; border-radius: 12px; padding: 15px; display: flex; flex-direction: column; gap: 8px; opacity: 0.92;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px;">
                        <span class="mono" style="font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; background-color: var(--ivory); color: var(--plum);">
                            📦 ARCHIVED • {{ $aPct }}%
                        </span>
                        @if($aOverflow > 0)
                            <span class="mono" style="font-size: 10px; font-weight: 800; color: #966611; background-color: rgba(229, 169, 60, 0.18); padding: 2px 8px; border-radius: 9999px;">
                                +{{ $user->currency }}{{ number_format($aOverflow, 2) }} Surplus
                            </span>
                        @endif
                    </div>
                    <strong style="font-size: 14.5px; color: var(--text-dark);">{{ $aGoal->title }}</strong>
                    <div class="mono" style="font-size: 15px; font-weight: 700; color: var(--emerald-dark);">
                        {{ $user->currency }}{{ number_format($aCurrent, 2) }}
                        <span style="font-size: 11.5px; color: var(--text-muted); font-weight: 500;">/ {{ $user->currency }}{{ number_format($aTarget, 2) }}</span>
                    </div>
                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 6px; padding-top: 6px; border-top: 1px solid var(--ivory-border);">
                        <form action="{{ route('savings.goals.archive', $aGoal) }}" method="POST" style="display: inline; margin: 0;">
                            @csrf
                            <button type="submit" class="btn btn-ivory btn-sm" style="padding: 3px 9px; font-size: 11px;">
                                ↺ Unarchive
                            </button>
                        </form>
                        <form action="{{ route('savings.goals.destroy', $aGoal) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Permanently delete archived goal \'{{ addslashes($aGoal->title) }}\'?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 9px; font-size: 11px;">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

<!-- FULL-WIDTH EXPLICIT SAVINGS DEPOSIT & WITHDRAWAL LEDGER CARD -->
<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h3 class="section-title">Explicit Savings Deposit & Withdrawal Ledger</h3>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">All explicitly recorded transfers into and out of dedicated savings vaults</div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <button type="button" class="btn btn-emerald btn-sm" onclick="openSavingsTransferModal()">+ Record Explicit Savings Transfer</button>
            <button type="button" class="btn btn-plum btn-sm" onclick="openGoalModal()">+ Create New Financial Goal</button>
        </div>
    </div>
    <div class="table-wrap">
        @php
            $compoundedGoalGroups = $savingsRecords->groupBy(fn($sr) => $sr->financial_goal_id ?: 'general_reserve');
        @endphp
        <table class="data-table">
            <thead>
                <tr>
                    <th>Latest Date & Action</th>
                    <th>Compounded Summary & Vault</th>
                    <th>Linked Goal & Method</th>
                    <th style="text-align: right;">Compounded Amount</th>
                    <th style="text-align: right;">Manage</th>
                </tr>
            </thead>
            <tbody>
                @forelse($compoundedGoalGroups as $groupKey => $groupRecords)
                    @php
                        $latestSr = $groupRecords->first();
                        $entryCount = $groupRecords->count();
                        $goalTitle = $latestSr->financialGoal?->title ?? 'General Reserve';
                        $goalColor = $latestSr->financialGoal?->color ?? '#2D9F78';
                        $depositsTotal = $groupRecords->where('action_type', 'deposit')->sum(fn($r) => (float) $r->amount);
                        $withdrawalsTotal = $groupRecords->where('action_type', 'withdrawal')->sum(fn($r) => (float) $r->amount);
                        $netCompounded = $depositsTotal - $withdrawalsTotal;
                        $vaultsSummary = $groupRecords->pluck('vault_or_account')->filter()->unique()->implode(', ') ?: 'Primary Savings Vault';
                        $methodsSummary = $groupRecords->map(fn($r) => $r->paymentMethod?->name)->filter()->unique()->implode(', ') ?: '—';
                        $overflowRecord = $groupRecords->first(fn($r) => $r->notes && str_contains($r->notes, 'Target Overflow:'));
                        $domGroupId = 'compounded_goal_' . $groupKey;
                    @endphp
                    <tr style="background-color: #FFFFFF;">
                        <td class="mono" style="white-space: nowrap;">
                            <strong>{{ $latestSr->record_date->format('d M Y') }}</strong>
                            <div style="margin-top: 4px;">
                                @if($entryCount > 1)
                                    <span class="mono" style="font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; background-color: var(--emerald-soft); color: var(--emerald-dark); text-transform: uppercase;">
                                        COMPOUNDED ({{ $entryCount }}x)
                                    </span>
                                @else
                                    <span class="mono" style="font-size: 10px; font-weight: 700; padding: 2px 8px; border-radius: 9999px; background-color: {{ $latestSr->action_type === 'deposit' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $latestSr->action_type === 'deposit' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                        {{ $latestSr->action_type }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                <strong>
                                    @if($entryCount > 1)
                                        {{ $entryCount }} Compounded Instalments (Latest: {{ $latestSr->title }})
                                    @else
                                        {{ $latestSr->title }}
                                    @endif
                                </strong>
                                @if($entryCount > 1)
                                    <button type="button"
                                            class="btn btn-ivory btn-sm"
                                            style="padding: 2px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700;"
                                            onclick="openCompoundedBreakdownModal('{{ $domGroupId }}')">
                                        View {{ $entryCount }} Instalments
                                    </button>
                                @endif
                            </div>
                            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Vault: {{ $vaultsSummary }}</div>
                            @if($overflowRecord)
                                <div class="mono" style="margin-top: 4px; display: inline-flex; align-items: center; gap: 4px; font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; background-color: rgba(229, 169, 60, 0.18); color: #966611;">
                                    ✦ {{ $overflowRecord->notes }}
                                </div>
                            @endif
                        </td>
                        <td>
                            <div>
                                <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                    <span class="cat-swatch" style="background-color: {{ $goalColor }};"></span>
                                    {{ $goalTitle }}
                                </span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; padding-left: 4px;">
                                Source / Method: <strong style="color: var(--text-dark); font-weight: 600;">{{ $methodsSummary }}</strong>
                            </div>
                            @php
                                $incomeCount = $groupRecords->where('funding_source', '!=', 'free_capital')->count();
                                $freeCount = $groupRecords->where('funding_source', 'free_capital')->count();
                            @endphp
                            <div style="margin-top: 4px; padding-left: 4px; display: flex; gap: 5px; flex-wrap: wrap;">
                                @if($incomeCount > 0)
                                    <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 7px; border-radius: 9999px; background-color: rgba(78, 115, 223, 0.14); color: var(--blue);">
                                        💼 From Monthly Income{{ $entryCount > 1 ? " ({$incomeCount})" : '' }}
                                    </span>
                                @endif
                                @if($freeCount > 0)
                                    <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 7px; border-radius: 9999px; background-color: rgba(45, 159, 120, 0.15); color: var(--emerald-dark);">
                                        ✦ Free / External Capital{{ $entryCount > 1 ? " ({$freeCount})" : '' }}
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="mono" style="text-align: right; font-weight: 800; font-size: 13.5px; white-space: nowrap; color: {{ $netCompounded >= 0 ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                            {{ $netCompounded >= 0 ? '+' : '-' }}{{ $user->currency }}{{ number_format(abs($netCompounded), 2) }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if($entryCount === 1)
                                <div style="display: inline-flex; align-items: center; gap: 5px;">
                                    <button type="button"
                                            class="btn btn-ivory btn-sm"
                                            style="padding: 3px 8px; font-size: 11px;"
                                            onclick='openEditSavingsRecordModal(@json($latestSr), "{{ $latestSr->record_date->format('Y-m-d') }}")'>
                                        ✎ Edit
                                    </button>
                                    <form action="{{ route('savings.records.destroy', $latestSr) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this savings transfer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 8px; font-size: 11px;">Delete</button>
                                    </form>
                                </div>
                            @else
                                <button type="button"
                                        class="btn btn-plum btn-sm"
                                        style="padding: 4px 10px; font-size: 11px;"
                                        onclick="openCompoundedBreakdownModal('{{ $domGroupId }}')">
                                    Manage ({{ $entryCount }})
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 28px; color: var(--text-muted);">
                            <strong>No explicit savings transfers recorded yet.</strong>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<!-- COMPOUNDED INSTALMENTS BREAKDOWN MODALS (ONE PER COMPOUNDED GOAL GROUP) -->
@foreach($compoundedGoalGroups as $groupKey => $groupRecords)
    @if($groupRecords->count() > 1)
        @php
            $firstRec = $groupRecords->first();
            $modalGoalTitle = $firstRec->financialGoal?->title ?? 'General Reserve';
            $modalGoalColor = $firstRec->financialGoal?->color ?? '#2D9F78';
            $modalDeposits = $groupRecords->where('action_type', 'deposit')->sum(fn($r) => (float) $r->amount);
            $modalWithdrawals = $groupRecords->where('action_type', 'withdrawal')->sum(fn($r) => (float) $r->amount);
            $modalNet = $modalDeposits - $modalWithdrawals;
            $modalDomId = 'compounded_goal_' . $groupKey;
        @endphp
        <div id="modal_{{ $modalDomId }}" class="modal-backdrop compounded-breakdown-modal" onclick="if(event.target === this) closeCompoundedBreakdownModals()">
            <div class="modal-box" style="max-width: 720px;">
                <div class="modal-header">
                    <div>
                        <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                            Compounded Savings Breakdown • {{ $groupRecords->count() }} Instalments
                        </div>
                        <h3 style="font-size: 16.5px; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                            <span class="cat-swatch" style="background-color: {{ $modalGoalColor }}; width: 10px; height: 10px;"></span>
                            <span>{{ $modalGoalTitle }}</span>
                        </h3>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        @if($firstRec->financial_goal_id)
                            <button type="button"
                                    class="btn btn-emerald btn-sm"
                                    onclick="closeCompoundedBreakdownModals(); openSavingsTransferModal({{ $firstRec->financial_goal_id }});">
                                + Add Instalment
                            </button>
                        @endif
                        <button type="button" class="btn btn-coral btn-sm" onclick="closeCompoundedBreakdownModals()">✕ Close</button>
                    </div>
                </div>
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 12px 14px;">
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Instalments Count</div>
                            <div class="mono" style="font-size: 15px; font-weight: 800; color: var(--text-dark); margin-top: 2px;">{{ $groupRecords->count() }} Transfers</div>
                        </div>
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Total Deposits</div>
                            <div class="mono" style="font-size: 15px; font-weight: 800; color: var(--emerald-dark); margin-top: 2px;">+{{ $user->currency }}{{ number_format($modalDeposits, 2) }}</div>
                        </div>
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Compounded Balance</div>
                            <div class="mono" style="font-size: 15px; font-weight: 800; color: {{ $modalNet >= 0 ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; margin-top: 2px;">
                                {{ $modalNet >= 0 ? '+' : '-' }}{{ $user->currency }}{{ number_format(abs($modalNet), 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="table-wrap" style="border: 1px solid var(--ivory-border); border-radius: 12px; overflow-x: auto; -webkit-overflow-scrolling: touch;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date & Action</th>
                                    <th>Instalment Title & Vault</th>
                                    <th>Method</th>
                                    <th style="text-align: right;">Amount</th>
                                    <th style="text-align: right;">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($groupRecords as $subSr)
                                    <tr>
                                        <td class="mono" style="white-space: nowrap; font-size: 12px;">
                                            <strong>{{ $subSr->record_date->format('d M Y') }}</strong>
                                            <div style="margin-top: 4px;">
                                                <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 2px 7px; border-radius: 9999px; background-color: {{ $subSr->action_type === 'deposit' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $subSr->action_type === 'deposit' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                                    {{ $subSr->action_type }}
                                                </span>
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="font-size: 12.5px; color: var(--text-dark);">{{ $subSr->title }}</strong>
                                            <div style="font-size: 11px; color: var(--text-muted);">Vault: {{ $subSr->vault_or_account }}</div>
                                            @if($subSr->notes && str_contains($subSr->notes, 'Target Overflow:'))
                                                <div class="mono" style="margin-top: 3px; display: inline-flex; align-items: center; gap: 4px; font-size: 9.5px; font-weight: 800; padding: 1.5px 7px; border-radius: 9999px; background-color: rgba(229, 169, 60, 0.18); color: #966611;">
                                                    ✦ {{ $subSr->notes }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                            <div>{{ $subSr->paymentMethod?->name ?? '—' }}</div>
                                            <div style="margin-top: 3px;">
                                                @if(($subSr->funding_source ?? 'monthly_income') === 'free_capital')
                                                    <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 6px; border-radius: 9999px; background-color: rgba(45, 159, 120, 0.15); color: var(--emerald-dark);">
                                                        ✦ Free Capital
                                                    </span>
                                                @else
                                                    <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 6px; border-radius: 9999px; background-color: rgba(78, 115, 223, 0.14); color: var(--blue);">
                                                        💼 Monthly Income
                                                    </span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="mono" style="text-align: right; font-weight: 700; font-size: 12.5px; white-space: nowrap; color: {{ $subSr->action_type === 'deposit' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                            {{ $subSr->action_type === 'deposit' ? '+' : '-' }}{{ $user->currency }}{{ number_format((float)$subSr->amount, 2) }}
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 5px;">
                                                <button type="button"
                                                        class="btn btn-ivory btn-sm"
                                                        style="padding: 3px 8px; font-size: 11px;"
                                                        onclick='closeCompoundedBreakdownModals(); openEditSavingsRecordModal(@json($subSr), "{{ $subSr->record_date->format('Y-m-d') }}");'>
                                                    ✎ Edit
                                                </button>
                                                <form action="{{ route('savings.records.destroy', $subSr) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this instalment?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 8px; font-size: 11px;">Delete</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<!-- MODAL 1: RECORD EXPLICIT SAVINGS TRANSFER -->
<div id="savingsTransferModal" class="modal-backdrop" onclick="if(event.target === this) closeSavingsTransferModal()">
    <div class="modal-box" style="max-width: 540px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Explicit Savings Vault</div>
                <h3 style="font-size: 16px;">Record Explicit Savings Transfer</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeSavingsTransferModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('savings.records.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Savings Entry Title</label>
                    <input type="text" name="title" id="sav_modal_title" class="form-control" placeholder="e.g., 91-Day T-Bill Rollover Top-Up" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Action</label>
                        <select name="action_type" id="sav_modal_action" class="form-control" onchange="evaluateSavingsGoalOverflow()">
                            <option value="deposit">Deposit into Savings</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 6px;">
                            <label class="form-label" for="sav_modal_amount">Amount ({{ $user->currency }})</label>
                            <span id="savAmountTopBadge" class="mono" style="display: none; font-size: 10px; font-weight: 800; padding: 1.5px 7px; border-radius: 9999px;"></span>
                        </div>
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   name="amount"
                                   id="sav_modal_amount"
                                   class="form-control mono"
                                   placeholder="0.00"
                                   style="width: 100%; padding-right: 110px; transition: all 0.18s ease;"
                                   oninput="evaluateSavingsGoalOverflow()"
                                   required>
                            <span id="savAmountInlineChip"
                                  class="mono"
                                  style="display: none; position: absolute; right: 28px; pointer-events: none; font-size: 9.5px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.03em;">
                            </span>
                        </div>
                        <div id="savAmountFieldHint" style="display: none; font-size: 11px; line-height: 1.35; margin-top: 3px; font-weight: 600;"></div>
                    </div>
                </div>

                <!-- LIVE GOAL TARGET & OVERFLOW INTELLIGENCE PRESENTATION -->
                <div id="savGoalImpactContainer" style="display: none; border-radius: 13px; overflow: hidden; transition: all 0.2s ease;"></div>

                <div class="form-group">
                    <label class="form-label">Savings Funding Source (Impact on Monthly Net Balance)</label>
                    <select name="funding_source" id="sav_modal_funding_source" class="form-control" onchange="updateFundingSourceHint()">
                        <option value="monthly_income">💼 From This Month's Total Income (Deducts from Monthly Net Balance)</option>
                        <option value="free_capital">✦ Free / External Amount (Does Not Deduct from Monthly Income)</option>
                    </select>
                    <div id="savFundingSourceHint" style="font-size: 11px; color: var(--blue); font-weight: 600; margin-top: 3px;">
                        💼 This amount comes from your recorded monthly income, so your Monthly Net Balance will decrease accordingly.
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Link to Financial Goal</label>
                    <select name="financial_goal_id" id="sav_modal_goal" class="form-control" onchange="evaluateSavingsGoalOverflow()">
                        <option value="" data-target="0" data-current="0" data-title="">— General Savings Reserve —</option>
                        @foreach($goals as $g)
                            @php
                                $gRem = max(0, (float)$g->target_amount - (float)$g->current_amount);
                            @endphp
                            <option value="{{ $g->id }}"
                                    data-target="{{ (float)$g->target_amount }}"
                                    data-current="{{ (float)$g->current_amount }}"
                                    data-title="{{ $g->title }}">
                                {{ $g->title }} ({{ $user->currency }}{{ number_format((float)$g->current_amount, 2) }} / {{ $user->currency }}{{ number_format((float)$g->target_amount, 2) }} • {{ $gRem > 0 ? $user->currency . number_format($gRem, 2) . ' remaining' : 'Target Achieved' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method_id" class="form-control">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="record_date" value="{{ now()->toDateString() }}" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Vault / Account</label>
                    <input type="text" name="vault_or_account" class="form-control" placeholder="e.g., Stanbic Money Market Trust">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeSavingsTransferModal()">Cancel</button>
                    <button type="submit" id="savModalSubmitBtn" class="btn btn-emerald">Record Explicit Savings</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: CREATE NEW FINANCIAL GOAL -->
<div id="createGoalModal" class="modal-backdrop" onclick="if(event.target === this) closeGoalModal()">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Capital Accumulation</div>
                <h3 style="font-size: 16px;">Create New Financial Goal</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeGoalModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('savings.goals.store') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">Goal Title</label>
                    <input type="text" name="title" id="goal_modal_title" class="form-control" placeholder="e.g., Solar Inverter Home Backup" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" class="form-control" value="Capital Project" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Amount ({{ $user->currency }})</label>
                        <input type="number" step="0.01" min="0.01" name="target_amount" class="form-control mono" placeholder="0.00" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" value="2027-06-30" class="form-control mono">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accent Color</label>
                        <select name="color" class="form-control mono" data-color-picker="true">
                            <option value="#2D9F78">Emerald Green (#2D9F78)</option>
                            <option value="#E5A93C">Mustard Yellow (#E5A93C)</option>
                            <option value="#4E73DF">Blue (#4E73DF)</option>
                            <option value="#E66D55">Coral (#E66D55)</option>
                            <option value="#D96D9B">Rose (#D96D9B)</option>
                            <option value="#1E1B2E">Deep Plum (#1E1B2E)</option>
                        </select>
                    </div>
                </div>
                <input type="hidden" name="priority" value="high">
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeGoalModal()">Cancel</button>
                    <button type="submit" class="btn btn-plum">Create Goal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: EDIT EXISTING FINANCIAL GOAL -->
<div id="editGoalModal" class="modal-backdrop" onclick="if(event.target === this) closeEditGoalModal()">
    <div class="modal-box" style="max-width: 520px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Capital Accumulation</div>
                <h3 style="font-size: 16px;">Edit Financial Goal</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeEditGoalModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form id="editGoalForm" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Goal Title</label>
                    <input type="text" name="title" id="edit_goal_title" class="form-control" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <input type="text" name="category" id="edit_goal_category" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Target Amount ({{ $user->currency }})</label>
                        <input type="number" step="0.01" min="0.01" name="target_amount" id="edit_goal_target" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Target Date</label>
                        <input type="date" name="target_date" id="edit_goal_date" class="form-control mono">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Accent Color</label>
                        <select name="color" id="edit_goal_color" class="form-control mono" data-color-picker="true">
                            <option value="#2D9F78">Emerald Green (#2D9F78)</option>
                            <option value="#E5A93C">Mustard Yellow (#E5A93C)</option>
                            <option value="#4E73DF">Blue (#4E73DF)</option>
                            <option value="#E66D55">Coral (#E66D55)</option>
                            <option value="#D96D9B">Rose (#D96D9B)</option>
                            <option value="#1E1B2E">Deep Plum (#1E1B2E)</option>
                        </select>
                    </div>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeEditGoalModal()">Cancel</button>
                    <button type="submit" class="btn btn-emerald">Save Goal Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 4: EDIT EXPLICIT SAVINGS RECORD -->
<div id="editSavingsRecordModal" class="modal-backdrop" onclick="if(event.target === this) closeEditSavingsRecordModal()">
    <div class="modal-box" style="max-width: 540px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Explicit Savings Vault</div>
                <h3 style="font-size: 16px;">Edit Savings Transfer Record</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeEditSavingsRecordModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form id="editSavingsRecordForm" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Savings Entry Title</label>
                    <input type="text" name="title" id="edit_sr_title" class="form-control" required>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Action</label>
                        <select name="action_type" id="edit_sr_action" class="form-control">
                            <option value="deposit">Deposit into Savings</option>
                            <option value="withdrawal">Withdrawal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount ({{ $user->currency }})</label>
                        <input type="number" step="0.01" min="0.01" name="amount" id="edit_sr_amount" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Savings Funding Source (Impact on Monthly Net Balance)</label>
                    <select name="funding_source" id="edit_sr_funding_source" class="form-control">
                        <option value="monthly_income">💼 From This Month's Total Income (Deducts from Monthly Net Balance)</option>
                        <option value="free_capital">✦ Free / External Amount (Does Not Deduct from Monthly Income)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Link to Financial Goal</label>
                    <select name="financial_goal_id" id="edit_sr_goal" class="form-control">
                        <option value="">— General Savings Reserve —</option>
                        @foreach($goals as $g)
                            <option value="{{ $g->id }}">{{ $g->title }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method_id" id="edit_sr_pm" class="form-control">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="record_date" id="edit_sr_date" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Target Vault / Account</label>
                    <input type="text" name="vault_or_account" id="edit_sr_vault" class="form-control">
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 4px;">
                    <button type="button" class="btn btn-ivory" onclick="closeEditSavingsRecordModal()">Cancel</button>
                    <button type="submit" class="btn btn-emerald">Save Transfer Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const SAV_CURRENCY = @json($user->currency ?? 'GH¢');

    function formatSavMoney(val) {
        const num = Number(val || 0);
        return SAV_CURRENCY + num.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function fillExactRemainingAmount(amountVal) {
        const amountInput = document.getElementById('sav_modal_amount');
        if (amountInput) {
            amountInput.value = Number(amountVal).toFixed(2);
            evaluateSavingsGoalOverflow();
            amountInput.focus();
        }
    }

    function evaluateSavingsGoalOverflow() {
        const goalSelect = document.getElementById('sav_modal_goal');
        const actionSelect = document.getElementById('sav_modal_action');
        const amountInput = document.getElementById('sav_modal_amount');
        const inlineChip = document.getElementById('savAmountInlineChip');
        const topBadge = document.getElementById('savAmountTopBadge');
        const fieldHint = document.getElementById('savAmountFieldHint');
        const container = document.getElementById('savGoalImpactContainer');
        const submitBtn = document.getElementById('savModalSubmitBtn');

        if (!goalSelect || !amountInput || !container) return;

        const selectedOpt = goalSelect.options[goalSelect.selectedIndex];
        const goalId = goalSelect.value;
        const actionType = actionSelect ? actionSelect.value : 'deposit';
        const enteredAmount = parseFloat(amountInput.value);
        const hasAmount = !isNaN(enteredAmount) && enteredAmount > 0;

        // Reset input styles by default
        amountInput.style.borderColor = '';
        amountInput.style.backgroundColor = '';
        amountInput.style.boxShadow = '';
        if (inlineChip) inlineChip.style.display = 'none';
        if (topBadge) topBadge.style.display = 'none';
        if (fieldHint) fieldHint.style.display = 'none';
        if (submitBtn) {
            submitBtn.textContent = 'Record Explicit Savings';
            submitBtn.className = 'btn btn-emerald';
        }

        if (!goalId || !selectedOpt) {
            container.style.display = 'none';
            container.innerHTML = '';
            return;
        }

        const targetAmount = parseFloat(selectedOpt.getAttribute('data-target') || '0');
        const currentAmount = parseFloat(selectedOpt.getAttribute('data-current') || '0');
        const goalTitle = selectedOpt.getAttribute('data-title') || 'Selected Goal';
        const remainingToTarget = Math.max(0, targetAmount - currentAmount);
        const currentPct = targetAmount > 0 ? Math.round((currentAmount / targetAmount) * 1000) / 10 : 0;

        container.style.display = 'block';

        if (actionType === 'withdrawal') {
            const newBalance = Math.max(0, currentAmount - (hasAmount ? enteredAmount : 0));
            const exceedsSaved = hasAmount && enteredAmount > currentAmount;
            if (exceedsSaved) {
                amountInput.style.borderColor = 'var(--coral)';
                amountInput.style.boxShadow = '0 0 0 3px rgba(230, 109, 85, 0.18)';
                if (inlineChip) {
                    inlineChip.style.display = 'inline-flex';
                    inlineChip.style.backgroundColor = 'rgba(230, 109, 85, 0.16)';
                    inlineChip.style.color = 'var(--coral-dark)';
                    inlineChip.textContent = 'EXCEEDS SAVED';
                }
            }
            container.innerHTML = `
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 12px 14px; font-size: 12px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div>
                        <strong style="color: var(--text-dark);">${goalTitle}</strong>
                        <div style="color: var(--text-muted); font-size: 11.5px;">Available in Goal: <strong class="mono">${formatSavMoney(currentAmount)}</strong></div>
                    </div>
                    <div class="mono" style="font-size: 12px; font-weight: 700; color: ${exceedsSaved ? 'var(--coral-dark)' : 'var(--text-dark)'};">
                        Projected After Withdrawal: ${formatSavMoney(newBalance)}
                    </div>
                </div>
            `;
            return;
        }

        // DEPOSIT MODE
        const projectedTotal = currentAmount + (hasAmount ? enteredAmount : 0);
        const projectedPct = targetAmount > 0 ? Math.round((projectedTotal / targetAmount) * 1000) / 10 : 0;
        const isOverflow = hasAmount && enteredAmount > remainingToTarget && targetAmount > 0;
        const isExactHit = hasAmount && Math.abs(enteredAmount - remainingToTarget) < 0.005 && remainingToTarget > 0;
        const overflowByEntry = hasAmount ? Math.max(0, projectedTotal - targetAmount) : 0;

        if (isOverflow) {
            // 1. Highlight the Amount field directly with Golden Overflow styling + Inline Chip + Sub-hint
            amountInput.style.borderColor = '#E5A93C';
            amountInput.style.backgroundColor = '#FFFDF7';
            amountInput.style.boxShadow = '0 0 0 3.5px rgba(229, 169, 60, 0.24)';

            if (inlineChip) {
                inlineChip.style.display = 'inline-flex';
                inlineChip.style.backgroundColor = '#1E1B2E';
                inlineChip.style.color = '#F5C469';
                inlineChip.style.border = '1px solid rgba(229, 169, 60, 0.5)';
                inlineChip.textContent = '✦ OVERFLOW';
            }

            if (topBadge) {
                topBadge.style.display = 'inline-flex';
                topBadge.style.backgroundColor = 'rgba(229, 169, 60, 0.22)';
                topBadge.style.color = '#966611';
                topBadge.textContent = '+' + formatSavMoney(overflowByEntry) + ' Above Target';
            }

            if (fieldHint) {
                fieldHint.style.display = 'block';
                fieldHint.style.color = '#966611';
                fieldHint.innerHTML = remainingToTarget > 0
                    ? `✦ Exceeds the remaining <strong>${formatSavMoney(remainingToTarget)}</strong> target by <strong>+${formatSavMoney(overflowByEntry)}</strong>. Overflow is allowed — you may continue.`
                    : `✦ Goal target already reached. This adds <strong>+${formatSavMoney(enteredAmount)}</strong> in surplus overflow — you may continue.`;
            }

            if (submitBtn) {
                submitBtn.textContent = `✦ Record Deposit (+${formatSavMoney(overflowByEntry)} Overflow) →`;
            }

            // 2. Render Premium Executive Overflow Presentation Card
            container.innerHTML = `
                <div style="background: linear-gradient(135deg, #1E1B2E 0%, #27233A 100%); color: #FFFFFF; border: 1.5px solid rgba(229, 169, 60, 0.55); border-radius: 13px; padding: 14px 16px; box-shadow: 0 12px 28px -8px rgba(30, 27, 46, 0.45); display: flex; flex-direction: column; gap: 11px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; flex-wrap: wrap;">
                        <div style="display: inline-flex; align-items: center; gap: 7px;">
                            <span class="mono" style="background-color: #E5A93C; color: #14161F; font-size: 10px; font-weight: 800; padding: 2.5px 8px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.05em;">
                                ✦ Target Overflow Detected
                            </span>
                            <span style="font-size: 11.5px; font-weight: 700; color: #5CE0B5;">
                                Surplus Capital Allocation Allowed
                            </span>
                        </div>
                        <span class="mono" style="font-size: 11px; font-weight: 800; color: #F5C469; background-color: rgba(229, 169, 60, 0.14); border: 1px solid rgba(229, 169, 60, 0.35); padding: 2px 9px; border-radius: 9999px;">
                            ${projectedPct}% of Target
                        </span>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; background-color: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.09); border-radius: 10px; padding: 10px 12px;">
                        <div>
                            <div style="font-size: 10px; text-transform: uppercase; color: #A3A7BA; font-weight: 700;">Remaining Needed</div>
                            <div class="mono" style="font-size: 13px; font-weight: 700; color: #FFFFFF; margin-top: 2px;">${formatSavMoney(remainingToTarget)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; text-transform: uppercase; color: #A3A7BA; font-weight: 700;">Projected Balance</div>
                            <div class="mono" style="font-size: 13px; font-weight: 700; color: #5CE0B5; margin-top: 2px;">${formatSavMoney(projectedTotal)}</div>
                        </div>
                        <div>
                            <div style="font-size: 10px; text-transform: uppercase; color: #F5C469; font-weight: 700;">Target Overflow</div>
                            <div class="mono" style="font-size: 13px; font-weight: 800; color: #F5C469; margin-top: 2px;">+${formatSavMoney(overflowByEntry)}</div>
                        </div>
                    </div>

                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 10.5px; color: #D5D8E6; margin-bottom: 5px;">
                            <span>100% Target (${formatSavMoney(targetAmount)})</span>
                            <span class="mono" style="color: #F5C469; font-weight: 700;">+${formatSavMoney(overflowByEntry)} Surplus Overflow</span>
                        </div>
                        <div style="width: 100%; height: 8px; background-color: rgba(255,255,255,0.12); border-radius: 9999px; overflow: hidden; display: flex;">
                            <div style="width: 78%; height: 100%; background-color: #2D9F78;"></div>
                            <div style="width: 22%; height: 100%; background: linear-gradient(90deg, #E5A93C 0%, #FFD479 100%);"></div>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding-top: 4px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 11.5px; color: #D5D8E6;">
                        <span>You may proceed to record this full overflow deposit, or cap at the exact remaining amount:</span>
                        ${remainingToTarget > 0 ? `
                            <button type="button"
                                    onclick="fillExactRemainingAmount(${remainingToTarget})"
                                    style="background-color: rgba(255,255,255,0.1); color: #FFFFFF; border: 1px solid rgba(255,255,255,0.22); border-radius: 7px; padding: 4px 10px; font-size: 11px; font-weight: 700; cursor: pointer; white-space: nowrap;">
                                Cap at ${formatSavMoney(remainingToTarget)}
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        } else if (isExactHit) {
            amountInput.style.borderColor = 'var(--emerald)';
            amountInput.style.backgroundColor = '#F4FBF8';
            amountInput.style.boxShadow = '0 0 0 3px rgba(45, 159, 120, 0.18)';

            if (inlineChip) {
                inlineChip.style.display = 'inline-flex';
                inlineChip.style.backgroundColor = 'rgba(45, 159, 120, 0.18)';
                inlineChip.style.color = 'var(--emerald-dark)';
                inlineChip.textContent = '✓ 100% TARGET';
            }

            if (fieldHint) {
                fieldHint.style.display = 'block';
                fieldHint.style.color = 'var(--emerald-dark)';
                fieldHint.innerHTML = `✓ Exact match! This deposit completes 100% of your <strong>${formatSavMoney(targetAmount)}</strong> target.`;
            }

            container.innerHTML = `
                <div style="background-color: #F4FBF8; border: 1.5px solid var(--emerald); border-radius: 12px; padding: 12px 15px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div>
                        <div style="font-size: 10.5px; font-weight: 800; text-transform: uppercase; color: var(--emerald-dark); letter-spacing: 0.05em;">
                            ✓ 100% Goal Completion Match
                        </div>
                        <div style="font-size: 12px; color: var(--text-dark); margin-top: 2px;">
                            Depositing <strong>${formatSavMoney(enteredAmount)}</strong> brings <strong>${goalTitle}</strong> to exactly <strong>${formatSavMoney(targetAmount)} (100%)</strong>.
                        </div>
                    </div>
                    <span class="mono" style="background-color: var(--emerald); color: #FFFFFF; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 9999px;">
                        100% ACHIEVED
                    </span>
                </div>
            `;
        } else {
            // Standard progress preview showing how much remains to hit the target
            const afterRemaining = Math.max(0, targetAmount - projectedTotal);
            if (hasAmount && fieldHint) {
                fieldHint.style.display = 'block';
                fieldHint.style.color = 'var(--text-muted)';
                fieldHint.innerHTML = `After this deposit, <strong>${formatSavMoney(afterRemaining)}</strong> will remain to hit the ${formatSavMoney(targetAmount)} target.`;
            }

            container.innerHTML = `
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 11px 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                    <div style="display: flex; flex-direction: column; gap: 2px;">
                        <div style="font-size: 11.5px; font-weight: 700; color: var(--text-dark);">
                            ${goalTitle} • <span class="mono" style="color: var(--emerald-dark);">${formatSavMoney(currentAmount)} / ${formatSavMoney(targetAmount)} (${currentPct}%)</span>
                        </div>
                        <div style="font-size: 11.5px; color: var(--text-muted);">
                            Remaining to Hit Target: <strong class="mono" style="color: var(--text-dark);">${formatSavMoney(remainingToTarget)}</strong>
                            ${hasAmount ? ` → Projected: <strong class="mono" style="color: var(--emerald-dark);">${formatSavMoney(projectedTotal)} (${projectedPct}%)</strong>` : ''}
                        </div>
                    </div>
                    ${remainingToTarget > 0 ? `
                        <button type="button"
                                class="btn btn-ivory btn-sm"
                                style="padding: 4px 10px; font-size: 11px;"
                                onclick="fillExactRemainingAmount(${remainingToTarget})">
                            Fill Remaining (${formatSavMoney(remainingToTarget)})
                        </button>
                    ` : ''}
                </div>
            `;
        }
    }

    function openCompoundedBreakdownModal(groupDomId) {
        document.getElementById('modal_' + groupDomId)?.classList.add('open');
    }

    function closeCompoundedBreakdownModals() {
        document.querySelectorAll('.compounded-breakdown-modal.open').forEach(function (m) {
            m.classList.remove('open');
        });
    }

    function openSavingsTransferModal(goalId = null) {
        const goalSelect = document.getElementById('sav_modal_goal');
        if (goalId && goalSelect) {
            goalSelect.value = goalId;
        }
        document.getElementById('savingsTransferModal').classList.add('open');
        evaluateSavingsGoalOverflow();
        setTimeout(() => document.getElementById('sav_modal_title')?.focus(), 50);
    }

    function closeSavingsTransferModal() {
        document.getElementById('savingsTransferModal').classList.remove('open');
    }

    function openGoalModal() {
        document.getElementById('createGoalModal').classList.add('open');
        setTimeout(() => document.getElementById('goal_modal_title')?.focus(), 50);
    }

    function closeGoalModal() {
        document.getElementById('createGoalModal').classList.remove('open');
    }

    function openEditGoalModal(id, title, category, targetAmount, targetDate, color) {
        const form = document.getElementById('editGoalForm');
        if (form) {
            form.action = '/savings/goals/' + id;
        }
        document.getElementById('edit_goal_title').value = title || '';
        document.getElementById('edit_goal_category').value = category || 'Capital Project';
        document.getElementById('edit_goal_target').value = targetAmount || '';
        document.getElementById('edit_goal_date').value = targetDate || '';

        const colorSelect = document.getElementById('edit_goal_color');
        if (colorSelect) {
            colorSelect.value = color || '#2D9F78';
            colorSelect.dispatchEvent(new CustomEvent('color-picker:refresh'));
        }

        document.getElementById('editGoalModal').classList.add('open');
        setTimeout(() => document.getElementById('edit_goal_title')?.focus(), 50);
    }

    function closeEditGoalModal() {
        document.getElementById('editGoalModal')?.classList.remove('open');
    }

    function updateFundingSourceHint() {
        const sel = document.getElementById('sav_modal_funding_source');
        const hint = document.getElementById('savFundingSourceHint');
        if (!sel || !hint) return;
        if (sel.value === 'free_capital') {
            hint.style.color = 'var(--emerald-dark)';
            hint.textContent = '✦ This is free / external capital outside your monthly income — your Monthly Net Balance will remain unchanged.';
        } else {
            hint.style.color = 'var(--blue)';
            hint.textContent = '💼 This amount comes from your recorded monthly income, so your Monthly Net Balance will decrease accordingly.';
        }
    }

    function openEditSavingsRecordModal(record, formattedDate) {
        const form = document.getElementById('editSavingsRecordForm');
        if (form) {
            form.action = '/savings/records/' + record.id;
        }
        document.getElementById('edit_sr_title').value = record.title || '';
        document.getElementById('edit_sr_action').value = record.action_type || 'deposit';
        document.getElementById('edit_sr_funding_source').value = record.funding_source || 'monthly_income';
        document.getElementById('edit_sr_amount').value = record.amount || '';
        document.getElementById('edit_sr_goal').value = record.financial_goal_id || '';
        document.getElementById('edit_sr_pm').value = record.payment_method_id || '';
        document.getElementById('edit_sr_date').value = formattedDate || (record.record_date || '').substring(0, 10);
        document.getElementById('edit_sr_vault').value = record.vault_or_account || '';

        document.getElementById('editSavingsRecordModal').classList.add('open');
        setTimeout(() => document.getElementById('edit_sr_title')?.focus(), 50);
    }

    function closeEditSavingsRecordModal() {
        document.getElementById('editSavingsRecordModal')?.classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeSavingsTransferModal();
            closeGoalModal();
            closeEditGoalModal();
            closeEditSavingsRecordModal();
            closeCompoundedBreakdownModals();
        }
    });
</script>
@endpush
