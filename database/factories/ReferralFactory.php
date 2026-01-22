<?php

namespace Database\Factories;

use App\Models\Referral;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReferralFactory extends Factory
{
    public function definition(): array
    {
        $bonusAmount = fake()->randomFloat(2, 5, 50);
        $isQualified = fake()->boolean(60);
        $isPaid = $isQualified && fake()->boolean(70);

        return [
            'referrer_id' => User::factory(),
            'referee_id' => User::factory(),
            'level' => fake()->numberBetween(1, 3),
            'bonus_amount' => $bonusAmount,
            'bonus_percentage' => 5.00,
            'total_bonus_earned' => $isPaid ? $bonusAmount : 0,
            'status' => $isPaid ? 'paid' : ($isQualified ? 'qualified' : 'pending'),
            'qualification_type' => fake()->randomElement(['registration', 'first_payment', 'constellation_joined', 'tour_completed']),
            'is_qualified' => $isQualified,
            'qualified_at' => $isQualified ? fake()->dateTimeBetween('-60 days', 'now') : null,
            'is_paid' => $isPaid,
            'paid_at' => $isPaid ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'referee_registered' => true,
            'referee_paid_initial' => fake()->boolean(70),
            'referee_joined_constellation' => fake()->boolean(60),
            'referee_tours_completed' => fake()->numberBetween(0, 7),
            'referred_at' => fake()->dateTimeBetween('-90 days', 'now'),
            'expires_at' => fake()->dateTimeBetween('now', '+90 days'),
        ];
    }

    public function qualified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'qualified',
            'is_qualified' => true,
            'qualified_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'is_qualified' => true,
            'is_paid' => true,
            'qualified_at' => fake()->dateTimeBetween('-30 days', '-5 days'),
            'paid_at' => fake()->dateTimeBetween('-5 days', 'now'),
        ]);
    }
}
