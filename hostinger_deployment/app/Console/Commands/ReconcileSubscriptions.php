<?php

namespace App\Console\Commands;

use App\Models\TenantSubscription;
use Illuminate\Console\Command;
use Stancl\Tenancy\Database\Models\Tenant;

class ReconcileSubscriptions extends Command
{
    protected $signature = 'app:reconcile-subscriptions {--fix : Try to fix missing mappings when possible} {--limit=100 : Number of Stripe subscriptions to fetch per page}';

    protected $description = 'Report Stripe subscriptions that are not mapped to tenants and optionally fix them using metadata';

    public function handle()
    {
        $this->info('Starting reconciliation');

    $stripeKey = config('services.stripe.secret');
        if (! $stripeKey) {
            $this->warn('STRIPE_SECRET not set — DB-only checks will be performed (no remote Stripe API checks).');
        }

        // 1) Report tenant_subscriptions with no subscription_id
        $missing = TenantSubscription::whereNull('subscription_id')->get();
        if ($missing->count()) {
            $this->line('TenantSubscription rows missing subscription_id:');
            foreach ($missing as $m) {
                $this->line(" - tenant_id={$m->tenant_id} id={$m->id}");
            }
        } else {
            $this->line('No tenant_subscriptions with null subscription_id.');
        }

        // 2) If Stripe key is set, fetch subscriptions and find orphan subscriptions
        if ($stripeKey) {
            $this->line('Querying Stripe for subscriptions...');
            $stripe = new \Stripe\StripeClient($stripeKey);

            $limit = (int) $this->option('limit');
            $params = ['limit' => $limit];

            $orphanCount = 0;
            $cursor = null;
            do {
                if ($cursor) {
                    $params['starting_after'] = $cursor;
                }
                // @phpstan-ignore-next-line Stripe client accepts a broader set of params at runtime
                $resp = $stripe->subscriptions->all($params);
                foreach ($resp->data as $sub) {
                    $subId = $sub->id;
                    $exists = TenantSubscription::where('subscription_id', $subId)->exists();
                    if (! $exists) {
                        $orphanCount++;
                        $this->line("Orphan subscription: {$subId} status={$sub->status}");
                        // if fix flag and metadata.tenant_id present, create mapping
                        if ($this->option('fix') && isset($sub->metadata) && isset($sub->metadata->tenant_id)) {
                            $tenantId = $sub->metadata->tenant_id;
                                $tenant = Tenant::find($tenantId);
                                if ($tenant) {
                                    $tenantKey = $tenant->getKey();
                                    $tenantUuid = is_string($tenantKey) && preg_match('/[0-9a-fA-F\-]{36}/', $tenantKey) ? $tenantKey : null;
                                    // if tenantKey is numeric but we still want UUID, try reading central table
                                    if (! $tenantUuid && is_numeric($tenantId)) {
                                        try {
                                            $tenantUuid = \Illuminate\Support\Facades\DB::table('tenants')->where('id', $tenantId)->value('uuid');
                                        } catch (\Throwable $e) {
                                            $tenantUuid = null;
                                        }
                                    }

                                    TenantSubscription::create([
                                        'tenant_id' => $tenant->getKey(),
                                        'tenant_uuid' => $tenantUuid,
                                        'subscription_id' => $subId,
                                        'stripe_customer_id' => $sub->customer ?? null,
                                        'price_id' => $sub->items->data[0]->price->id ?? null,
                                        'status' => $sub->status ?? null,
                                        'raw' => json_decode(json_encode($sub), true),
                                    ]);
                                $this->info(" -> Fixed mapping for subscription {$subId} -> tenant {$tenantId}");
                            } else {
                                $this->warn(" -> metadata tenant_id={$tenantId} not found in tenants table");
                            }
                        }
                    }
                }

                $cursor = count($resp->data) ? end($resp->data)->id : null;
            } while ($resp->has_more);

            $this->line("Done. Orphan subscriptions found: {$orphanCount}");
        }

        $this->info('Reconciliation finished.');

        return 0;
    }
}
