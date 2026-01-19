<?php

namespace App\Exports;

use App\Models\Shift;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ShiftReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Shift::query();

        if (isset($this->filters['shift_type'])) {
            $query->where('shift_type', $this->filters['shift_type']);
        }

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        return $query->orderBy('shift_name')->get();
    }

    public function headings(): array
    {
        return [
            'Shift Name',
            'Shift Code',
            'Start Time',
            'End Time',
            'Break Duration (mins)',
            'Working Hours',
            'Shift Type',
            'Days of Week',
            'Status',
            'Created On',
        ];
    }

    public function map($shift): array
    {
        return [
            $shift->shift_name,
            $shift->shift_code,
            $shift->start_time,
            $shift->end_time,
            $shift->break_duration,
            $shift->working_hours,
            ucfirst($shift->shift_type),
            implode(', ', $shift->days_of_week ?? []),
            $shift->is_active ? 'Active' : 'Inactive',
            $shift->created_at->format('d-m-Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
