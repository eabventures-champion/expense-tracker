<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CediLedger Account Status Update</title>
</head>
<body style="margin: 0; padding: 28px 16px; background-color: #F4F5F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #14161F;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; border: 1px solid #E5E7EF; box-shadow: 0 8px 24px rgba(20, 22, 31, 0.08);">
        <tr>
            <td style="background-color: #1E1B2E; padding: 24px 30px; color: #FFFFFF;">
                <div style="font-size: 11px; color: {{ $status === 'approved' ? '#5CE0B5' : '#FF8A73' }}; text-transform: uppercase; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 4px;">
                    CediLedger Cloud • Account Notification
                </div>
                <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">
                    @if($status === 'approved')
                        Your Financial Workspace Is Ready!
                    @else
                        Update on Your CediLedger Account Request
                    @endif
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 28px 30px;">
                <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6; color: #14161F;">
                    Hello <strong>{{ $user->name }}</strong>,
                </p>

                @if($status === 'approved')
                    <p style="margin: 0 0 18px; font-size: 14px; line-height: 1.6; color: #4A4F5E;">
                        Great news! The platform administrator has approved your CediLedger Cloud account (<span style="font-family: monospace;">{{ $user->email }}</span>) and provisioned your private financial workspace.
                    </p>

                    <div style="background-color: #F4FBF8; border: 1px solid rgba(45, 159, 120, 0.3); border-radius: 12px; padding: 16px 20px; margin-bottom: 22px; font-size: 13px; color: #14161F; line-height: 1.6;">
                        <strong>How to get started:</strong><br>
                        1. <strong>Sign In &amp; Take the Guided Tour:</strong> Your workspace comes pre-loaded with sample transactions so you can explore the dashboard, period explorer, charts, budgets, and savings vaults.<br>
                        2. <strong>Reset &amp; Start Fresh:</strong> At the end of the tour, click <em>"Reset Database &amp; Start Fresh"</em> to clear the sample data and begin recording your own finances—while keeping your profile details intact.
                    </div>

                    <div style="margin-bottom: 20px;">
                        <a href="{{ $loginUrl }}" style="display: inline-block; background-color: #2D9F78; color: #FFFFFF; font-size: 14px; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 10px;">
                            Sign In &amp; Launch Guided Tour →
                        </a>
                    </div>
                @else
                    <p style="margin: 0 0 18px; font-size: 14px; line-height: 1.6; color: #4A4F5E;">
                        Your account registration request for <strong>{{ $user->email }}</strong> could not be approved at this time. Please contact the platform administrator if you believe this was in error.
                    </p>
                @endif
            </td>
        </tr>
    </table>
</body>
</html>
