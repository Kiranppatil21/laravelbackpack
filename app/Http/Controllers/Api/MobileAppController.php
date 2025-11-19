<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Visitor;
use App\Models\VisitLog;
use App\Models\VisitorInvitation;
use App\Services\VisitorMobileAppService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class MobileAppController extends Controller
{
    protected VisitorMobileAppService $mobileAppService;

    public function __construct(VisitorMobileAppService $mobileAppService)
    {
        $this->mobileAppService = $mobileAppService;
    }

    /**
     * Register mobile device for push notifications
     */
    public function registerDevice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'device_identifier' => 'required|string|max:255',
            'device_type' => 'required|in:ios,android',
            'device_name' => 'nullable|string|max:255',
            'push_token' => 'required|string',
            'app_version' => 'nullable|string|max:50',
            'os_version' => 'nullable|string|max:50'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->mobileAppService->registerMobileDevice(
            $request->all(),
            Auth::id()
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Generate QR code for visitor
     */
    public function generateQRCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_code' => 'required|string|exists:visitors,visitor_code'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid visitor code',
                'errors' => $validator->errors()
            ], 422);
        }

        $visitor = Visitor::where('visitor_code', $request->visitor_code)->first();

        if (!$visitor->is_approved) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor is not approved for entry'
            ], 403);
        }

        $qrCode = $this->mobileAppService->generateVisitorQRCode($visitor);

        return response()->json([
            'success' => true,
            'qr_code' => base64_encode($qrCode),
            'expires_at' => now()->addMinutes(15)->toISOString(),
            'visitor' => $visitor->only(['name', 'company', 'purpose_of_visit'])
        ]);
    }

    /**
     * Process QR code scan for check-in
     */
    public function scanQRCode(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'qr_data' => 'required|json',
            'device_id' => 'nullable|exists:visitor_devices,id',
            'location_data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request data',
                'errors' => $validator->errors()
            ], 422);
        }

        $qrData = json_decode($request->qr_data, true);
        $result = $this->mobileAppService->processQRCodeScan(
            $qrData,
            $request->device_id
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Upload visitor photo
     */
    public function uploadPhoto(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_code' => 'required|string|exists:visitors,visitor_code',
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid photo upload',
                'errors' => $validator->errors()
            ], 422);
        }

        $visitor = Visitor::where('visitor_code', $request->visitor_code)->first();
        $result = $this->mobileAppService->processPhotoCapture($request, $visitor);

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get visitor status and active visits
     */
    public function getVisitorStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_code' => 'required|string|exists:visitors,visitor_code'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid visitor code'
            ], 422);
        }

        $visitor = Visitor::where('visitor_code', $request->visitor_code)->first();
        $status = $this->mobileAppService->getVisitorStatus($visitor);

        return response()->json([
            'success' => true,
            'data' => $status
        ]);
    }

    /**
     * Check out visitor via mobile app
     */
    public function checkOut(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_code' => 'required|string|exists:visitors,visitor_code',
            'rating' => 'nullable|integer|min:1|max:5',
            'feedback' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid checkout data',
                'errors' => $validator->errors()
            ], 422);
        }

        $visitor = Visitor::where('visitor_code', $request->visitor_code)->first();
        $result = $this->mobileAppService->mobileCheckOut(
            $visitor,
            $request->only(['rating', 'feedback', 'notes'])
        );

        return response()->json($result, $result['success'] ? 200 : 400);
    }

    /**
     * Get visitor's visit history
     */
    public function getVisitHistory(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_code' => 'required|string|exists:visitors,visitor_code',
            'limit' => 'nullable|integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid request'
            ], 422);
        }

        $visitor = Visitor::where('visitor_code', $request->visitor_code)->first();
        $limit = $request->get('limit', 10);

        $visits = VisitLog::where('visitor_id', $visitor->id)
                         ->with(['hostEmployee:id,name'])
                         ->orderBy('checked_in_at', 'desc')
                         ->limit($limit)
                         ->get()
                         ->map(function ($visit) {
                             return [
                                 'id' => $visit->id,
                                 'purpose' => $visit->purpose_of_visit,
                                 'host' => $visit->hostEmployee?->name,
                                 'checked_in_at' => $visit->checked_in_at,
                                 'checked_out_at' => $visit->checked_out_at,
                                 'duration' => $visit->checked_out_at ? 
                                     $visit->checked_out_at->diffForHumans($visit->checked_in_at, true) : 
                                     'Still checked in',
                                 'rating' => $visit->visitor_rating
                             ];
                         });

        return response()->json([
            'success' => true,
            'visits' => $visits,
            'visitor' => $visitor->only(['name', 'company', 'total_visits'])
        ]);
    }

    /**
     * Get visitor invitations
     */
    public function getInvitations(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'email' => 'nullable|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid contact information'
            ], 422);
        }

        $query = VisitorInvitation::where('status', 'pending')
                                 ->where('valid_until', '>', now())
                                 ->where('visitor_phone', $request->phone);

        if ($request->email) {
            $query->orWhere('visitor_email', $request->email);
        }

        $invitations = $query->with(['invitedBy:id,name', 'hostEmployee:id,name'])
                            ->get()
                            ->map(function ($invitation) {
                                return [
                                    'id' => $invitation->id,
                                    'invitation_code' => $invitation->invitation_code,
                                    'visitor_name' => $invitation->visitor_name,
                                    'company' => $invitation->visitor_company,
                                    'purpose' => $invitation->purpose_of_visit,
                                    'scheduled_date' => $invitation->scheduled_date,
                                    'host' => $invitation->hostEmployee?->name,
                                    'invited_by' => $invitation->invitedBy?->name,
                                    'valid_until' => $invitation->valid_until,
                                    'special_instructions' => $invitation->special_instructions
                                ];
                            });

        return response()->json([
            'success' => true,
            'invitations' => $invitations
        ]);
    }

    /**
     * Accept invitation and create visitor profile
     */
    public function acceptInvitation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invitation_code' => 'required|string|exists:visitor_invitations,invitation_code',
            'id_proof_type' => 'required|in:national_id,passport,driving_license',
            'id_proof_number' => 'required|string|max:50',
            'emergency_contact' => 'required|string|max:15',
            'health_declaration' => 'required|boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $invitation = VisitorInvitation::where('invitation_code', $request->invitation_code)
                                      ->where('status', 'pending')
                                      ->where('valid_until', '>', now())
                                      ->first();

        if (!$invitation) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired invitation'
            ], 404);
        }

        // Create or update visitor
        $visitor = Visitor::updateOrCreate(
            [
                'phone' => $invitation->visitor_phone
            ],
            [
                'name' => $invitation->visitor_name,
                'email' => $invitation->visitor_email,
                'company' => $invitation->visitor_company,
                'purpose_of_visit' => $invitation->purpose_of_visit,
                'host_employee_id' => $invitation->host_employee_id,
                'id_proof_type' => $request->id_proof_type,
                'id_proof_number' => $request->id_proof_number,
                'emergency_contact' => $request->emergency_contact,
                'health_declaration' => $request->health_declaration,
                'is_approved' => $invitation->requires_approval ? false : true,
                'invited_by_id' => $invitation->invited_by_id,
                'invitation_id' => $invitation->id
            ]
        );

        // Update invitation status
        $invitation->update([
            'status' => 'accepted',
            'accepted_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Invitation accepted successfully',
            'visitor_code' => $visitor->visitor_code,
            'requires_approval' => !$visitor->is_approved,
            'approval_message' => !$visitor->is_approved ? 
                'Your visit request is pending approval. You will be notified once approved.' : 
                'You are approved for entry. Generate QR code when ready to visit.'
        ]);
    }

    /**
     * Send test push notification
     */
    public function sendTestNotification(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'push_token' => 'required|string',
            'title' => 'nullable|string|max:100',
            'message' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $title = $request->get('title', 'Test Notification');
        $message = $request->get('message', 'This is a test notification from the Visitor Management System');

        $sent = $this->mobileAppService->sendPushNotification(
            $request->push_token,
            $title,
            $message,
            ['type' => 'test']
        );

        return response()->json([
            'success' => $sent,
            'message' => $sent ? 'Notification sent successfully' : 'Failed to send notification'
        ]);
    }

    /**
     * Get app configuration and settings
     */
    public function getAppConfig(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'config' => [
                'app_version' => config('app.version', '1.0.0'),
                'api_version' => 'v1',
                'features' => [
                    'qr_checkin' => true,
                    'photo_upload' => true,
                    'push_notifications' => true,
                    'visit_history' => true,
                    'feedback_rating' => true,
                    'health_screening' => true,
                    'contact_tracing' => true
                ],
                'settings' => [
                    'qr_code_expiry_minutes' => 15,
                    'photo_max_size_mb' => 5,
                    'supported_id_types' => [
                        'national_id' => 'National ID',
                        'passport' => 'Passport',
                        'driving_license' => 'Driving License'
                    ],
                    'visit_purposes' => [
                        'meeting' => 'Business Meeting',
                        'interview' => 'Job Interview',
                        'delivery' => 'Delivery',
                        'maintenance' => 'Maintenance',
                        'event' => 'Event/Conference',
                        'other' => 'Other'
                    ]
                ]
            ]
        ]);
    }

    /**
     * Emergency contact tracing
     */
    public function getContactTracing(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'visitor_code' => 'required|string|exists:visitors,visitor_code',
            'time_range_hours' => 'nullable|integer|min:1|max:168' // Max 1 week
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $visitor = Visitor::where('visitor_code', $request->visitor_code)->first();
        $timeRange = $request->get('time_range_hours', 24) * 60; // Convert to minutes

        $contacts = $this->mobileAppService->getNearbyVisitors($visitor, $timeRange);

        return response()->json([
            'success' => true,
            'contact_count' => count($contacts),
            'contacts' => $contacts,
            'time_range' => $timeRange . ' minutes',
            'disclaimer' => 'Contact information is anonymized for privacy protection'
        ]);
    }
}