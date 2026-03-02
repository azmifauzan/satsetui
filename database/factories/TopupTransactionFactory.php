<?php

namespace Database\Factories;

use App\Models\CreditPackage;
use App\Models\TopupTransaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TopupTransaction>
 */
class TopupTransactionFactory extends Factory
{
    protected $model = TopupTransaction::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'credit_package_id' => CreditPackage::factory(),
            'amount' => 50000,
            'credits_added' => 100,
            'mayar_transaction_id' => fake()->uuid(),
            'mayar_payment_link' => fake()->url(),
            'status' => TopupTransaction::STATUS_PENDING,
            'paid_at' => null,
            'mayar_payload' => null,
        ];
    }

    /**
     * Indicate the transaction was successful.
     */
    public function successful(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TopupTransaction::STATUS_SUCCESS,
            'paid_at' => now(),
            'mayar_payload' => ['status' => 'PAID'],
        ]);
    }

    /**
     * Indicate the transaction failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TopupTransaction::STATUS_FAILED,
        ]);
    }

    /**
     * Indicate the transaction expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TopupTransaction::STATUS_EXPIRED,
        ]);
    }
}
