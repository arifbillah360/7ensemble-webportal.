<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentMethodFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['card', 'bank_transfer', 'paypal', 'mobile_money']);

        $data = [
            'user_id' => User::factory(),
            'type' => $type,
            'provider' => match($type) {
                'card' => 'stripe',
                'paypal' => 'paypal',
                'mobile_money' => fake()->randomElement(['mpesa', 'orange_money', 'mtn_money']),
                default => null,
            },
            'is_default' => fake()->boolean(30),
            'is_verified' => fake()->boolean(80),
            'verified_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-6 months', 'now') : null,
            'status' => 'active',
        ];

        // Type-specific fields
        if ($type === 'card') {
            $data = array_merge($data, [
                'card_brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
                'card_last_four' => fake()->numerify('####'),
                'card_exp_month' => fake()->numerify('##'),
                'card_exp_year' => fake()->numberBetween(2024, 2030),
            ]);
        } elseif ($type === 'bank_transfer') {
            $data = array_merge($data, [
                'bank_name' => fake()->company() . ' Bank',
                'account_holder_name' => fake()->name(),
                'iban' => 'FR' . fake()->numerify('##') . ' ' . fake()->numerify('#### #### #### #### ####'),
                'bic_swift' => fake()->lexify('????FR??'),
            ]);
        } elseif ($type === 'paypal') {
            // PayPal uses email
        } elseif ($type === 'mobile_money') {
            $data = array_merge($data, [
                'mobile_money_provider' => $data['provider'],
                'mobile_number' => '+' . fake()->numerify('###########'),
            ]);
        }

        return $data;
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'card',
            'provider' => 'stripe',
            'card_brand' => fake()->randomElement(['visa', 'mastercard', 'amex']),
            'card_last_four' => fake()->numerify('####'),
            'card_exp_month' => fake()->numerify('##'),
            'card_exp_year' => fake()->numberBetween(2024, 2030),
        ]);
    }

    public function bankTransfer(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'bank_transfer',
            'bank_name' => fake()->company() . ' Bank',
            'account_holder_name' => fake()->name(),
            'iban' => 'FR' . fake()->numerify('##') . ' ' . fake()->numerify('#### #### #### #### ####'),
            'bic_swift' => fake()->lexify('????FR??'),
        ]);
    }
}
