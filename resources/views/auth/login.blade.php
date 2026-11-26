<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign In — CediLedger Financial Intelligence</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --plum: #1E1B2E;
            --plum-dark: #151321;
            --plum-surface: #27233A;
            --plum-border: rgba(255, 255, 255, 0.1);
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
            max-width: 980px;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
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
            padding: 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            position: relative;
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

        .showcase-headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.2;
            letter-spacing: -0.03em;
            margin-bottom: 14px;
        }

        .showcase-desc {
            font-size: 14px;
            color: #B4B8CA;
            line-height: 1.6;
            margin-bottom: 24px;
        }

        .feature-pills {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            background-color: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 12px 14px;
            border-radius: 12px;
            font-size: 13px;
        }

        .feature-dot {
            width: 10px;
            height: 10px;
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
            padding-top: 18px;
        }

        /* RIGHT FORM PANEL */
        .auth-form-panel {
            padding: 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #FFFFFF;
            min-width: 0;
        }

        .form-kicker {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--emerald);
            margin-bottom: 6px;
        }

        .form-heading {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .form-subheading {
            font-size: 13.5px;
            color: var(--text-muted);
            margin-bottom: 24px;
        }

        .alert-box {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 18px;
        }

        .alert-error {
            background-color: rgba(230, 109, 85, 0.12);
            color: var(--coral-dark);
            border: 1px solid rgba(230, 109, 85, 0.3);
        }

        .alert-success {
            background-color: rgba(45, 159, 120, 0.12);
            color: var(--emerald-dark);
            border: 1px solid rgba(45, 159, 120, 0.3);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 16px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
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
            padding-right: 44px;
        }

        .btn-toggle-password {
            position: absolute;
            right: 10px;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 5px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s ease, background-color 0.15s ease;
        }

        .btn-toggle-password:hover {
            color: var(--text-dark);
            background-color: rgba(20, 22, 31, 0.05);
        }

        .btn-toggle-password.is-visible {
            color: var(--emerald-dark);
            background-color: rgba(45, 159, 120, 0.14);
            border: 1px solid rgba(45, 159, 120, 0.3);
        }

        .form-row-between {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12.5px;
            color: var(--text-muted);
        }

        .btn-submit {
            width: 100%;
            padding: 12px 18px;
            background-color: var(--plum);
            color: #FFFFFF;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.16s ease;
            box-shadow: 0 4px 12px rgba(30, 27, 46, 0.2);
        }

        .btn-submit:hover {
            background-color: var(--plum-surface);
            transform: translateY(-1px);
        }

        .demo-credentials-card {
            margin-top: 22px;
            padding: 14px 16px;
            background-color: var(--ivory);
            border: 1px solid var(--ivory-border);
            border-radius: 12px;
            font-size: 12px;
        }

        .demo-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }

        .btn-fill-demo {
            background-color: var(--emerald);
            color: #FFFFFF;
            border: none;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-fill-demo:hover {
            background-color: var(--emerald-dark);
        }

        @media (max-width: 820px) {
            body {
                padding: 10px;
                align-items: flex-start;
            }
            .auth-wrapper {
                grid-template-columns: minmax(0, 1fr);
                border-radius: 16px;
            }
            .auth-showcase {
                padding: 18px 18px 14px;
                gap: 10px;
            }
            .showcase-headline {
                font-size: 20px;
                margin-bottom: 8px;
            }
            .showcase-desc {
                margin-bottom: 0;
                font-size: 13px;
            }
            .feature-pills {
                display: none;
            }
            .showcase-footer {
                flex-wrap: wrap;
                gap: 6px;
                padding-top: 10px;
                margin-top: 2px;
            }
            .auth-form-panel {
                padding: 22px 18px;
            }
            .form-row-between {
                flex-wrap: wrap;
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-showcase">
            <a href="{{ route('welcome') }}" class="brand-logo" style="color: inherit; text-decoration: none;" title="Go to CediLedger Cloud Welcome Page">
                <span class="brand-mark">GH¢</span>
                <div>
                    <div class="brand-title">CediLedger</div>
                    <div class="brand-sub">Personal Expense Tracker & Financial Intelligence</div>
                </div>
            </a>

            <div>
                <h1 class="showcase-headline">Complete clarity over every cedi you earn, spend, and save.</h1>
                <p class="showcase-desc">
                    Track daily, weekly, monthly, and annual expenditure with hierarchical drill-downs, category budgets, recurring commitments, and explicit savings vaults.
                </p>

                <div class="feature-pills">
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #2D9F78;"></span>
                        <span><strong>Multi-Horizon Explorer</strong> — Year → Month → Week 1–4 → Daily ledger</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #E5A93C;"></span>
                        <span><strong>Explicit Savings Vaults</strong> — Dedicated capital & Treasury Bill tracking</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #E66D55;"></span>
                        <span><strong>Automated Diagnostics</strong> — Live budget alerts & spending velocity insights</span>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <span>CediLedger Executive Suite</span>
                <span class="mono">GH¢ • USD • GBP • EUR</span>
            </div>
        </div>

        <div class="auth-form-panel">
            <span class="form-kicker">Secure Ledger Access</span>
            <h2 class="form-heading">Sign in to your account</h2>
            <p class="form-subheading">Enter your credentials below to access your financial command center.</p>

            @if(session('success'))
                <div class="alert-box alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.submit') }}" id="loginForm">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email', app()->isLocal() ? $defaultEmail : '') }}" class="form-control" placeholder="name@example.com" required autofocus>
                </div>

                <div class="form-group">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <label class="form-label" for="password">Password</label>
                        <a href="{{ route('password.request') }}"
                           onclick="const em = document.getElementById('email')?.value; if(em) this.href='{{ route('password.request') }}?email=' + encodeURIComponent(em);"
                           style="font-size: 12px; font-weight: 700; color: var(--emerald-dark); text-decoration: none;">
                            Forgot password?
                        </a>
                    </div>
                    <div class="password-input-wrap">
                        <input type="password" id="password" name="password" value="{{ old('password', app()->isLocal() ? 'password123' : '') }}" class="form-control" placeholder="••••••••••••" required>
                        <button type="button" class="btn-toggle-password" onclick="togglePasswordVisibility()" id="togglePasswordBtn" aria-label="Show password" title="Show / Hide Password">
                            <svg id="eyeOpenIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg id="eyeClosedIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-row-between">
                    <label for="remember" style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" id="remember" name="remember" value="1" {{ old('remember', true) ? 'checked' : '' }} style="width: 15px; height: 15px; accent-color: var(--plum); cursor: pointer;">
                        <span>Keep me signed in</span>
                    </label>
                    <span class="mono" style="font-size: 11px; color: var(--emerald);">Encrypted Session</span>
                </div>

                <button type="submit" class="btn-submit">Sign In to CediLedger →</button>
            </form>

            <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--ivory-border); text-align: center; font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 6px;">
                <div>
                    New to CediLedger? <a href="{{ route('register') }}" style="color: var(--emerald-dark); font-weight: 700; text-decoration: underline;">Create a free account →</a>
                </div>
                <div>
                    <a href="{{ route('welcome') }}" style="font-size: 12px; color: var(--text-muted); text-decoration: underline;">Explore CediLedger Cloud SaaS Overview</a>
                </div>
            </div>

            @if(app()->isLocal())
                <div class="demo-credentials-card">
                    <div class="demo-header">
                        <strong style="color: var(--text-dark);">Owner Account Credentials (Local Dev Only)</strong>
                        <button type="button" class="btn-fill-demo" onclick="fillAndSignIn()">Quick Sign In</button>
                    </div>
                    <div class="mono" style="color: var(--text-muted); font-size: 11.5px; line-height: 1.6;">
                        <div>Email: <strong style="color: var(--text-dark);">{{ $defaultEmail }}</strong></div>
                        <div>Password: <strong style="color: var(--text-dark);">password123</strong></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        const LOGIN_STORAGE_KEY = 'cediledger_login_form_state_v1';

        function setLoginEyeVisual(showPlain) {
            const pwdInput = document.getElementById('password');
            const eyeOpen = document.getElementById('eyeOpenIcon');
            const eyeClosed = document.getElementById('eyeClosedIcon');
            const btn = document.getElementById('togglePasswordBtn');
            if (!pwdInput || !btn) return;

            if (showPlain) {
                pwdInput.type = 'text';
                eyeOpen.style.display = 'none';
                eyeClosed.style.display = 'block';
                btn.classList.add('is-visible');
                btn.setAttribute('aria-label', 'Hide password');
                btn.setAttribute('title', 'Hide password (currently visible)');
            } else {
                pwdInput.type = 'password';
                eyeOpen.style.display = 'block';
                eyeClosed.style.display = 'none';
                btn.classList.remove('is-visible');
                btn.setAttribute('aria-label', 'Show password');
                btn.setAttribute('title', 'Show password');
            }
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            if (!pwdInput) return;
            setLoginEyeVisual(pwdInput.type === 'password');
            saveLoginDraft();
        }

        function saveLoginDraft() {
            try {
                const emailEl = document.getElementById('email');
                const pwdEl = document.getElementById('password');
                const remEl = document.getElementById('remember');
                sessionStorage.setItem(LOGIN_STORAGE_KEY, JSON.stringify({
                    email: emailEl ? emailEl.value : '',
                    password: pwdEl ? pwdEl.value : '',
                    remember: remEl ? remEl.checked : true,
                    pwd_visible: pwdEl ? pwdEl.type === 'text' : false
                }));
            } catch (e) {}
        }

        document.addEventListener('DOMContentLoaded', function () {
            try {
                const raw = sessionStorage.getItem(LOGIN_STORAGE_KEY);
                if (raw) {
                    const saved = JSON.parse(raw);
                    const emailEl = document.getElementById('email');
                    const pwdEl = document.getElementById('password');
                    const remEl = document.getElementById('remember');
                    if (emailEl && typeof saved.email === 'string' && saved.email.length > 0) {
                        emailEl.value = saved.email;
                    }
                    if (pwdEl && typeof saved.password === 'string' && saved.password.length > 0) {
                        pwdEl.value = saved.password;
                    }
                    if (remEl && typeof saved.remember === 'boolean') {
                        remEl.checked = saved.remember;
                    }
                    if (saved.pwd_visible) {
                        setLoginEyeVisual(true);
                    }
                }
            } catch (e) {}

            ['email', 'password', 'remember'].forEach(function (id) {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('input', saveLoginDraft);
                    el.addEventListener('change', saveLoginDraft);
                }
            });
        });
    </script>

    @if(app()->isLocal())
    <script>
        function fillAndSignIn() {
            document.getElementById('email').value = @json($defaultEmail);
            document.getElementById('password').value = 'password123';
            document.getElementById('loginForm').submit();
        }
    </script>
    @endif
</body>
</html>
