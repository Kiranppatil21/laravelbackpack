<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Models\User;
use App\Models\Client;

class EmployeeKycUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_tenant_user_can_upload_kyc_files_on_employee_create()
    {
        Storage::fake('local');

        $tenantId = \Illuminate\Support\Facades\DB::table('tenants')->insertGetId([
            'name' => 'KYC Tenant',
            'domain' => 'kyc.example',
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $client = Client::factory()->create(['tenant_id' => $tenantId]);

        $user = User::factory()->create();
        $user->tenant_id = $tenantId; // in-memory, test harness uses this

        $aadhar = UploadedFile::fake()->create('aadhar.pdf', 100, 'application/pdf');
        $pan = UploadedFile::fake()->image('pan.jpg');
        $police = UploadedFile::fake()->create('police.pdf', 80, 'application/pdf');

        $response = $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'KYC',
            'last_name' => 'User',
            'client_id' => $client->id,
            'aadhar' => $aadhar,
            'pan' => $pan,
            'police_verification' => $police,
        ]);

        $response->assertStatus(201);

        $employee = $response->json();

        $this->assertNotEmpty($employee['aadhar_path'] ?? null);
        $this->assertNotEmpty($employee['pan_path'] ?? null);
        $this->assertNotEmpty($employee['police_verification_path'] ?? null);

        Storage::disk('local')->assertExists($employee['aadhar_path']);
        Storage::disk('local')->assertExists($employee['pan_path']);
        Storage::disk('local')->assertExists($employee['police_verification_path']);
    }
}
