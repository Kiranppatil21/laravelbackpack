<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SecurityAlert;
use App\Models\VisitorWatchlist;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class SecurityController extends Controller
{
    /**
     * Security dashboard with alerts and statistics.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SecurityAlert::class);

        $stats = [
            'open_alerts' => SecurityAlert::open()->count(),
            'high_priority_alerts' => SecurityAlert::open()->highPriority()->count(),
            'unassigned_alerts' => SecurityAlert::open()->unassigned()->count(),
            'alerts_today' => SecurityAlert::recent(24)->count(),
            'watchlist_entries' => VisitorWatchlist::active()->count(),
            'critical_threats' => VisitorWatchlist::active()->where('threat_level', 'critical')->count(),
        ];

        $recentAlerts = SecurityAlert::with(['visitor', 'visitLog', 'triggeredBy', 'assignedTo'])
            ->latest('occurred_at')
            ->limit(10)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'type' => $alert->type,
                    'severity' => $alert->severity,
                    'title' => $alert->title,
                    'visitor_name' => $alert->visitor?->name,
                    'triggered_by' => $alert->triggeredBy?->name ?? 'System',
                    'assigned_to' => $alert->assignedTo?->name,
                    'status' => $alert->status,
                    'occurred_at' => $alert->occurred_at,
                ];
            });

        $alertsByType = SecurityAlert::open()
            ->selectRaw('type, severity, COUNT(*) as count')
            ->groupBy('type', 'severity')
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_alerts' => $recentAlerts,
            'alerts_by_type' => $alertsByType,
        ]);
    }

    /**
     * List security alerts with filtering.
     */
    public function alerts(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SecurityAlert::class);

        $query = SecurityAlert::with(['visitor', 'visitLog', 'triggeredBy', 'assignedTo'])
            ->latest('occurred_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->query('severity'));
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->query('assigned_to'));
        }

        if ($request->filled('unassigned') && $request->query('unassigned')) {
            $query->whereNull('assigned_to');
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('occurred_at', [
                $request->query('date_from'),
                $request->query('date_to'),
            ]);
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $alerts = $query->paginate($perPage);

        return response()->json($alerts);
    }

    /**
     * Show a specific alert.
     */
    public function showAlert(SecurityAlert $alert): JsonResponse
    {
        $this->authorize('view', $alert);

        return response()->json($alert->load([
            'visitor', 'visitLog', 'triggeredBy', 'assignedTo'
        ]));
    }

    /**
     * Assign an alert to a user.
     */
    public function assignAlert(Request $request, SecurityAlert $alert): JsonResponse
    {
        $this->authorize('update', $alert);

        $data = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $user = \App\Models\User::find($data['assigned_to']);
        $alert->assign($user);

        return response()->json([
            'message' => 'Alert assigned successfully',
            'alert' => $alert->fresh(['assignedTo']),
        ]);
    }

    /**
     * Resolve an alert.
     */
    public function resolveAlert(Request $request, SecurityAlert $alert): JsonResponse
    {
        $this->authorize('update', $alert);

        $data = $request->validate([
            'resolution_notes' => 'nullable|string|max:1000',
            'false_alarm' => 'nullable|boolean',
        ]);

        if ($data['false_alarm'] ?? false) {
            $alert->markAsFalseAlarm($data['resolution_notes'] ?? null);
        } else {
            $alert->resolve($data['resolution_notes'] ?? null, Auth::user());
        }

        return response()->json([
            'message' => 'Alert resolved successfully',
            'alert' => $alert->fresh(),
        ]);
    }

    /**
     * Escalate an alert.
     */
    public function escalateAlert(SecurityAlert $alert): JsonResponse
    {
        $this->authorize('update', $alert);

        $alert->escalate();

        return response()->json([
            'message' => 'Alert escalated successfully',
            'alert' => $alert->fresh(),
        ]);
    }

    /**
     * List watchlist entries.
     */
    public function watchlist(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitorWatchlist::class);

        $query = VisitorWatchlist::with(['visitor', 'addedBy'])
            ->latest('created_at');

        // Filters
        if ($request->filled('active_only')) {
            $query->active();
        }

        if ($request->filled('threat_level')) {
            $query->where('threat_level', $request->query('threat_level'));
        }

        if ($request->filled('reason')) {
            $query->where('reason', $request->query('reason'));
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_email', 'like', "%{$search}%")
                  ->orWhere('visitor_phone', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $watchlist = $query->paginate($perPage);

        return response()->json($watchlist);
    }

    /**
     * Add visitor to watchlist.
     */
    public function addToWatchlist(Request $request): JsonResponse
    {
        $this->authorize('create', VisitorWatchlist::class);

        $data = $request->validate([
            'visitor_id' => 'nullable|exists:visitors,id',
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'visitor_phone' => 'nullable|string|max:50',
            'visitor_id_value' => 'nullable|string|max:100',
            'threat_level' => 'required|in:low,medium,high,critical',
            'reason' => 'required|in:security_incident,theft,harassment,trespassing,other',
            'description' => 'required|string|max:1000',
            'alert_on_entry' => 'nullable|boolean',
            'auto_deny' => 'nullable|boolean',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $watchlistEntry = VisitorWatchlist::create([
            ...$data,
            'added_by' => Auth::id(),
            'alert_on_entry' => $data['alert_on_entry'] ?? true,
            'auto_deny' => $data['auto_deny'] ?? false,
        ]);

        // Update visitor record if exists
        if ($data['visitor_id']) {
            $visitor = Visitor::find($data['visitor_id']);
            $visitor->update(['on_watchlist' => true]);
        }

        return response()->json([
            'message' => 'Visitor added to watchlist successfully',
            'watchlist_entry' => $watchlistEntry->fresh(['addedBy']),
        ], 201);
    }

    /**
     * Remove visitor from watchlist.
     */
    public function removeFromWatchlist(VisitorWatchlist $watchlistEntry): JsonResponse
    {
        $this->authorize('delete', $watchlistEntry);

        $watchlistEntry->deactivate();

        // Update visitor record if exists
        if ($watchlistEntry->visitor) {
            $hasOtherEntries = VisitorWatchlist::active()
                ->where('visitor_id', $watchlistEntry->visitor_id)
                ->where('id', '!=', $watchlistEntry->id)
                ->exists();

            if (!$hasOtherEntries) {
                $watchlistEntry->visitor->update(['on_watchlist' => false]);
            }
        }

        return response()->json([
            'message' => 'Visitor removed from watchlist successfully',
            'watchlist_entry' => $watchlistEntry->fresh(),
        ]);
    }

    /**
     * Update watchlist entry.
     */
    public function updateWatchlistEntry(Request $request, VisitorWatchlist $watchlistEntry): JsonResponse
    {
        $this->authorize('update', $watchlistEntry);

        $data = $request->validate([
            'threat_level' => 'sometimes|in:low,medium,high,critical',
            'reason' => 'sometimes|in:security_incident,theft,harassment,trespassing,other',
            'description' => 'sometimes|string|max:1000',
            'alert_on_entry' => 'sometimes|boolean',
            'auto_deny' => 'sometimes|boolean',
            'expires_at' => 'sometimes|nullable|date|after:now',
        ]);

        $watchlistEntry->update($data);

        return response()->json([
            'message' => 'Watchlist entry updated successfully',
            'watchlist_entry' => $watchlistEntry->fresh(),
        ]);
    }

    /**
     * Get security statistics and reports.
     */
    public function reports(Request $request): JsonResponse
    {
        $this->authorize('viewAny', SecurityAlert::class);

        $period = $request->query('period', '30'); // days

        $alertStats = [
            'total_alerts' => SecurityAlert::where('occurred_at', '>=', now()->subDays($period))->count(),
            'resolved_alerts' => SecurityAlert::where('occurred_at', '>=', now()->subDays($period))
                ->where('status', 'resolved')->count(),
            'false_alarms' => SecurityAlert::where('occurred_at', '>=', now()->subDays($period))
                ->where('status', 'false_alarm')->count(),
            'critical_alerts' => SecurityAlert::where('occurred_at', '>=', now()->subDays($period))
                ->where('severity', 'critical')->count(),
        ];

        $alertTrends = SecurityAlert::where('occurred_at', '>=', now()->subDays($period))
            ->selectRaw('DATE(occurred_at) as date, type, severity, COUNT(*) as count')
            ->groupBy('date', 'type', 'severity')
            ->orderBy('date')
            ->get();

        $watchlistStats = [
            'total_entries' => VisitorWatchlist::active()->count(),
            'high_threat' => VisitorWatchlist::active()->whereIn('threat_level', ['high', 'critical'])->count(),
            'auto_deny_entries' => VisitorWatchlist::active()->where('auto_deny', true)->count(),
            'recent_additions' => VisitorWatchlist::where('created_at', '>=', now()->subDays($period))->count(),
        ];

        return response()->json([
            'period' => $period,
            'alert_stats' => $alertStats,
            'alert_trends' => $alertTrends,
            'watchlist_stats' => $watchlistStats,
        ]);
    }
}