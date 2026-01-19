<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Exports\ShiftReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ShiftReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.shift');
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['is_active']);
        
        $query = Shift::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $shifts = $query->orderBy('shift_name')->get();
        
        $stats = [
            'total' => $shifts->count(),
            'active' => $shifts->where('is_active', true)->count(),
            'inactive' => $shifts->where('is_active', false)->count(),
            'total_hours' => $shifts->sum('working_hours'),
        ];

        return view('admin.reports.shift_preview', compact('shifts', 'stats', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['is_active']);
        
        $query = Shift::query();

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $shifts = $query->orderBy('shift_name')->get();
        
        $stats = [
            'total' => $shifts->count(),
            'active' => $shifts->where('is_active', true)->count(),
            'inactive' => $shifts->where('is_active', false)->count(),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.shift', compact('shifts', 'stats', 'filters'));
        
        return $pdf->download('shift-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['is_active']);
        
        return Excel::download(
            new ShiftReportExport($filters),
            'shift-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['is_active']);
        
        return Excel::download(
            new ShiftReportExport($filters),
            'shift-report-' . now()->format('Y-m-d') . '.csv'
        );
    }
}
