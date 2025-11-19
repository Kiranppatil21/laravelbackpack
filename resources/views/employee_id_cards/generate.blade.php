<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee ID Card - {{ $employee->first_name }} {{ $employee->last_name }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .id-card-container {
            perspective: 1000px;
            margin: 20px;
        }

        .id-card {
            width: 350px;
            height: 550px;
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1), 0 0 0 1px rgba(255,255,255,0.1);
            overflow: hidden;
            position: relative;
            transform-style: preserve-3d;
            transition: transform 0.6s;
        }

        .id-card:hover {
            transform: rotateY(5deg) rotateX(5deg);
        }

        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 120px;
            position: relative;
            overflow: hidden;
        }

        .card-header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent 40%, rgba(255,255,255,0.1) 50%, transparent 60%);
            animation: shine 3s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%) translateY(-100%) rotate(45deg); }
            100% { transform: translateX(100%) translateY(100%) rotate(45deg); }
        }

        .company-logo {
            position: absolute;
            top: 15px;
            left: 20px;
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
            font-weight: bold;
            border: 2px solid rgba(255,255,255,0.3);
        }

        .company-name {
            position: absolute;
            top: 25px;
            right: 20px;
            color: white;
            font-size: 16px;
            font-weight: bold;
            text-shadow: 0 1px 3px rgba(0,0,0,0.3);
        }

        .id-text {
            position: absolute;
            bottom: 15px;
            left: 20px;
            color: rgba(255,255,255,0.9);
            font-size: 14px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .employee-photo {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 6px solid white;
            position: absolute;
            top: 60px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #667eea;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            z-index: 10;
            overflow: hidden;
        }

        .employee-photo img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .employee-info {
            margin-top: 80px;
            padding: 20px 30px;
            text-align: center;
        }

        .employee-name {
            font-size: 22px;
            font-weight: bold;
            color: #2d3748;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .employee-position {
            font-size: 14px;
            color: #667eea;
            margin-bottom: 20px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .employee-details {
            background: #f8fafc;
            border-radius: 12px;
            padding: 15px;
            margin: 20px 0;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-size: 12px;
            color: #718096;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        .detail-value {
            font-size: 14px;
            color: #2d3748;
            font-weight: 500;
        }

        .qr-code {
            width: 80px;
            height: 80px;
            background: #f0f0f0;
            border-radius: 8px;
            margin: 15px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #666;
            font-size: 10px;
            text-align: center;
            border: 2px dashed #ccc;
        }

        .security-strip {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 8px;
            background: linear-gradient(90deg, #667eea, #764ba2, #667eea);
            background-size: 200% 100%;
            animation: gradientMove 2s ease infinite;
        }

        @keyframes gradientMove {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .valid-until {
            position: absolute;
            bottom: 15px;
            right: 20px;
            font-size: 10px;
            color: #a0aec0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .print-controls {
            position: fixed;
            top: 20px;
            right: 20px;
            display: flex;
            gap: 10px;
            z-index: 100;
        }

        .btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            box-shadow: 0 4px 6px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #718096;
            color: white;
            box-shadow: 0 4px 6px rgba(113, 128, 150, 0.4);
        }

        .btn-secondary:hover {
            background: #4a5568;
            transform: translateY(-2px);
            box-shadow: 0 8px 12px rgba(113, 128, 150, 0.4);
        }

        @media print {
            body {
                background: white;
                padding: 0;
                display: block;
            }
            
            .print-controls {
                display: none;
            }
            
            .id-card-container {
                margin: 0;
                page-break-inside: avoid;
            }
            
            .id-card {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
                margin: 0 auto;
            }
        }

        /* Accessibility */
        @media (prefers-reduced-motion: reduce) {
            .id-card {
                transition: none;
            }
            
            .card-header::before,
            .security-strip {
                animation: none;
            }
        }
    </style>
</head>
<body>
    <div class="print-controls">
        <button class="btn btn-primary" onclick="window.print()">
            <span>🖨️</span> Print ID Card
        </button>
        <button class="btn btn-secondary" onclick="window.close()">
            <span>✕</span> Close
        </button>
    </div>

    <div class="id-card-container">
        <div class="id-card">
            <div class="card-header">
                <div class="company-logo">
                    {{ strtoupper(substr($employee->first_name ?? 'C', 0, 1)) }}
                </div>
                <div class="company-name">
                    Your Company
                </div>
                <div class="id-text">
                    Employee ID Card
                </div>
            </div>

            <div class="employee-photo">
                @if(isset($employee->photo) && $employee->photo)
                    <img src="{{ $employee->photo }}" alt="Employee Photo">
                @else
                    {{ strtoupper(substr($employee->first_name ?? 'E', 0, 1) . substr($employee->last_name ?? 'M', 0, 1)) }}
                @endif
            </div>

            <div class="employee-info">
                <div class="employee-name">
                    {{ $employee->first_name ?? 'N/A' }} {{ $employee->last_name ?? '' }}
                </div>
                <div class="employee-position">
                    {{ $employee->job_title ?? $employee->position ?? 'Employee' }}
                </div>

                <div class="employee-details">
                    <div class="detail-row">
                        <span class="detail-label">Employee ID</span>
                        <span class="detail-value">#{{ $employee->id ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Department</span>
                        <span class="detail-value">{{ $employee->department ?? 'General' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Phone</span>
                        <span class="detail-value">{{ $employee->mobile ?? $employee->phone ?? 'N/A' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Joined</span>
                        <span class="detail-value">{{ $employee->created_at ? $employee->created_at->format('M Y') : 'N/A' }}</span>
                    </div>
                    @if(isset($employee->emergency_contact))
                    <div class="detail-row">
                        <span class="detail-label">Emergency</span>
                        <span class="detail-value">{{ $employee->emergency_contact }}</span>
                    </div>
                    @endif
                </div>

                <div class="qr-code">
                    QR Code
                    <br>
                    <small>{{ $employee->id ?? 'N/A' }}</small>
                </div>
            </div>

            <div class="security-strip"></div>
            
            <div class="valid-until">
                Valid until {{ now()->addYear()->format('M Y') }}
            </div>
        </div>
    </div>

    <script>
        // Add some interactive features
        document.addEventListener('DOMContentLoaded', function() {
            const card = document.querySelector('.id-card');
            
            // Tilt effect on mouse move
            document.addEventListener('mousemove', function(e) {
                if (window.matchMedia('(prefers-reduced-motion: no-preference)').matches) {
                    const rect = card.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    
                    const centerX = rect.width / 2;
                    const centerY = rect.height / 2;
                    
                    const rotateX = ((y - centerY) / centerY) * 5;
                    const rotateY = ((centerX - x) / centerX) * 5;
                    
                    card.style.transform = `rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
                }
            });
            
            // Reset on mouse leave
            document.addEventListener('mouseleave', function() {
                card.style.transform = 'rotateX(0deg) rotateY(0deg)';
            });
            
            // Print shortcut
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                    e.preventDefault();
                    window.print();
                }
            });
        });

        // Print optimization
        window.addEventListener('beforeprint', function() {
            document.querySelector('.id-card').style.transform = 'none';
        });
    </script>
</body>
</html>