<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\VisitLog;
use App\Models\SecurityAlert;
use App\Models\VisitorFeedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VisitorAnalyticsController extends Controller
{
    /**
     * Get comprehensive visitor analytics dashboard.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitLog::class);

        $period = $request->query('period', '30'); // days
        $startDate = now()->subDays($period);

        $analytics = [
            'overview' => $this->getOverviewMetrics($startDate),
            'trends' => $this->getTrendAnalysis($startDate),
            'visitor_patterns' => $this->getVisitorPatterns($startDate),
            'security_metrics' => $this->getSecurityMetrics($startDate),
            'satisfaction_metrics' => $this->getSatisfactionMetrics($startDate),
            'capacity_analysis' => $this->getCapacityAnalysis($startDate),
        ];

        return response()->json($analytics);
    }

    /**
     * Get overview metrics for the specified period.
     */
    protected function getOverviewMetrics(Carbon $startDate): array
    {
        $totalVisits = VisitLog::where('check_in_at', '>=', $startDate)->count();
        $uniqueVisitors = VisitLog::where('check_in_at', '>=', $startDate)
            ->distinct('visitor_id')
            ->count('visitor_id');
        
        $averageVisitDuration = VisitLog::where('check_in_at', '>=', $startDate)
            ->whereNotNull('check_out_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at)) as avg_duration')
            ->value('avg_duration');

        $currentVisitors = VisitLog::active()->count();
        $peakOccupancy = $this->calculatePeakOccupancy($startDate);

        return [
            'total_visits' => $totalVisits,
            'unique_visitors' => $uniqueVisitors,
            'average_visit_duration_minutes' => round($averageVisitDuration ?? 0),
            'current_visitors' => $currentVisitors,
            'peak_occupancy' => $peakOccupancy,
            'return_visitor_rate' => $uniqueVisitors > 0 ? round((($totalVisits - $uniqueVisitors) / $totalVisits) * 100, 2) : 0,
        ];
    }

    /**
     * Get trend analysis data.
     */
    protected function getTrendAnalysis(Carbon $startDate): array
    {
        // Daily visit trends
        $dailyVisits = VisitLog::where('check_in_at', '>=', $startDate)
            ->selectRaw('DATE(check_in_at) as date, COUNT(*) as visits')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Hourly patterns
        $hourlyPatterns = VisitLog::where('check_in_at', '>=', $startDate)
            ->selectRaw('HOUR(check_in_at) as hour, COUNT(*) as visits')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // Day of week patterns
        $weeklyPatterns = VisitLog::where('check_in_at', '>=', $startDate)
            ->selectRaw('DAYNAME(check_in_at) as day, COUNT(*) as visits')
            ->groupBy('day')
            ->get();

        return [
            'daily_visits' => $dailyVisits,
            'hourly_patterns' => $hourlyPatterns,
            'weekly_patterns' => $weeklyPatterns,
        ];
    }

    /**
     * Get visitor patterns and behavior analysis.
     */
    protected function getVisitorPatterns(Carbon $startDate): array
    {
        // Visit types distribution
        $visitTypes = VisitLog::where('check_in_at', '>=', $startDate)
            ->selectRaw('visit_type, COUNT(*) as count')
            ->groupBy('visit_type')
            ->get();

        // Entry methods
        $entryMethods = VisitLog::where('check_in_at', '>=', $startDate)
            ->selectRaw('entry_method, COUNT(*) as count')
            ->groupBy('entry_method')
            ->get();

        // Company/organization analysis
        $topCompanies = Visitor::whereHas('visitLogs', function ($query) use ($startDate) {
                $query->where('check_in_at', '>=', $startDate);
            })
            ->whereNotNull('company')
            ->selectRaw('company, COUNT(DISTINCT visit_logs.id) as visit_count')
            ->join('visit_logs', 'visitors.id', '=', 'visit_logs.visitor_id')
            ->where('visit_logs.check_in_at', '>=', $startDate)
            ->groupBy('company')
            ->orderBy('visit_count', 'desc')
            ->limit(10)
            ->get();

        // Frequent visitors
        $frequentVisitors = Visitor::whereHas('visitLogs', function ($query) use ($startDate) {
                $query->where('check_in_at', '>=', $startDate);
            })
            ->withCount(['visitLogs as visit_count' => function ($query) use ($startDate) {
                $query->where('check_in_at', '>=', $startDate);
            }])
            ->orderBy('visit_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'company']);

        return [
            'visit_types' => $visitTypes,
            'entry_methods' => $entryMethods,
            'top_companies' => $topCompanies,
            'frequent_visitors' => $frequentVisitors,
        ];
    }

    /**
     * Get security metrics and alerts analysis.
     */
    protected function getSecurityMetrics(Carbon $startDate): array
    {
        $totalAlerts = SecurityAlert::where('occurred_at', '>=', $startDate)->count();
        $openAlerts = SecurityAlert::where('occurred_at', '>=', $startDate)
            ->where('status', 'open')
            ->count();

        $alertsByType = SecurityAlert::where('occurred_at', '>=', $startDate)
            ->selectRaw('type, severity, COUNT(*) as count')
            ->groupBy('type', 'severity')
            ->get();

        $watchlistEncounters = SecurityAlert::where('occurred_at', '>=', $startDate)
            ->where('type', 'watchlist_entry')
            ->count();

        $overstayAlerts = SecurityAlert::where('occurred_at', '>=', $startDate)
            ->where('type', 'overstay')
            ->count();

        $averageResolutionTime = SecurityAlert::where('occurred_at', '>=', $startDate)
            ->whereNotNull('resolved_at')
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, occurred_at, resolved_at)) as avg_resolution')
            ->value('avg_resolution');

        return [
            'total_alerts' => $totalAlerts,
            'open_alerts' => $openAlerts,
            'alerts_by_type' => $alertsByType,
            'watchlist_encounters' => $watchlistEncounters,
            'overstay_alerts' => $overstayAlerts,
            'average_resolution_time_minutes' => round($averageResolutionTime ?? 0),
        ];
    }

    /**
     * Get visitor satisfaction metrics.
     */
    protected function getSatisfactionMetrics(Carbon $startDate): array
    {
        $totalFeedback = VisitorFeedback::where('created_at', '>=', $startDate)->count();
        $averageRating = VisitorFeedback::where('created_at', '>=', $startDate)
            ->whereNotNull('rating')
            ->avg('rating');

        $ratingDistribution = VisitorFeedback::where('created_at', '>=', $startDate)
            ->whereNotNull('rating')
            ->selectRaw('rating, COUNT(*) as count')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get();

        $feedbackByType = VisitorFeedback::where('created_at', '>=', $startDate)
            ->selectRaw('feedback_type, AVG(rating) as avg_rating, COUNT(*) as count')
            ->groupBy('feedback_type')
            ->get();

        $npsScore = $this->calculateNPS($startDate);

        return [
            'total_feedback' => $totalFeedback,
            'average_rating' => round($averageRating ?? 0, 2),
            'rating_distribution' => $ratingDistribution,
            'feedback_by_type' => $feedbackByType,
            'nps_score' => $npsScore,
        ];
    }

    /**
     * Get capacity analysis and utilization metrics.
     */
    protected function getCapacityAnalysis(Carbon $startDate): array
    {
        // Calculate daily peak occupancy
        $dailyPeaks = DB::select("
            SELECT DATE(check_in_at) as date, 
                   MAX(concurrent_visitors) as peak_visitors
            FROM (
                SELECT check_in_at,
                       (SELECT COUNT(*) 
                        FROM visit_logs v2 
                        WHERE v2.check_in_at <= v1.check_in_at 
                        AND (v2.check_out_at IS NULL OR v2.check_out_at > v1.check_in_at)
                        AND v2.check_in_at >= ?) as concurrent_visitors
                FROM visit_logs v1
                WHERE check_in_at >= ?
            ) as occupancy
            GROUP BY DATE(check_in_at)
            ORDER BY date
        ", [$startDate, $startDate]);

        // Average visit duration by time of day
        $durationByHour = VisitLog::where('check_in_at', '>=', $startDate)
            ->whereNotNull('check_out_at')
            ->selectRaw('HOUR(check_in_at) as hour, 
                        AVG(TIMESTAMPDIFF(MINUTE, check_in_at, check_out_at)) as avg_duration')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return [
            'daily_peak_occupancy' => $dailyPeaks,
            'average_duration_by_hour' => $durationByHour,
        ];
    }

    /**
     * Generate compliance and audit report.
     */
    public function complianceReport(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitLog::class);

        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'format' => 'sometimes|in:json,csv,pdf',
        ]);

        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        $report = [
            'period' => [
                'start' => $startDate->toDateString(),
                'end' => $endDate->toDateString(),
            ],
            'visitor_statistics' => $this->getComplianceVisitorStats($startDate, $endDate),
            'security_incidents' => $this->getComplianceSecurityData($startDate, $endDate),
            'data_processing' => $this->getDataProcessingMetrics($startDate, $endDate),
            'policy_compliance' => $this->getPolicyComplianceMetrics($startDate, $endDate),
        ];

        if (($data['format'] ?? 'json') === 'csv') {
            return $this->exportReportAsCsv($report);
        }

        return response()->json($report);
    }

    /**
     * Export visitor data for compliance purposes.
     */
    public function exportVisitorData(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitLog::class);

        $data = $request->validate([
            'visitor_ids' => 'required|array',
            'visitor_ids.*' => 'exists:visitors,id',
            'include_photos' => 'sometimes|boolean',
            'anonymize' => 'sometimes|boolean',
        ]);

        $visitors = Visitor::whereIn('id', $data['visitor_ids'])
            ->with(['visitLogs', 'feedback', 'securityAlerts'])
            ->get();

        $exportData = $visitors->map(function ($visitor) use ($data) {
            $visitorData = [
                'id' => $visitor->id,
                'name' => ($data['anonymize'] ?? false) ? 'REDACTED' : $visitor->name,
                'email' => ($data['anonymize'] ?? false) ? 'REDACTED' : $visitor->email,
                'phone' => ($data['anonymize'] ?? false) ? 'REDACTED' : $visitor->phone,
                'company' => $visitor->company,
                'visit_history' => $visitor->visitLogs->map(function ($visit) {
                    return [
                        'check_in_at' => $visit->check_in_at,
                        'check_out_at' => $visit->check_out_at,
                        'duration_minutes' => $visit->getDuration(),
                        'visit_type' => $visit->visit_type,
                        'location' => $visit->location,
                    ];
                }),
                'feedback_history' => $visitor->feedback,
                'security_alerts' => $visitor->securityAlerts,
            ];

            if (($data['include_photos'] ?? false) && !($data['anonymize'] ?? false)) {
                $visitorData['photo_url'] = $visitor->photo_path ? asset('storage/' . $visitor->photo_path) : null;
            }

            return $visitorData;
        });

        return response()->json([
            'exported_at' => now(),
            'total_visitors' => $exportData->count(),
            'data' => $exportData,
        ]);
    }

    // Helper methods

    protected function calculatePeakOccupancy(Carbon $startDate): int
    {
        // This is a simplified calculation - in production you'd want more sophisticated logic
        return VisitLog::where('check_in_at', '>=', $startDate)
            ->whereDate('check_in_at', '>=', $startDate)
            ->selectRaw('DATE(check_in_at) as date, COUNT(*) as daily_visits')
            ->groupBy('date')
            ->max('daily_visits') ?? 0;
    }

    protected function calculateNPS(Carbon $startDate): ?float
    {
        $ratings = VisitorFeedback::where('created_at', '>=', $startDate)
            ->whereNotNull('rating')
            ->pluck('rating');

        if ($ratings->isEmpty()) {
            return null;
        }

        $promoters = $ratings->filter(fn($rating) => $rating >= 9)->count();
        $detractors = $ratings->filter(fn($rating) => $rating <= 6)->count();
        $total = $ratings->count();

        return round((($promoters - $detractors) / $total) * 100, 2);
    }

    protected function getComplianceVisitorStats(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_visitors' => Visitor::whereBetween('created_at', [$startDate, $endDate])->count(),
            'visitors_with_id_verification' => Visitor::whereBetween('created_at', [$startDate, $endDate])
                ->where('id_verified', true)->count(),
            'visitors_with_background_check' => Visitor::whereBetween('created_at', [$startDate, $endDate])
                ->whereNotNull('background_check_status')->count(),
            'pre_approved_visitors' => Visitor::whereBetween('created_at', [$startDate, $endDate])
                ->where('pre_approved', true)->count(),
        ];
    }

    protected function getComplianceSecurityData(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'total_security_alerts' => SecurityAlert::whereBetween('occurred_at', [$startDate, $endDate])->count(),
            'critical_alerts' => SecurityAlert::whereBetween('occurred_at', [$startDate, $endDate])
                ->where('severity', 'critical')->count(),
            'watchlist_encounters' => SecurityAlert::whereBetween('occurred_at', [$startDate, $endDate])
                ->where('type', 'watchlist_entry')->count(),
            'unresolved_alerts' => SecurityAlert::whereBetween('occurred_at', [$startDate, $endDate])
                ->where('status', 'open')->count(),
        ];
    }

    protected function getDataProcessingMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'visits_data_processed' => VisitLog::whereBetween('check_in_at', [$startDate, $endDate])
                ->where('data_processed', true)->count(),
            'visits_pending_processing' => VisitLog::whereBetween('check_in_at', [$startDate, $endDate])
                ->where('data_processed', false)->count(),
            'data_retention_compliance' => VisitLog::whereBetween('check_in_at', [$startDate, $endDate])
                ->whereNotNull('data_retention_until')->count(),
        ];
    }

    protected function getPolicyComplianceMetrics(Carbon $startDate, Carbon $endDate): array
    {
        return [
            'nda_signed_percentage' => $this->calculateNdaComplianceRate($startDate, $endDate),
            'health_screening_compliance' => $this->calculateHealthScreeningRate($startDate, $endDate),
            'escort_compliance' => $this->calculateEscortComplianceRate($startDate, $endDate),
        ];
    }

    protected function calculateNdaComplianceRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalVisits = VisitLog::whereBetween('check_in_at', [$startDate, $endDate])->count();
        $ndaSigned = VisitLog::whereBetween('check_in_at', [$startDate, $endDate])
            ->where('nda_signed', true)->count();

        return $totalVisits > 0 ? round(($ndaSigned / $totalVisits) * 100, 2) : 0;
    }

    protected function calculateHealthScreeningRate(Carbon $startDate, Carbon $endDate): float
    {
        $totalVisitors = Visitor::whereBetween('created_at', [$startDate, $endDate])->count();
        $screeningPassed = Visitor::whereBetween('created_at', [$startDate, $endDate])
            ->where('health_screening_passed', true)->count();

        return $totalVisitors > 0 ? round(($screeningPassed / $totalVisitors) * 100, 2) : 0;
    }

    protected function calculateEscortComplianceRate(Carbon $startDate, Carbon $endDate): float
    {
        $requiresEscort = VisitLog::whereBetween('check_in_at', [$startDate, $endDate])
            ->whereNotNull('escort_required')->count();
        $hasEscort = VisitLog::whereBetween('check_in_at', [$startDate, $endDate])
            ->whereNotNull('escorted_by')->count();

        return $requiresEscort > 0 ? round(($hasEscort / $requiresEscort) * 100, 2) : 100;
    }

    protected function exportReportAsCsv(array $report): JsonResponse
    {
        // This would generate a CSV download in a real implementation
        // For now, returning JSON with CSV indication
        return response()->json([
            'message' => 'CSV export would be generated here',
            'report' => $report,
            'format' => 'csv',
        ]);
    }
}