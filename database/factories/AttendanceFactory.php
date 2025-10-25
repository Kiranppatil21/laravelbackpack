<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-60 days', 'now')->format('Y-m-d');

        return [
            'tenant_id' => null,
            // create an employee automatically and associate
            'employee_id' => Employee::factory(),
            'date' => $date,
            'status' => $this->faker->randomElement(['present','absent','leave']),
            'check_in' => $this->faker->optional(0.9)->dateTimeBetween($date . ' 07:00', $date . ' 10:00'),
            'check_out' => $this->faker->optional(0.8)->dateTimeBetween($date . ' 15:00', $date . ' 19:00'),
            'notes' => $this->faker->optional()->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
