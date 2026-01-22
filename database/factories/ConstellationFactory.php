<?php

namespace Database\Factories;

use App\Models\Constellation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Constellation>
 */
class ConstellationFactory extends Factory
{
    /**
     * Constellation names (cosmic themed)
     */
    protected $constellationNames = [
        'Andromède', 'Cassiopée', 'Orion', 'Persée', 'Pégase', 'Hercule',
        'Lyra', 'Cygne', 'Aigle', 'Scorpion', 'Verseau', 'Lion',
        'Gémeaux', 'Balance', 'Sagittaire', 'Capricorne', 'Poissons',
        'Bélier', 'Taureau', 'Cancer', 'Vierge', 'Dragon', 'Phénix',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['triangulum', 'pleiades']);
        $maxMembers = $type === 'triangulum' ? 3 : 7;

        return [
            'type' => $type,
            'name' => fake()->randomElement($this->constellationNames),
            'code' => $this->generateCode(),
            'alcyone_id' => null, // Set by seeder
            'current_tour' => fake()->numberBetween(1, 7),
            'max_members' => $maxMembers,
            'current_members' => 0, // Will be updated by members
            'status' => fake()->randomElement([
                'forming' => 20,
                'active' => 60,
                'completed' => 15,
                'frozen' => 5,
            ]),
            'total_collected' => fake()->randomFloat(2, 0, 50000),
            'total_distributed' => fake()->randomFloat(2, 0, 45000),
            'pending_amount' => fake()->randomFloat(2, 0, 5000),
            'formed_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'completed_at' => null,
            'last_tour_at' => fake()->dateTimeBetween('-30 days', 'now'),
            'metadata' => [
                'auto_created' => true,
                'fill_rate' => fake()->numberBetween(50, 100),
            ],
        ];
    }

    /**
     * Generate unique constellation code.
     */
    protected function generateCode(): string
    {
        do {
            $code = 'CONST-' . date('Y') . '-' . strtoupper(Str::random(6));
        } while (Constellation::where('code', $code)->exists());

        return $code;
    }

    /**
     * Indicate that the constellation is Triangulum.
     */
    public function triangulum(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'triangulum',
            'max_members' => 3,
        ]);
    }

    /**
     * Indicate that the constellation is Pléiades.
     */
    public function pleiades(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pleiades',
            'max_members' => 7,
        ]);
    }

    /**
     * Indicate that the constellation is forming.
     */
    public function forming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'forming',
            'formed_at' => null,
        ]);
    }

    /**
     * Indicate that the constellation is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'formed_at' => fake()->dateTimeBetween('-3 months', 'now'),
        ]);
    }

    /**
     * Indicate that the constellation is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'current_tour' => 7,
            'formed_at' => fake()->dateTimeBetween('-1 year', '-6 months'),
            'completed_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
