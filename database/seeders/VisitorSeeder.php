<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Visitor;
use App\Models\VisitLog;

class VisitorSeeder extends Seeder
{
    public function run(): void
    {
        $visitor = Visitor::create([
            'name' => 'Test Visitor',
            'email' => 'visitor@example.test',
            'phone' => '9999999999',
            'company' => 'Acme Inc',
            'source' => 'seeder',
        ]);

        VisitLog::create([
            'visitor_id' => $visitor->id,
            'host_id' => null,
            'check_in_at' => now()->subHour(),
            'check_out_at' => now()->subMinutes(30),
            'source' => 'seeder',
            'notes' => 'Seeded test visit',
        ]);
    }
}

