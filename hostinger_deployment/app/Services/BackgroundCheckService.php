<?php

namespace App\Services;

use App\Models\Visitor;
use App\Models\SecurityAlert;
use App\Models\VisitorWatchlist;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BackgroundCheckService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('services.background_check', []);
    }

    /**
     * Request background check for a visitor.
     */
    public function requestBackgroundCheck(Visitor $visitor): array
    {
        if (!$this->isEnabled()) {
            return [
                'success' => false,
                'message' => 'Background check service not configured',
            ];
        }

        try {
            $response = Http::timeout(30)
                ->withToken($this->config['api_key'])
                ->post($this->config['endpoint'] . '/checks', [
                    'first_name' => $this->getFirstName($visitor->name),
                    'last_name' => $this->getLastName($visitor->name),
                    'email' => $visitor->email,
                    'phone' => $visitor->phone,
                    'id_type' => $visitor->id_type,
                    'id_number' => $visitor->id_value,
                    'date_of_birth' => $visitor->metadata['date_of_birth'] ?? null,
                    'address' => $visitor->address,
                    'check_types' => $this->getCheckTypes(),
                    'callback_url' => route('api.background-check.webhook'),
                    'reference_id' => "visitor_{$visitor->id}",
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                $visitor->update([
                    'background_check_status' => 'pending',
                    'metadata' => array_merge($visitor->metadata ?? [], [
                        'background_check_id' => $data['check_id'],
                        'background_check_requested_at' => now()->toISOString(),
                    ]),
                ]);

                return [
                    'success' => true,
                    'check_id' => $data['check_id'],
                    'estimated_completion' => $data['estimated_completion'] ?? null,
                ];
            }

            Log::error('Background check request failed', [
                'visitor_id' => $visitor->id,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            return [
                'success' => false,
                'message' => 'Background check request failed',
            ];

        } catch (\Exception $e) {
            Log::error('Background check service error', [
                'visitor_id' => $visitor->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => 'Background check service unavailable',
            ];
        }
    }

    /**
     * Process background check webhook result.
     */
    public function processWebhookResult(array $payload): bool
    {
        try {
            $checkId = $payload['check_id'];
            $status = $payload['status']; // 'passed', 'failed', 'requires_review'
            $results = $payload['results'] ?? [];
            
            // Find visitor by check ID
            $visitor = Visitor::whereJsonContains('metadata->background_check_id', $checkId)->first();
            
            if (!$visitor) {
                Log::warning('Background check webhook: visitor not found', ['check_id' => $checkId]);
                return false;
            }

            $visitor->update([
                'background_check_status' => $status,
                'background_check_date' => now(),
                'metadata' => array_merge($visitor->metadata ?? [], [
                    'background_check_results' => $results,
                    'background_check_completed_at' => now()->toISOString(),
                ]),
            ]);

            // Handle different outcomes
            $this->handleBackgroundCheckResult($visitor, $status, $results);

            return true;

        } catch (\Exception $e) {
            Log::error('Background check webhook processing error', [
                'payload' => $payload,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Handle background check result and take appropriate actions.
     */
    protected function handleBackgroundCheckResult(Visitor $visitor, string $status, array $results): void
    {
        switch ($status) {
            case 'passed':
                // Approve visitor if they were pending approval
                if ($visitor->status === 'pending_approval') {
                    $visitor->approve(\App\Models\User::where('email', 'system@example.com')->first());
                }
                break;

            case 'failed':
                // Add to watchlist and block
                $reasons = collect($results)
                    ->where('status', 'failed')
                    ->pluck('type')
                    ->implode(', ');

                $visitor->addToWatchlist(
                    "Background check failed: {$reasons}",
                    'high'
                );

                $visitor->update(['status' => 'blocked']);

                // Create security alert
                SecurityAlert::create([
                    'type' => 'background_check_failed',
                    'severity' => 'high',
                    'title' => 'Background Check Failed',
                    'description' => "Background check failed for visitor {$visitor->name}. Reasons: {$reasons}",
                    'visitor_id' => $visitor->id,
                    'occurred_at' => now(),
                    'metadata' => [
                        'check_results' => $results,
                    ],
                ]);
                break;

            case 'requires_review':
                // Flag for manual review
                $visitor->update(['status' => 'pending_approval']);
                
                SecurityAlert::create([
                    'type' => 'background_check_review',
                    'severity' => 'medium',
                    'title' => 'Background Check Requires Review',
                    'description' => "Background check for visitor {$visitor->name} requires manual review.",
                    'visitor_id' => $visitor->id,
                    'occurred_at' => now(),
                    'metadata' => [
                        'check_results' => $results,
                    ],
                ]);
                break;
        }
    }

    /**
     * Check if background check service is enabled.
     */
    protected function isEnabled(): bool
    {
        return !empty($this->config['api_key']) && !empty($this->config['endpoint']);
    }

    /**
     * Get background check types based on configuration.
     */
    protected function getCheckTypes(): array
    {
        return $this->config['check_types'] ?? [
            'criminal_history',
            'identity_verification',
            'watchlist_screening',
        ];
    }

    /**
     * Extract first name from full name.
     */
    protected function getFirstName(string $fullName): string
    {
        return explode(' ', trim($fullName))[0] ?? '';
    }

    /**
     * Extract last name from full name.
     */
    protected function getLastName(string $fullName): string
    {
        $parts = explode(' ', trim($fullName));
        return count($parts) > 1 ? end($parts) : '';
    }

    /**
     * Get background check status for multiple visitors.
     */
    public function getBulkStatus(array $visitorIds): array
    {
        $visitors = Visitor::whereIn('id', $visitorIds)
            ->select('id', 'name', 'background_check_status', 'background_check_date', 'metadata')
            ->get();

        return $visitors->map(function ($visitor) {
            return [
                'visitor_id' => $visitor->id,
                'visitor_name' => $visitor->name,
                'status' => $visitor->background_check_status,
                'check_date' => $visitor->background_check_date,
                'check_id' => $visitor->metadata['background_check_id'] ?? null,
            ];
        })->toArray();
    }
}