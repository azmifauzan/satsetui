<?php

namespace Database\Factories;

use App\Models\LlmModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LlmModel>
 */
class LlmModelFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = LlmModel::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'model_type' => fake()->randomElement(['satset', 'expert']),
            'provider' => fake()->randomElement(['gemini', 'openai']),
            'model_name' => fake()->randomElement([
                'gemini-2.0-flash-exp',
                'gemini-1.5-pro',
                'gpt-4o',
                'gpt-4-turbo',
            ]),
            'api_key' => fake()->sha256(),
            'base_url' => fake()->optional()->url(),
            'base_credits' => fake()->numberBetween(3, 20),
            'is_active' => fake()->boolean(80), // 80% chance of being active
        ];
    }

    /**
     * Indicate that the model is for the "satset" type.
     */
    public function satset(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => 'satset',
        ]);
    }

    /**
     * Indicate that the model is for the "expert" type.
     */
    public function expert(): static
    {
        return $this->state(fn (array $attributes) => [
            'model_type' => 'expert',
        ]);
    }

    /**
     * Indicate that the model uses Gemini provider.
     */
    public function gemini(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'gemini',
            'model_name' => fake()->randomElement([
                'gemini-2.0-flash-exp',
                'gemini-1.5-pro',
                'gemini-1.5-flash',
            ]),
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
    }

    /**
     * Indicate that the model uses OpenAI provider.
     */
    public function openai(): static
    {
        return $this->state(fn (array $attributes) => [
            'provider' => 'openai',
            'model_name' => fake()->randomElement([
                'gpt-4o',
                'gpt-4-turbo',
                'gpt-4',
                'gpt-3.5-turbo',
            ]),
            'base_url' => 'https://api.openai.com/v1',
        ]);
    }

    /**
     * Indicate that the model is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the model is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
