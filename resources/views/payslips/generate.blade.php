<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - {{ $payroll->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }
        .payslip-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            max-width: 800px;
            margin: 0 auto;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
        }
        .payslip-title {
            font-size: 18px;
            color: #666;
        }
        .payslip-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
        }
        .info-value {
            color: #6c757d;
            margin-bottom: 10px;
        }
        .earnings-deductions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
            margin-bottom: 30px;
        }
        .earnings, .deductions {
            background: white;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .earnings h3, .deductions h3 {
            background: #007bff;
            color: white;
            margin: 0;
            padding: 15px;
            text-align: center;
        }
        .deductions h3 {
            background: #dc3545;
        }
        .amount-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
        }
        .amount-row:last-child {
            border-bottom: none;
            font-weight: bold;
            background: #f8f9fa;
        }
        .net-pay {
            text-align: center;
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 5px;
            font-size: 20px;
            font-weight: bold;
        }
        .print-btn {
            text-align: center;
            margin: 20px 0;
        }
        .btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn:hover {
            background: #0056b3;
        }
        @media print {
            .print-btn { display: none; }
            body { background: white; }
            .payslip-container { box-shadow: none; }
        }
    </style>
</head>
<body>
    <div class="payslip-container">
        <div class="header">
            <div class="company-name">Your Company Name</div>
            <div class="payslip-title">Employee Payslip</div>
        </div>

        <div class="payslip-info">
            <div class="info-group">
                <div class="info-label">Payslip ID:</div>
                <div class="info-value">{{ $payroll->id }}</div>
                
                <div class="info-label">Employee ID:</div>
                <div class="info-value">{{ $payroll->employee_id ?? 'N/A' }}</div>
                
                <div class="info-label">Pay Period:</div>
                <div class="info-value">{{ $payroll->pay_period ?? 'N/A' }}</div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Generated On:</div>
                <div class="info-value">{{ now()->format('d M Y, h:i A') }}</div>
                
                <div class="info-label">Status:</div>
                <div class="info-value">Generated</div>
                
                <div class="info-label">Created:</div>
                <div class="info-value">{{ $payroll->created_at->format('d M Y') }}</div>
            </div>
        </div>

        <div class="earnings-deductions">
            <div class="earnings">
                <h3>Earnings</h3>
                <div class="amount-row">
                    <span>Basic Salary</span>
                    <span>₹{{ number_format($payroll->salary_amount ?? 0, 2) }}</span>
                </div>
                <div class="amount-row">
                    <span>Total Earnings</span>
                    <span>₹{{ number_format($payroll->salary_amount ?? 0, 2) }}</span>
                </div>
            </div>
            
            <div class="deductions">
                <h3>Deductions</h3>
                <div class="amount-row">
                    <span>Tax Deduction</span>
                    <span>₹0.00</span>
                </div>
                <div class="amount-row">
                    <span>Total Deductions</span>
                    <span>₹0.00</span>
                </div>
            </div>
        </div>

        <div class="net-pay">
            Net Pay: ₹{{ number_format($payroll->salary_amount ?? 0, 2) }}
        </div>

        <div class="print-btn">
            <button class="btn" onclick="window.print()">Print Payslip</button>
            <button class="btn" onclick="window.close()" style="background: #6c757d;">Close</button>
        </div>
    </div>
</body>
</html>