<?php

namespace App\Http\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
use Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use App\Http\Requests\TenantRequest;
use Stancl\Tenancy\Database\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;
use App\Models\TenantSubscription;

class TenantCrudController extends CrudController
{
    use ListOperation;
    use CreateOperation;
    use UpdateOperation;
    use DeleteOperation;
    use ShowOperation;

    public function setup(): void
    {
        CRUD::setModel(Tenant::class);
        CRUD::setRoute(config('backpack.base.route_prefix').'/tenant');
        CRUD::setEntityNameStrings('tenant', 'tenants');
    }

    protected function setupListOperation(): void
    {
        CRUD::column('id');
        CRUD::column('name');
        // Subscription status column (reads tenant metadata stored in data.subscription.status)
        CRUD::addColumn([
            'name' => 'subscription_status',
            'label' => 'Subscription',
            'type' => 'closure',
            'function' => function($entry) {
                // Prefer explicit tenant_subscriptions table
                try {
                    $rec = TenantSubscription::where('tenant_id', $entry->getKey())->first();
                } catch (\Exception $e) {
                    $rec = null;
                }

                $status = null;
                if ($rec && $rec->status) {
                    $status = $rec->status;
                } else {
                    // fallback to tenant data
                    if (isset($entry->data) && $entry->data) {
                        $data = $entry->data;
                        if (is_array($data) && isset($data['subscription']['status'])) {
                            $status = $data['subscription']['status'];
                        } elseif (is_object($data) && isset($data->subscription->status)) {
                            $status = $data->subscription->status;
                        }
                    }
                }

                if ($status) {
                    $label = ucfirst((string) $status);
                    $class = in_array(strtolower($status), ['active', 'paid']) ? 'badge bg-success' : 'badge bg-warning text-dark';
                    return "<span class=\"{$class}\">{$label}</span>";
                }

                return '<span class="text-muted">none</span>';
            },
            'escaped' => false,
        ]);
        CRUD::addColumn([
            'name' => 'created_at',
            'label' => 'Created',
        ]);
    }

    protected function setupShowOperation(): void
    {
        // reuse list columns and add subscription details
        $this->setupListOperation();

        CRUD::addColumn([
            'name' => 'price_id',
            'label' => 'Price ID',
            'type' => 'closure',
            'function' => function($entry) {
                $rec = \App\Models\TenantSubscription::where('tenant_id', $entry->getKey())->first();
                return $rec && $rec->price_id ? e($rec->price_id) : '<span class="text-muted">-</span>';
            },
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'stripe_customer_id',
            'label' => 'Stripe Customer',
            'type' => 'closure',
            'function' => function($entry) {
                $rec = \App\Models\TenantSubscription::where('tenant_id', $entry->getKey())->first();
                return $rec && $rec->stripe_customer_id ? e($rec->stripe_customer_id) : '<span class="text-muted">-</span>';
            },
            'escaped' => false,
        ]);

        CRUD::addColumn([
            'name' => 'subscription_updated_at',
            'label' => 'Subscription updated',
            'type' => 'closure',
            'function' => function($entry) {
                $rec = \App\Models\TenantSubscription::where('tenant_id', $entry->getKey())->first();
                return $rec && $rec->updated_at ? $rec->updated_at->toDateTimeString() : '<span class="text-muted">-</span>';
            },
            'escaped' => false,
        ]);
    }

    protected function setupCreateOperation(): void
    {
        CRUD::setValidation(TenantRequest::class);

        CRUD::field('name');
        // Simple domain field: we'll create a Domain record after tenant is created
        CRUD::addField([
            'name' => 'domain',
            'label' => 'Primary domain',
            'type' => 'text',
            'hint' => 'Primary domain for this tenant (eg. demo.example.com).',
        ]);
    }

    protected function setupUpdateOperation(): void
    {
        $this->setupCreateOperation();
    }

    public function store()
    {
        $response = $this->traitStore();

        $tenant = $this->data['entry'];

        $domain = request()->input('domain');
        if ($domain) {
            // create a Domain record pointing to this tenant
            Domain::create([
                'domain' => $domain,
                'tenant_id' => $tenant->getTenantKey(),
            ]);
        }

        return $response;
    }

    public function update()
    {
        $response = $this->traitUpdate();

        // if domain provided, ensure it exists
        $tenant = $this->data['entry'];
        $domain = request()->input('domain');
        if ($domain) {
            Domain::firstOrCreate([
                'domain' => $domain,
                'tenant_id' => $tenant->getTenantKey(),
            ]);
        }

        return $response;
    }
}
