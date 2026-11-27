@extends('layouts.app')

@section('kicker', $forcedType === 'expense' ? 'Expenses & Receipts' : ($forcedType === 'income' ? 'Income Records' : 'All Transactions'))
@section('page_title', $pageTitle)

@section('content')
<!-- SUMMARY STRIP FOR FILTERED LEDGER -->
<div class="kpi-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
    <div class="kpi-cell bg-coral">
        <div class="kpi-label">Filtered Expenses Outflow</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$filteredExpenseTotal, 2) }}</div>
        <div class="kpi-footer"><span>Period: {{ $startDate }} to {{ $endDate }}</span></div>
    </div>
    <div class="kpi-cell bg-emerald">
        <div class="kpi-label">Filtered Income Inflow</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$filteredIncomeTotal, 2) }}</div>
        <div class="kpi-footer"><span>Salary, Business, Freelance & Investments</span></div>
    </div>
    <div class="kpi-cell bg-blue">
        <div class="kpi-label">Filtered Net Cash Flow</div>
        <div class="kpi-amount">{{ $user->currency }}{{ number_format((float)$filteredNet, 2) }}</div>
        <div class="kpi-footer"><span>Income minus Expenses (Excludes Auto-Savings)</span></div>
    </div>
</div>

@if($forcedType === 'income' && count($incomeBreakdown) > 0)
    <section class="section-block theme-ivory">
        <div class="section-header">
            <h3 class="section-title">Income Streams Breakdown</h3>
        </div>
        <div class="section-body">
            <div class="form-grid">
                @foreach($incomeBreakdown as $ib)
                    <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-left: 4px solid var(--emerald); border-radius: 10px; padding: 14px;">
                        <div style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">{{ $ib->income_source ?: 'General Income' }}</div>
                        <div class="mono" style="font-size: 18px; font-weight: 700; color: var(--text-dark); margin: 4px 0;">
                            {{ $user->currency }}{{ number_format((float)$ib->total_amount, 2) }}
                        </div>
                        <div style="font-size: 11.5px; color: var(--emerald-dark); font-weight: 600;">{{ $ib->tx_count }} recorded inflow(s)</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
@endif

<!-- SMART TRANSACTION FILTER BAR (COLLAPSED BY DEFAULT) -->
<section class="section-block theme-ivory always-collapsible-card" id="txFilterSectionCard">
    <div class="section-header collapsible-form-header" onclick="toggleCollapsibleCard('txFilterSectionCard', 'always')">
        <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
            <span class="collapsible-chevron-badge mono">▸</span>
            <h3 class="section-title" style="font-size: 13.5px;">Search, Filter &amp; Record Transactions</h3>
        </div>
        <button type="button"
                class="btn btn-ivory btn-sm collapsible-toggle-btn"
                data-expand-label="Filter ▾"
                data-hide-label="Hide ▴"
                style="padding: 3px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700; flex-shrink: 0;">
            Filter ▾
        </button>
    </div>
    <div class="section-body collapsible-card-body">
        <form method="GET" action="{{ url()->current() }}" class="form-grid" style="align-items: end;">
            <div class="form-group">
                <label class="form-label">Search Keyword / Ref</label>
                <input type="text" name="q" value="{{ $search }}" class="form-control" placeholder="Title, merchant, note...">
            </div>
            @if(!$forcedType)
                <div class="form-group">
                    <label class="form-label">Transaction Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="expense" {{ $type === 'expense' ? 'selected' : '' }}>Expenses Only</option>
                        <option value="income" {{ $type === 'income' ? 'selected' : '' }}>Income Only</option>
                    </select>
                </div>
            @endif
            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-control">
                    <option value="">All Categories</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>
                            {{ $cat->name }} ({{ ucfirst($cat->type) }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Payment Method</label>
                <select name="payment_method_id" class="form-control">
                    <option value="">All Methods</option>
                    @foreach($paymentMethods as $pm)
                        <option value="{{ $pm->id }}" {{ (string)$paymentMethodId === (string)$pm->id ? 'selected' : '' }}>
                            {{ $pm->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">From Date</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="form-control mono">
            </div>
            <div class="form-group">
                <label class="form-label">To Date</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="form-control mono">
            </div>
            <div class="form-group" style="display: flex; flex-direction: row; gap: 6px;">
                <button type="submit" class="btn btn-plum">Filter Ledger</button>
                <a href="{{ url()->current() }}" class="btn btn-ivory">Reset</a>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        @php
            $compoundedTxGroups = $transactions->getCollection();
            $categoryBuckets = $compoundedTxGroups->groupBy(function ($txGroupItems) {
                $first = $txGroupItems->first();
                return $first->type . '|' . ($first->expense_category_id ?: 'uncat');
            });
        @endphp
        @if($categoryBuckets->isNotEmpty())
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 18px; background-color: var(--ivory-alt); border-bottom: 1px solid var(--ivory-border); flex-wrap: wrap;">
                <span style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">
                    Click any <strong>Category</strong> row below to expand or collapse its transaction rows ({{ $categoryBuckets->count() }} {{ \Illuminate\Support\Str::plural('category', $categoryBuckets->count()) }}).
                </span>
                <div style="display: inline-flex; align-items: center; gap: 6px;">
                    <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 10px; font-size: 11px; font-weight: 700;" onclick="setAllCategoryBuckets(true)">
                        ▾ Expand All Categories
                    </button>
                    <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 10px; font-size: 11px; font-weight: 700;" onclick="setAllCategoryBuckets(false)">
                        ▸ Collapse All
                    </button>
                </div>
            </div>
        @endif
        <table class="data-table">
            <thead>
                <tr>
                    <th>Latest Date & Status</th>
                    <th>Title & Description</th>
                    <th>Category & Subcategory</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categoryBuckets as $catBucketKey => $bucketItemGroups)
                    @php
                        $bucketSampleTx = $bucketItemGroups->first()->first();
                        $bucketCatName = $bucketSampleTx->category?->name ?? ($bucketSampleTx->income_source ?: 'Uncategorised');
                        $bucketCatColor = $bucketSampleTx->category?->color ?? '#2D9F78';
                        $bucketType = $bucketSampleTx->type;
                        $bucketTotalAmount = $bucketItemGroups->sum(fn($grp) => $grp->sum(fn($i) => (float) $i->amount));
                        $bucketTotalOccurrences = $bucketItemGroups->sum(fn($grp) => $grp->count());
                        $bucketRowCount = $bucketItemGroups->count();
                        $bucketDomId = 'cat_bucket_' . $loop->index;
                        $bucketSubcategoryBreakdown = $bucketItemGroups
                            ->flatMap(fn($grp) => $grp)
                            ->groupBy(fn($i) => $i->subcategory?->name ?: 'General / Unspecified')
                            ->map(fn($subItems, $subName) => [
                                'name' => $subName,
                                'total' => $subItems->sum(fn($i) => (float) $i->amount),
                                'count' => $subItems->count(),
                            ])
                            ->sortByDesc('total');
                    @endphp
                    <tr id="header_{{ $bucketDomId }}"
                        class="cat-bucket-header"
                        data-bucket-id="{{ $bucketDomId }}"
                        aria-expanded="false"
                        onclick="toggleCategoryBucket('{{ $bucketDomId }}')"
                        style="background-color: var(--ivory-alt); border-top: 2px solid var(--ivory-border); cursor: pointer; user-select: none; transition: background-color 0.15s ease;">
                        <td colspan="4" style="padding: 11px 16px;">
                            <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span id="chevron_{{ $bucketDomId }}"
                                          class="cat-bucket-chevron mono"
                                          style="display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 6px; background-color: #FFFFFF; border: 1px solid var(--ivory-border); font-size: 12px; font-weight: 800; color: var(--text-dark); transition: transform 0.18s ease;">
                                        ▸
                                    </span>
                                    <span class="cat-badge" style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); font-weight: 800; font-size: 12px; padding: 3px 10px;">
                                        <span class="cat-swatch" style="background-color: {{ $bucketCatColor }}; width: 9px; height: 9px;"></span>
                                        {{ $bucketCatName }}
                                    </span>
                                    <span class="mono" style="font-size: 10.5px; color: var(--text-muted); font-weight: 700;">
                                        {{ $bucketRowCount }} {{ \Illuminate\Support\Str::plural('item', $bucketRowCount) }} • {{ $bucketTotalOccurrences }} {{ \Illuminate\Support\Str::plural('record', $bucketTotalOccurrences) }}
                                    </span>
                                    @foreach($bucketSubcategoryBreakdown as $subMeta)
                                        @if($subMeta['name'] !== 'General / Unspecified' || $bucketSubcategoryBreakdown->count() > 1)
                                            <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 10.5px; padding: 2px 8px; border-radius: 9999px; background-color: #FFFFFF; border: 1px solid var(--ivory-border); color: var(--text-dark);">
                                                <span style="color: var(--text-muted);">↳</span>
                                                <strong style="font-weight: 600;">{{ $subMeta['name'] }}</strong>
                                                <span class="mono" style="font-size: 10px; font-weight: 700; color: {{ $bucketType === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                                    {{ $user->currency }}{{ number_format($subMeta['total'], 2) }}
                                                </span>
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div class="mono" style="font-size: 12.5px; font-weight: 800; color: {{ $bucketType === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                        Category Total: {{ $bucketType === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format($bucketTotalAmount, 2) }}
                                    </div>
                                    <span id="toggle_label_{{ $bucketDomId }}"
                                          class="btn btn-ivory btn-sm"
                                          style="padding: 2px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700; pointer-events: none;">
                                        Show Rows ({{ $bucketRowCount }})
                                    </span>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @foreach($bucketItemGroups as $txGroupItems)
                        @php
                            $tx = $txGroupItems->first();
                            $txCount = $txGroupItems->count();
                            $compoundedTotal = $txGroupItems->sum(fn($item) => (float) $item->amount);
                            $methodsJoined = $txGroupItems->map(fn($item) => $item->paymentMethod?->name)->filter()->unique()->implode(', ') ?: '—';
                            $distinctTitlesCount = $txGroupItems->map(fn($item) => strtolower(trim((string) $item->title)))->unique()->count();
                            $isIncomeCategoryGroup = $tx->type === 'income' && $txCount > 1 && $distinctTitlesCount > 1;
                            $displayHeading = $isIncomeCategoryGroup
                                ? ($tx->category?->name ?? $tx->income_source ?? $tx->title)
                                : $tx->title;
                            $domTxGroupId = 'compounded_tx_' . $tx->id;
                            $distinctSubcategories = $txGroupItems->map(fn($item) => $item->subcategory?->name)->filter()->unique()->values();
                        @endphp
                        <tr class="cat-bucket-child-row"
                            data-parent-bucket="{{ $bucketDomId }}"
                            style="display: none;">
                            <td class="mono" style="font-size: 11.5px; white-space: nowrap; padding-left: 22px;">
                                <strong>{{ $tx->transaction_date->format('d M Y') }}</strong>
                                @if($txCount > 1)
                                    <div style="margin-top: 4px;">
                                        <span class="mono" style="font-size: 9.5px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; background-color: {{ $tx->type === 'income' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                            COMPOUNDED ({{ $txCount }}x)
                                        </span>
                                    </div>
                                @else
                                    <div style="font-size: 10.5px; color: var(--text-muted);">{{ $tx->reference_code }} • {{ $tx->transaction_time ? substr($tx->transaction_time, 0, 5) : '' }}</div>
                                @endif
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <strong>
                                        @if($isIncomeCategoryGroup)
                                            {{ $txCount }} {{ $displayHeading }} Inflows (Latest: {{ $tx->title }})
                                        @else
                                            {{ $tx->title }}
                                        @endif
                                    </strong>
                                    @if($txCount > 1)
                                        <button type="button"
                                                class="btn btn-ivory btn-sm"
                                                style="padding: 2px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700;"
                                                onclick="openCompoundedTxModal('{{ $domTxGroupId }}')">
                                            View {{ $txCount }} Entries
                                        </button>
                                    @endif
                                </div>
                                @if(!$isIncomeCategoryGroup)
                                    <div style="font-size: 11.5px; color: var(--text-muted);">
                                        {{ $tx->merchant_or_source }}
                                        @if($txCount > 1)
                                            • {{ $txCount }} occurrences compounded
                                        @endif
                                    </div>
                                @endif
                                @if($tx->notes && $txCount === 1)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ \Illuminate\Support\Str::limit($tx->notes, 70) }}</div>
                                @endif
                                <div style="display: flex; align-items: baseline; gap: 8px; flex-wrap: wrap; margin-top: 4px;">
                                    <span class="mono" style="font-weight: 800; font-size: 13px; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                        {{ $tx->type === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format($compoundedTotal, 2) }}
                                    </span>
                                    @if($txCount > 1)
                                        @php
                                            $minAmt = $txGroupItems->min(fn($i) => (float) $i->amount);
                                            $maxAmt = $txGroupItems->max(fn($i) => (float) $i->amount);
                                        @endphp
                                        <span class="mono" style="font-size: 10.5px; color: var(--text-muted); font-weight: 600;">
                                            @if(abs($maxAmt - $minAmt) > 0.005)
                                                ({{ $txCount }}x • {{ $user->currency }}{{ number_format($minAmt, 2) }} – {{ $user->currency }}{{ number_format($maxAmt, 2) }})
                                            @else
                                                ({{ $txCount }}x • {{ $user->currency }}{{ number_format($minAmt, 2) }} each)
                                            @endif
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="cat-badge" style="background-color: var(--ivory-alt);">
                                        <span class="cat-swatch" style="background-color: {{ $tx->category?->color ?? '#2D9F78' }};"></span>
                                        {{ $tx->category?->name ?? 'Uncategorised' }}
                                    </span>
                                </div>
                                @if($distinctSubcategories->isNotEmpty())
                                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 3px; padding-left: 4px;">
                                        ↳ {{ $distinctSubcategories->implode(', ') }}
                                    </div>
                                @endif
                                <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 5px; padding-left: 4px;">
                                    <span style="font-size: 11.5px; color: var(--text-muted);">
                                        Method: <strong style="color: var(--text-dark); font-weight: 600;">{{ $methodsJoined }}</strong>
                                    </span>
                                    <span class="mono" style="font-size: 9.5px; font-weight: 700; padding: 1.5px 7px; border-radius: 9999px; background-color: {{ $tx->type === 'income' ? 'var(--emerald-soft)' : 'var(--coral-soft)' }}; color: {{ $tx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; text-transform: uppercase;">
                                        {{ $tx->type }}
                                    </span>
                                    @if($tx->receipts->isNotEmpty())
                                        @foreach($tx->receipts as $rc)
                                            <a href="{{ asset('storage/' . $rc->file_path) }}" target="_blank" class="btn btn-ivory btn-sm" style="padding: 1.5px 7px; font-size: 10px; border-radius: 9999px;">Receipt ↗</a>
                                        @endforeach
                                    @endif
                                </div>
                                @if($tx->is_recurring)
                                    <div style="font-size: 10px; color: var(--blue); font-weight: 700; margin-top: 3px; padding-left: 4px;">↻ {{ ucfirst($tx->recurring_frequency) }}</div>
                                @endif
                            </td>
                            <td style="text-align: right; white-space: nowrap;">
                                <div style="display: inline-flex; align-items: center; gap: 5px;">
                                    <button type="button"
                                            class="btn btn-emerald btn-sm"
                                            style="padding: 3px 8px; font-size: 11px;"
                                            title="Record '{{ $tx->title }}' again with the same details"
                                            onclick='repeatQuickTransaction(@json($tx))'>
                                        ↻ Repeat
                                    </button>
                                    @if($txCount === 1)
                                        <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 8px; font-size: 11px;"
                                                onclick='openEditTxModal(@json($tx))'>Edit</button>
                                        <form action="{{ route('transactions.destroy', $tx) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this transaction?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 8px; font-size: 11px;">Del</button>
                                        </form>
                                    @else
                                        <button type="button"
                                                class="btn btn-plum btn-sm"
                                                style="padding: 3px 9px; font-size: 11px;"
                                                onclick="openCompoundedTxModal('{{ $domTxGroupId }}')">
                                            Manage ({{ $txCount }})
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 32px 24px; color: var(--text-muted);">
                            <strong style="color: var(--text-dark); font-size: 14px; display: block; margin-bottom: 4px;">
                                No transactions match your current filter criteria.
                            </strong>
                            <div style="font-size: 12.5px; margin-bottom: 14px;">
                                Choose whether you want to record a new <strong>Expense Outflow</strong> or <strong>Income Inflow</strong> below:
                            </div>
                            <div style="display: inline-flex; align-items: center; justify-content: center; gap: 10px; flex-wrap: wrap;">
                                @if($forcedType !== 'income')
                                    <button type="button" class="btn btn-coral btn-sm" style="padding: 8px 14px;" onclick="openQuickModal('expense')">
                                        + Record New Expense Outflow
                                    </button>
                                @endif
                                @if($forcedType !== 'expense')
                                    <button type="button" class="btn btn-emerald btn-sm" style="padding: 8px 14px;" onclick="openQuickModal('income')">
                                        + Record New Income Inflow
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 14px 20px;">
        {{ $transactions->links() }}
    </div>
</section>

<!-- COMPOUNDED TRANSACTIONS BREAKDOWN MODALS (ONE PER COMPOUNDED ITEM GROUP) -->
@foreach($compoundedTxGroups as $txGroupItems)
    @if($txGroupItems->count() > 1)
        @php
            $firstTx = $txGroupItems->first();
            $groupTotal = $txGroupItems->sum(fn($i) => (float) $i->amount);
            $distinctModalTitles = $txGroupItems->map(fn($item) => strtolower(trim((string) $item->title)))->unique()->count();
            $modalHeaderTitle = ($firstTx->type === 'income' && $distinctModalTitles > 1)
                ? ($firstTx->category?->name ?? $firstTx->income_source ?? $firstTx->title)
                : $firstTx->title;
            $domTxGroupId = 'compounded_tx_' . $firstTx->id;
        @endphp
        <div id="modal_{{ $domTxGroupId }}" class="modal-backdrop compounded-tx-modal" onclick="if(event.target === this) closeCompoundedTxModals()">
            <div class="modal-box" style="max-width: 740px;">
                <div class="modal-header">
                    <div>
                        <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.05em;">
                            Compounded {{ ucfirst($firstTx->type) }} Breakdown • {{ $txGroupItems->count() }} Entries
                        </div>
                        <h3 style="font-size: 16.5px; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                            <span class="cat-swatch" style="background-color: {{ $firstTx->category?->color ?? '#2D9F78' }}; width: 10px; height: 10px;"></span>
                            <span>{{ $modalHeaderTitle }}</span>
                        </h3>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <button type="button"
                                class="btn btn-emerald btn-sm"
                                onclick='closeCompoundedTxModals(); repeatQuickTransaction(@json($firstTx));'>
                            ↻ Record '{{ $firstTx->title }}' Again
                        </button>
                        <button type="button" class="btn btn-coral btn-sm" onclick="closeCompoundedTxModals()">✕ Close</button>
                    </div>
                </div>
                <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 12px 14px;">
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Compounded Count</div>
                            <div class="mono" style="font-size: 15px; font-weight: 800; color: var(--text-dark); margin-top: 2px;">{{ $txGroupItems->count() }} Occurrences</div>
                        </div>
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Category</div>
                            <div style="font-size: 13.5px; font-weight: 700; color: var(--text-dark); margin-top: 2px;">
                                {{ $firstTx->category?->name ?? 'General' }}
                                @if($firstTx->subcategory) <span style="color: var(--text-muted); font-weight: 500;">• {{ $firstTx->subcategory->name }}</span> @endif
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 10.5px; text-transform: uppercase; font-weight: 700; color: var(--text-muted);">Compounded Total</div>
                            <div class="mono" style="font-size: 15px; font-weight: 800; color: {{ $firstTx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }}; margin-top: 2px;">
                                {{ $firstTx->type === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format($groupTotal, 2) }}
                            </div>
                        </div>
                    </div>

                    <div class="table-wrap" style="border: 1px solid var(--ivory-border); border-radius: 12px; overflow: hidden;">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date & Ref</th>
                                    <th>Title & Merchant</th>
                                    <th>Method</th>
                                    <th style="text-align: right;">Amount</th>
                                    <th style="text-align: right;">Manage</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($txGroupItems as $subTx)
                                    <tr>
                                        <td class="mono" style="white-space: nowrap; font-size: 11.5px;">
                                            <strong>{{ $subTx->transaction_date->format('d M Y') }}</strong>
                                            <div style="font-size: 10px; color: var(--text-muted);">
                                                {{ $subTx->reference_code }} • {{ $subTx->transaction_time ? substr($subTx->transaction_time, 0, 5) : '' }}
                                            </div>
                                        </td>
                                        <td>
                                            <strong style="font-size: 12.5px; color: var(--text-dark);">{{ $subTx->title }}</strong>
                                            <div style="font-size: 11px; color: var(--text-muted);">{{ $subTx->merchant_or_source }}</div>
                                        </td>
                                        <td style="font-size: 12px; color: var(--text-muted); white-space: nowrap;">
                                            {{ $subTx->paymentMethod?->name ?? '—' }}
                                        </td>
                                        <td class="mono" style="text-align: right; font-weight: 700; font-size: 12.5px; white-space: nowrap; color: {{ $subTx->type === 'income' ? 'var(--emerald-dark)' : 'var(--coral-dark)' }};">
                                            {{ $subTx->type === 'income' ? '+' : '-' }}{{ $user->currency }}{{ number_format((float)$subTx->amount, 2) }}
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <div style="display: inline-flex; align-items: center; gap: 5px;">
                                                <button type="button"
                                                        class="btn btn-ivory btn-sm"
                                                        style="padding: 3px 8px; font-size: 11px;"
                                                        onclick='closeCompoundedTxModals(); openEditTxModal(@json($subTx));'>
                                                    ✎ Edit
                                                </button>
                                                <form action="{{ route('transactions.destroy', $subTx) }}" method="POST" style="display: inline; margin: 0;" onsubmit="return confirm('Delete this occurrence?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-coral btn-sm" style="padding: 3px 8px; font-size: 11px;">Del</button>
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

<!-- EDIT TRANSACTION MODAL -->
<div id="editTransactionModal" class="modal-backdrop">
    <div class="modal-box">
        <div class="modal-header">
            <h3 style="font-size: 16px;">Edit Transaction Record</h3>
            <button type="button" class="btn btn-coral btn-sm" onclick="document.getElementById('editTransactionModal').classList.remove('open')">✕ Close</button>
        </div>
        <div class="modal-body">
            <form id="editTransactionForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="form-grid" style="margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" id="edit_type" class="form-control" required>
                            <option value="expense">Expense</option>
                            <option value="income">Income</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amount ({{ $user->currency }})</label>
                        <input type="number" step="0.01" name="amount" id="edit_amount" class="form-control mono" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="transaction_date" id="edit_date" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-grid" style="margin-bottom: 14px;">
                    <div class="form-group" style="grid-column: span 2;">
                        <label class="form-label">Title</label>
                        <input type="text" name="title" id="edit_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Merchant / Source</label>
                        <input type="text" name="merchant_or_source" id="edit_merchant" class="form-control">
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Category</label>
                        <select name="expense_category_id" id="edit_category" class="form-control" onchange="syncEditSubcategories()" required>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" data-type="{{ $cat->type }}" data-subcategories='@json($cat->subcategories)'>
                                    {{ $cat->name }} ({{ ucfirst($cat->type) }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subcategory (Optional)</label>
                        <select name="expense_subcategory_id" id="edit_subcategory" class="form-control">
                            <option value="">General / Unspecified</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method_id" id="edit_pm" class="form-control">
                            @foreach($paymentMethods as $pm)
                                <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Attach New Receipt</label>
                        <input type="file" name="receipt" class="form-control">
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label">Notes</label>
                    <textarea name="notes" id="edit_notes" rows="2" class="form-control"></textarea>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-emerald">Update Transaction</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleCategoryBucket(bucketDomId, forceState) {
        const headerRow = document.getElementById('header_' + bucketDomId);
        const childRows = document.querySelectorAll('.cat-bucket-child-row[data-parent-bucket="' + bucketDomId + '"]');
        const chevron = document.getElementById('chevron_' + bucketDomId);
        const label = document.getElementById('toggle_label_' + bucketDomId);
        if (!headerRow) return;

        const currentlyExpanded = headerRow.getAttribute('aria-expanded') === 'true';
        const nextExpanded = typeof forceState === 'boolean' ? forceState : !currentlyExpanded;

        headerRow.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
        childRows.forEach(function (row) {
            row.style.display = nextExpanded ? '' : 'none';
        });

        if (chevron) {
            chevron.textContent = nextExpanded ? '▾' : '▸';
            chevron.style.backgroundColor = nextExpanded ? 'var(--emerald-soft)' : '#FFFFFF';
            chevron.style.color = nextExpanded ? 'var(--emerald-dark)' : 'var(--text-dark)';
        }
        if (label) {
            const count = childRows.length;
            label.textContent = (nextExpanded ? 'Hide Rows (' : 'Show Rows (') + count + ')';
        }
    }

    function setAllCategoryBuckets(expand) {
        document.querySelectorAll('.cat-bucket-header').forEach(function (header) {
            const bucketId = header.getAttribute('data-bucket-id');
            if (bucketId) {
                toggleCategoryBucket(bucketId, expand);
            }
        });
    }

    function openCompoundedTxModal(domGroupId) {
        document.getElementById('modal_' + domGroupId)?.classList.add('open');
    }

    function closeCompoundedTxModals() {
        document.querySelectorAll('.compounded-tx-modal.open').forEach(function (m) {
            m.classList.remove('open');
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeCompoundedTxModals();
            document.getElementById('editTransactionModal')?.classList.remove('open');
        }
    });

    function syncEditSubcategories(preselectedSubcategoryId = null) {
        const catSelect = document.getElementById('edit_category');
        const subSelect = document.getElementById('edit_subcategory');
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

    function openEditTxModal(tx) {
        const form = document.getElementById('editTransactionForm');
        form.action = '/transactions/' + tx.id;
        document.getElementById('edit_type').value = tx.type;
        document.getElementById('edit_amount').value = tx.amount;
        document.getElementById('edit_date').value = (tx.transaction_date || '').substring(0, 10);
        document.getElementById('edit_title').value = tx.title;
        document.getElementById('edit_merchant').value = tx.merchant_or_source || '';
        document.getElementById('edit_category').value = tx.expense_category_id;
        syncEditSubcategories(tx.expense_subcategory_id || '');
        document.getElementById('edit_pm').value = tx.payment_method_id;
        document.getElementById('edit_notes').value = tx.notes || '';
        document.getElementById('editTransactionModal').classList.add('open');
    }
</script>
@endpush
