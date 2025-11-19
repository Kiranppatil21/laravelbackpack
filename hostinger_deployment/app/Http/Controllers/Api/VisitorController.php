<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visitor;
use App\Models\VisitLog;
use App\Models\VisitorInvitation;
use App\Models\VisitorWatchlist;
use App\Models\SecurityAlert;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Notifications\VisitorCheckedIn;
use Illuminate\Validation\ValidationException;

class VisitorController extends Controller
{
    /**
     * Enhanced check-in endpoint with security features and photo capture.
     */
    public function checkIn(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:255',
            'host_id' => 'nullable|integer',
            'source' => 'nullable|string|max:50',
            'external_id' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:500',
            'id_type' => 'nullable|string|max:100',
            'id_value' => 'nullable|string|max:100',
            'photo' => 'nullable|image|max:2048', // 2MB max
            'temperature' => 'nullable|numeric|between:30,50',
            'health_questions' => 'nullable|array',
            'visit_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'expected_checkout_at' => 'nullable|date|after:now',
            'security_items' => 'nullable|array',
            'nda_required' => 'nullable|boolean',
            'entry_method' => 'nullable|string|in:qr_code,rfid,biometric,manual',
            'device_id' => 'nullable|string|max:255',
            'invitation_code' => 'nullable|string|exists:visitor_invitations,invitation_code',
        ]);

        // Check for valid invitation if provided
        $invitation = null;
        if (!empty($data['invitation_code'])) {
            $invitation = VisitorInvitation::where('invitation_code', $data['invitation_code'])
                ->valid()
                ->first();
            
            if (!$invitation) {
                throw ValidationException::withMessages([
                    'invitation_code' => ['Invalid or expired invitation code.']
                ]);
            }
        }

        // Find or create visitor
        $visitor = null;
        if (!empty($data['email'])) {
            $visitor = Visitor::firstWhere('email', $data['email']);
        }

        if (!$visitor) {
            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('visitor-photos', 'public');
            }

            $visitor = Visitor::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'id_type' => $data['id_type'] ?? null,
                'id_value' => $data['id_value'] ?? null,
                'host_id' => $invitation ? $invitation->host_id : ($data['host_id'] ?? null),
                'source' => $data['source'] ?? 'kiosk',
                'photo_path' => $photoPath,
                'purpose' => $data['purpose'] ?? $invitation?->purpose,
                'temperature' => $data['temperature'] ?? null,
                'health_questions' => $data['health_questions'] ?? null,
                'health_screening_passed' => $this->evaluateHealthScreening($data),
                'pre_approved' => $invitation ? true : false,
                'approved_by' => $invitation?->invited_by,
                'approved_at' => $invitation ? now() : null,
            ]);
        }

        // Security checks
        $this->performSecurityChecks($visitor);

        // Check if visitor can check in
        if (!$visitor->canCheckIn()) {
            return response()->json([
                'message' => 'Check-in denied',
                'reason' => $this->getCheckInDenialReason($visitor),
                'visitor' => $visitor,
            ], 403);
        }

        // Create visit log
        $visit = VisitLog::create([
            'visitor_id' => $visitor->id,
            'host_id' => $invitation ? $invitation->host_id : ($data['host_id'] ?? null),
            'check_in_at' => now(),
            'source' => $data['source'] ?? 'kiosk',
            'external_id' => $data['external_id'] ?? null,
            'visit_type' => $data['visit_type'] ?? 'regular',
            'location' => $data['location'] ?? null,
            'expected_checkout_at' => $data['expected_checkout_at'] ? now()->parse($data['expected_checkout_at']) : null,
            'security_items' => $data['security_items'] ?? null,
            'nda_signed' => $data['nda_required'] ?? false,
            'entry_method' => $data['entry_method'] ?? 'manual',
            'device_id' => $data['device_id'] ?? null,
            'purpose' => $visitor->purpose,
        ]);

        // Mark invitation as used
        if ($invitation) {
            $invitation->markAsUsed($visit);
        }

        // Notify host
        $this->notifyHost($visit);

        // Check for security alerts
        $this->checkSecurityAlerts($visitor, $visit);

        return response()->json([
            'message' => 'Check-in successful',
            'visitor' => $visitor->fresh(),
            'visit' => $visit->fresh(),
            'qr_code' => $visitor->qr_code,
        ], 201);
    }

    /**
     * Enhanced checkout endpoint with photo and rating capture.
     */
    public function checkOut(Request $request, VisitLog $visit): JsonResponse
    {
        $data = $request->validate([
            'checkout_reason' => 'nullable|string|max:500',
            'exit_photo' => 'nullable|image|max:2048',
            'visitor_rating' => 'nullable|integer|between:1,5',
            'visitor_feedback' => 'nullable|string|max:1000',
            'host_rating' => 'nullable|integer|between:1,5',
            'host_feedback' => 'nullable|string|max:1000',
            'safety_incidents' => 'nullable|string|max:1000',
        ]);

        // Authorization check
        if (Auth::check()) {
            $this->authorize('update', $visit);
        }

        if ($visit->check_out_at) {
            return response()->json(['message' => 'Already checked out'], 200);
        }

        // Handle exit photo
        $exitPhotoPath = null;
        if ($request->hasFile('exit_photo')) {
            $exitPhotoPath = $request->file('exit_photo')->store('visitor-exit-photos', 'public');
        }

        // Update visit log
        $visit->update([
            'check_out_at' => now(),
            'checkout_reason' => $data['checkout_reason'] ?? null,
            'exit_photo_path' => $exitPhotoPath,
            'visitor_rating' => $data['visitor_rating'] ?? null,
            'visitor_feedback' => $data['visitor_feedback'] ?? null,
            'host_rating' => $data['host_rating'] ?? null,
            'host_feedback' => $data['host_feedback'] ?? null,
            'safety_incidents' => $data['safety_incidents'] ?? null,
        ]);

        return response()->json([
            'message' => 'Check-out successful',
            'visit' => $visit->fresh(),
            'duration' => $visit->getDurationHuman(),
        ]);
    }

    /**
     * Real-time dashboard data for visitor management.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitLog::class);

        $stats = [
            'current_visitors' => VisitLog::active()->count(),
            'today_checkins' => VisitLog::today()->count(),
            'today_checkouts' => VisitLog::today()->completed()->count(),
            'overstayed_visitors' => VisitLog::overstayed()->count(),
            'pending_approvals' => Visitor::where('status', 'pending_approval')->count(),
            'watchlist_alerts' => SecurityAlert::open()->where('type', 'watchlist_entry')->count(),
            'active_alerts' => SecurityAlert::open()->count(),
        ];

        $recentActivity = VisitLog::with(['visitor', 'host'])
            ->latest('check_in_at')
            ->limit(10)
            ->get()
            ->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'visitor_name' => $visit->visitor->name,
                    'host_name' => $visit->host?->name ?? 'No Host',
                    'action' => $visit->isActive() ? 'checked_in' : 'checked_out',
                    'time' => $visit->check_in_at,
                    'status' => $visit->getStatusBadge(),
                    'entry_method' => $visit->getEntryMethodBadge(),
                ];
            });

        $currentVisitors = VisitLog::with(['visitor', 'host'])
            ->active()
            ->get()
            ->map(function ($visit) {
                return [
                    'id' => $visit->id,
                    'visitor' => [
                        'id' => $visit->visitor->id,
                        'name' => $visit->visitor->name,
                        'company' => $visit->visitor->company,
                        'photo' => $visit->visitor->photo_path ? Storage::url($visit->visitor->photo_path) : null,
                    ],
                    'host' => $visit->host ? [
                        'id' => $visit->host->id,
                        'name' => $visit->host->name,
                    ] : null,
                    'check_in_at' => $visit->check_in_at,
                    'expected_checkout_at' => $visit->expected_checkout_at,
                    'duration' => $visit->getDurationHuman(),
                    'location' => $visit->location,
                    'status' => $visit->getStatusBadge(),
                    'overstayed' => $visit->overstayed || ($visit->expected_checkout_at && $visit->expected_checkout_at < now()),
                ];
            });

        $alerts = SecurityAlert::with(['visitor', 'visitLog'])
            ->open()
            ->latest('occurred_at')
            ->limit(5)
            ->get()
            ->map(function ($alert) {
                return [
                    'id' => $alert->id,
                    'type' => $alert->type,
                    'severity' => $alert->severity,
                    'title' => $alert->title,
                    'description' => $alert->description,
                    'visitor_name' => $alert->visitor?->name,
                    'occurred_at' => $alert->occurred_at,
                    'status' => $alert->status,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_activity' => $recentActivity,
            'current_visitors' => $currentVisitors,
            'alerts' => $alerts,
        ]);
    }

    /**
     * List visit logs with enhanced filtering and search.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitLog::class);

        $query = VisitLog::with(['visitor', 'host', 'securityAlerts'])
            ->latest('check_in_at');

        // Enhanced filtering
        if ($request->filled('status')) {
            $status = $request->query('status');
            if ($status === 'active') {
                $query->active();
            } elseif ($status === 'completed') {
                $query->completed();
            } elseif ($status === 'overstayed') {
                $query->overstayed();
            }
        }

        if ($request->filled('host_id')) {
            $query->where('host_id', $request->query('host_id'));
        }

        if ($request->filled('visit_type')) {
            $query->where('visit_type', $request->query('visit_type'));
        }

        if ($request->filled('entry_method')) {
            $query->where('entry_method', $request->query('entry_method'));
        }

        if ($request->filled('location')) {
            $query->where('location', 'like', '%' . $request->query('location') . '%');
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('check_in_at', [
                $request->query('date_from'),
                $request->query('date_to'),
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->whereHas('visitor', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $visits = $query->paginate($perPage);

        // Transform the data
        $visits->getCollection()->transform(function ($visit) {
            return [
                'id' => $visit->id,
                'visitor' => [
                    'id' => $visit->visitor->id,
                    'name' => $visit->visitor->name,
                    'email' => $visit->visitor->email,
                    'phone' => $visit->visitor->phone,
                    'company' => $visit->visitor->company,
                    'photo' => $visit->visitor->photo_path ? Storage::url($visit->visitor->photo_path) : null,
                ],
                'host' => $visit->host ? [
                    'id' => $visit->host->id,
                    'name' => $visit->host->name,
                ] : null,
                'check_in_at' => $visit->check_in_at,
                'check_out_at' => $visit->check_out_at,
                'expected_checkout_at' => $visit->expected_checkout_at,
                'duration' => $visit->getDurationHuman(),
                'visit_type' => $visit->visit_type,
                'location' => $visit->location,
                'entry_method' => $visit->entry_method,
                'status' => $visit->getStatusBadge(),
                'overstayed' => $visit->overstayed,
                'has_alerts' => $visit->securityAlerts->where('status', 'open')->isNotEmpty(),
                'rating' => $visit->visitor_rating,
            ];
        });

        return response()->json($visits);
    }

    // Private helper methods

    private function evaluateHealthScreening(array $data): bool
    {
        // Basic health screening logic
        if (isset($data['temperature']) && $data['temperature'] > 37.5) {
            return false;
        }

        // Add more health screening logic based on questions
        return true;
    }

    private function performSecurityChecks(Visitor $visitor): void
    {
        // Check watchlist
        $watchlistEntry = VisitorWatchlist::checkVisitor($visitor);
        if ($watchlistEntry) {
            $visitor->update(['on_watchlist' => true]);
            SecurityAlert::createWatchlistAlert($visitor, $watchlistEntry);
        }
    }

    private function getCheckInDenialReason(Visitor $visitor): string
    {
        if ($visitor->status === 'blocked') {
            return 'Visitor is blocked from entry.';
        }

        if ($visitor->isOnWatchlist()) {
            return 'Visitor is on security watchlist.';
        }

        if (!$visitor->passedHealthScreening()) {
            return 'Health screening requirements not met.';
        }

        if ($visitor->requiresBackgroundCheck()) {
            return 'Background check required and not completed.';
        }

        return 'Check-in denied for security reasons.';
    }

    private function notifyHost(VisitLog $visit): void
    {
        if ($visit->host_id) {
            $host = User::find($visit->host_id);
            if ($host) {
                try {
                    $host->notify(new VisitorCheckedIn($visit->visitor, $visit));
                    $visit->update(['host_notified_at' => now()]);
                } catch (\Throwable $e) {
                    // Log but don't fail check-in
                }
            }
        }
    }

    private function checkSecurityAlerts(Visitor $visitor, VisitLog $visit): void
    {
        // Check for automatic security alerts
        if ($visitor->isOnWatchlist()) {
            $visit->alertSecurity(['watchlist_visitor']);
        }

        // Add more security check logic here
    }
}

