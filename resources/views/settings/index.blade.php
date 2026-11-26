@extends('layouts.app')

@section('kicker', 'Settings')
@section('page_title', 'Currency, Financial Thresholds & Payment Channels')

@section('content')
<div class="grid-2">
    <section class="section-block theme-ivory">
        <div class="section-header">
            <h2 class="section-title">Owner Profile, Currency & Master Thresholds</h2>
        </div>
        <div class="section-body">
            <form action="{{ route('settings.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Active Default Currency</label>
                        <select name="currency" class="form-control mono">
                            <option value="GH¢" {{ $user->currency === 'GH¢' ? 'selected' : '' }}>GH¢ — Ghana Cedi (Default)</option>
                            <option value="$" {{ $user->currency === '$' ? 'selected' : '' }}>$ — US Dollar (USD)</option>
                            <option value="£" {{ $user->currency === '£' ? 'selected' : '' }}>£ — British Pound (GBP)</option>
                            <option value="€" {{ $user->currency === '€' ? 'selected' : '' }}>€ — Euro (EUR)</option>
                            <option value="₦" {{ $user->currency === '₦' ? 'selected' : '' }}>₦ — Nigerian Naira (NGN)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Monthly Income Target</label>
                        <input type="number" step="0.01" name="monthly_income_target" value="{{ $user->monthly_income_target }}" class="form-control mono" required>
                    </div>
                </div>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Overall Monthly Budget</label>
                        <input type="number" step="0.01" name="overall_monthly_budget" value="{{ $user->overall_monthly_budget }}" class="form-control mono" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weekly Spending Limit</label>
                        <input type="number" step="0.01" name="weekly_spending_limit" value="{{ $user->weekly_spending_limit }}" class="form-control mono" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Yearly Spending Target</label>
                        <input type="number" step="0.01" name="yearly_spending_target" value="{{ $user->yearly_spending_target }}" class="form-control mono" required>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-plum">Save Platform Settings</button>
                </div>
            </form>
        </div>
    </section>

    <section class="section-block theme-ivory">
        <div class="section-header">
            <h2 class="section-title">Configured Payment Methods ({{ $paymentMethods->count() }})</h2>
        </div>
        <div class="section-body">
            <form action="{{ route('settings.payment-methods.store') }}" method="POST" style="margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid var(--ivory-border);">
                @csrf
                <div class="form-grid" style="margin-bottom: 12px;">
                    <div class="form-group">
                        <label class="form-label">Method Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g., CalBank Visa Card" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="mobile_money">Mobile Money</option>
                            <option value="bank">Bank Account</option>
                            <option value="card">Debit / Credit Card</option>
                            <option value="cash">Physical Cash</option>
                            <option value="wallet">Digital Wallet</option>
                        </select>
                    </div>
                </div>
                <div class="form-grid" style="margin-bottom: 12px;">
                    <div class="form-group">
                        <label class="form-label">Institution / Provider</label>
                        <input type="text" name="provider" class="form-control" placeholder="e.g., CalBank Ghana">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account / Masked Ref</label>
                        <input type="text" name="account_reference" class="form-control mono" placeholder="e.g., 4111 •••• 9012">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Badge Color</label>
                        <select name="color" class="form-control mono" data-color-picker="true">
                            <option value="#E5A93C">Mustard Yellow (#E5A93C)</option>
                            <option value="#2D9F78">Emerald Green (#2D9F78)</option>
                            <option value="#4E73DF">Blue (#4E73DF)</option>
                            <option value="#E66D55">Coral (#E66D55)</option>
                            <option value="#D96D9B">Rose (#D96D9B)</option>
                            <option value="#1E1B2E">Deep Plum (#1E1B2E)</option>
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-emerald btn-sm">+ Add Payment Method</button>
            </form>

            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Payment Method</th>
                            <th>Provider</th>
                            <th>Reference</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($paymentMethods as $pm)
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: flex-start; gap: 8px;">
                                        <span class="cat-swatch" style="background-color: {{ $pm->color }}; margin-top: 5px;"></span>
                                        <div>
                                            <div style="font-weight: 700; color: var(--text-dark);">{{ $pm->name }}</div>
                                            <div class="mono" style="font-size: 10.5px; color: var(--text-muted); margin-top: 2px;">{{ strtoupper(str_replace('_', ' ', $pm->type)) }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $pm->provider }}</td>
                                <td class="mono" style="color: var(--text-muted); white-space: nowrap;">{{ $pm->account_reference }}</td>
                                <td style="text-align: right;">
                                    <form action="{{ route('settings.payment-methods.destroy', $pm) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ivory btn-sm">✕</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>
@endsection
