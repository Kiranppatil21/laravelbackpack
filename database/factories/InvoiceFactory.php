<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition()
    {
        $date = $this->faker->dateTimeBetween('-120 days', 'now')->format('Y-m-d');

        return [
            'tenant_id' => null,
            // create a client automatically
            'client_id' => Client::factory(),
            'invoice_number' => strtoupper($this->faker->bothify('INV-####-??')),
            'date' => $date,
            'due_date' => now()->addDays(30)->format('Y-m-d'),
            'total' => $this->faker->randomFloat(2, 50, 5000),
            'status' => $this->faker->randomElement(['draft','sent','paid']),
            'notes' => $this->faker->optional()->sentence,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
