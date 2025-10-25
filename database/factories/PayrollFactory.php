<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Payroll;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayrollFactory extends Factory
{
    protected $model = Payroll::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('-6 months', 'now');
        $end = (clone $start);
        $gross = $this->faker->randomFloat(2, 1000, 8000);
        $tax = round($gross * 0.12, 2);
        $net = round($gross - $tax, 2);

        return [
            'tenant_id' => null,
            // create an employee for payroll
            'employee_id' => Employee::factory(),
            'period_start' => $start->format('Y-m-d'),
            'period_end' => $end->format('Y-m-d'),
            'gross' => $gross,
            'tax' => $tax,
            'net' => $net,
            'status' => $this->faker->randomElement(['paid','pending']),
            'paid_at' => $this->faker->optional(0.6)->dateTimeBetween($start, $end),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
