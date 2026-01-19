<?php

namespace App\Exports;

use App\Models\Leave;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Facades\Request;

class LeaveReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Leave::with(['employee']);

        if (isset($this->filters['start_date'])) {
            $query->where('start_date', '>=', $this->filters['start_date']);
        }

        if (isset($this->filters['end_date'])) {
            $query->where('end_date', '<=', $this->filters['end_date']);
        }

        if (isset($this->filters['leave_type'])) {
            $query->where('leave_type', $this->filters['leave_type']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Employee Code',
            'Leave Type',
            'Start Date',
            'End Date',
            'Days',
            'Reason',
            'Status',
            'Applied On',
            'Approved By',
        ];
    }

    public function map($leave): array
    {
        return [
            $leave->employee->name ?? 'N/A',
            $leave->employee->employee_code ?? 'N/A',
            ucfirst($leave->leave_type),
            $leave->start_date->format('d-m-Y'),
            $leave->end_date->format('d-m-Y'),
            $leave->days,
            $leave->reason,
            ucfirst($leave->status),
            $leave->created_at->format('d-m-Y H:i'),
            $leave->approved_by ? \App\Models\User::find($leave->approved_by)->name ?? 'N/A' : 'Pending',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
