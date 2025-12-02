<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\VisitorDevice;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class PushNotificationService
{
    /**
     * Send notification to visitor
     */
    public function notifyVisitor(Visitor $visitor, string $title, string $message, array $data = []): bool
    {
        $devices = VisitorDevice::where('user_id', $visitor->id)
                               ->where('is_active', true)
                               ->whereNotNull('push_token')
                               ->get();

        $sent = false;
        foreach ($devices as $device) {
            if ($this->sendNotification($device->push_token, $title, $message, $data)) {
                $sent = true;
            }
        }

        return $sent;
    }

    /**
     * Send notification to staff/employees
     */
    public function notifyStaff(array $userIds, string $title, string $message, array $data = []): int
    {
        $devices = VisitorDevice::whereIn('user_id', $userIds)
                               ->where('is_active', true)
                               ->whereNotNull('push_token')
                               ->get();

        $sentCount = 0;
        foreach ($devices as $device) {
            if ($this->sendNotification($device->push_token, $title, $message, $data)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Send individual push notification
     */
    private function sendNotification(string $token, string $title, string $message, array $data = []): bool
    {
        try {
            $payload = [
                'to' => $token,
                'notification' => [
                    'title' => $title,
                    'body' => $message,
                    'sound' => 'default',
                    'badge' => 1
                ],
                'data' => array_merge($data, [
                    'timestamp' => now()->toISOString(),
                    'app_version' => config('app.version', '1.0.0')
                ]),
                'priority' => 'high',
                'time_to_live' => 3600 // 1 hour
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . config('services.firebase.server_key'),
                'Content-Type' => 'application/json'
            ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $result = $response->json();
                return isset($result['success']) && $result['success'] > 0;
            }

            Log::warning('Push notification failed', [
                'response' => $response->body(),
                'status' => $response->status()
            ]);

            return false;

        } catch (Exception $e) {
            Log::error('Push notification exception', [
                'token' => substr($token, 0, 10) . '...',
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send visitor check-in notification to host
     */
    public function notifyHostOfVisitorArrival(Visitor $visitor): bool
    {
        if (!$visitor->host_employee_id) {
            return false;
        }

        $title = "Visitor Arrival";
        $message = "{$visitor->name} from {$visitor->company} has checked in for their appointment.";
        $data = [
            'type' => 'visitor_checkin',
            'visitor_id' => $visitor->id,
            'visitor_code' => $visitor->visitor_code,
            'visitor_name' => $visitor->name,
            'visitor_company' => $visitor->company
        ];

        return $this->notifyStaff([$visitor->host_employee_id], $title, $message, $data);
    }

    /**
     * Send approval notification to visitor
     */
    public function notifyVisitorApproval(Visitor $visitor, bool $approved): bool
    {
        $title = $approved ? "Visit Approved" : "Visit Declined";
        $message = $approved ? 
            "Your visit request has been approved. You can now generate a QR code for check-in." :
            "Your visit request has been declined. Please contact your host for more information.";
        
        $data = [
            'type' => 'approval_status',
            'visitor_id' => $visitor->id,
            'approved' => $approved,
            'visitor_code' => $visitor->visitor_code
        ];

        return $this->notifyVisitor($visitor, $title, $message, $data);
    }

    /**
     * Send security alert notification
     */
    public function sendSecurityAlert(string $alertType, string $message, array $additionalData = []): int
    {
        // Get security staff and admins
        $securityRoles = ['Super Admin', 'Agency Owner', 'Guard'];
        $users = User::whereHas('roles', function ($query) use ($securityRoles) {
            $query->whereIn('name', $securityRoles);
        })->pluck('id')->toArray();

        $title = "Security Alert";
        $data = array_merge([
            'type' => 'security_alert',
            'alert_type' => $alertType,
            'priority' => 'high'
        ], $additionalData);

        return $this->notifyStaff($users, $title, $message, $data);
    }

    /**
     * Send invitation notification
     */
    public function notifyVisitorInvitation(string $phone, string $email, string $visitorName, string $hostName): bool
    {
        // This would typically integrate with SMS service for phone notifications
        // For now, we'll focus on email-based notifications through existing Laravel mechanisms
        
        // If the visitor has the app installed and registered with this phone/email,
        // we can send a push notification
        $device = VisitorDevice::whereHas('user', function ($query) use ($phone, $email) {
            $query->where('phone', $phone)->orWhere('email', $email);
        })->where('is_active', true)->first();

        if ($device) {
            $title = "New Visit Invitation";
            $message = "You have been invited by {$hostName}. Open the app to view details.";
            $data = [
                'type' => 'invitation',
                'visitor_name' => $visitorName,
                'host_name' => $hostName
            ];

            return $this->sendNotification($device->push_token, $title, $message, $data);
        }

        return false;
    }

    /**
     * Send reminder notification for upcoming visit
     */
    public function sendVisitReminder(Visitor $visitor, int $minutesBefore = 30): bool
    {
        $title = "Visit Reminder";
        $message = "Your visit is scheduled in {$minutesBefore} minutes. Please prepare for check-in.";
        $data = [
            'type' => 'visit_reminder',
            'visitor_id' => $visitor->id,
            'minutes_before' => $minutesBefore,
            'host_name' => $visitor->hostEmployee?->name
        ];

        return $this->notifyVisitor($visitor, $title, $message, $data);
    }

    /**
     * Send checkout reminder for overdue visitors
     */
    public function sendCheckoutReminder(Visitor $visitor, int $hoursOverdue): bool
    {
        $title = "Check-out Reminder";
        $message = "Your visit appears to be complete. Please remember to check out using the app.";
        $data = [
            'type' => 'checkout_reminder',
            'visitor_id' => $visitor->id,
            'hours_overdue' => $hoursOverdue
        ];

        return $this->notifyVisitor($visitor, $title, $message, $data);
    }

    /**
     * Send emergency/evacuation notification
     */
    public function sendEmergencyNotification(string $emergencyType, string $instructions): int
    {
        // Send to all active devices (visitors and staff currently in building)
        $devices = VisitorDevice::where('is_active', true)
                               ->whereNotNull('push_token')
                               ->where('last_heartbeat', '>', now()->subMinutes(30))
                               ->get();

        $title = "EMERGENCY ALERT";
        $data = [
            'type' => 'emergency',
            'emergency_type' => $emergencyType,
            'priority' => 'critical'
        ];

        $sentCount = 0;
        foreach ($devices as $device) {
            if ($this->sendNotification($device->push_token, $title, $instructions, $data)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Send bulk notification to all visitors
     */
    public function broadcastToVisitors(string $title, string $message, array $data = []): int
    {
        $visitorDevices = VisitorDevice::whereHas('user', function ($query) {
            $query->whereNotNull('visitor_code'); // Assuming visitors have visitor_code
        })->where('is_active', true)->get();

        $sentCount = 0;
        foreach ($visitorDevices as $device) {
            if ($this->sendNotification($device->push_token, $title, $message, $data)) {
                $sentCount++;
            }
        }

        return $sentCount;
    }

    /**
     * Clean up expired device tokens
     */
    public function cleanupInactiveTokens(): int
    {
        $inactiveDevices = VisitorDevice::where('last_heartbeat', '<', now()->subDays(30))
                                       ->orWhere('is_active', false);

        $count = $inactiveDevices->count();
        $inactiveDevices->delete();

        Log::info("Cleaned up {$count} inactive device tokens");
        return $count;
    }

    /**
     * Test notification delivery
     */
    public function testNotification(string $token): array
    {
        $title = "Test Notification";
        $message = "This is a test notification to verify your device configuration.";
        $data = [
            'type' => 'test',
            'timestamp' => now()->toISOString()
        ];

        $sent = $this->sendNotification($token, $title, $message, $data);

        return [
            'success' => $sent,
            'message' => $sent ? 'Test notification sent successfully' : 'Failed to send test notification',
            'token_preview' => substr($token, 0, 10) . '...'
        ];
    }
}