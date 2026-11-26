<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CediLedger Financial Statement ({{ $startDate }} to {{ $endDate }})</title>
    <style>
        body { font-family: 'Inter', Arial, sans-serif; color: #1E1F22; background: #FFFFFF; padding: 28px; font-size: 12px; }
        .header { background-color: #29243D; color: #F3EFE8; padding: 18px 22px; display: flex; justify-content: space-between; align-items: center; border-bottom: 4px solid #319B79; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; margin: 18px 0; }
        .summary-box { padding: 12px; border: 1.5px solid #292B30; background-color: #F3EFE8; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background-color: #292B30; color: #F3EFE8; text-align: left; padding: 8px; font-size: 11px; text-transform: uppercase; }
        td { padding: 7px 8px; border-bottom: 1px solid #D6CFC2; }
        .mono { font-family: Consolas, monospace; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 14px; display: flex; justify-content: flex-end; gap: 10px;">
        <button onclick="window.print()" style="background:#319B79;color:#FFF;border:1.5px solid #292B30;padding:8px 16px;font-weight:700;cursor:pointer;">🖨 Print / Save as PDF</button>
    </div>
    <div class="header">
        <div>
            <h1 style="margin:0;font-size:20px;">CEDILEDGER PERSONAL FINANCIAL STATEMENT</h1>
            <div style="font-size:12px;color:#E9B94E;margin-top:4px;">Account Holder: {{ $user->name }} ({{ $user->email }})</div>
        </div>
        <div style="text-align:right;">
            <div style="font-weight:700;">Reporting Period: {{ $startDate }} to {{ $endDate }}</div>
            <div style="font-size:11px;color:#B5B0C4;">Generated: {{ now()->format('d M Y H:i') }}</div>
        </div>
    </div>

    <div class="summary-grid">
        <div class="summary-box">
            <div style="font-size:10px;text-transform:uppercase;font-weight:700;">Total Recorded Income</div>
            <div class="mono" style="font-size:18px;font-weight:700;color:#319B79;">{{ $user->currency }}{{ number_format((float)$totalIncome, 2) }}</div>
        </div>
        <div class="summary-box">
            <div style="font-size:10px;text-transform:uppercase;font-weight:700;">Total Recorded Expenditure</div>
            <div class="mono" style="font-size:18px;font-weight:700;color:#E66D55;">{{ $user->currency }}{{ number_format((float)$totalExpenses, 2) }}</div>
        </div>
        <div class="summary-box">
            <div style="font-size:10px;text-transform:uppercase;font-weight:700;">Net Cash Flow Position</div>
            <div class="mono" style="font-size:18px;font-weight:700;color:#29243D;">{{ $user->currency }}{{ number_format((float)$netBalance, 2) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Reference</th>
                <th>Description</th>
                <th>Category</th>
                <th>Payment Method</th>
                <th>Type</th>
                <th style="text-align:right;">Amount ({{ $user->currency }})</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $tx)
                <tr>
                    <td class="mono">{{ $tx->transaction_date->format('Y-m-d') }}</td>
                    <td class="mono">{{ $tx->reference_code }}</td>
                    <td><strong>{{ $tx->title }}</strong></td>
                    <td>{{ $tx->category?->name ?? 'General' }}</td>
                    <td>{{ $tx->paymentMethod?->name ?? '—' }}</td>
                    <td class="mono">{{ strtoupper($tx->type) }}</td>
                    <td class="mono" style="text-align:right;font-weight:700;">{{ number_format((float)$tx->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
