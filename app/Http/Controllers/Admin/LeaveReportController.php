<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use App\Exports\LeaveReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LeaveReportController extends Controller
{
    public function index()
    {
        $leaveTypes = ['casual', 'sick', 'annual', 'compensatory', 'maternity', 'paternity', 'unpaid'];
        $statuses = ['pending', 'approved', 'rejected', 'cancelled'];
        
        return view('admin.reports.leave', compact('leaveTypes', 'statuses'));
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'leave_type', 'status', 'employee_id']);
        
        $query = Leave::with(['employee']);

        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        if ($request->leave_type) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->employee_id) {
            $query->where('employee_id', $request->employee_id);
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();
        
        // Statistics
        $stats = [
            'total' => $leaves->count(),
            'approved' => $leaves->where('status', 'approved')->count(),
            'pending' => $leaves->where('status', 'pending')->count(),
            'rejected' => $leaves->where('status', 'rejected')->count(),
            'total_days' => $leaves->sum('days'),
        ];

        return view('admin.reports.leave_preview', compact('leaves', 'stats', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'leave_type', 'status']);
        
        $query = Leave::with(['employee']);

        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('end_date', '<=', $request->end_date);
        }

        if ($request->leave_type) {
            $query->where('leave_type', $request->leave_type);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $leaves = $query->orderBy('start_date', 'desc')->get();
        
        $stats = [
            'total' => $leaves->count(),
            'approved' => $leaves->where('status', 'approved')->count(),
            'pending' => $leaves->where('status', 'pending')->count(),
            'rejected' => $leaves->where('status', 'rejected')->count(),
            'total_days' => $leaves->sum('days'),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.leave', compact('leaves', 'stats', 'filters'));
        
        return $pdf->download('leave-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'leave_type', 'status']);
        
        return Excel::download(
            new LeaveReportExport($filters),
            'leave-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['start_date', 'end_date', 'leave_type', 'status']);
        
        return Excel::download(
            new LeaveReportExport($filters),
            'leave-report-' . now()->format('Y-m-d') . '.csv'
        );
    }
}
