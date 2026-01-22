<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password = null;

    /**
     * French first names
     */
    protected $frenchFirstNames = [
        'male' => ['Pierre', 'Jean', 'Louis', 'Marc', 'François', 'Michel', 'Bernard', 'Jacques', 'Philippe', 'André', 'Nicolas', 'Laurent', 'Thomas', 'Julien', 'Alexandre', 'David', 'Olivier', 'Christophe', 'Antoine', 'Mathieu', 'Sébastien', 'Maxime', 'Luc', 'Vincent', 'Romain'],
        'female' => ['Marie', 'Sophie', 'Isabelle', 'Catherine', 'Nathalie', 'Anne', 'Christine', 'Sylvie', 'Valérie', 'Sandrine', 'Julie', 'Céline', 'Stéphanie', 'Émilie', 'Charlotte', 'Camille', 'Léa', 'Chloé', 'Manon', 'Aurélie', 'Laura', 'Marine', 'Emma', 'Sarah', 'Lucie'],
    ];

    /**
     * French last names
     */
    protected $frenchLastNames = [
        'Martin', 'Bernard', 'Dubois', 'Thomas', 'Robert', 'Richard', 'Petit', 'Durand', 'Leroy', 'Moreau',
        'Simon', 'Laurent', 'Lefebvre', 'Michel', 'Garcia', 'David', 'Bertrand', 'Roux', 'Vincent', 'Fournier',
        'Morel', 'Girard', 'André', 'Lefevre', 'Mercier', 'Dupont', 'Lambert', 'Bonnet', 'François', 'Martinez',
        'Legrand', 'Garnier', 'Faure', 'Rousseau', 'Blanc', 'Guerin', 'Muller', 'Henry', 'Roussel', 'Nicolas',
        'Perrin', 'Morin', 'Mathieu', 'Clement', 'Gauthier', 'Dumont', 'Lopez', 'Fontaine', 'Chevalier', 'Robin',
    ];

    /**
     * European countries
     */
    protected $europeanCountries = [
        'FR', 'BE', 'CH', 'LU', 'DE', 'IT', 'ES', 'PT', 'NL', 'AT',
        'GB', 'IE', 'PL', 'CZ', 'GR', 'SE', 'DK', 'FI', 'NO',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $gender = fake()->randomElement(['male', 'female']);
        $firstName = fake()->randomElement($this->frenchFirstNames[$gender]);
        $lastName = fake()->randomElement($this->frenchLastNames);
        $name = $firstName . ' ' . $lastName;

        return [
            'name' => $name,
            'email' => strtolower($firstName) . '.' . strtolower($lastName) . fake()->unique()->numberBetween(1, 999) . '@example.com',
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),

            // Profile Information
            'phone' => '+33 ' . fake()->numerify('# ## ## ## ##'),
            'country' => fake()->randomElement($this->europeanCountries),
            'date_of_birth' => fake()->dateTimeBetween('-60 years', '-18 years'),

            // Constellation & Tour Progress
            'constellation_id' => null, // Set later by seeder
            'current_tour' => 1,
            'constellation_type' => fake()->randomElement(['triangulum', 'pleiades']),
            'is_alcyone' => false,

            // Referral System
            'referral_code' => User::generateReferralCode(),
            'referred_by_id' => null, // Set later by seeder
            'referral_earnings' => 0,

            // Financial Tracking
            'total_paid' => 0,
            'total_received' => 0,
            'total_earnings' => 0,
            'available_balance' => 0,

            // Payment & Verification
            'has_paid_initial' => fake()->boolean(80), // 80% have paid
            'initial_payment_at' => fake()->boolean(80) ? fake()->dateTimeBetween('-30 days', 'now') : null,
            'payment_verified' => fake()->boolean(90),

            // User Status & Role
            'status' => fake()->randomElement(['active' => 90, 'pending_verification' => 5, 'suspended' => 3, 'banned' => 2]),
            'role' => 'user',

            // Preferences
            'preferences' => [
                'language' => 'fr',
                'timezone' => 'Europe/Zurich',
                'notifications' => true,
                'theme' => 'cosmic',
            ],

            // Security
            'two_factor_enabled' => fake()->boolean(20),
            'last_login_at' => fake()->dateTimeBetween('-7 days', 'now'),
            'last_login_ip' => fake()->ipv4(),
        ];
    }

    /**
     * Indicate that the user is an admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
            'email_verified_at' => now(),
            'has_paid_initial' => true,
            'payment_verified' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the user is Alcyone.
     */
    public function alcyone(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_alcyone' => true,
            'has_paid_initial' => true,
            'payment_verified' => true,
            'status' => 'active',
        ]);
    }

    /**
     * Indicate that the user hasn't paid yet.
     */
    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'has_paid_initial' => false,
            'initial_payment_at' => null,
            'payment_verified' => false,
        ]);
    }

    /**
     * Indicate that the user's email is unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user is in a Triangulum constellation.
     */
    public function triangulum(): static
    {
        return $this->state(fn (array $attributes) => [
            'constellation_type' => 'triangulum',
        ]);
    }

    /**
     * Indicate that the user is in a Pléiades constellation.
     */
    public function pleiades(): static
    {
        return $this->state(fn (array $attributes) => [
            'constellation_type' => 'pleiades',
        ]);
    }

    /**
     * Indicate that the user is on a specific tour.
     */
    public function onTour(int $tourNumber): static
    {
        return $this->state(fn (array $attributes) => [
            'current_tour' => $tourNumber,
        ]);
    }
}
