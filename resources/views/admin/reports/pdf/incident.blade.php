<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Incident Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            padding: 8px 6px;
            text-align: left;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        td {
            padding: 6px;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-critical { background-color: #dc3545; color: white; }
        .badge-high { background-color: #fd7e14; color: white; }
        .badge-medium { background-color: #ffc107; color: #333; }
        .badge-low { background-color: #28a745; color: white; }
        .badge-success { background-color: #d4edda; color: #155724; }
        .badge-warning { background-color: #fff3cd; color: #856404; }
        .badge-info { background-color: #d1ecf1; color: #0c5460; }
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
        <h1>Incident Report</h1>
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
            <div class="stat-label">Total Incidents</div>
            <div class="stat-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Critical</div>
            <div class="stat-value">{{ $stats['critical'] }}</div>
        </div>
        <div class="stat-item">
            <div class="stat-label">Resolved</div>
            <div class="stat-value">{{ $stats['resolved'] }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Incident #</th>
                <th>Date/Time</th>
                <th>Type</th>
                <th>Severity</th>
                <th>Client</th>
                <th>Location</th>
                <th>Status</th>
                <th>Description</th>
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $incident)
            <tr>
                <td>{{ $incident->incident_number }}</td>
                <td>{{ $incident->incident_datetime->format('d M Y H:i') }}</td>
                <td>{{ ucwords(str_replace('-', ' ', $incident->incident_type)) }}</td>
                <td>
                    @if($incident->severity == 'critical')
                        <span class="badge badge-critical">Critical</span>
                    @elseif($incident->severity == 'high')
                        <span class="badge badge-high">High</span>
                    @elseif($incident->severity == 'medium')
                        <span class="badge badge-medium">Medium</span>
                    @else
                        <span class="badge badge-low">Low</span>
                    @endif
                </td>
                <td>{{ $incident->client ? $incident->client->name : 'N/A' }}</td>
                <td>{{ $incident->location }}</td>
                <td>
                    @if($incident->status == 'resolved' || $incident->status == 'closed')
                        <span class="badge badge-success">{{ ucfirst($incident->status) }}</span>
                    @elseif($incident->status == 'investigating')
                        <span class="badge badge-warning">Investigating</span>
                    @else
                        <span class="badge badge-info">{{ ucfirst($incident->status) }}</span>
                    @endif
                </td>
                <td>{{ \Illuminate\Support\Str::limit($incident->description, 80) }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px;">No incident records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p><strong>Confidential:</strong> This is a computer-generated incident report and does not require a signature.</p>
    </div>
</body>
</html>
