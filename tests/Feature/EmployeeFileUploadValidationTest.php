<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EmployeeFileUploadValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_validation_rejects_large_or_wrong_type_and_accepts_valid()
    {
        Storage::fake(config('filesystems.default'));

        $user = User::factory()->create(['tenant_id' => 1]);

        // too large file (6MB)
        $large = UploadedFile::fake()->create('aadhar.pdf', 6144, 'application/pdf');

        $resp = $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'Test',
            'email' => 'test@example.com',
            'aadhar' => $large,
        ]);

        $resp->assertStatus(422);
        $resp->assertJsonValidationErrors('aadhar');

        // wrong mime type
        $exe = UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload');
        $resp2 = $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'Test2',
            'email' => 'test2@example.com',
            'aadhar' => $exe,
        ]);

        $resp2->assertStatus(422);
        $resp2->assertJsonValidationErrors('aadhar');

        // valid small pdf
        $small = UploadedFile::fake()->create('aadhar.pdf', 100, 'application/pdf');
        $resp3 = $this->actingAs($user)->postJson('/api/employees', [
            'first_name' => 'Good',
            'email' => 'good@example.com',
            'aadhar' => $small,
        ]);

        // created
        $resp3->assertStatus(201);
    }
}
