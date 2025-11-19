<?php

namespace App\Jobs;

use App\Models\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Database\Models\Tenant;

class ProcessStripeEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public array $event;

    public function __construct(array $event)
    {
        $this->event = $event;
    }

    public function handle()
    {
        $event = $this->event;
        $eventId = $event['id'] ?? null;

        if ($eventId) {
            try {
                DB::table('webhook_events')->insert([
                    'event_id' => $eventId,
                    'payload' => json_encode($event),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                // likely duplicate key, so this event was already processed
                Log::info('Duplicate Stripe event ignored: '.$eventId);

                return;
            }
        }

        $type = $event['type'] ?? null;

        switch ($type) {
            case 'checkout.session.completed':
                $session = $event['data']['object'] ?? [];
                $tenantId = $session['metadata']['tenant_id'] ?? null;
                $subscriptionId = $session['subscription'] ?? null;
                $customerId = $session['customer'] ?? null;

                $foundTenantId = null;
                if ($tenantId) {
                    $foundTenantId = $tenantId;
                }

                if (! $foundTenantId && $subscriptionId) {
                    $found = Tenant::where('data->subscription->id', $subscriptionId)->first();
                    $foundTenantId = $found ? $found->getKey() : null;
                }

                if ($foundTenantId) {
                    // determine tenant_uuid when possible (foundTenantId may be integer id or UUID)
                    $tenantUuid = null;
                    if (is_string($foundTenantId) && preg_match('/[0-9a-fA-F\-]{36}/', $foundTenantId)) {
                        $tenantUuid = $foundTenantId;
                    } elseif (is_numeric($foundTenantId)) {
                        try {
                            $tenantUuid = DB::table('tenants')->where('id', $foundTenantId)->value('uuid');
                        } catch (\Throwable $e) {
                            $tenantUuid = null;
                        }
                    }

                    TenantSubscription::updateOrCreate(
                        ['tenant_id' => $foundTenantId],
                        [
                            'tenant_uuid' => $tenantUuid,
                            'subscription_id' => $subscriptionId,
                            'stripe_customer_id' => $customerId,
                            'status' => 'active',
                            'raw' => $session,
                        ]
                    );

                    try {
                        DB::table('tenants')->where('id', $foundTenantId)->update(['active' => true, 'activated_at' => now()]);
                    } catch (\Exception $e) {
                        Log::error('Failed to mark tenant active: '.$e->getMessage());
                    }
                }

                break;

            case 'customer.subscription.created':
            case 'customer.subscription.updated':
            case 'customer.subscription.deleted':
                $sub = $event['data']['object'] ?? [];
                $subscriptionId = $sub['id'] ?? null;
                $status = $sub['status'] ?? null;

                $tenant = null;
                if ($subscriptionId) {
                    $rec = TenantSubscription::where('subscription_id', $subscriptionId)->first();
                    if ($rec) {
                        $tenant = $rec->tenant;
                    } else {
                        $tenant = Tenant::where('data->subscription->id', $subscriptionId)->first();
                    }
                }

                if ($tenant) {
                    $tenantIdToUse = $tenant->getKey();
                    // compute tenant_uuid where available
                    $tenantUuid = null;
                    if (is_string($tenantIdToUse) && preg_match('/[0-9a-fA-F\-]{36}/', $tenantIdToUse)) {
                        $tenantUuid = $tenantIdToUse;
                    } else {
                        try {
                            $tenantUuid = DB::table('tenants')->where('id', $tenantIdToUse)->value('uuid');
                        } catch (\Throwable $e) {
                            $tenantUuid = null;
                        }
                    }

                    TenantSubscription::updateOrCreate(
                        ['tenant_id' => $tenantIdToUse],
                        [
                            'tenant_uuid' => $tenantUuid,
                            'subscription_id' => $subscriptionId,
                            'stripe_customer_id' => $sub['customer'] ?? null,
                            'price_id' => $sub['items']['data'][0]['price']['id'] ?? ($sub['items'][0]['price']['id'] ?? null),
                            'status' => $status,
                            'raw' => $sub,
                        ]
                    );

                    try {
                        if ($status === 'active') {
                            DB::table('tenants')->where('id', $tenantIdToUse)->update(['active' => true, 'activated_at' => now()]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to update tenant activation status: '.$e->getMessage());
                    }
                }

                break;

            case 'invoice.payment_failed':
            case 'invoice.payment_succeeded':
                $invoice = $event['data']['object'] ?? [];
                $subscriptionId = $invoice['subscription'] ?? null;
                if ($subscriptionId) {
                    $rec = TenantSubscription::where('subscription_id', $subscriptionId)->first();
                    if ($rec) {
                        $status = $invoice['status'] ?? null;
                        if ($status) {
                                $rec->update(['status' => $status, 'raw' => $invoice]);
                            try {
                                if ($status === 'paid' || $status === 'succeeded') {
                                    $tenantIdToUse = $rec->tenant_id;
                                    if ($tenantIdToUse) {
                                        DB::table('tenants')->where('id', $tenantIdToUse)->update(['active' => true, 'activated_at' => now()]);
                                    }
                                }
                            } catch (\Exception $e) {
                                Log::error('Failed to mark tenant active on invoice payment: '.$e->getMessage());
                            }
                        } else {
                            $rec->update(['raw' => $invoice]);
                        }
                    }
                }
                break;

            default:
                Log::debug('Unhandled Stripe webhook event in job: '.($type ?? 'unknown'));
        }
    }
}
