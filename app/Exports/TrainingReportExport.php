<?php

namespace App\Exports;

use App\Models\Training;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TrainingReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Training::with(['participants']);

        if (isset($this->filters['category'])) {
            $query->where('category', $this->filters['category']);
        }

        if (isset($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (isset($this->filters['start_date'])) {
            $query->where('start_date', '>=', $this->filters['start_date']);
        }

        return $query->orderBy('start_date', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Training Name',
            'Training Code',
            'Category',
            'Start Date',
            'End Date',
            'Duration (hrs)',
            'Venue',
            'Trainer',
            'Participants',
            'Status',
            'Cost/Participant',
            'Total Cost',
        ];
    }

    public function map($training): array
    {
        $participantCount = $training->participants->count();
        $totalCost = $training->cost_per_participant * $participantCount;

        return [
            $training->training_name,
            $training->training_code,
            ucfirst($training->category),
            $training->start_date->format('d-m-Y'),
            $training->end_date->format('d-m-Y'),
            $training->duration_hours,
            $training->venue,
            $training->trainer_name ?? 'N/A',
            $participantCount,
            ucfirst($training->status),
            number_format($training->cost_per_participant ?? 0, 2),
            number_format($totalCost, 2),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
