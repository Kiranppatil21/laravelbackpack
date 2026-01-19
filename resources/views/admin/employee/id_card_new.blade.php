<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Employee ID Card</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .id-card {
            width: 300px;
            height: 450px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 10px;
            padding: 20px;
            color: white;
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            margin: 0 auto;
            position: relative;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid rgba(255,255,255,0.3);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .company-subtitle {
            font-size: 12px;
            margin: 5px 0 0 0;
            opacity: 0.8;
        }
        
        .employee-photo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 20px;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            border: 3px solid rgba(255,255,255,0.3);
        }
        
        .employee-info {
            text-align: center;
        }
        
        .employee-name {
            font-size: 16px;
            font-weight: bold;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .employee-details {
            font-size: 12px;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .employee-details div {
            margin: 5px 0;
        }
        
        .emergency-contact {
            background: rgba(255,255,255,0.1);
            padding: 10px;
            border-radius: 5px;
            margin: 15px 0;
        }
        
        .emergency-title {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            opacity: 0.7;
            border-top: 1px solid rgba(255,255,255,0.3);
            padding-top: 10px;
        }
        
        .validity {
            font-size: 10px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="header">
            <div class="company-name">SecureServe</div>
            <div class="company-subtitle">Security Services</div>
        </div>
        
        <div class="employee-photo">
            👤
        </div>
        
        <div class="employee-info">
            <div class="employee-name">{{ $employee->name ?? 'N/A' }}</div>
            <div class="employee-details">
                <div><strong>ID:</strong> {{ str_pad($employee->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div><strong>Position:</strong> {{ $employee->designation ?? 'Security Officer' }}</div>
                <div><strong>Phone:</strong> {{ $employee->phone ?? 'N/A' }}</div>
                <div><strong>Agency:</strong> {{ $employee->agency->name ?? 'SecureServe' }}</div>
            </div>
            
            <div class="emergency-contact">
                <div class="emergency-title">Emergency Contact</div>
                <div>📞 +91-911-SECURE</div>
                <div>📧 emergency@secureserve.com</div>
            </div>
            
            <div class="validity">
                <strong>Issued:</strong> {{ \Carbon\Carbon::now()->format('d/m/Y') }}<br>
                <strong>Valid Until:</strong> {{ \Carbon\Carbon::now()->addYear()->format('d/m/Y') }}
            </div>
        </div>
        
        <div class="footer">
            Authorized Personnel Only
        </div>
    </div>
</body>
</html>