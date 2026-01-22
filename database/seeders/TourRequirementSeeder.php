<?php

namespace Database\Seeders;

use App\Models\TourRequirement;
use Illuminate\Database\Seeder;

class TourRequirementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing tour requirements
        TourRequirement::truncate();

        // Triangulum Tour Requirements (3 people)
        $triangulumTours = [
            1 => ['offered' => 21, 'received' => 42],
            2 => ['offered' => 42, 'received' => 84],
            3 => ['offered' => 84, 'received' => 168],
            4 => ['offered' => 168, 'received' => 336],
            5 => ['offered' => 336, 'received' => 672],
            6 => ['offered' => 672, 'received' => 1344],
            7 => ['offered' => 1344, 'received' => 2688],
        ];

        foreach ($triangulumTours as $tourNumber => $amounts) {
            TourRequirement::create([
                'tour_number' => $tourNumber,
                'option_type' => 'triangulum',
                'amount_to_pay' => $amounts['offered'],
                'amount_to_receive' => $amounts['received'],
                'amount_to_keep' => $amounts['received'] - $amounts['offered'],
                'required_members' => 3,
                'description' => "Tour {$tourNumber} pour constellation Triangulum",
                'is_active' => true,
            ]);
        }

        // Pléiades Tour Requirements (7 people)
        $pleiadesTours = [
            1 => ['offered' => 21, 'received' => 147],
            2 => ['offered' => 147, 'received' => 1029],
            3 => ['offered' => 1029, 'received' => 7203],
            4 => ['offered' => 7203, 'received' => 50421],
            5 => ['offered' => 50421, 'received' => 352947],
            6 => ['offered' => 352947, 'received' => 2470629],
            7 => ['offered' => 2470629, 'received' => 17294403],
        ];

        foreach ($pleiadesTours as $tourNumber => $amounts) {
            TourRequirement::create([
                'tour_number' => $tourNumber,
                'option_type' => 'pleiades',
                'amount_to_pay' => $amounts['offered'],
                'amount_to_receive' => $amounts['received'],
                'amount_to_keep' => $amounts['received'] - $amounts['offered'],
                'required_members' => 7,
                'description' => "Tour {$tourNumber} pour constellation Les Pléiades",
                'is_active' => true,
            ]);
        }

        $this->command->info('✅ Tour requirements seeded successfully!');
        $this->command->info('   - 7 Triangulum tours created');
        $this->command->info('   - 7 Pléiades tours created');
    }
}
