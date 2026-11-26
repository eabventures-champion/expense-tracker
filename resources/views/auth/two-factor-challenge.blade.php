<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication — CediLedger</title>
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
            --ivory: #F4F5F8;
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

        .challenge-card {
            width: 100%;
            max-width: 460px;
            background-color: #FFFFFF;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .challenge-header {
            background-color: var(--plum);
            color: #FFFFFF;
            padding: 26px 32px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .shield-badge {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background-color: var(--emerald);
            color: #FFFFFF;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 6px 16px rgba(45, 159, 120, 0.35);
        }

        .challenge-body {
            padding: 32px;
        }

        .alert-error {
            padding: 11px 14px;
            border-radius: 10px;
            font-size: 12.5px;
            font-weight: 600;
            margin-bottom: 18px;
            background-color: rgba(230, 109, 85, 0.12);
            color: var(--coral-dark);
            border: 1px solid rgba(230, 109, 85, 0.3);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 18px;
        }

        .form-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-dark);
        }

        .form-control {
            width: 100%;
            padding: 13px 16px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: 0.18em;
            text-align: center;
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
        }

        .btn-submit:hover {
            background-color: var(--plum-surface);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="challenge-card">
        <div class="challenge-header">
            <div class="shield-badge">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
            </div>
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">Two-Factor Security</div>
                <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 19px; font-weight: 800;">Verify Your Identity</h1>
            </div>
        </div>

        <div class="challenge-body">
            <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.55; margin-bottom: 20px;">
                Enter the 6-digit verification code from your authenticator app (or one of your backup recovery codes) for <strong>{{ $user->email }}</strong>.
            </p>

            @if($errors->any())
                <div class="alert-error">
                    @foreach($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('two-factor.verify') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label" for="code">6-Digit Authenticator Code or Recovery Code</label>
                    <input type="text" id="code" name="code" class="form-control mono" placeholder="000000" autocomplete="one-time-code" required autofocus>
                </div>

                <button type="submit" class="btn-submit">Verify & Sign In →</button>
            </form>

            @if(app()->isLocal() && $localLiveCode)
                <div style="margin-top: 18px; padding: 12px 14px; background-color: var(--ivory); border: 1px solid var(--ivory-border); border-radius: 10px; display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
                    <div>
                        <span style="color: var(--text-muted);">Local Dev Live TOTP:</span>
                        <strong class="mono" style="color: var(--text-dark); margin-left: 6px; font-size: 14px;">{{ $localLiveCode }}</strong>
                    </div>
                    <button type="button" onclick="document.getElementById('code').value='{{ $localLiveCode }}'" style="background-color: var(--emerald); color: #FFF; border: none; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 600; cursor: pointer;">
                        Fill Code
                    </button>
                </div>
            @endif

            <div style="margin-top: 18px; text-align: center;">
                <a href="{{ route('login') }}" style="font-size: 12.5px; color: var(--text-muted); text-decoration: underline;">← Return to Sign In</a>
            </div>
        </div>
    </div>
</body>
</html>
