<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $data['report_type'] ?? 'Farm Report' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white;
            padding: 20px;
            border-radius: 8px 8px 0 0;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 24px;
        }

        .content {
            background: #f9fafb;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-top: none;
        }

        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
        }

        .stat-label {
            font-size: 12px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #111827;
        }

        .footer {
            text-align: center;
            padding: 20px;
            color: #6b7280;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>ANIBUKID Farm Report</h1>
        <p>{{ ucfirst($report->report_type) }} Report - {{ now()->format('F j, Y') }}</p>
    </div>

    <div class="content">
        <p>Hello {{ $user->name }},</p>
        <p>Here is your {{ $report->frequency }} {{ $report->report_type }} report:</p>

        @if($report->report_type === 'financial')
            <div class="stat-card">
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value" style="color: #22c55e;">₱{{ number_format($data['total_revenue'] ?? 0, 2) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Expenses</div>
                <div class="stat-value" style="color: #ef4444;">₱{{ number_format($data['total_expenses'] ?? 0, 2) }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Net Profit</div>
                <div class="stat-value">₱{{ number_format($data['net_profit'] ?? 0, 2) }}</div>
            </div>
        @elseif($report->report_type === 'crop_yield')
            <div class="stat-card">
                <div class="stat-label">Total Harvests</div>
                <div class="stat-value">{{ $data['total_harvests'] ?? 0 }}</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Total Yield</div>
                <div class="stat-value">{{ number_format($data['total_yield'] ?? 0) }} kg</div>
            </div>
        @else
            <div class="stat-card">
                <p>{{ $data['message'] ?? 'Report data not available' }}</p>
            </div>
        @endif

        <p style="margin-top: 20px;">
            <small>Report period: Last {{ $data['period_days'] ?? 30 }} days</small>
        </p>
    </div>

    <div class="footer">
        <p>This is an automated report from ANIBUKID Farm Management System.</p>
        <p>To manage your scheduled reports, please log in to your dashboard.</p>
    </div>
</body>

</html>