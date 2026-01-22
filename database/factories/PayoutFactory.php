<?php

namespace Database\Factories;

use App\Models\Payout;
use App\Models\User;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

class PayoutFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->randomFloat(2, 100, 10000);
        $fee = $amount * 0.02; // 2% fee
        $netAmount = $amount - $fee;

        return [
            'user_id' => User::factory(),
            'tour_id' => Tour::factory(),
            'amount' => $amount,
            'currency' => 'EUR',
            'fee' => $fee,
            'net_amount' => $netAmount,
            'payment_method_type' => fake()->randomElement(['bank_transfer', 'paypal', 'mobile_money']),
            'bank_account' => 'FR' . fake()->numerify('####################'),
            'iban' => 'FR' . fake()->numerify('##') . ' ' . fake()->numerify('#### #### #### #### ####'),
            'account_holder_name' => fake()->name(),
            'status' => fake()->randomElement(['pending' => 30, 'processing' => 20, 'completed' => 40, 'failed' => 10]),
            'reference_number' => 'PYT-' . date('Ymd') . '-' . strtoupper(\Illuminate\Support\Str::random(8)),
            'processed_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'completed_at' => fake()->boolean(40) ? fake()->dateTimeBetween('-15 days', 'now') : null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'processed_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'completed_at' => fake()->dateTimeBetween('-15 days', 'now'),
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
            'completed_at' => null,
        ]);
    }
}
