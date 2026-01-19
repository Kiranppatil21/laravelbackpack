<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Payslip - {{ $payslip->id }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #222; }
        .header { text-align: center; margin-bottom: 20px; }
        .section { margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border: 1px solid #ddd; text-align: left; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Company Name</h1>
        <div>Payslip for period {{ $payslip->period_start }} to {{ $payslip->period_end }}</div>
    </div>

    <div class="section">
        <strong>Employee:</strong> {{ optional($payslip->employee)->name ?? 'N/A' }}<br>
        <strong>Payslip ID:</strong> {{ $payslip->id }}
    </div>

    <div class="section">
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Base Salary</td>
                    <td class="right">{{ number_format($payslip->breakdown['base'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Allowances</td>
                    <td class="right">{{ number_format($payslip->breakdown['allowances'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Gross</td>
                    <td class="right">{{ number_format($payslip->gross, 2) }}</td>
                </tr>
                <tr>
                    <td>Tax</td>
                    <td class="right">-{{ number_format($payslip->breakdown['tax'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>Deductions</td>
                    <td class="right">-{{ number_format($payslip->breakdown['deductions'] ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <th>Net Pay</th>
                    <th class="right">{{ number_format($payslip->net, 2) }}</th>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="section">
        <small>Generated on {{ now()->toDateTimeString() }}</small>
    </div>
</body>
</html>
