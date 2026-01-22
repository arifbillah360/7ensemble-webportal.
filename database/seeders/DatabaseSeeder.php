<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Constellation;
use App\Models\ConstellationMember;
use App\Models\Tour;
use App\Models\Transaction;
use App\Models\Payout;
use App\Models\Referral;
use App\Models\PaymentMethod;
use App\Models\TourRequirement;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->command->info('🌟 Starting 7 Ensemble Database Seeding...');
        $this->command->newLine();

        // 1. Seed tour requirements first (foundational data)
        $this->command->info('📋 Step 1: Seeding Tour Requirements...');
        $this->call(TourRequirementSeeder::class);
        $this->command->newLine();

        // 2. Create admin user
        $this->command->info('👨‍💼 Step 2: Creating Admin User...');
        $admin = User::factory()->admin()->create([
            'name' => 'Admin 7 Ensemble',
            'email' => 'admin@7ensemble.ch',
            'password' => Hash::make('Admin123!'),
        ]);
        $this->command->info("   ✅ Admin created: {$admin->email}");
        $this->command->newLine();

        // 3. Create 100 regular users
        $this->command->info('👥 Step 3: Creating 100 Users...');
        $users = User::factory(100)->create();
        $this->command->info('   ✅ 100 users created');
        $this->command->newLine();

        // 4. Create constellations (mix of Triangulum and Pléiades)
        $this->command->info('🌌 Step 4: Creating 20 Constellations...');

        // 12 Triangulum constellations (3 members each)
        $triangulumConstellations = Constellation::factory(12)->triangulum()->active()->create();
        $this->command->info('   ✅ 12 Triangulum constellations created');

        // 8 Pléiades constellations (7 members each)
        $pleiadesConstellations = Constellation::factory(8)->pleiades()->active()->create();
        $this->command->info('   ✅ 8 Pléiades constellations created');
        $this->command->newLine();

        // 5. Assign users to constellations
        $this->command->info('🔗 Step 5: Assigning Users to Constellations...');
        $allConstellations = $triangulumConstellations->merge($pleiadesConstellations);
        $userIndex = 0;

        foreach ($allConstellations as $constellation) {
            $membersCount = $constellation->max_members;

            for ($position = 1; $position <= $membersCount; $position++) {
                if ($userIndex >= $users->count()) {
                    break 2; // Stop if we run out of users
                }

                $user = $users[$userIndex];
                $isAlcyone = ($position === 1); // First member is Alcyone

                // Create constellation member
                ConstellationMember::create([
                    'constellation_id' => $constellation->id,
                    'user_id' => $user->id,
                    'position' => $position,
                    'role' => $isAlcyone ? 'alcyone' : 'member',
                    'status' => 'active',
                    'is_active' => true,
                    'joined_at' => now()->subDays(rand(30, 90)),
                    'current_tour' => rand(1, 7),
                ]);

                // Update user with constellation info
                $user->update([
                    'constellation_id' => $constellation->id,
                    'constellation_type' => $constellation->type,
                    'is_alcyone' => $isAlcyone,
                    'current_tour' => rand(1, 7),
                    'has_paid_initial' => true,
                    'initial_payment_at' => now()->subDays(rand(30, 90)),
                    'payment_verified' => true,
                ]);

                // Set Alcyone for constellation
                if ($isAlcyone) {
                    $constellation->update(['alcyone_id' => $user->id]);
                }

                $userIndex++;
            }

            // Update constellation current_members
            $constellation->update(['current_members' => $membersCount]);
        }
        $this->command->info("   ✅ {$userIndex} users assigned to constellations");
        $this->command->newLine();

        // 6. Create tours for users in constellations
        $this->command->info('🎯 Step 6: Creating Tours...');
        $tourCount = 0;
        foreach ($users as $user) {
            if ($user->constellation_id) {
                $constellation = $user->constellation;
                $currentTour = $user->current_tour;

                // Create tours 1 through current tour
                for ($tourNum = 1; $tourNum <= $currentTour; $tourNum++) {
                    $requirement = TourRequirement::getRequirement($tourNum, $constellation->type);

                    if ($requirement) {
                        Tour::create([
                            'user_id' => $user->id,
                            'constellation_id' => $constellation->id,
                            'tour_requirement_id' => $requirement->id,
                            'tour_number' => $tourNum,
                            'constellation_type' => $constellation->type,
                            'amount_to_pay' => $requirement->amount_to_pay,
                            'amount_paid' => $requirement->amount_to_pay,
                            'amount_to_receive' => $requirement->amount_to_receive,
                            'amount_received' => $user->is_alcyone && $tourNum < $currentTour ? $requirement->amount_to_receive : 0,
                            'amount_kept' => $user->is_alcyone && $tourNum < $currentTour ? $requirement->amount_to_keep : 0,
                            'payment_status' => 'paid',
                            'receipt_status' => $user->is_alcyone ? 'completed' : 'waiting',
                            'status' => $tourNum < $currentTour ? 'completed' : 'active',
                            'alcyone_id' => $constellation->alcyone_id,
                            'is_alcyone' => $user->is_alcyone && ($tourNum === $currentTour),
                            'members_paid' => $constellation->max_members,
                            'required_members' => $constellation->max_members,
                            'started_at' => now()->subDays(rand(10, 60)),
                            'completed_at' => $tourNum < $currentTour ? now()->subDays(rand(1, 30)) : null,
                        ]);
                        $tourCount++;
                    }
                }
            }
        }
        $this->command->info("   ✅ {$tourCount} tours created");
        $this->command->newLine();

        // 7. Create transactions
        $this->command->info('💰 Step 7: Creating Transactions...');
        $transactionCount = 0;
        foreach ($users->take(50) as $user) { // Create transactions for first 50 users
            // Initial payment
            Transaction::factory()->create([
                'user_id' => $user->id,
                'type' => 'initial_payment',
                'direction' => 'debit',
                'amount' => 21.00,
                'status' => 'completed',
            ]);
            $transactionCount++;

            // Create 2-5 random transactions per user
            $txCount = rand(2, 5);
            Transaction::factory($txCount)->create(['user_id' => $user->id]);
            $transactionCount += $txCount;
        }
        $this->command->info("   ✅ {$transactionCount} transactions created");
        $this->command->newLine();

        // 8. Create payment methods
        $this->command->info('💳 Step 8: Creating Payment Methods...');
        $paymentMethodCount = 0;
        foreach ($users->take(60) as $user) {
            PaymentMethod::factory()->create([
                'user_id' => $user->id,
                'is_default' => true,
            ]);
            $paymentMethodCount++;

            // 30% of users have 2 payment methods
            if (rand(1, 100) <= 30) {
                PaymentMethod::factory()->create(['user_id' => $user->id]);
                $paymentMethodCount++;
            }
        }
        $this->command->info("   ✅ {$paymentMethodCount} payment methods created");
        $this->command->newLine();

        // 9. Create payouts
        $this->command->info('💸 Step 9: Creating Payouts...');
        $payoutCount = 0;
        foreach ($users->take(30) as $user) {
            if ($user->constellation_id && $user->current_tour > 2) {
                Payout::factory()->create(['user_id' => $user->id]);
                $payoutCount++;
            }
        }
        $this->command->info("   ✅ {$payoutCount} payouts created");
        $this->command->newLine();

        // 10. Create referrals
        $this->command->info('👥 Step 10: Creating Referrals...');
        $referralCount = 0;
        foreach ($users->take(40) as $referrer) {
            // Each user refers 1-3 people
            $refereeCount = rand(1, 3);
            for ($i = 0; $i < $refereeCount; $i++) {
                $referee = $users->random();

                // Avoid self-referral and duplicate referrals
                if ($referee->id !== $referrer->id && !$referee->referred_by_id) {
                    $referee->update(['referred_by_id' => $referrer->id]);

                    Referral::create([
                        'referrer_id' => $referrer->id,
                        'referee_id' => $referee->id,
                        'level' => 1,
                        'bonus_amount' => 10.00,
                        'bonus_percentage' => 5.00,
                        'status' => 'qualified',
                        'qualification_type' => 'first_payment',
                        'is_qualified' => true,
                        'qualified_at' => now()->subDays(rand(1, 30)),
                        'is_paid' => rand(0, 1),
                        'paid_at' => rand(0, 1) ? now()->subDays(rand(1, 15)) : null,
                        'referee_registered' => true,
                        'referee_paid_initial' => true,
                        'referee_joined_constellation' => true,
                        'referred_at' => now()->subDays(rand(30, 90)),
                    ]);
                    $referralCount++;
                }
            }
        }
        $this->command->info("   ✅ {$referralCount} referrals created");
        $this->command->newLine();

        // Summary
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('🎉 Database Seeding Completed!');
        $this->command->info('═══════════════════════════════════════');
        $this->command->table(
            ['Resource', 'Count'],
            [
                ['Users', User::count()],
                ['Constellations', Constellation::count()],
                ['Constellation Members', ConstellationMember::count()],
                ['Tours', Tour::count()],
                ['Transactions', Transaction::count()],
                ['Payouts', Payout::count()],
                ['Referrals', Referral::count()],
                ['Payment Methods', PaymentMethod::count()],
                ['Tour Requirements', TourRequirement::count()],
            ]
        );
        $this->command->newLine();
        $this->command->info('✅ You can now login with:');
        $this->command->info('   Email: admin@7ensemble.ch');
        $this->command->info('   Password: Admin123!');
        $this->command->newLine();
    }
}
