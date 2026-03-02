<?php

namespace Database\Factories;

use App\Models\CreditPackage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CreditPackage>
 */
class CreditPackageFactory extends Factory
{
    protected $model = CreditPackage::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Starter', 'Basic', 'Pro', 'Enterprise', 'Ultimate']),
            'description' => fake()->sentence(),
            'credits' => fake()->randomElement([50, 100, 250, 500, 1000]),
            'price' => fake()->randomElement([25000, 50000, 100000, 200000, 500000]),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    /**
     * Indicate that the package is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
