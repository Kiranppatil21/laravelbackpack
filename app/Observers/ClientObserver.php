<?php

namespace App\Observers;

use App\Models\Client;
use Illuminate\Support\Facades\DB;

class ClientObserver
{
    /**
     * Handle the Client "created" event.
     */
    public function creating(Client $client): void
    {
        if (auth()->check()) {
            $client->tenant_id = auth()->user()->tenant_id; // or tenant('id') if using stancl/tenancy
            // also set tenant_uuid for forward-compat with UUID-based tenancy
            try {
                $client->tenant_uuid = DB::table('tenants')->where('id', $client->tenant_id)->value('uuid');
            } catch (\Throwable $e) {
                // defensive: if central tenants table is not available, leave tenant_uuid null
            }
        }
    }

    /**
     * Handle the Client "updated" event.
     */
    public function updated(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "deleted" event.
     */
    public function deleted(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "restored" event.
     */
    public function restored(Client $client): void
    {
        //
    }

    /**
     * Handle the Client "force deleted" event.
     */
    public function forceDeleted(Client $client): void
    {
        //
    }
}
