<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\VisitorInvitation;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VisitorInvitationController extends Controller
{
    /**
     * Create a new visitor invitation.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', VisitorInvitation::class);

        $data = $request->validate([
            'visitor_name' => 'required|string|max:255',
            'visitor_email' => 'nullable|email|max:255',
            'visitor_phone' => 'nullable|string|max:50',
            'visitor_company' => 'nullable|string|max:255',
            'host_id' => 'required|exists:users,id',
            'purpose' => 'required|string|max:500',
            'valid_from' => 'required|date|after_or_equal:today',
            'valid_until' => 'required|date|after:valid_from',
            'access_areas' => 'nullable|array',
            'special_instructions' => 'nullable|string|max:1000',
            'escort_required' => 'nullable|boolean',
            'required_documents' => 'nullable|array',
        ]);

        $invitation = VisitorInvitation::create([
            ...$data,
            'invited_by' => Auth::id(),
            'escort_required' => $data['escort_required'] ?? false,
        ]);

        // Send invitation email if email provided
        if ($invitation->visitor_email) {
            // You can implement email notification here
            // Mail::to($invitation->visitor_email)->send(new VisitorInvitationMail($invitation));
        }

        return response()->json([
            'message' => 'Invitation created successfully',
            'invitation' => $invitation->fresh(['host', 'invitedBy']),
        ], 201);
    }

    /**
     * List invitations with filtering.
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitorInvitation::class);

        $query = VisitorInvitation::with(['host', 'invitedBy', 'visitLog'])
            ->latest('created_at');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->query('status'));
        }

        if ($request->filled('host_id')) {
            $query->where('host_id', $request->query('host_id'));
        }

        if ($request->filled('invited_by')) {
            $query->where('invited_by', $request->query('invited_by'));
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->whereBetween('valid_from', [
                $request->query('date_from'),
                $request->query('date_to'),
            ]);
        }

        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where(function ($q) use ($search) {
                $q->where('visitor_name', 'like', "%{$search}%")
                  ->orWhere('visitor_email', 'like', "%{$search}%")
                  ->orWhere('visitor_company', 'like', "%{$search}%")
                  ->orWhere('purpose', 'like', "%{$search}%");
            });
        }

        $perPage = min((int) $request->query('per_page', 25), 100);
        $invitations = $query->paginate($perPage);

        return response()->json($invitations);
    }

    /**
     * Show a specific invitation.
     */
    public function show(VisitorInvitation $invitation): JsonResponse
    {
        $this->authorize('view', $invitation);

        return response()->json($invitation->load(['host', 'invitedBy', 'visitLog']));
    }

    /**
     * Update an invitation.
     */
    public function update(Request $request, VisitorInvitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        if ($invitation->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Cannot update invitation that has been used or cancelled.']
            ]);
        }

        $data = $request->validate([
            'visitor_name' => 'sometimes|string|max:255',
            'visitor_email' => 'sometimes|nullable|email|max:255',
            'visitor_phone' => 'sometimes|nullable|string|max:50',
            'visitor_company' => 'sometimes|nullable|string|max:255',
            'host_id' => 'sometimes|exists:users,id',
            'purpose' => 'sometimes|string|max:500',
            'valid_from' => 'sometimes|date|after_or_equal:today',
            'valid_until' => 'sometimes|date|after:valid_from',
            'access_areas' => 'sometimes|nullable|array',
            'special_instructions' => 'sometimes|nullable|string|max:1000',
            'escort_required' => 'sometimes|boolean',
            'required_documents' => 'sometimes|nullable|array',
        ]);

        $invitation->update($data);

        return response()->json([
            'message' => 'Invitation updated successfully',
            'invitation' => $invitation->fresh(['host', 'invitedBy']),
        ]);
    }

    /**
     * Cancel an invitation.
     */
    public function cancel(VisitorInvitation $invitation): JsonResponse
    {
        $this->authorize('delete', $invitation);

        if ($invitation->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => ['Cannot cancel invitation that has been used or already cancelled.']
            ]);
        }

        $invitation->cancel();

        return response()->json([
            'message' => 'Invitation cancelled successfully',
            'invitation' => $invitation->fresh(),
        ]);
    }

    /**
     * Extend invitation validity.
     */
    public function extend(Request $request, VisitorInvitation $invitation): JsonResponse
    {
        $this->authorize('update', $invitation);

        $data = $request->validate([
            'valid_until' => 'required|date|after:' . $invitation->valid_from,
        ]);

        $invitation->extend(new \DateTime($data['valid_until']));

        return response()->json([
            'message' => 'Invitation extended successfully',
            'invitation' => $invitation->fresh(),
        ]);
    }

    /**
     * Validate invitation code (for kiosks).
     */
    public function validate(Request $request): JsonResponse
    {
        $data = $request->validate([
            'invitation_code' => 'required|string',
        ]);

        $invitation = VisitorInvitation::where('invitation_code', $data['invitation_code'])
            ->with(['host'])
            ->first();

        if (!$invitation) {
            return response()->json([
                'valid' => false,
                'message' => 'Invitation not found',
            ], 404);
        }

        if (!$invitation->isValid()) {
            return response()->json([
                'valid' => false,
                'message' => 'Invitation is expired or already used',
                'invitation' => $invitation,
            ], 400);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Invitation is valid',
            'invitation' => [
                'id' => $invitation->id,
                'visitor_name' => $invitation->visitor_name,
                'visitor_email' => $invitation->visitor_email,
                'visitor_phone' => $invitation->visitor_phone,
                'visitor_company' => $invitation->visitor_company,
                'host' => [
                    'id' => $invitation->host->id,
                    'name' => $invitation->host->name,
                ],
                'purpose' => $invitation->purpose,
                'valid_until' => $invitation->valid_until,
                'access_areas' => $invitation->access_areas,
                'special_instructions' => $invitation->special_instructions,
                'escort_required' => $invitation->escort_required,
                'required_documents' => $invitation->required_documents,
            ],
        ]);
    }

    /**
     * Get invitation statistics.
     */
    public function stats(Request $request): JsonResponse
    {
        $this->authorize('viewAny', VisitorInvitation::class);

        $stats = [
            'total_invitations' => VisitorInvitation::count(),
            'pending_invitations' => VisitorInvitation::where('status', 'pending')->count(),
            'used_invitations' => VisitorInvitation::where('status', 'used')->count(),
            'expired_invitations' => VisitorInvitation::expired()->count(),
            'invitations_this_month' => VisitorInvitation::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'upcoming_visits' => VisitorInvitation::where('status', 'pending')
                ->where('valid_from', '<=', now()->addDays(7))
                ->count(),
        ];

        // Recent activity
        $recentInvitations = VisitorInvitation::with(['host', 'invitedBy'])
            ->latest('created_at')
            ->limit(10)
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'visitor_name' => $invitation->visitor_name,
                    'host_name' => $invitation->host->name,
                    'invited_by' => $invitation->invitedBy->name,
                    'status' => $invitation->status,
                    'valid_from' => $invitation->valid_from,
                    'valid_until' => $invitation->valid_until,
                    'created_at' => $invitation->created_at,
                ];
            });

        return response()->json([
            'stats' => $stats,
            'recent_invitations' => $recentInvitations,
        ]);
    }
}