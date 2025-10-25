<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AdminClientEmployeeInvoiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        config(['backpack.base.guard' => 'web']);
        Role::create(['name' => 'Super Admin']);
    }

    public function test_admin_client_search_shows_created_client()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $client = Client::factory()->create(['name' => 'Zenith Client']);

        $this->actingAs($user)->get('/admin/client')->assertStatus(200);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $ajax = $this->actingAs($user)->post('/admin/client/search', []);
        $ajax->assertStatus(200);
        $ajax->assertSee('Zenith Client');
    }

    public function test_admin_employee_search_shows_created_employee()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $employee = Employee::factory()->create(['first_name' => 'John', 'last_name' => 'Doe']);

        $this->actingAs($user)->get('/admin/employee')->assertStatus(200);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $ajax = $this->actingAs($user)->post('/admin/employee/search', []);
        $ajax->assertStatus(200);
        $ajax->assertSee('John');
    }

    public function test_admin_invoice_search_shows_created_invoice()
    {
        $user = User::factory()->create();
        $user->assignRole('Super Admin');

        $invoice = Invoice::factory()->create(['invoice_number' => 'INV-TEST-0001']);

        $this->actingAs($user)->get('/admin/invoice')->assertStatus(200);

        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        $ajax = $this->actingAs($user)->post('/admin/invoice/search', []);
        $ajax->assertStatus(200);
        $ajax->assertSee('INV-TEST-0001');
    }
}
