<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending Approval — CediLedger Cloud</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --plum: #1E1B2E;
            --plum-dark: #151321;
            --plum-surface: #27233A;
            --coral: #E66D55;
            --emerald: #2D9F78;
            --mustard: #E5A93C;
            --ivory: #F4F5F8;
            --ivory-border: #E5E7EF;
            --text-dark: #14161F;
            --text-muted: #646A7A;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

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

        .approval-card {
            width: 100%;
            max-width: 520px;
            background-color: #FFFFFF;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .approval-header {
            background-color: var(--plum);
            color: #FFFFFF;
            padding: 28px 32px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .status-badge-icon {
            width: 46px;
            height: 46px;
            border-radius: 12px;
            background-color: var(--mustard);
            color: #14161F;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            font-weight: 800;
        }

        .approval-body {
            padding: 32px;
            display: flex;
            flex-direction: column;
            gap: 18px;
        }

        .step-box {
            background-color: var(--ivory);
            border: 1px solid var(--ivory-border);
            border-radius: 12px;
            padding: 16px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 13px;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            padding: 12px 18px;
            background-color: var(--plum);
            color: #FFFFFF;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            text-decoration: none;
            transition: background-color 0.15s ease;
        }

        .btn-back:hover {
            background-color: var(--plum-surface);
        }
    </style>
</head>
<body>
    <div class="approval-card">
        <div class="approval-header">
            <div class="status-badge-icon">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
            <div>
                <div style="font-size: 10.5px; color: #F5C469; text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em;">
                    @if($pendingUser && $pendingUser->approval_status === 'rejected')
                        Access Declined
                    @else
                        Awaiting Administrator Approval
                    @endif
                </div>
                <h1 style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 19px; font-weight: 800;">
                    @if($pendingUser && $pendingUser->approval_status === 'rejected')
                        Your account request was not approved
                    @else
                        Your SaaS workspace is pending setup
                    @endif
                </h1>
            </div>
        </div>

        <div class="approval-body">
            @if(session('success'))
                <div style="padding: 12px 14px; background-color: rgba(45, 159, 120, 0.12); border: 1px solid rgba(45, 159, 120, 0.3); color: #238261; border-radius: 10px; font-size: 12.5px; font-weight: 600;">
                    ✓ {{ session('success') }}
                </div>
            @endif

            @if($pendingUser)
                <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
                    Hello <strong>{{ $pendingUser->name }}</strong> (<span class="mono">{{ $pendingUser->email }}</span>), your CediLedger registration is currently in <strong style="color: var(--text-dark); text-transform: uppercase;">{{ $pendingUser->approval_status }}</strong> status.
                </p>
            @else
                <p style="font-size: 13.5px; color: var(--text-muted); line-height: 1.6;">
                    Your CediLedger account registration has been received and is awaiting review by the platform administrator before your workspace can be provisioned.
                </p>
            @endif

            <div class="step-box">
                <strong style="color: var(--text-dark); font-size: 13px;">What happens next?</strong>
                <div style="color: var(--text-muted); font-size: 12.5px; line-height: 1.55;">
                    1. <strong>Admin Approval:</strong> Once the platform administrator approves your request, your workspace is automatically provisioned with sample financial data.<br>
                    2. <strong>Interactive Guided Tour:</strong> When you sign in, you will be guided step-by-step through the dashboard, period explorer, budgets, and savings vaults.<br>
                    3. <strong>One-Click Fresh Start:</strong> After finishing the tour, you will be prompted to reset the sample ledger so you can enter your own data while keeping your profile details intact.
                </div>
            </div>

            <a href="{{ route('login') }}" class="btn-back">← Return to Sign In</a>
        </div>
    </div>
    <script>
        try {
            sessionStorage.removeItem('cediledger_register_form_state_v1');
        } catch (e) {}
    </script>
</body>
</html>
