<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Leave Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        .stats {
            display: table;
            width: 100%;
            margin-bottom: 20px;
            border: 1px solid #ddd;
        }
        .stat-item {
            display: table-cell;
            padding: 10px;
            text-align: center;
            border-right: 1px solid #ddd;
        }
        .stat-item:last-child {
            border-right: none;
        }
        .stat-label {
            font-weight: bold;
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
        }
        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
            padding: 8px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        td {
            padding: 6px 8px;
            border: 1px solid #ddd;
            font-size: 10px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-secondary { background-color: #e2e3e5; color: #383d41; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Leave Report</h1>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
        @if(isset($filters['start_date']) || isset($filters['end_date']))
        <p>
            Period: 
            {{ isset($filters['start_date']) ? \Carbon\Carbon::parse($filters['start_date'])->format('d M Y') : 'Start' }}
            to 
            {{ isset($filters['end_date']) ? \Carbon\Carbon::parse($filters['end_date'])->format('d M Y') : 'End' }}
        </p>
        @endif
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-label">Total Leaves</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Approved</div>
            <div class="stat-value">{{ $stats['approved'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Pending</div>
            <div class="stat-value">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Rejected</div>
            <div class="stat-value">{{ $stats['rejected'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Total Days</div>
            <div class="stat-value">{{ number_format($stats['total_days'], 1) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Employee</th>
                <th>Leave Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Days</th>
                <th>Status</th>
                <th>Reason</th>
            </tr>
        </thead>
        <tbody>
            @forelse($leaves as $leave)
            <tr>
                <td>{{ $leave->employee ? $leave->employee->first_name . ' ' . $leave->employee->last_name : 'N/A' }}</td>
                <td>{{ ucfirst($leave->leave_type) }}</td>
                <td>{{ $leave->start_date->format('d M Y') }}</td>
                <td>{{ $leave->end_date->format('d M Y') }}</td>
                <td>{{ $leave->days }}</td>
                <td>
                    @if($leave->status == 'approved')
                        <span class="badge badge-success">Approved</span>
                    @elseif($leave->status == 'pending')
                        <span class="badge badge-warning">Pending</span>
                    @elseif($leave->status == 'rejected')
                        <span class="badge badge-danger">Rejected</span>
                    @else
                        <span class="badge badge-secondary">{{ ucfirst($leave->status) }}</span>
                    @endif
                </td>
                <td>{{ $leave->reason }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; padding: 20px;">No leave records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated report and does not require a signature.</p>
    </div>
</body>
</html>
