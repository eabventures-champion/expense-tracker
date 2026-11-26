<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\WorkspaceOnboardingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'selase@cediledger.gh'],
            [
                'name' => 'Selase Jesse',
                'password' => Hash::make('password123'),
                'role' => 'owner',
                'approval_status' => 'approved',
                'approved_at' => now(),
                'currency' => 'GH¢',
                'monthly_income_target' => '16500.00',
                'overall_monthly_budget' => '10000.00',
                'weekly_spending_limit' => '2400.00',
                'yearly_spending_target' => '115000.00',
                'email_verified_at' => now(),
                'has_sample_data' => true,
                'tour_completed' => false,
            ]
        );

        app(WorkspaceOnboardingService::class)->initializeUserWorkspace($user, true);
    }
}
