<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Create Free Account — CediLedger SaaS Financial Intelligence</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --plum: #1E1B2E;
            --plum-dark: #151321;
            --plum-surface: #27233A;
            --coral: #E66D55;
            --coral-dark: #D05A42;
            --emerald: #2D9F78;
            --emerald-dark: #238261;
            --mustard: #E5A93C;
            --ivory: #F4F5F8;
            --ivory-card: #FFFFFF;
            --ivory-border: #E5E7EF;
            --blue: #4E73DF;
            --text-dark: #14161F;
            --text-muted: #646A7A;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        html, body {
            max-width: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--plum-dark);
            color: var(--text-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            -webkit-font-smoothing: antialiased;
        }

        .mono {
            font-family: 'JetBrains Mono', monospace;
        }

        .auth-wrapper {
            width: 100%;
            max-width: 1040px;
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1.1fr);
            background-color: var(--ivory-card);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* LEFT SHOWCASE PANEL */
        .auth-showcase {
            background-color: var(--plum);
            color: #FFFFFF;
            padding: 42px 38px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 28px;
            min-width: 0;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            background-color: var(--emerald);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            font-size: 15px;
            border-radius: 12px;
            box-shadow: 0 6px 16px rgba(45, 159, 120, 0.35);
        }

        .brand-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 19px;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .brand-sub {
            font-size: 11.5px;
            color: #A3A7BA;
        }

        .saas-pill {
            display: inline-block;
            background-color: rgba(45, 159, 120, 0.18);
            color: #5CE0B5;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 4px 10px;
            border-radius: 9999px;
            margin-bottom: 12px;
        }

        .showcase-headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 26px;
            font-weight: 800;
            line-height: 1.22;
            letter-spacing: -0.03em;
            margin-bottom: 12px;
        }

        .showcase-desc {
            font-size: 13.5px;
            color: #B4B8CA;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        .feature-pills {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 11px;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 11px 14px;
            border-radius: 11px;
            font-size: 12.5px;
        }

        .feature-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .showcase-footer {
            font-size: 12px;
            color: #8E93A8;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 16px;
        }

        /* RIGHT FORM PANEL */
        .auth-form-panel {
            padding: 38px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #FFFFFF;
        }

        .form-kicker {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--emerald);
            margin-bottom: 5px;
        }

        .form-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 23px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 5px;
        }

        .form-subheading {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 20px;
        }

        .alert-error {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 16px;
            background-color: rgba(230, 109, 85, 0.12);
            color: var(--coral-dark);
            border: 1px solid rgba(230, 109, 85, 0.3);
        }

        .form-grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
            margin-bottom: 14px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 10px 13px;
            font-size: 13.5px;
            font-family: inherit;
            border-radius: 10px;
            border: 1px solid var(--ivory-border);
            background-color: #FAFBFD;
            color: var(--text-dark);
            outline: none;
            transition: all 0.15s ease;
        }

        .form-control:focus {
            background-color: #FFFFFF;
            border-color: var(--blue);
            box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.16);
        }

        .password-input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .password-input-wrap .form-control {
            padding-right: 40px;
        }

        .btn-toggle-password {
            position: absolute;
            right: 8px;
            background: none;
            border: 1px solid transparent;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            border-radius: 7px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.15s ease;
        }

        .btn-toggle-password:hover {
            color: var(--text-dark);
            background-color: rgba(20, 22, 31, 0.05);
        }

        .btn-toggle-password.is-visible {
            color: var(--emerald-dark);
            background-color: rgba(45, 159, 120, 0.14);
            border-color: rgba(45, 159, 120, 0.3);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 18px;
            background-color: var(--emerald);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.16s ease;
            box-shadow: 0 4px 12px rgba(45, 159, 120, 0.25);
            margin-top: 4px;
        }

        .btn-submit:hover {
            background-color: var(--emerald-dark);
            transform: translateY(-1px);
        }

        .auth-switch-bar {
            margin-top: 18px;
            padding-top: 16px;
            border-top: 1px solid var(--ivory-border);
            text-align: center;
            font-size: 13px;
            color: var(--text-muted);
        }

        .auth-switch-bar a {
            color: var(--plum);
            font-weight: 700;
            text-decoration: underline;
        }

        @media (max-width: 820px) {
            body { padding: 10px; align-items: flex-start; }
            .auth-wrapper { grid-template-columns: minmax(0, 1fr); border-radius: 16px; }
            .auth-showcase { padding: 18px 18px 14px; gap: 10px; }
            .showcase-headline { font-size: 20px; margin-bottom: 8px; }
            .showcase-desc { margin-bottom: 0; font-size: 13px; }
            .feature-pills { display: none; }
            .showcase-footer { flex-wrap: wrap; gap: 6px; padding-top: 10px; margin-top: 2px; }
            .auth-form-panel { padding: 22px 18px; min-width: 0; }
            .form-grid-2 { grid-template-columns: minmax(0, 1fr); gap: 0; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-showcase">
            <a href="{{ route('welcome') }}" class="brand-logo" style="color: inherit; text-decoration: none;" title="Go to CediLedger Cloud Welcome Page">
                <span class="brand-mark">GH¢</span>
                <div>
                    <div class="brand-title">CediLedger Cloud</div>
                    <div class="brand-sub">Personal & Business Financial Intelligence SaaS</div>
                </div>
            </a>

            <div>
                <span class="saas-pill">Instant Workspace Provisioning</span>
                <h1 class="showcase-headline">Launch your private financial command center in 30 seconds.</h1>
                <p class="showcase-desc">
                    Every account comes pre-configured with 16+ customizable expense & income categories, multi-currency support, interactive period drill-downs, and optional 2FA security.
                </p>

                <div class="feature-pills">
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #2D9F78;"></span>
                        <span><strong>100% Isolated Private Ledger</strong> — Your data, budgets & receipts belong strictly to your account</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #E5A93C;"></span>
                        <span><strong>Pre-Built Taxonomy & Channels</strong> — 16 expense categories, 5 income streams & 5 payment methods ready</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #4E73DF;"></span>
                        <span><strong>TOTP Two-Factor Security</strong> — Protect your financial ledger with Google Authenticator or Authy</span>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <span>CediLedger SaaS Platform</span>
                <span class="mono">GH¢ • USD • GBP • EUR • NGN</span>
            </div>
        </div>

        <div class="auth-form-panel">
            <span class="form-kicker">Start Your Free Workspace</span>
            <h2 class="form-heading">Create your CediLedger account</h2>
            <p class="form-subheading">Set up your profile, default currency, and monthly budget target below.</p>

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.submit') }}" id="registerForm">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="e.g., Kwame Mensah" required autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="kwame@example.com" required>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="currency">Default Currency</label>
                        <select id="currency" name="currency" class="form-control mono" required>
                            <option value="GH¢" {{ old('currency', 'GH¢') === 'GH¢' ? 'selected' : '' }}>GH¢ — Ghana Cedi</option>
                            <option value="$" {{ old('currency') === '$' ? 'selected' : '' }}>$ — US Dollar (USD)</option>
                            <option value="£" {{ old('currency') === '£' ? 'selected' : '' }}>£ — British Pound (GBP)</option>
                            <option value="€" {{ old('currency') === '€' ? 'selected' : '' }}>€ — Euro (EUR)</option>
                            <option value="₦" {{ old('currency') === '₦' ? 'selected' : '' }}>₦ — Nigerian Naira (NGN)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="overall_monthly_budget">Monthly Budget Target</label>
                        <input type="number" step="0.01" min="0" id="overall_monthly_budget" name="overall_monthly_budget" value="{{ old('overall_monthly_budget', '5000.00') }}" class="form-control mono" placeholder="5000.00">
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="reg_password">Password (Min 8 chars)</label>
                        <div class="password-input-wrap">
                            <input type="password" id="reg_password" name="password" value="{{ old('password') }}" class="form-control" placeholder="••••••••" required>
                            <button type="button" class="btn-toggle-password" id="btn_toggle_reg_password" onclick="toggleRegPassword('reg_password', 'btn_toggle_reg_password')" aria-label="Show password" title="Show password">
                                <svg class="eye-open-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-closed-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reg_password_confirmation">Confirm Password</label>
                        <div class="password-input-wrap">
                            <input type="password" id="reg_password_confirmation" name="password_confirmation" value="{{ old('password_confirmation') }}" class="form-control" placeholder="••••••••" required>
                            <button type="button" class="btn-toggle-password" id="btn_toggle_reg_password_confirmation" onclick="toggleRegPassword('reg_password_confirmation', 'btn_toggle_reg_password_confirmation')" aria-label="Show password" title="Show password">
                                <svg class="eye-open-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-closed-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Create My Financial Workspace →</button>
            </form>

            <div class="auth-switch-bar" style="display: flex; flex-direction: column; gap: 6px;">
                <div>Already have a CediLedger account? <a href="{{ route('login') }}">Sign in here</a></div>
                <div><a href="{{ route('welcome') }}" style="font-size: 12px; color: var(--text-muted); font-weight: 500;">Explore CediLedger Cloud SaaS Overview</a></div>
            </div>
        </div>
    </div>

    <script>
        const REG_STORAGE_KEY = 'cediledger_register_form_state_v1';
        const REG_FIELD_IDS = ['name', 'email', 'currency', 'overall_monthly_budget', 'reg_password', 'reg_password_confirmation'];

        function setPasswordEyeVisual(input, btn, showPlain) {
            if (!input || !btn) return;
            const eyeOpen = btn.querySelector('.eye-open-icon');
            const eyeClosed = btn.querySelector('.eye-closed-icon');

            if (showPlain) {
                input.type = 'text';
                if (eyeOpen) eyeOpen.style.display = 'none';
                if (eyeClosed) eyeClosed.style.display = 'block';
                btn.classList.add('is-visible');
                btn.setAttribute('aria-label', 'Hide password');
                btn.setAttribute('title', 'Hide password (currently visible)');
            } else {
                input.type = 'password';
                if (eyeOpen) eyeOpen.style.display = 'block';
                if (eyeClosed) eyeClosed.style.display = 'none';
                btn.classList.remove('is-visible');
                btn.setAttribute('aria-label', 'Show password');
                btn.setAttribute('title', 'Show password');
            }
        }

        function toggleRegPassword(fieldId, btnId) {
            const input = document.getElementById(fieldId);
            const btn = document.getElementById(btnId);
            if (!input) return;

            const shouldShow = input.type === 'password';
            setPasswordEyeVisual(input, btn, shouldShow);
            saveRegFormDraft();
        }

        function saveRegFormDraft() {
            try {
                const payload = {};
                REG_FIELD_IDS.forEach(function (id) {
                    const el = document.getElementById(id);
                    if (el) {
                        payload[id] = el.value;
                    }
                });
                const pwdEl = document.getElementById('reg_password');
                const confirmEl = document.getElementById('reg_password_confirmation');
                payload._pwd_visible = pwdEl && pwdEl.type === 'text';
                payload._confirm_visible = confirmEl && confirmEl.type === 'text';
                sessionStorage.setItem(REG_STORAGE_KEY, JSON.stringify(payload));
            } catch (e) {}
        }

        function restoreRegFormDraft() {
            try {
                const raw = sessionStorage.getItem(REG_STORAGE_KEY);
                if (!raw) return;
                const saved = JSON.parse(raw);
                if (!saved || typeof saved !== 'object') return;

                REG_FIELD_IDS.forEach(function (id) {
                    const el = document.getElementById(id);
                    if (!el) return;
                    // Restore saved draft if field is empty or has the default 5000.00 budget placeholder
                    const isDefaultBudget = id === 'overall_monthly_budget' && el.value === '5000.00' && saved[id] && saved[id] !== '5000.00';
                    const isDefaultCurrency = id === 'currency' && saved[id];
                    if ((!el.value || isDefaultBudget || isDefaultCurrency) && typeof saved[id] === 'string' && saved[id].length > 0) {
                        el.value = saved[id];
                    }
                });

                if (saved._pwd_visible) {
                    setPasswordEyeVisual(
                        document.getElementById('reg_password'),
                        document.getElementById('btn_toggle_reg_password'),
                        true
                    );
                }
                if (saved._confirm_visible) {
                    setPasswordEyeVisual(
                        document.getElementById('reg_password_confirmation'),
                        document.getElementById('btn_toggle_reg_password_confirmation'),
                        true
                    );
                }
            } catch (e) {}
        }

        document.addEventListener('DOMContentLoaded', function () {
            restoreRegFormDraft();
            REG_FIELD_IDS.forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', saveRegFormDraft);
                    el.addEventListener('change', saveRegFormDraft);
                }
            });
        });
    </script>
</body>
</html>
