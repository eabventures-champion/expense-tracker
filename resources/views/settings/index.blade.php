@extends('layouts.app')

@section('kicker', 'Settings')
@section('page_title', 'Currency, Financial Thresholds & Payment Channels')

@section('content')
<div class="grid-2">
    <section class="section-block theme-ivory mobile-collapsible-card" id="settingsThresholdsFormCard">
        <div class="section-header settings-thresholds-header" onclick="toggleCollapsibleCard('settingsThresholdsFormCard', 'mobile')" style="flex-wrap: nowrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 8px; min-width: 0; flex: 1;">
                <span class="collapsible-chevron-badge mono mobile-only-collapse-el">▸</span>
                <h2 class="section-title settings-thresholds-title" style="min-width: 0;">Owner Profile, Currency &amp; Master Thresholds</h2>
            </div>
            <button type="button"
                    class="btn btn-ivory btn-sm collapsible-toggle-btn mobile-only-collapse-el"
                    data-expand-label="Edit ▾"
                    data-hide-label="Hide ▴"
                    style="padding: 3px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700; flex-shrink: 0;">
                Edit ▾
            </button>
        </div>
        <div class="section-body collapsible-card-body">
            <form action="{{ route('settings.update') }}" method="POST" class="settings-thresholds-form" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf
                @method('PUT')
                <div class="form-grid settings-mobile-grid-2">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
                    </div>
                </div>
                <div class="form-grid settings-mobile-grid-2">
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
                <div class="form-grid settings-mobile-grid-2">
                    <div class="form-group">
                        <label class="form-label">Overall Monthly Budget</label>
                        <input type="number" step="0.01" name="overall_monthly_budget" value="{{ $user->overall_monthly_budget }}" class="form-control mono" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weekly Spending Limit</label>
                        <input type="number" step="0.01" name="weekly_spending_limit" value="{{ $user->weekly_spending_limit }}" class="form-control mono" required>
                    </div>
                    <div class="form-group settings-mobile-span-2">
                        <label class="form-label">Yearly Spending Target</label>
                        <input type="number" step="0.01" name="yearly_spending_target" value="{{ $user->yearly_spending_target }}" class="form-control mono" required>
                    </div>
                </div>
                <div>
                    <button type="submit" class="btn btn-plum settings-save-btn">Save Platform Settings</button>
                </div>
            </form>

            <!-- HEADER NAVIGATION & ONBOARDING SHORTCUTS VISIBILITY -->
            <div style="margin-top: 22px; padding-top: 18px; border-top: 1px solid var(--ivory-border); display: flex; flex-direction: column; gap: 12px;">
                <div>
                    <div style="font-size: 10.5px; color: var(--emerald-dark); text-transform: uppercase; font-weight: 800; letter-spacing: 0.06em;">
                        Interface &amp; Top Header Customization
                    </div>
                    <h3 style="font-size: 14.5px; font-weight: 700; color: var(--text-dark); margin-top: 2px;">
                        Top Header Onboarding &amp; Tour Buttons
                    </h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        Choose whether to show or hide the <strong>🧭 Setup Guide</strong> and <strong>✦ Guided Tour</strong> buttons in the top header bar across all pages.
                    </p>
                </div>

                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label for="toggle_show_setup_guide_btn" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 14px; border-radius: 10px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); cursor: pointer;">
                        <div>
                            <strong style="font-size: 13px; color: var(--text-dark); display: block;">Show "🧭 Setup Guide" in Top Header</strong>
                            <span style="font-size: 11.5px; color: var(--text-muted);">Displays the 5-step chronological setup roadmap shortcut at the top of the screen</span>
                        </div>
                        <input type="checkbox"
                               id="toggle_show_setup_guide_btn"
                               {{ ($headerPrefs['show_setup_guide'] ?? true) ? 'checked' : '' }}
                               onchange="updateHeaderShortcutVisibility('setup_guide', this.checked)"
                               style="width: 18px; height: 18px; accent-color: var(--emerald); cursor: pointer; flex-shrink: 0;">
                    </label>

                    <label for="toggle_show_guided_tour_btn" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 14px; border-radius: 10px; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); cursor: pointer;">
                        <div>
                            <strong style="font-size: 13px; color: var(--text-dark); display: block;">Show "✦ Guided Tour" in Top Header</strong>
                            <span style="font-size: 11.5px; color: var(--text-muted);">Displays the interactive platform walkthrough button at the top of the screen</span>
                        </div>
                        <input type="checkbox"
                               id="toggle_show_guided_tour_btn"
                               {{ ($headerPrefs['show_guided_tour'] ?? true) ? 'checked' : '' }}
                               onchange="updateHeaderShortcutVisibility('guided_tour', this.checked)"
                               style="width: 18px; height: 18px; accent-color: var(--plum); cursor: pointer; flex-shrink: 0;">
                    </label>
                </div>

                <div id="headerVisibilitySavedNotice" style="display: none; font-size: 12px; font-weight: 700; color: var(--emerald-dark); background-color: #F4FBF8; border: 1px solid rgba(45, 159, 120, 0.3); padding: 8px 12px; border-radius: 8px;">
                    ✓ Top header button preferences updated immediately.
                </div>
            </div>
        </div>
    </section>

    <section class="section-block theme-ivory">
        <div class="section-header">
            <h2 class="section-title">Configured Payment Methods ({{ $paymentMethods->count() }})</h2>
        </div>
        <div class="section-body">
            <div class="mobile-collapsible-card" id="addPaymentMethodFormCard" style="margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid var(--ivory-border);">
                <div class="section-header mobile-only-collapse-el" onclick="toggleCollapsibleCard('addPaymentMethodFormCard', 'mobile')" style="width: 100%; padding: 8px 12px !important; border: 1px solid var(--ivory-border); border-radius: 10px; background-color: var(--ivory-alt); margin-bottom: 10px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span class="collapsible-chevron-badge mono">▸</span>
                        <strong style="font-size: 12.5px; color: var(--text-dark);">Add New Payment Method</strong>
                    </div>
                    <button type="button"
                            class="btn btn-ivory btn-sm collapsible-toggle-btn"
                            data-expand-label="+ Add ▾"
                            data-hide-label="Hide ▴"
                            style="padding: 3px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700;">
                        + Add ▾
                    </button>
                </div>
                <form action="{{ route('settings.payment-methods.store') }}" method="POST" class="collapsible-card-body">
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
            </div>

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

@push('scripts')
<script>
    const HEADER_SETUP_BTN_HIDE_KEY = 'cediledger_hide_header_setup_btn_{{ $user->id }}';
    const HEADER_TOUR_BTN_HIDE_KEY = 'cediledger_hide_header_tour_btn_{{ $user->id }}';
    const SERVER_SHOW_SETUP = @json((bool) ($headerPrefs['show_setup_guide'] ?? true));
    const SERVER_SHOW_TOUR = @json((bool) ($headerPrefs['show_guided_tour'] ?? true));

    function setHeaderPrefCookie(name, val) {
        document.cookie = name + '=' + val + '; path=/; max-age=' + (60 * 60 * 24 * 365 * 5) + '; SameSite=Lax';
    }

    function syncHeaderShortcutCheckboxes() {
        const setupCheckbox = document.getElementById('toggle_show_setup_guide_btn');
        const tourCheckbox = document.getElementById('toggle_show_guided_tour_btn');
        try {
            const localHideSetup = localStorage.getItem(HEADER_SETUP_BTN_HIDE_KEY) === '1'
                || localStorage.getItem('cediledger_hide_header_setup_btn') === '1';
            const localHideTour = localStorage.getItem(HEADER_TOUR_BTN_HIDE_KEY) === '1'
                || localStorage.getItem('cediledger_hide_header_tour_btn') === '1';

            const showSetup = SERVER_SHOW_SETUP && !localHideSetup;
            const showTour = SERVER_SHOW_TOUR && !localHideTour;

            if (setupCheckbox) setupCheckbox.checked = showSetup;
            if (tourCheckbox) tourCheckbox.checked = showTour;

            // Keep localStorage, cookies, and server in sync if localStorage already hid them
            if (!showSetup) {
                localStorage.setItem(HEADER_SETUP_BTN_HIDE_KEY, '1');
                localStorage.setItem('cediledger_hide_header_setup_btn', '1');
                setHeaderPrefCookie('cediledger_hide_setup_btn', '1');
            }
            if (!showTour) {
                localStorage.setItem(HEADER_TOUR_BTN_HIDE_KEY, '1');
                localStorage.setItem('cediledger_hide_header_tour_btn', '1');
                setHeaderPrefCookie('cediledger_hide_tour_btn', '1');
            }
            if ((localHideSetup && SERVER_SHOW_SETUP) || (localHideTour && SERVER_SHOW_TOUR)) {
                persistHeaderPreferencesToServer(showSetup, showTour);
            }
        } catch (e) {}
    }

    function persistHeaderPreferencesToServer(showSetup, showTour) {
        try {
            fetch('{{ route("settings.header-preferences") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    show_setup_guide: showSetup,
                    show_guided_tour: showTour
                })
            }).catch(function () {});
        } catch (e) {}
    }

    function updateHeaderShortcutVisibility(which, shouldShow) {
        try {
            if (which === 'setup_guide') {
                document.documentElement.classList.toggle('hide-topbar-setup-btn', !shouldShow);
                setHeaderPrefCookie('cediledger_hide_setup_btn', shouldShow ? '0' : '1');
                if (shouldShow) {
                    localStorage.removeItem(HEADER_SETUP_BTN_HIDE_KEY);
                    localStorage.removeItem('cediledger_hide_header_setup_btn');
                } else {
                    localStorage.setItem(HEADER_SETUP_BTN_HIDE_KEY, '1');
                    localStorage.setItem('cediledger_hide_header_setup_btn', '1');
                }
            } else if (which === 'guided_tour') {
                document.documentElement.classList.toggle('hide-topbar-tour-btn', !shouldShow);
                setHeaderPrefCookie('cediledger_hide_tour_btn', shouldShow ? '0' : '1');
                if (shouldShow) {
                    localStorage.removeItem(HEADER_TOUR_BTN_HIDE_KEY);
                    localStorage.removeItem('cediledger_hide_header_tour_btn');
                } else {
                    localStorage.setItem(HEADER_TOUR_BTN_HIDE_KEY, '1');
                    localStorage.setItem('cediledger_hide_header_tour_btn', '1');
                }
            }
        } catch (e) {}

        const setupCheckbox = document.getElementById('toggle_show_setup_guide_btn');
        const tourCheckbox = document.getElementById('toggle_show_guided_tour_btn');
        persistHeaderPreferencesToServer(
            setupCheckbox ? setupCheckbox.checked : true,
            tourCheckbox ? tourCheckbox.checked : true
        );

        if (typeof applyTopbarGuideButtonsVisibility === 'function') {
            applyTopbarGuideButtonsVisibility();
        }

        const notice = document.getElementById('headerVisibilitySavedNotice');
        if (notice) {
            notice.style.display = 'block';
            clearTimeout(window._headerVisTimer);
            window._headerVisTimer = setTimeout(function () {
                notice.style.display = 'none';
            }, 2600);
        }
    }

    document.addEventListener('DOMContentLoaded', syncHeaderShortcutCheckboxes);
</script>
@endpush
