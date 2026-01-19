<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Exports\ContractReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ContractReportController extends Controller
{
    public function index()
    {
        $contractTypes = ['security-services', 'facility-management', 'consulting', 'maintenance', 'other'];
        $statuses = ['draft', 'active', 'expired', 'terminated', 'renewed'];
        
        return view('admin.reports.contract', compact('contractTypes', 'statuses'));
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['contract_type', 'status', 'start_date']);
        
        $query = Contract::with(['client']);

        if ($request->contract_type) {
            $query->where('contract_type', $request->contract_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        $contracts = $query->orderBy('start_date', 'desc')->get();
        
        $stats = [
            'total' => $contracts->count(),
            'active' => $contracts->where('status', 'active')->count(),
            'expired' => $contracts->where('status', 'expired')->count(),
            'total_value' => $contracts->sum('contract_value'),
            'avg_value' => $contracts->avg('contract_value'),
        ];

        return view('admin.reports.contract_preview', compact('contracts', 'stats', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['contract_type', 'status', 'start_date']);
        
        $query = Contract::with(['client']);

        if ($request->contract_type) {
            $query->where('contract_type', $request->contract_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        $contracts = $query->orderBy('start_date', 'desc')->get();
        
        $stats = [
            'total' => $contracts->count(),
            'active' => $contracts->where('status', 'active')->count(),
            'total_value' => $contracts->sum('contract_value'),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.contract', compact('contracts', 'stats', 'filters'));
        
        return $pdf->download('contract-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['contract_type', 'status', 'start_date']);
        
        return Excel::download(
            new ContractReportExport($filters),
            'contract-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['contract_type', 'status', 'start_date']);
        
        return Excel::download(
            new ContractReportExport($filters),
            'contract-report-' . now()->format('Y-m-d') . '.csv'
        );
    }
}
