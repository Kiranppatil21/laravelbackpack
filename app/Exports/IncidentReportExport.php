<?php

namespace App\Exports;

use App\Models\Incident;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class IncidentReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Incident::with(['client']);

        if (isset($this->filters['incident_type'])) {
            $query->where('incident_type', $this->filters['incident_type']);
        }

        if (isset($this->filters['severity'])) {
            $query->where('severity', $this->filters['severity']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['start_date'])) {
            $query->where('incident_date', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('incident_date', '<=', $this->filters['end_date']);
        }

        return $query->orderBy('incident_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Incident Number',
            'Client',
            'Incident Date',
            'Time',
            'Type',
            'Severity',
            'Location',
            'Description',
            'Reported By',
            'Status',
            'Action Taken',
        ];
    }

    public function map($incident): array
    {
        return [
            $incident->incident_number,
            $incident->client->name ?? 'N/A',
            $incident->incident_date->format('d-m-Y'),
            $incident->incident_time,
            ucfirst(str_replace('-', ' ', $incident->incident_type)),
            ucfirst($incident->severity),
            $incident->location,
            substr($incident->description, 0, 100) . (strlen($incident->description) > 100 ? '...' : ''),
            $incident->reported_by_name,
            ucfirst($incident->status),
            substr($incident->action_taken ?? 'Pending', 0, 100),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
