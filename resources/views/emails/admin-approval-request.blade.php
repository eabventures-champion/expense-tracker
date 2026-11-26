<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>New SaaS User Approval Request</title>
</head>
<body style="margin: 0; padding: 28px 16px; background-color: #F4F5F8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #14161F;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="max-width: 600px; margin: 0 auto; background-color: #FFFFFF; border-radius: 16px; overflow: hidden; border: 1px solid #E5E7EF; box-shadow: 0 8px 24px rgba(20, 22, 31, 0.08);">
        <tr>
            <td style="background-color: #1E1B2E; padding: 24px 30px; color: #FFFFFF;">
                <div style="font-size: 11px; color: #5CE0B5; text-transform: uppercase; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 4px;">
                    CediLedger Cloud • SaaS Admin Alert
                </div>
                <h1 style="margin: 0; font-size: 20px; font-weight: 800; color: #FFFFFF;">
                    New User Workspace Approval Request
                </h1>
            </td>
        </tr>
        <tr>
            <td style="padding: 28px 30px;">
                <p style="margin: 0 0 18px; font-size: 14px; line-height: 1.6; color: #4A4F5E;">
                    A new user has registered on <strong>CediLedger Cloud</strong> and is awaiting administrator approval before their financial workspace is provisioned.
                </p>

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color: #F8F9FB; border: 1px solid #E5E7EF; border-radius: 12px; padding: 16px 20px; margin-bottom: 24px;">
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #646A7A; width: 150px;"><strong>Full Name:</strong></td>
                        <td style="padding: 6px 0; font-size: 13.5px; font-weight: 700; color: #14161F;">{{ $registeredUser->name }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #646A7A;"><strong>Email Address:</strong></td>
                        <td style="padding: 6px 0; font-size: 13.5px; font-family: monospace; color: #14161F;">{{ $registeredUser->email }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #646A7A;"><strong>Default Currency:</strong></td>
                        <td style="padding: 6px 0; font-size: 13.5px; font-family: monospace; font-weight: 700; color: #2D9F78;">{{ $registeredUser->currency }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #646A7A;"><strong>Monthly Budget Target:</strong></td>
                        <td style="padding: 6px 0; font-size: 13.5px; font-family: monospace; color: #14161F;">{{ $registeredUser->currency }}{{ number_format((float) $registeredUser->overall_monthly_budget, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="padding: 6px 0; font-size: 13px; color: #646A7A;"><strong>Requested At:</strong></td>
                        <td style="padding: 6px 0; font-size: 12.5px; font-family: monospace; color: #646A7A;">{{ $registeredUser->created_at?->format('d M Y, H:i T') ?? now()->format('d M Y, H:i') }}</td>
                    </tr>
                </table>

                <p style="margin: 0 0 16px; font-size: 13.5px; line-height: 1.55; color: #4A4F5E;">
                    Click <strong>Approve & Setup Workspace</strong> below to immediately activate this user's account, provision their guided-tour sample data, and send them a welcome email:
                </p>

                <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="margin-bottom: 22px;">
                    <tr>
                        <td align="left" style="padding-right: 10px;">
                            <a href="{{ $approveUrl }}" style="display: inline-block; background-color: #2D9F78; color: #FFFFFF; font-size: 14px; font-weight: 700; text-decoration: none; padding: 12px 22px; border-radius: 10px;">
                                ✓ Approve &amp; Setup Workspace
                            </a>
                        </td>
                        <td align="left">
                            <a href="{{ $rejectUrl }}" style="display: inline-block; background-color: #FDF6F4; color: #D05A42; border: 1px solid #E66D55; font-size: 13.5px; font-weight: 700; text-decoration: none; padding: 11px 18px; border-radius: 10px;">
                                ✕ Reject Request
                            </a>
                        </td>
                    </tr>
                </table>

                <div style="border-top: 1px solid #E5E7EF; padding-top: 16px; font-size: 12px; color: #646A7A; line-height: 1.5;">
                    You can also review and manage all users from the <a href="{{ $adminConsoleUrl }}" style="color: #1E1B2E; font-weight: 700;">SaaS User Approvals Console</a>.
                </div>
            </td>
        </tr>
    </table>
</body>
</html>
