<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Card - {{ $employee->name ?? 'Unknown' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: #f0f0f0;
            padding: 20px;
        }

        .id-card {
            width: 3.375in; /* 85.7mm */
            height: 5.375in; /* 136.5mm */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            margin: 0 auto;
            page-break-after: always;
        }

        .id-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 60px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
        }

        .header {
            text-align: center;
            padding: 15px;
            color: white;
            position: relative;
            z-index: 2;
        }

        .company-logo {
            width: 50px;
            height: 50px;
            background: white;
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            font-weight: bold;
            color: #667eea;
        }

        .company-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .id-badge {
            background: white;
            margin: 10px 15px;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .employee-photo {
            width: 100px;
            height: 120px;
            background: #f0f0f0;
            border: 3px solid #667eea;
            border-radius: 8px;
            margin: 0 auto 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #666;
            overflow: hidden;
        }

        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .employee-info {
            text-align: center;
        }

        .employee-name {
            font-size: 16px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
            text-transform: uppercase;
        }

        .employee-id {
            font-size: 14px;
            color: #667eea;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .employee-designation {
            font-size: 12px;
            color: #666;
            background: #f8f9fa;
            padding: 4px 8px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 10px;
        }

        .details-grid {
            margin-top: 15px;
            font-size: 10px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
            padding-bottom: 4px;
            border-bottom: 1px dotted #ddd;
        }

        .detail-label {
            font-weight: bold;
            color: #666;
        }

        .detail-value {
            color: #333;
            text-align: right;
            max-width: 60%;
            word-wrap: break-word;
        }

        .validity {
            background: linear-gradient(90deg, #667eea, #764ba2);
            color: white;
            text-align: center;
            padding: 8px;
            margin: 15px -20px -20px;
            font-size: 10px;
            font-weight: bold;
        }

        .emergency-contact {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 8px;
            margin-top: 10px;
            font-size: 9px;
        }

        .emergency-title {
            font-weight: bold;
            color: #856404;
            margin-bottom: 4px;
        }

        .qr-code {
            position: absolute;
            bottom: 10px;
            right: 10px;
            width: 40px;
            height: 40px;
            background: white;
            border: 2px solid #667eea;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            color: #667eea;
        }

        .security-strip {
            position: absolute;
            left: 0;
            top: 50%;
            width: 5px;
            height: 60%;
            background: linear-gradient(180deg, #ff6b6b, #feca57, #48dbfb, #ff9ff3);
            transform: translateY(-50%);
        }

        @media print {
            body {
                background: white;
                padding: 0;
            }
            
            .id-card {
                box-shadow: none;
                page-break-after: always;
            }
        }
    </style>
</head>
<body>
    <div class="id-card">
        <div class="security-strip"></div>
        
        <div class="header">
            <div class="company-logo">
                SS
            </div>
            <div class="company-name">SecureServe</div>
            <div style="font-size: 12px; opacity: 0.9;">Security Services</div>
        </div>

        <div class="id-badge">
            <div class="employee-photo">
                @if(isset($employee->profile_photo_path) && $employee->profile_photo_path)
                    <img src="{{ asset('storage/' . $employee->profile_photo_path) }}" alt="Employee Photo">
                @else
                    <div style="text-align: center; color: #999;">
                        <div style="font-size: 24px; margin-bottom: 5px;">👤</div>
                        <div>No Photo</div>
                    </div>
                @endif
            </div>

            <div class="employee-info">
                <div class="employee-name">{{ $employee->name ?? ($employee->first_name . ' ' . $employee->last_name) ?? 'Unknown Employee' }}</div>
                <div class="employee-id">ID: EMP{{ str_pad($employee->id ?? 0, 4, '0', STR_PAD_LEFT) }}</div>
                <div class="employee-designation">
                    {{ $employee->designation ?? $employee->job_role ?? $employee->position ?? 'Security Guard' }}
                </div>
            </div>

            <div class="details-grid">
                @if(isset($employee->father_name) && $employee->father_name)
                <div class="detail-row">
                    <span class="detail-label">Father:</span>
                    <span class="detail-value">{{ $employee->father_name }}</span>
                </div>
                @endif

                @if(isset($employee->phone) && $employee->phone)
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value">{{ $employee->phone }}</span>
                </div>
                @endif

                @if(isset($employee->blood_group) && $employee->blood_group)
                <div class="detail-row">
                    <span class="detail-label">Blood Group:</span>
                    <span class="detail-value">{{ $employee->blood_group }}</span>
                </div>
                @endif

                @if(isset($employee->hired_at) && $employee->hired_at)
                <div class="detail-row">
                    <span class="detail-label">Joined:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($employee->hired_at)->format('d/m/Y') }}</span>
                </div>
                @elseif(isset($employee->created_at) && $employee->created_at)
                <div class="detail-row">
                    <span class="detail-label">Joined:</span>
                    <span class="detail-value">{{ \Carbon\Carbon::parse($employee->created_at)->format('d/m/Y') }}</span>
                </div>
                @endif

                @if(isset($employee->client_id) && $employee->client_id)
                <div class="detail-row">
                    <span class="detail-label">Client:</span>
                    <span class="detail-value">{{ $employee->client_id }}</span>
                </div>
                @endif

                @if(isset($employee->email) && $employee->email)
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value" style="font-size: 9px;">{{ $employee->email }}</span>
                </div>
                @endif
            </div>

            @if((isset($employee->emergency_contact_name) && $employee->emergency_contact_name) || (isset($employee->emergency_contact_phone) && $employee->emergency_contact_phone))
            <div class="emergency-contact">
                <div class="emergency-title">Emergency Contact</div>
                @if(isset($employee->emergency_contact_name) && $employee->emergency_contact_name)
                    <div>{{ $employee->emergency_contact_name }}</div>
                @endif
                @if(isset($employee->emergency_contact_phone) && $employee->emergency_contact_phone)
                    <div>{{ $employee->emergency_contact_phone }}</div>
                @endif
            </div>
            @endif
        </div>

        <div class="validity">
            Valid Until: {{ \Carbon\Carbon::now()->addYear()->format('M Y') }} | 
            Authorized Personnel Only
        </div>

        <div class="qr-code">
            QR
        </div>
    </div>
</body>
</html>