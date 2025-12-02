<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\VisitLog;
use App\Models\VisitorWatchlist;
use App\Models\SecurityAlert;
use App\Models\VisitorDevice;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class VisitorManagementService
{
    /**
     * Process visitor check-in with comprehensive security checks.
     */
    public function processCheckIn(array $visitorData, array $visitData = []): array
    {
        // Create or update visitor
        $visitor = $this->createOrUpdateVisitor($visitorData);

        // Perform security checks
        $securityCheck = $this->performSecurityChecks($visitor);
        
        if (!$securityCheck['allowed']) {
            return [
                'success' => false,
                'message' => $securityCheck['reason'],
                'visitor' => $visitor,
            ];
        }

        // Create visit log
        $visit = $this->createVisitLog($visitor, $visitData);

        // Process post-checkin actions
        $this->processPostCheckInActions($visitor, $visit);

        return [
            'success' => true,
            'message' => 'Check-in successful',
            'visitor' => $visitor->fresh(),
            'visit' => $visit->fresh(),
            'warnings' => $securityCheck['warnings'] ?? [],
        ];
    }

    /**
     * Create or update visitor record.
     */
    protected function createOrUpdateVisitor(array $data): Visitor
    {
        $visitor = null;

        // Try to find existing visitor
        if (!empty($data['email'])) {
            $visitor = Visitor::where('email', $data['email'])->first();
        } elseif (!empty($data['phone'])) {
            $visitor = Visitor::where('phone', $data['phone'])->first();
        } elseif (!empty($data['id_value'])) {
            $visitor = Visitor::where('id_value', $data['id_value'])->first();
        }

        if ($visitor) {
            // Update existing visitor with new information
            $visitor->update(array_filter([
                'name' => $data['name'] ?? $visitor->name,
                'phone' => $data['phone'] ?? $visitor->phone,
                'company' => $data['company'] ?? $visitor->company,
                'id_type' => $data['id_type'] ?? $visitor->id_type,
                'id_value' => $data['id_value'] ?? $visitor->id_value,
                'temperature' => $data['temperature'] ?? null,
                'health_questions' => $data['health_questions'] ?? null,
                'health_screening_passed' => $this->evaluateHealthScreening($data),
            ]));
        } else {
            // Create new visitor
            $visitor = Visitor::create([
                'name' => $data['name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'company' => $data['company'] ?? null,
                'id_type' => $data['id_type'] ?? null,
                'id_value' => $data['id_value'] ?? null,
                'source' => $data['source'] ?? 'kiosk',
                'purpose' => $data['purpose'] ?? null,
                'temperature' => $data['temperature'] ?? null,
                'health_questions' => $data['health_questions'] ?? null,
                'health_screening_passed' => $this->evaluateHealthScreening($data),
                'photo_path' => $this->processPhoto($data['photo'] ?? null),
            ]);
        }

        return $visitor;
    }

    /**
     * Perform comprehensive security checks.
     */
    protected function performSecurityChecks(Visitor $visitor): array
    {
        $warnings = [];
        
        // Check watchlist
        $watchlistEntry = VisitorWatchlist::checkVisitor($visitor);
        if ($watchlistEntry) {
            $visitor->update(['on_watchlist' => true]);

            if ($watchlistEntry->shouldAutoDeny()) {
                return [
                    'allowed' => false,
                    'reason' => 'Visitor is on security watchlist and marked for auto-deny.',
                ];
            }

            if ($watchlistEntry->shouldAlertOnEntry()) {
                SecurityAlert::createWatchlistAlert($visitor, $watchlistEntry);
                $warnings[] = 'Visitor is on security watchlist - alert has been triggered.';
            }
        }

        // Check visitor status
        if ($visitor->status === 'blocked') {
            return [
                'allowed' => false,
                'reason' => 'Visitor is blocked from entry.',
            ];
        }

        if ($visitor->status === 'pending_approval') {
            return [
                'allowed' => false,
                'reason' => 'Visitor requires approval before entry.',
            ];
        }

        // Check health screening
        if (!$visitor->passedHealthScreening()) {
            return [
                'allowed' => false,
                'reason' => 'Health screening requirements not met.',
            ];
        }

        // Check background check requirements
        if ($visitor->requiresBackgroundCheck()) {
            return [
                'allowed' => false,
                'reason' => 'Background check required and not completed.',
            ];
        }

        // Check for multiple active visits
        if ($visitor->isCurrentlyVisiting()) {
            $warnings[] = 'Visitor has an active visit in progress.';
        }

        return [
            'allowed' => true,
            'warnings' => $warnings,
        ];
    }

    /**
     * Create visit log entry.
     */
    protected function createVisitLog(Visitor $visitor, array $data): VisitLog
    {
        return VisitLog::create([
            'visitor_id' => $visitor->id,
            'host_id' => $data['host_id'] ?? null,
            'check_in_at' => now(),
            'source' => $data['source'] ?? 'kiosk',
            'external_id' => $data['external_id'] ?? null,
            'visit_type' => $data['visit_type'] ?? 'regular',
            'location' => $data['location'] ?? null,
            'expected_checkout_at' => isset($data['expected_checkout_at']) 
                ? now()->parse($data['expected_checkout_at']) 
                : null,
            'security_items' => $data['security_items'] ?? null,
            'nda_signed' => $data['nda_required'] ?? false,
            'entry_method' => $data['entry_method'] ?? 'manual',
            'device_id' => $data['device_id'] ?? null,
            'device_data' => $data['device_data'] ?? null,
            'entry_photo_path' => $this->processPhoto($data['entry_photo'] ?? null),
        ]);
    }

    /**
     * Process post check-in actions.
     */
    protected function processPostCheckInActions(Visitor $visitor, VisitLog $visit): void
    {
        // Notify host
        $visit->notifyHost();

        // Update device heartbeat if applicable
        if ($visit->device_id) {
            $device = VisitorDevice::where('device_id', $visit->device_id)->first();
            $device?->updateHeartbeat();
        }

        // Schedule overstay check if expected checkout time is set
        if ($visit->expected_checkout_at) {
            // You could dispatch a job here to check for overstay later
            // dispatch(new CheckVisitorOverstayJob($visit))->delay($visit->expected_checkout_at);
        }
    }

    /**
     * Process visitor checkout.
     */
    public function processCheckOut(VisitLog $visit, array $data = []): array
    {
        if ($visit->isCompleted()) {
            return [
                'success' => false,
                'message' => 'Visitor is already checked out.',
            ];
        }

        $visit->update([
            'check_out_at' => now(),
            'checkout_reason' => $data['checkout_reason'] ?? null,
            'exit_photo_path' => $this->processPhoto($data['exit_photo'] ?? null),
            'visitor_rating' => $data['visitor_rating'] ?? null,
            'visitor_feedback' => $data['visitor_feedback'] ?? null,
            'host_rating' => $data['host_rating'] ?? null,
            'host_feedback' => $data['host_feedback'] ?? null,
            'safety_incidents' => $data['safety_incidents'] ?? null,
        ]);

        // Mark data as processed for compliance
        $visit->markDataProcessed();

        return [
            'success' => true,
            'message' => 'Check-out successful',
            'visit' => $visit->fresh(),
            'duration' => $visit->getDurationHuman(),
        ];
    }

    /**
     * Check and alert for visitor overstays.
     */
    public function checkOverstays(): array
    {
        $overstayedVisits = VisitLog::active()
            ->whereNotNull('expected_checkout_at')
            ->where('expected_checkout_at', '<', now())
            ->where('overstayed', false)
            ->get();

        $alertsCreated = 0;

        foreach ($overstayedVisits as $visit) {
            $visit->checkForOverstay();
            $alertsCreated++;
        }

        return [
            'checked' => $overstayedVisits->count(),
            'alerts_created' => $alertsCreated,
        ];
    }

    /**
     * Generate visitor capacity report.
     */
    public function getCapacityReport(): array
    {
        $currentVisitors = VisitLog::active()->count();
        $todayCheckins = VisitLog::today()->count();
        $todayCheckouts = VisitLog::today()->completed()->count();
        
        return [
            'current_visitors' => $currentVisitors,
            'today_checkins' => $todayCheckins,
            'today_checkouts' => $todayCheckouts,
            'peak_occupancy_today' => $todayCheckins - $todayCheckouts + $currentVisitors,
        ];
    }

    /**
     * Evaluate health screening based on data.
     */
    protected function evaluateHealthScreening(array $data): bool
    {
        // Temperature check
        if (isset($data['temperature']) && $data['temperature'] > 37.5) {
            return false;
        }

        // Additional health questions can be evaluated here
        if (isset($data['health_questions']) && is_array($data['health_questions'])) {
            // Example: Check if they answered "yes" to any concerning questions
            foreach ($data['health_questions'] as $question => $answer) {
                if (str_contains($question, 'symptoms') && $answer === 'yes') {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Process and store photo uploads.
     */
    protected function processPhoto(?UploadedFile $photo): ?string
    {
        if (!$photo || !$photo->isValid()) {
            return null;
        }

        try {
            return $photo->store('visitor-photos', 'public');
        } catch (\Exception $e) {
            // Log error but don't fail the operation
            return null;
        }
    }

    /**
     * Device integration for IoT systems.
     */
    public function registerDeviceCheckIn(string $deviceId, array $visitorData, array $deviceData = []): array
    {
        $device = VisitorDevice::where('device_id', $deviceId)->first();
        
        if (!$device || !$device->isActive()) {
            return [
                'success' => false,
                'message' => 'Device not registered or inactive.',
            ];
        }

        $visitData = [
            'device_id' => $deviceId,
            'device_data' => $deviceData,
            'entry_method' => $device->device_type,
            'source' => 'iot_device',
        ];

        $device->updateHeartbeat();
        
        return $this->processCheckIn($visitorData, $visitData);
    }
}