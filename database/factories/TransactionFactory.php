<?php

namespace Database\Factories;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Tour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['payment', 'payout', 'transfer', 'referral_bonus', 'initial_payment']);
        $direction = in_array($type, ['payout', 'transfer']) ? 'debit' : 'credit';
        $amount = fake()->randomFloat(2, 21, 5000);
        $fee = $amount * 0.029 + 0.30; // Typical payment gateway fee
        $netAmount = $amount - $fee;
        $gateway = fake()->randomElement(['stripe', 'paypal', 'bank_transfer', 'mobile_money']);

        return [
            'transaction_id' => Transaction::generateTransactionId(),
            'user_id' => User::factory(),
            'tour_id' => fake()->boolean(70) ? Tour::factory() : null,
            'type' => $type,
            'direction' => $direction,
            'amount' => $amount,
            'currency' => 'EUR',
            'fee' => $fee,
            'net_amount' => $netAmount,
            'payment_gateway' => $gateway,
            'gateway_transaction_id' => 'GW-' . strtoupper(\Illuminate\Support\Str::random(16)),
            'gateway_reference' => fake()->uuid(),
            'status' => fake()->randomElement(['pending' => 15, 'processing' => 10, 'completed' => 70, 'failed' => 5]),
            'requires_verification' => $gateway === 'bank_transfer',
            'is_verified' => $gateway !== 'bank_transfer' || fake()->boolean(70),
            'verified_at' => $gateway === 'bank_transfer' && fake()->boolean(70) ? fake()->dateTimeBetween('-7 days', 'now') : null,
            'description' => $this->getDescriptionForType($type),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'processed_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'completed_at' => fake()->boolean(70) ? fake()->dateTimeBetween('-30 days', 'now') : null,
        ];
    }

    /**
     * Get description based on transaction type.
     */
    protected function getDescriptionForType(string $type): string
    {
        return match($type) {
            'payment' => 'Paiement pour Tour',
            'payout' => 'Retrait de gains',
            'transfer' => 'Transfert bancaire',
            'referral_bonus' => 'Bonus de parrainage',
            'initial_payment' => 'Paiement initial 21€',
            default => 'Transaction',
        };
    }

    /**
     * Indicate that the transaction is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'completed_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the transaction is pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that this is a payment transaction.
     */
    public function payment(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payment',
            'direction' => 'debit',
        ]);
    }

    /**
     * Indicate that this is a payout transaction.
     */
    public function payout(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'payout',
            'direction' => 'credit',
        ]);
    }
}
