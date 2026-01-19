<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Shift Report</title>
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
        .badge-danger { background-color: #f8d7da; color: #721c24; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
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
        <h1>Shift Report</h1>
        <p>Generated on {{ now()->format('d M Y, h:i A') }}</p>
    </div>

    <div class="stats">
        <div class="stat-item">
            <div class="stat-label">Total Shifts</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Active Shifts</div>
            <div class="stat-value">{{ $stats['active'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Inactive Shifts</div>
            <div class="stat-value">{{ $stats['inactive'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Shift Name</th>
                <th>Shift Code</th>
                <th>Start Time</th>
                <th>End Time</th>
                <th>Duration (hrs)</th>
                <th>OT After (hrs)</th>
                <th>Night Shift</th>
                <th>Night Allowance</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($shifts as $shift)
            <tr>
                <td>{{ $shift->shift_name }}</td>
                <td>{{ $shift->shift_code }}</td>
                <td>{{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}</td>
                <td>{{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}</td>
                <td>{{ $shift->duration_hours }}</td>
                <td>{{ $shift->ot_after_hours ?? 'N/A' }}</td>
                <td>
                    @if($shift->is_night_shift)
                        <span class="badge badge-info">Yes</span>
                    @else
                        <span>No</span>
                    @endif
                </td>
                <td>₹{{ number_format($shift->night_allowance, 2) }}</td>
                <td>
                    @if($shift->is_active)
                        <span class="badge badge-success">Active</span>
                    @else
                        <span class="badge badge-danger">Inactive</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center; padding: 20px;">No shift records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated report and does not require a signature.</p>
    </div>
</body>
</html>
