<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reset Your CediLedger Password</title>
</head>
<body style="margin: 0; padding: 28px 16px; background-color: #F4F5F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #14161F;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; border: 1px solid #E5E7EF; box-shadow: 0 8px 24px rgba(20, 22, 31, 0.08);">
        <tr>
            <td style="background-color: #1E1B2E; padding: 24px 30px; color: #FFFFFF;">
                <div style="font-size: 11px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 4px;">
                    CediLedger Cloud • Security &amp; Access Recovery
                </div>
                <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">
                    Password Reset Request
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 28px 30px;">
                <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6; color: #14161F;">
                    Hello <strong>{{ $user->name }}</strong>,
                </p>

                <p style="margin: 0 0 18px; font-size: 14px; line-height: 1.6; color: #4A4F5E;">
                    We received a request to reset the password for your CediLedger account (<span style="font-family: monospace;">{{ $user->email }}</span>). Click the button below to choose a new password:
                </p>

                <div style="margin-bottom: 22px;">
                    <a href="{{ $resetUrl }}" style="display: inline-block; background-color: #2D9F78; color: #FFFFFF; font-size: 14px; font-weight: 700; text-decoration: none; padding: 12px 24px; border-radius: 10px;">
                        Reset My Password →
                    </a>
                </div>

                <div style="background-color: #F4F5F8; border: 1px solid #E5E7EF; border-radius: 10px; padding: 14px 16px; margin-bottom: 18px; font-size: 12.5px; color: #4A4F5E; line-height: 1.5;">
                    <strong>Security Notice:</strong> This password reset link is valid for <strong>60 minutes</strong>. If you did not request a password reset, no further action is required and your password will remain unchanged.
                </div>

                <p style="margin: 0; font-size: 11.5px; color: #646A7A; word-break: break-all;">
                    If the button above doesn't work, copy and paste this URL into your browser:<br>
                    <a href="{{ $resetUrl }}" style="color: #2D9F78;">{{ $resetUrl }}</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
