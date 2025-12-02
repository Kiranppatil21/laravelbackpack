<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\VisitorInvitation;
use App\Models\SecurityAlert;
use App\Notifications\VisitorInvitationNotification;
use App\Notifications\SecurityAlertNotification;
use Illuminate\Support\Facades\Mail;

class VisitorApprovalWorkflow
{
    protected BackgroundCheckService $backgroundCheckService;

    public function __construct(BackgroundCheckService $backgroundCheckService)
    {
        $this->backgroundCheckService = $backgroundCheckService;
    }

    /**
     * Start the approval process for a visitor.
     */
    public function startApprovalProcess(Visitor $visitor, array $options = []): array
    {
        $steps = $this->determineApprovalSteps($visitor, $options);
        
        foreach ($steps as $step) {
            $result = $this->executeApprovalStep($visitor, $step);
            
            if (!$result['success']) {
                return [
                    'success' => false,
                    'message' => $result['message'],
                    'failed_step' => $step,
                ];
            }
        }

        return [
            'success' => true,
            'message' => 'Approval process completed',
            'steps_executed' => $steps,
        ];
    }

    /**
     * Determine which approval steps are required.
     */
    protected function determineApprovalSteps(Visitor $visitor, array $options): array
    {
        $steps = [];

        // ID Verification
        if ($this->requiresIdVerification($visitor, $options)) {
            $steps[] = 'id_verification';
        }

        // Background Check
        if ($this->requiresBackgroundCheck($visitor, $options)) {
            $steps[] = 'background_check';
        }

        // Health Screening
        if ($this->requiresHealthScreening($visitor, $options)) {
            $steps[] = 'health_screening';
        }

        // Manual Approval
        if ($this->requiresManualApproval($visitor, $options)) {
            $steps[] = 'manual_approval';
        }

        // Watchlist Check
        $steps[] = 'watchlist_check';

        return $steps;
    }

    /**
     * Execute a specific approval step.
     */
    protected function executeApprovalStep(Visitor $visitor, string $step): array
    {
        switch ($step) {
            case 'id_verification':
                return $this->performIdVerification($visitor);
                
            case 'background_check':
                return $this->initiateBackgroundCheck($visitor);
                
            case 'health_screening':
                return $this->performHealthScreening($visitor);
                
            case 'manual_approval':
                return $this->requestManualApproval($visitor);
                
            case 'watchlist_check':
                return $this->performWatchlistCheck($visitor);
                
            default:
                return [
                    'success' => false,
                    'message' => "Unknown approval step: {$step}",
                ];
        }
    }

    /**
     * Perform ID verification.
     */
    protected function performIdVerification(Visitor $visitor): array
    {
        // Basic ID verification logic
        if (empty($visitor->id_type) || empty($visitor->id_value)) {
            return [
                'success' => false,
                'message' => 'ID information is required for verification',
            ];
        }

        // You could integrate with an ID verification service here
        // For now, we'll mark as verified if basic info is present
        $visitor->update([
            'id_verified' => true,
            'id_verified_at' => now(),
        ]);

        return [
            'success' => true,
            'message' => 'ID verification completed',
        ];
    }

    /**
     * Initiate background check.
     */
    protected function initiateBackgroundCheck(Visitor $visitor): array
    {
        $result = $this->backgroundCheckService->requestBackgroundCheck($visitor);
        
        if (!$result['success']) {
            return $result;
        }

        return [
            'success' => true,
            'message' => 'Background check initiated',
            'check_id' => $result['check_id'],
        ];
    }

    /**
     * Perform health screening.
     */
    protected function performHealthScreening(Visitor $visitor): array
    {
        // Health screening logic
        $passed = $visitor->passedHealthScreening();
        
        if (!$passed) {
            return [
                'success' => false,
                'message' => 'Health screening requirements not met',
            ];
        }

        return [
            'success' => true,
            'message' => 'Health screening passed',
        ];
    }

    /**
     * Request manual approval.
     */
    protected function requestManualApproval(Visitor $visitor): array
    {
        $visitor->update(['status' => 'pending_approval']);

        // Notify administrators
        $admins = \App\Models\User::role(['Super Admin', 'Agency', 'HR'])->get();
        
        foreach ($admins as $admin) {
            // You could send a notification here
            // $admin->notify(new VisitorApprovalRequiredNotification($visitor));
        }

        return [
            'success' => true,
            'message' => 'Manual approval requested',
        ];
    }

    /**
     * Perform watchlist check.
     */
    protected function performWatchlistCheck(Visitor $visitor): array
    {
        $watchlistEntry = \App\Models\VisitorWatchlist::checkVisitor($visitor);
        
        if ($watchlistEntry) {
            $visitor->update(['on_watchlist' => true]);
            
            if ($watchlistEntry->shouldAutoDeny()) {
                return [
                    'success' => false,
                    'message' => 'Visitor is on security watchlist and marked for auto-deny',
                ];
            }

            // Create alert but allow approval to continue
            SecurityAlert::createWatchlistAlert($visitor, $watchlistEntry);
        }

        return [
            'success' => true,
            'message' => 'Watchlist check completed',
        ];
    }

    /**
     * Auto-approve visitor based on criteria.
     */
    public function autoApprove(Visitor $visitor): bool
    {
        // Auto-approval criteria
        $criteria = [
            'has_valid_invitation' => $this->hasValidInvitation($visitor),
            'id_verified' => $visitor->id_verified,
            'health_screening_passed' => $visitor->health_screening_passed,
            'not_on_watchlist' => !$visitor->isOnWatchlist(),
            'background_check_passed' => $visitor->background_check_status === 'passed',
        ];

        // Determine if auto-approval is allowed
        $autoApprovalRules = config('visitor.auto_approval_rules', []);
        $requiresManualApproval = false;

        foreach ($autoApprovalRules as $rule => $required) {
            if ($required && !($criteria[$rule] ?? false)) {
                $requiresManualApproval = true;
                break;
            }
        }

        if (!$requiresManualApproval) {
            $visitor->approve(\App\Models\User::where('email', 'system@example.com')->first());
            return true;
        }

        return false;
    }

    /**
     * Manually approve visitor.
     */
    public function manualApprove(Visitor $visitor, \App\Models\User $approver, string $notes = null): bool
    {
        $visitor->approve($approver);
        
        if ($notes) {
            $visitor->update([
                'notes' => ($visitor->notes ? $visitor->notes . "\n" : '') . "Manually approved by {$approver->name}: {$notes}"
            ]);
        }

        return true;
    }

    /**
     * Reject visitor approval.
     */
    public function rejectApproval(Visitor $visitor, \App\Models\User $rejector, string $reason): bool
    {
        $visitor->update([
            'status' => 'blocked',
            'notes' => ($visitor->notes ? $visitor->notes . "\n" : '') . "Approval rejected by {$rejector->name}: {$reason}"
        ]);

        // Create security alert
        SecurityAlert::create([
            'type' => 'approval_rejected',
            'severity' => 'medium',
            'title' => 'Visitor Approval Rejected',
            'description' => "Visitor {$visitor->name} approval was rejected by {$rejector->name}. Reason: {$reason}",
            'visitor_id' => $visitor->id,
            'occurred_at' => now(),
        ]);

        return true;
    }

    // Helper methods for determining requirements

    protected function requiresIdVerification(Visitor $visitor, array $options): bool
    {
        return !$visitor->id_verified && 
               ($options['require_id_verification'] ?? config('visitor.require_id_verification', true));
    }

    protected function requiresBackgroundCheck(Visitor $visitor, array $options): bool
    {
        return $visitor->background_check_required || 
               ($options['require_background_check'] ?? false);
    }

    protected function requiresHealthScreening(Visitor $visitor, array $options): bool
    {
        return $options['require_health_screening'] ?? config('visitor.require_health_screening', true);
    }

    protected function requiresManualApproval(Visitor $visitor, array $options): bool
    {
        // High-risk visitors always need manual approval
        if ($visitor->isOnWatchlist()) {
            return true;
        }

        // First-time visitors might need manual approval
        if (!$visitor->pre_approved && config('visitor.require_manual_approval_first_time', false)) {
            return true;
        }

        return $options['require_manual_approval'] ?? false;
    }

    protected function hasValidInvitation(Visitor $visitor): bool
    {
        return VisitorInvitation::where('visitor_email', $visitor->email)
            ->orWhere('visitor_phone', $visitor->phone)
            ->valid()
            ->exists();
    }
}