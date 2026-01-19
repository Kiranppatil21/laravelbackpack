<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Exports\TrainingReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class TrainingReportController extends Controller
{
    public function index()
    {
        $categories = ['security', 'safety', 'first-aid', 'fire-fighting', 'customer-service', 'technical'];
        $statuses = ['scheduled', 'ongoing', 'completed', 'cancelled'];
        
        return view('admin.reports.training', compact('categories', 'statuses'));
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['category', 'status', 'start_date']);
        
        $query = Training::with(['participants.employee']);

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        $trainings = $query->orderBy('start_date', 'desc')->get();
        
        $stats = [
            'total' => $trainings->count(),
            'completed' => $trainings->where('status', 'completed')->count(),
            'ongoing' => $trainings->where('status', 'ongoing')->count(),
            'scheduled' => $trainings->where('status', 'scheduled')->count(),
            'total_participants' => $trainings->sum(fn($t) => $t->participants->count()),
            'total_hours' => $trainings->sum('duration_hours'),
        ];

        return view('admin.reports.training_preview', compact('trainings', 'stats', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['category', 'status', 'start_date']);
        
        $query = Training::with(['participants']);

        if ($request->category) {
            $query->where('category', $request->category);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('start_date', '>=', $request->start_date);
        }

        $trainings = $query->orderBy('start_date', 'desc')->get();
        
        $stats = [
            'total' => $trainings->count(),
            'completed' => $trainings->where('status', 'completed')->count(),
            'total_participants' => $trainings->sum(fn($t) => $t->participants->count()),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.training', compact('trainings', 'stats', 'filters'));
        
        return $pdf->download('training-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['category', 'status', 'start_date']);
        
        return Excel::download(
            new TrainingReportExport($filters),
            'training-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['category', 'status', 'start_date']);
        
        return Excel::download(
            new TrainingReportExport($filters),
            'training-report-' . now()->format('Y-m-d') . '.csv'
        );
    }
}
