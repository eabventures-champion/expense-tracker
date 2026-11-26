<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Forgot Password — CediLedger Financial Intelligence</title>
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

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body { max-width: 100%; overflow-x: hidden; }

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

        .mono { font-family: 'JetBrains Mono', monospace; }

        .auth-wrapper {
            width: 100%;
            max-width: 960px;
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr);
            background-color: var(--ivory-card);
            border-radius: 22px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .auth-showcase {
            background-color: var(--plum);
            color: #FFFFFF;
            padding: 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
            min-width: 0;
        }

        .brand-logo { display: flex; align-items: center; gap: 12px; color: inherit; text-decoration: none; }
        .brand-mark {
            width: 42px; height: 42px; background-color: var(--emerald); color: #FFFFFF;
            display: flex; align-items: center; justify-content: center;
            font-family: 'JetBrains Mono', monospace; font-weight: 700; font-size: 15px;
            border-radius: 12px; box-shadow: 0 6px 16px rgba(45, 159, 120, 0.35);
        }
        .brand-title { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 19px; font-weight: 800; letter-spacing: -0.02em; }
        .brand-sub { font-size: 11.5px; color: #A3A7BA; }
        .showcase-headline { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 28px; font-weight: 800; line-height: 1.2; letter-spacing: -0.03em; margin-bottom: 14px; }
        .showcase-desc { font-size: 14px; color: #B4B8CA; line-height: 1.6; margin-bottom: 24px; }
        .feature-pills { display: flex; flex-direction: column; gap: 12px; }
        .feature-item {
            display: flex; align-items: center; gap: 12px;
            background-color: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 12px 14px; border-radius: 12px; font-size: 13px;
        }
        .feature-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
        .showcase-footer {
            font-size: 12px; color: #8E93A8; display: flex; align-items: center; justify-content: space-between;
            border-top: 1px solid rgba(255, 255, 255, 0.08); padding-top: 18px;
        }

        .auth-form-panel {
            padding: 44px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background-color: #FFFFFF;
            min-width: 0;
        }

        .form-kicker {
            display: inline-block; font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.08em; color: var(--emerald); margin-bottom: 6px;
        }
        .form-heading { font-family: 'Plus Jakarta Sans', sans-serif; font-size: 24px; font-weight: 800; color: var(--text-dark); margin-bottom: 6px; }
        .form-subheading { font-size: 13.5px; color: var(--text-muted); margin-bottom: 22px; line-height: 1.5; }

        .alert-box { padding: 12px 14px; border-radius: 10px; font-size: 12.5px; font-weight: 600; margin-bottom: 18px; line-height: 1.5; }
        .alert-error { background-color: rgba(230, 109, 85, 0.12); color: var(--coral-dark); border: 1px solid rgba(230, 109, 85, 0.3); }
        .alert-success { background-color: rgba(45, 159, 120, 0.12); color: var(--emerald-dark); border: 1px solid rgba(45, 159, 120, 0.3); }

        .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 18px; }
        .form-label { font-size: 12px; font-weight: 600; color: var(--text-dark); }
        .form-control {
            width: 100%; padding: 11px 14px; font-size: 13.5px; font-family: inherit;
            border-radius: 10px; border: 1px solid var(--ivory-border); background-color: #FAFBFD;
            color: var(--text-dark); outline: none; transition: all 0.15s ease;
        }
        .form-control:focus { background-color: #FFFFFF; border-color: var(--blue); box-shadow: 0 0 0 3px rgba(78, 115, 223, 0.16); }

        .btn-submit {
            width: 100%; padding: 12px 18px; background-color: var(--plum); color: #FFFFFF;
            border: none; border-radius: 10px; font-size: 14px; font-weight: 700; cursor: pointer;
            transition: all 0.16s ease; box-shadow: 0 4px 12px rgba(30, 27, 46, 0.2);
        }
        .btn-submit:hover { background-color: var(--plum-surface); transform: translateY(-1px); }

        @media (max-width: 820px) {
            body { padding: 10px; align-items: flex-start; }
            .auth-wrapper { grid-template-columns: minmax(0, 1fr); border-radius: 16px; }
            .auth-showcase { padding: 18px 18px 14px; gap: 10px; }
            .showcase-headline { font-size: 20px; margin-bottom: 8px; }
            .showcase-desc { margin-bottom: 0; font-size: 13px; }
            .feature-pills { display: none; }
            .showcase-footer { flex-wrap: wrap; gap: 6px; padding-top: 10px; margin-top: 2px; }
            .auth-form-panel { padding: 22px 18px; }
        }
    </style>
</head>
<body>
    <div class="auth-wrapper">
        <div class="auth-showcase">
            <a href="{{ route('welcome') }}" class="brand-logo" title="Go to CediLedger Cloud Welcome Page">
                <span class="brand-mark">GH¢</span>
                <div>
                    <div class="brand-title">CediLedger</div>
                    <div class="brand-sub">Personal Expense Tracker &amp; Financial Intelligence</div>
                </div>
            </a>

            <div>
                <h1 class="showcase-headline">Recover access to your financial command center.</h1>
                <p class="showcase-desc">
                    Enter your registered email address and we will generate a secure time-limited password reset link for your CediLedger account.
                </p>

                <div class="feature-pills">
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #2D9F78;"></span>
                        <span><strong>Cryptographic Reset Token</strong> — Single-use 60-minute expiry window</span>
                    </div>
                    <div class="feature-item">
                        <span class="feature-dot" style="background-color: #E5A93C;"></span>
                        <span><strong>Zero Ledger Data Loss</strong> — Your transactions, budgets, and vaults stay intact</span>
                    </div>
                </div>
            </div>

            <div class="showcase-footer">
                <span>CediLedger Account Security</span>
                <span class="mono">SHA-256 • Bcrypt</span>
            </div>
        </div>

        <div class="auth-form-panel">
            <span class="form-kicker">Password Recovery</span>
            <h2 class="form-heading">Forgot your password?</h2>
            <p class="form-subheading">Enter the email address associated with your CediLedger account to receive a password reset link.</p>

            @if(session('success'))
                <div class="alert-box alert-success">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if(session('local_reset_url'))
                <div style="margin-bottom: 18px; padding: 14px 16px; background-color: var(--ivory); border: 1px solid var(--emerald); border-radius: 12px; font-size: 12.5px;">
                    <div style="font-weight: 700; color: var(--text-dark); margin-bottom: 6px;">
                        ⚡ Instant Reset Link (Mail Preview / Direct Recovery):
                    </div>
                    <a href="{{ session('local_reset_url') }}"
                       style="display: inline-block; background-color: var(--emerald); color: #FFFFFF; font-weight: 700; font-size: 12.5px; text-decoration: none; padding: 8px 14px; border-radius: 8px; margin-top: 2px;">
                        Open Password Reset Form →
                    </a>
                </div>
            @endif

            @if($errors->any())
                <div class="alert-box alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('password.email') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="email">Registered Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email', request('email')) }}"
                           class="form-control"
                           placeholder="name@example.com"
                           required
                           autofocus>
                </div>

                <button type="submit" class="btn-submit">Send Password Reset Link →</button>
            </form>

            <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--ivory-border); text-align: center; font-size: 13px; color: var(--text-muted); display: flex; flex-direction: column; gap: 6px;">
                <div>
                    Remembered your password? <a href="{{ route('login') }}" style="color: var(--emerald-dark); font-weight: 700; text-decoration: underline;">← Back to Sign In</a>
                </div>
                <div>
                    Need an account? <a href="{{ route('register') }}" style="font-size: 12px; color: var(--text-muted); text-decoration: underline;">Create a free account</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
