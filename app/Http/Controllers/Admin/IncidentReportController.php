<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Incident;
use App\Exports\IncidentReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class IncidentReportController extends Controller
{
    public function index()
    {
        $incidentTypes = ['security-breach', 'theft', 'vandalism', 'trespassing', 'fire', 'medical-emergency', 'accident', 'other'];
        $severities = ['low', 'medium', 'high', 'critical'];
        $statuses = ['reported', 'investigating', 'resolved', 'closed'];
        
        return view('admin.reports.incident', compact('incidentTypes', 'severities', 'statuses'));
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['incident_type', 'severity', 'status', 'start_date', 'end_date']);
        
        $query = Incident::with(['client']);

        if ($request->incident_type) {
            $query->where('incident_type', $request->incident_type);
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->where('incident_date', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->where('incident_date', '<=', $request->end_date);
        }

        $incidents = $query->orderBy('incident_date', 'desc')->get();
        
        $stats = [
            'total' => $incidents->count(),
            'critical' => $incidents->where('severity', 'critical')->count(),
            'high' => $incidents->where('severity', 'high')->count(),
            'resolved' => $incidents->where('status', 'resolved')->count(),
            'investigating' => $incidents->where('status', 'investigating')->count(),
        ];

        return view('admin.reports.incident_preview', compact('incidents', 'stats', 'filters'));
    }

    public function exportPdf(Request $request)
    {
        $filters = $request->only(['incident_type', 'severity', 'status', 'start_date', 'end_date']);
        
        $query = Incident::with(['client']);

        if ($request->incident_type) {
            $query->where('incident_type', $request->incident_type);
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->start_date) {
            $query->whereDate('incident_datetime', '>=', $request->start_date);
        }

        if ($request->end_date) {
            $query->whereDate('incident_datetime', '<=', $request->end_date);
        }

        $incidents = $query->orderBy('incident_datetime', 'desc')->get();
        
        $stats = [
            'total' => $incidents->count(),
            'critical' => $incidents->where('severity', 'critical')->count(),
            'resolved' => $incidents->where('status', 'resolved')->count(),
        ];

        $pdf = Pdf::loadView('admin.reports.pdf.incident', compact('incidents', 'stats', 'filters'));
        
        return $pdf->download('incident-report-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request)
    {
        $filters = $request->only(['incident_type', 'severity', 'status', 'start_date', 'end_date']);
        
        return Excel::download(
            new IncidentReportExport($filters),
            'incident-report-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function exportCsv(Request $request)
    {
        $filters = $request->only(['incident_type', 'severity', 'status', 'start_date', 'end_date']);
        
        return Excel::download(
            new IncidentReportExport($filters),
            'incident-report-' . now()->format('Y-m-d') . '.csv'
        );
    }
}
