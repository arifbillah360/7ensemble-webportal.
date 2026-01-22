<?php

namespace Database\Factories;

use App\Models\Tour;
use App\Models\User;
use App\Models\Constellation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Tour amounts by type and tour number (from config/7ensemble.php).
     */
    protected $tourAmounts = [
        'triangulum' => [
            1 => ['offered' => 21, 'received' => 42],
            2 => ['offered' => 42, 'received' => 84],
            3 => ['offered' => 84, 'received' => 168],
            4 => ['offered' => 168, 'received' => 336],
            5 => ['offered' => 336, 'received' => 672],
            6 => ['offered' => 672, 'received' => 1344],
            7 => ['offered' => 1344, 'received' => 2688],
        ],
        'pleiades' => [
            1 => ['offered' => 21, 'received' => 147],
            2 => ['offered' => 147, 'received' => 1029],
            3 => ['offered' => 1029, 'received' => 7203],
            4 => ['offered' => 7203, 'received' => 50421],
            5 => ['offered' => 50421, 'received' => 352947],
            6 => ['offered' => 352947, 'received' => 2470629],
            7 => ['offered' => 2470629, 'received' => 17294403],
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tourNumber = fake()->numberBetween(1, 7);
        $type = fake()->randomElement(['triangulum', 'pleiades']);
        $amounts = $this->tourAmounts[$type][$tourNumber];
        $requiredMembers = $type === 'triangulum' ? 3 : 7;
        $membersPaid = fake()->numberBetween(0, $requiredMembers);
        $isAlcyone = fake()->boolean(14); // ~1 in 7 chance

        $amountPaid = $membersPaid > 0 ? $amounts['offered'] : 0;
        $amountReceived = $isAlcyone && $membersPaid > 0 ? ($amounts['received'] * $membersPaid / $requiredMembers) : 0;

        return [
            'user_id' => User::factory(),
            'constellation_id' => Constellation::factory(),
            'tour_number' => $tourNumber,
            'constellation_type' => $type,
            'amount_to_pay' => $amounts['offered'],
            'amount_paid' => $amountPaid,
            'amount_to_receive' => $amounts['received'],
            'amount_received' => $amountReceived,
            'amount_kept' => $amountReceived - $amountPaid,
            'payment_status' => $amountPaid >= $amounts['offered'] ? 'paid' : ($amountPaid > 0 ? 'partial' : 'pending'),
            'payment_due_date' => fake()->dateTimeBetween('now', '+30 days'),
            'payment_completed_at' => $amountPaid >= $amounts['offered'] ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'receipt_status' => $isAlcyone ? ($membersPaid === $requiredMembers ? 'completed' : 'partial') : 'waiting',
            'receipt_expected_date' => fake()->dateTimeBetween('now', '+60 days'),
            'receipt_completed_at' => $isAlcyone && $membersPaid === $requiredMembers ? fake()->dateTimeBetween('-15 days', 'now') : null,
            'status' => fake()->randomElement(['pending' => 20, 'active' => 50, 'in_progress' => 15, 'completed' => 10, 'failed' => 5]),
            'alcyone_id' => null, // Set by seeder
            'is_alcyone' => $isAlcyone,
            'members_paid' => $membersPaid,
            'required_members' => $requiredMembers,
            'started_at' => fake()->dateTimeBetween('-60 days', 'now'),
            'completed_at' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the tour is completed.
     */
    public function completed(): static
    {
        return $this->state(function (array $attributes) {
            $startedAt = fake()->dateTimeBetween('-90 days', '-30 days');
            $completedAt = fake()->dateTimeBetween($startedAt, 'now');
            $duration = $startedAt->diffInDays($completedAt);

            return [
                'status' => 'completed',
                'payment_status' => 'paid',
                'receipt_status' => 'completed',
                'amount_paid' => $attributes['amount_to_pay'],
                'amount_received' => $attributes['amount_to_receive'],
                'amount_kept' => $attributes['amount_to_receive'] - $attributes['amount_to_pay'],
                'started_at' => $startedAt,
                'completed_at' => $completedAt,
                'duration_days' => $duration,
                'payment_completed_at' => $startedAt->modify('+' . fake()->numberBetween(1, 7) . ' days'),
                'receipt_completed_at' => $completedAt,
            ];
        });
    }

    /**
     * Indicate that the tour is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'started_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    /**
     * Indicate that the user is Alcyone for this tour.
     */
    public function asAlcyone(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_alcyone' => true,
            'receipt_status' => 'partial',
        ]);
    }
}
