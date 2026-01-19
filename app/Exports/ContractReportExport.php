<?php

namespace App\Exports;

use App\Models\Contract;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ContractReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = Contract::with(['client']);

        if (isset($this->filters['contract_type'])) {
            $query->where('contract_type', $this->filters['contract_type']);
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
            'Contract Number',
            'Client',
            'Contract Type',
            'Start Date',
            'End Date',
            'Contract Value',
            'Payment Terms',
            'Billing Cycle',
            'Status',
            'Renewal Date',
        ];
    }

    public function map($contract): array
    {
        return [
            $contract->contract_number,
            $contract->client->name ?? 'N/A',
            ucfirst(str_replace('-', ' ', $contract->contract_type)),
            $contract->start_date->format('d-m-Y'),
            $contract->end_date->format('d-m-Y'),
            number_format($contract->contract_value, 2),
            ucfirst($contract->payment_terms),
            ucfirst($contract->billing_cycle),
            ucfirst($contract->status),
            $contract->auto_renewal ? ($contract->renewal_date ? $contract->renewal_date->format('d-m-Y') : 'Auto-Renew') : 'No',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }
}
