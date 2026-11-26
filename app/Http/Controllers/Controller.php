<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    /**
     * Resolve the active authenticated user (or auto-authenticate the primary ledger owner for seamless single-owner experience).
     */
    protected function currentUser(): User
    {
        if (Auth::check()) {
            return Auth::user();
        }

        $user = User::first();
        if ($user) {
            if (!$user->isApproved()) {
                abort(403, 'Your account is awaiting administrator approval.');
            }
            Auth::login($user);
            return $user;
        }

        return User::create([
            'name' => 'Selase Jesse',
            'email' => 'selase@cediledger.gh',
            'password' => bcrypt('password123'),
            'role' => 'owner',
            'currency' => 'GH¢',
            'monthly_income_target' => '16500.00',
            'overall_monthly_budget' => '10000.00',
            'weekly_spending_limit' => '2400.00',
            'yearly_spending_target' => '115000.00',
        ]);
    }
}
