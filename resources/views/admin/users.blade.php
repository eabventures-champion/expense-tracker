@extends('layouts.app')

@section('kicker', 'SaaS Administration')
@section('page_title', 'User Account Approvals & Workspace Provisioning')

@section('content')
<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr);">
    <div class="kpi-cell bg-mustard">
        <div class="kpi-label">Pending Approval Requests</div>
        <div class="kpi-amount">{{ $pendingCount }}</div>
        <div class="kpi-footer"><span>Awaiting administrator review & workspace setup</span></div>
    </div>
    <div class="kpi-cell bg-emerald">
        <div class="kpi-label">Approved Active Workspaces</div>
        <div class="kpi-amount">{{ $approvedCount }}</div>
        <div class="kpi-footer"><span>Provisioned with isolated ledgers & tour data</span></div>
    </div>
    <div class="kpi-cell bg-plum">
        <div class="kpi-label">Total Registered SaaS Users</div>
        <div class="kpi-amount">{{ $allUsers->count() }}</div>
        <div class="kpi-footer"><span>Multi-tenant CediLedger Cloud accounts</span></div>
    </div>
</div>

<section class="section-block theme-ivory">
    <div class="section-header">
        <div>
            <h2 class="section-title">Registered SaaS Users & Approval Queue ({{ $allUsers->count() }})</h2>
            <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                Approving a user automatically sets up their workspace with sample data for the Guided Tour
            </div>
        </div>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User & Email</th>
                    <th>Role & Status</th>
                    <th>Currency & Budget</th>
                    <th>Workspace State</th>
                    <th style="text-align: right;">Admin Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($allUsers as $u)
                    <tr>
                        <td>
                            <div style="display: flex; align-items: flex-start; gap: 10px;">
                                <div class="user-avatar" style="width: 34px; height: 34px; margin-top: 2px;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <strong>{{ $u->name }}</strong>
                                    <div class="mono" style="font-size: 11.5px; color: var(--text-muted);">{{ $u->email }}</div>
                                    @if($u->created_at)
                                        <div class="mono" style="font-size: 10.5px; color: var(--text-muted); opacity: 0.85;">
                                            Registered: {{ $u->created_at->format('d M Y, H:i') }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td style="white-space: nowrap;">
                            <div style="margin-bottom: 4px;">
                                <span class="mono" style="font-size: 10.5px; font-weight: 700; padding: 3px 9px; border-radius: 9999px; text-transform: uppercase; background-color: {{ $u->approval_status === 'approved' ? 'var(--emerald-soft)' : ($u->approval_status === 'pending' ? 'var(--mustard-soft)' : 'var(--coral-soft)') }}; color: {{ $u->approval_status === 'approved' ? 'var(--emerald-dark)' : ($u->approval_status === 'pending' ? 'var(--mustard-dark)' : 'var(--coral-dark)') }};">
                                    {{ $u->approval_status }}
                                </span>
                            </div>
                            <span style="font-size: 11px; color: var(--text-muted); text-transform: uppercase; font-weight: 600;">
                                Role: {{ $u->role }}
                            </span>
                        </td>
                        <td class="mono" style="white-space: nowrap;">
                            <strong>{{ $u->currency }}{{ number_format((float)$u->overall_monthly_budget, 2) }}</strong>
                            <div style="font-size: 11px; color: var(--text-muted);">Monthly Target</div>
                        </td>
                        <td>
                            @if($u->approval_status === 'approved')
                                <div style="font-size: 12px; font-weight: 600; color: var(--text-dark);">
                                    {{ $u->transactions_count }} tx • {{ $u->categories_count }} categories
                                </div>
                                <div style="font-size: 11px; color: {{ $u->has_sample_data ? 'var(--mustard-dark)' : 'var(--emerald-dark)' }};">
                                    {{ $u->has_sample_data ? 'Exploring Sample Tour Data' : '✓ Reset to Fresh Personal Ledger' }}
                                </div>
                            @else
                                <span style="font-size: 11.5px; color: var(--text-muted);">Not provisioned yet</span>
                            @endif
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            @if($u->id === $user->id)
                                <span style="font-size: 11.5px; color: var(--text-muted); font-weight: 600;">Primary Admin</span>
                            @else
                                @if($u->approval_status !== 'approved')
                                    <form action="{{ route('admin.users.approve', $u) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-emerald btn-sm">✓ Approve & Setup Workspace</button>
                                    </form>
                                @endif
                                @if($u->approval_status !== 'rejected')
                                    <form action="{{ route('admin.users.reject', $u) }}" method="POST" style="display: inline;" onsubmit="return confirm('Revoke or reject access for this user?')">
                                        @csrf
                                        <button type="submit" class="btn btn-coral btn-sm">
                                            {{ $u->approval_status === 'approved' ? 'Revoke Access' : 'Reject' }}
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
