<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ID Card - {{ $employee->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        
        .id-card {
            width: 350px;
            height: 550px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0 auto;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        
        .card-content {
            background: white;
            height: 100%;
            border-radius: 10px;
            padding: 20px;
            position: relative;
        }
        
        .header-section {
            text-align: center;
            border-bottom: 2px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .logo-circle {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 32px;
            font-weight: bold;
        }
        
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .company-subtitle {
            font-size: 12px;
            color: #666;
        }
        
        .photo-section {
            text-align: center;
            margin: 20px 0;
        }
        
        .photo-frame {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            margin: 0 auto;
            border: 4px solid #667eea;
            background: #f0f0f0;
        }
        
        .photo-frame img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            color: #999;
        }
        
        .employee-info {
            text-align: center;
            margin: 20px 0;
        }
        
        .employee-name {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .employee-id {
            font-size: 14px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .employee-designation {
            font-size: 13px;
            color: #666;
            margin-bottom: 15px;
        }
        
        .contact-info {
            text-align: left;
            margin: 20px 0;
        }
        
        .contact-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            font-size: 12px;
            color: #555;
        }
        
        .contact-item svg {
            width: 16px;
            height: 16px;
            margin-right: 10px;
            color: #667eea;
        }
        
        .security-section {
            text-align: center;
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px dashed #ddd;
        }
        
        .barcode-lines {
            display: flex;
            justify-content: center;
            gap: 2px;
            margin-bottom: 10px;
        }
        
        .barcode-lines span {
            width: 3px;
            height: 40px;
            background: #333;
            display: inline-block;
        }
        
        .barcode-lines span:nth-child(even) {
            width: 2px;
        }
        
        .validity {
            font-size: 11px;
            color: #666;
            margin-top: 10px;
        }
        
        .footer-section {
            position: absolute;
            bottom: 15px;
            left: 20px;
            right: 20px;
            text-align: center;
            font-size: 10px;
            color: #999;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="card-content">
            <!-- Header -->
            <div class="header-section">
                <div class="logo-circle">S</div>
                <div class="company-name">SecureServe</div>
                <div class="company-subtitle">Security Services</div>
            </div>
            
            <!-- Photo -->
            <div class="photo-section">
                <div class="photo-frame">
                    @if($employee->photo_path && file_exists(storage_path('app/public/'.$employee->photo_path)))
                        <img src="{{ storage_path('app/public/'.$employee->photo_path) }}" alt="Photo">
                    @else
                        <div class="photo-placeholder">👤</div>
                    @endif
                </div>
            </div>
            
            <!-- Employee Info -->
            <div class="employee-info">
                <div class="employee-name">{{ strtoupper($employee->name ?? 'N/A') }}</div>
                <div class="employee-id">ID: {{ str_pad($employee->id, 6, '0', STR_PAD_LEFT) }}</div>
                <div class="employee-designation">{{ $employee->designation ?? 'Security Officer' }}</div>
            </div>
            
            <!-- Contact Info -->
            <div class="contact-info">
                <div class="contact-item">
                    <span>📱</span> {{ $employee->phone ?? 'N/A' }}
                </div>
                <div class="contact-item">
                    <span>🏢</span> {{ $employee->agency->name ?? 'SecureServe Agency' }}
                </div>
            </div>
            
            <!-- Security -->
            <div class="security-section">
                <div class="barcode-lines">
                    <span></span><span></span><span></span><span></span><span></span>
                    <span></span><span></span><span></span><span></span><span></span>
                </div>
                <div class="validity">Valid Until: {{ \Carbon\Carbon::now()->addYear()->format('M Y') }}</div>
            </div>
            
            <!-- Footer -->
            <div class="footer-section">
                Authorized Personnel Only • Emergency: 911
            </div>
        </div>
    </div>
</body>
</html>
