<?php

namespace Tests\Unit;

use App\Mail\AgencyCredentials;
use App\Models\Agency;
use App\Models\User;
use App\Services\AgencyUserProvisioner;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AgencyUserProvisionerTest extends TestCase
{
    use RefreshDatabase;

    public function test_provision_creates_user_and_sends_email()
    {
        Mail::fake();

        $agency = Agency::create([
            'name' => 'Test Agency',
            'email' => 'agency-test@example.test',
            'contact_person_name' => 'Alice',
        ]);

        $password = 'secret123';

        $user = AgencyUserProvisioner::provision($agency, $password);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', ['email' => $agency->email]);

        Mail::assertSent(AgencyCredentials::class, function ($mailable) use ($agency) {
            return $mailable->hasTo($agency->email);
        });
    }
}
