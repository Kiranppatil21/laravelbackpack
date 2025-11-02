<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\VisitLog;
use App\Models\Visitor;

class VisitLogFactory extends Factory
{
    protected $model = VisitLog::class;

    public function definition()
    {
        return [
            'visitor_id' => Visitor::factory(),
            'host_id' => null,
            'check_in_at' => now()->subMinutes(rand(1, 120)),
            'check_out_at' => null,
            'source' => 'factory',
            'notes' => $this->faker->sentence(),
        ];
    }
}
