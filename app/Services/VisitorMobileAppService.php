<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\VisitLog;
use App\Models\VisitorInvitation;
use App\Models\VisitorDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Exception;

class VisitorMobileAppService
{
    /**
     * Generate QR code for visitor check-in
     */
    public function generateVisitorQRCode(Visitor $visitor): string
    {
        $data = [
            'visitor_id' => $visitor->id,
            'visitor_code' => $visitor->visitor_code,
            'name' => $visitor->name,
            'phone' => $visitor->phone,
            'expires_at' => now()->addMinutes(15)->toISOString(),
            'hash' => hash('sha256', $visitor->id . $visitor->visitor_code . config('app.key'))
        ];

        return QrCode::size(300)->generate(json_encode($data));
    }

    /**
     * Process QR code scan for check-in
     */
    public function processQRCodeScan(array $qrData, $deviceId = null): array
    {
        try {
            // Validate QR code structure
            $requiredFields = ['visitor_id', 'visitor_code', 'hash', 'expires_at'];
            foreach ($requiredFields as $field) {
                if (!isset($qrData[$field])) {
                    throw new Exception("Invalid QR code: Missing {$field}");
                }
            }

            // Check if QR code has expired
            if (Carbon::parse($qrData['expires_at'])->isPast()) {
                throw new Exception('QR code has expired. Please generate a new one.');
            }

            // Find visitor
            $visitor = Visitor::where('id', $qrData['visitor_id'])
                            ->where('visitor_code', $qrData['visitor_code'])
                            ->first();

            if (!$visitor) {
                throw new Exception('Visitor not found');
            }

            // Validate hash
            $expectedHash = hash('sha256', $visitor->id . $visitor->visitor_code . config('app.key'));
            if ($qrData['hash'] !== $expectedHash) {
                throw new Exception('Invalid QR code: Security verification failed');
            }

            // Check visitor status
            if (!$visitor->is_approved) {
                throw new Exception('Visitor is not approved for entry');
            }

            // Check if visitor is already checked in
            $existingVisit = VisitLog::where('visitor_id', $visitor->id)
                                   ->whereNull('checked_out_at')
                                   ->first();

            if ($existingVisit) {
                return [
                    'success' => false,
                    'message' => 'Visitor is already checked in',
                    'visitor' => $visitor->only(['name', 'phone', 'company']),
                    'visit_log' => $existingVisit
                ];
            }

            // Create visit log
            $visitLog = VisitLog::create([
                'visitor_id' => $visitor->id,
                'purpose_of_visit' => $visitor->purpose_of_visit,
                'host_employee_id' => $visitor->host_employee_id,
                'checked_in_at' => now(),
                'check_in_method' => 'qr_code',
                'device_id' => $deviceId,
                'temperature' => null, // Can be added by mobile app
                'health_declaration' => $visitor->health_declaration ?? true
            ]);

            // Update visitor last visit
            $visitor->update(['last_visit_at' => now()]);

            return [
                'success' => true,
                'message' => 'Check-in successful',
                'visitor' => $visitor->only(['name', 'phone', 'company', 'visitor_code']),
                'visit_log' => $visitLog,
                'instructions' => $this->getVisitorInstructions($visitor)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error_code' => 'QR_SCAN_ERROR'
            ];
        }
    }

    /**
     * Send push notification
     */
    public function sendPushNotification(string $token, string $title, string $body, array $data = []): bool
    {
        try {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                    'sound' => 'default'
                ],
                'data' => $data,
                'priority' => 'high'
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . config('services.firebase.server_key'),
                'Content-Type' => 'application/json'
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            return $response->successful();

        } catch (Exception $e) {
            logger()->error('Push notification failed', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Register mobile device for notifications
     */
    public function registerMobileDevice(array $deviceData, $userId): array
    {
        try {
            $device = VisitorDevice::updateOrCreate(
                [
                    'device_identifier' => $deviceData['device_identifier'],
                    'user_id' => $userId
                ],
                [
                    'device_type' => $deviceData['device_type'] ?? 'mobile',
                    'device_name' => $deviceData['device_name'] ?? 'Mobile App',
                    'push_token' => $deviceData['push_token'] ?? null,
                    'app_version' => $deviceData['app_version'] ?? null,
                    'os_version' => $deviceData['os_version'] ?? null,
                    'is_active' => true,
                    'last_heartbeat' => now()
                ]
            );

            return [
                'success' => true,
                'device_id' => $device->id,
                'message' => 'Device registered successfully'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Device registration failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process photo capture and verification
     */
    public function processPhotoCapture(Request $request, Visitor $visitor): array
    {
        try {
            $photo = $request->file('photo');
            
            if (!$photo || !$photo->isValid()) {
                throw new Exception('Invalid photo upload');
            }

            // Validate photo
            $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($photo->getMimeType(), $allowedMimes)) {
                throw new Exception('Invalid photo format. Only JPEG and PNG allowed.');
            }

            if ($photo->getSize() > 5 * 1024 * 1024) { // 5MB
                throw new Exception('Photo size too large. Maximum 5MB allowed.');
            }

            // Store photo
            $filename = 'visitors/photos/' . $visitor->id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $path = $photo->storeAs('public', $filename);

            // Update visitor
            $visitor->update([
                'photo_path' => $filename,
                'photo_uploaded_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Photo uploaded successfully',
                'photo_url' => Storage::url($filename)
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get visitor check-in status
     */
    public function getVisitorStatus(Visitor $visitor): array
    {
        $activeVisit = VisitLog::where('visitor_id', $visitor->id)
                              ->whereNull('checked_out_at')
                              ->with(['visitor', 'hostEmployee'])
                              ->first();

        return [
            'visitor' => $visitor->only(['id', 'name', 'visitor_code', 'is_approved']),
            'is_checked_in' => !is_null($activeVisit),
            'active_visit' => $activeVisit,
            'check_in_time' => $activeVisit?->checked_in_at,
            'duration' => $activeVisit ? now()->diffForHumans($activeVisit->checked_in_at) : null
        ];
    }

    /**
     * Process mobile check-out
     */
    public function mobileCheckOut(Visitor $visitor, array $data = []): array
    {
        try {
            $activeVisit = VisitLog::where('visitor_id', $visitor->id)
                                  ->whereNull('checked_out_at')
                                  ->first();

            if (!$activeVisit) {
                throw new Exception('No active visit found for check-out');
            }

            // Update visit log
            $activeVisit->update([
                'checked_out_at' => now(),
                'checkout_method' => 'mobile_app',
                'visitor_rating' => $data['rating'] ?? null,
                'visit_feedback' => $data['feedback'] ?? null,
                'checkout_notes' => $data['notes'] ?? null
            ]);

            // Calculate visit duration
            $duration = $activeVisit->checked_out_at->diff($activeVisit->checked_in_at);

            return [
                'success' => true,
                'message' => 'Check-out successful',
                'visit_duration' => $duration->format('%H:%I:%S'),
                'visit_summary' => [
                    'check_in' => $activeVisit->checked_in_at->format('Y-m-d H:i:s'),
                    'check_out' => $activeVisit->checked_out_at->format('Y-m-d H:i:s'),
                    'purpose' => $activeVisit->purpose_of_visit,
                    'host' => $activeVisit->hostEmployee?->name
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get visitor instructions based on visit purpose and location
     */
    private function getVisitorInstructions(Visitor $visitor): array
    {
        $instructions = [
            'general' => [
                'Please wear your visitor badge at all times',
                'Stay with your host or in designated visitor areas',
                'Follow all safety protocols and emergency procedures'
            ]
        ];

        // Add purpose-specific instructions
        switch (strtolower($visitor->purpose_of_visit)) {
            case 'meeting':
                $instructions['specific'] = [
                    'Proceed to the conference room as directed',
                    'Keep mobile devices on silent during meetings'
                ];
                break;
            case 'interview':
                $instructions['specific'] = [
                    'Report to HR reception',
                    'Bring required documents and ID proof'
                ];
                break;
            case 'delivery':
                $instructions['specific'] = [
                    'Proceed to receiving dock',
                    'Obtain delivery confirmation before leaving'
                ];
                break;
            default:
                $instructions['specific'] = [
                    'Follow your host\'s guidance',
                    'Ask for assistance if needed'
                ];
        }

        return $instructions;
    }

    /**
     * Get nearby visitors for contact tracing
     */
    public function getNearbyVisitors(Visitor $visitor, int $timeRangeMinutes = 60): array
    {
        $activeVisit = VisitLog::where('visitor_id', $visitor->id)
                              ->whereNull('checked_out_at')
                              ->first();

        if (!$activeVisit) {
            return [];
        }

        $nearbyVisits = VisitLog::where('id', '!=', $activeVisit->id)
                               ->where(function ($query) use ($activeVisit, $timeRangeMinutes) {
                                   $timeWindow = now()->subMinutes($timeRangeMinutes);
                                   $query->where('checked_in_at', '>=', $timeWindow)
                                         ->whereNull('checked_out_at');
                               })
                               ->with(['visitor'])
                               ->get();

        return $nearbyVisits->map(function ($visit) {
            return [
                'visitor_id' => $visit->visitor->id,
                'name' => $visit->visitor->name,
                'company' => $visit->visitor->company,
                'check_in_time' => $visit->checked_in_at,
                'contact_phone' => substr($visit->visitor->phone, 0, 4) . '****' . substr($visit->visitor->phone, -2)
            ];
        })->toArray();
    }
}