<?php

namespace Database\Factories;

use App\Models\Designation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Designation>
 */
class DesignationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Designation::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $designations = [
            'Manager',
            'Assistant Manager',
            'Supervisor',
            'Executive',
            'Senior Executive',
            'Team Lead',
            'Director',
            'Administrator',
            'Coordinator',
            'Officer'
        ];

        return [
            'name' => $this->faker->unique()->randomElement($designations),
            'description' => $this->faker->sentence,
        ];
    }
}