<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $heading }} — CediLedger SaaS Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600;700&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #151321;
            color: #14161F;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            margin: 0;
        }
        .card {
            width: 100%;
            max-width: 500px;
            background-color: #FFFFFF;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.55);
        }
        .card-header {
            background-color: #1E1B2E;
            color: #FFFFFF;
            padding: 26px 30px;
        }
        .card-body {
            padding: 30px;
        }
        .btn {
            display: inline-block;
            background-color: #1E1B2E;
            color: #FFFFFF;
            text-decoration: none;
            padding: 11px 20px;
            border-radius: 10px;
            font-size: 13.5px;
            font-weight: 700;
            margin-top: 14px;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <div style="font-size: 11px; color: {{ $status === 'approved' ? '#5CE0B5' : '#FF8A73' }}; text-transform: uppercase; font-weight: 700; letter-spacing: 0.08em; margin-bottom: 4px;">
                Email Approval Action Completed
            </div>
            <h1 style="margin: 0; font-family: 'Plus Jakarta Sans', sans-serif; font-size: 20px; font-weight: 800;">
                {{ $heading }}
            </h1>
        </div>
        <div class="card-body">
            <p style="margin: 0 0 16px; font-size: 14px; line-height: 1.6; color: #4A4F5E;">
                {{ $messageText }}
            </p>
            <a href="{{ route('admin.users.index') }}" class="btn">Open User Approvals Console →</a>
        </div>
    </div>
</body>
</html>
