<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_login_page_renders_for_guests(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Sign in to your account');
    }

    public function test_homepage_renders_saas_welcome_landing_page(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('CediLedger Cloud');
    }

    public function test_unauthenticated_users_are_redirected_to_login_from_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_dashboard(): void
    {
        $user = User::first() ?? User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertStatus(200);
        $response->assertSee('CediLedger');
    }

    public function test_keep_me_signed_in_sets_remember_cookie_when_checked_and_omits_when_unchecked(): void
    {
        $user = User::first() ?? User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        $recallerName = \Illuminate\Support\Facades\Auth::guard()->getRecallerName();

        // 1. Login WITH "Keep me signed in" checked
        $responseWithRemember = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => '1',
        ]);

        $responseWithRemember->assertRedirect('/dashboard');
        $responseWithRemember->assertCookie($recallerName);
        $this->assertNotNull($user->fresh()->getRememberToken());

        // Logout
        $this->post('/logout');

        // 2. Login WITHOUT "Keep me signed in" checked
        $responseWithoutRemember = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $responseWithoutRemember->assertRedirect('/dashboard');
        $responseWithoutRemember->assertCookieMissing($recallerName);
    }

    public function test_user_can_enable_and_disable_two_factor_authentication_and_login_with_2fa(): void
    {
        $user = User::first() ?? User::factory()->create([
            'password' => bcrypt('password123'),
        ]);

        // Ensure 2FA starts disabled
        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
        ]);

        $tfaService = app(\App\Services\TwoFactorAuthService::class);
        $secret = $tfaService->generateSecretKey();
        $validCode = $tfaService->getCode($secret);

        // 1. Enable 2FA via ProfileController
        $enableResponse = $this->actingAs($user)
            ->withSession(['two_factor_setup_secret' => $secret])
            ->post('/profile/two-factor/enable', [
                'code' => $validCode,
            ]);

        $enableResponse->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue($user->two_factor_enabled);
        $this->assertEquals($secret, $user->two_factor_secret);
        $this->assertCount(8, $user->two_factor_recovery_codes);

        // 2. Logout and attempt login — should redirect to /two-factor-challenge
        $this->post('/logout');

        $loginResponse = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertRedirect('/two-factor-challenge');
        $this->assertGuest();

        // 3. Complete 2FA challenge with valid TOTP code
        $challengeResponse = $this->post('/two-factor-challenge', [
            'code' => $tfaService->getCode($secret),
        ]);

        $challengeResponse->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // 4. Disable 2FA
        $disableResponse = $this->actingAs($user)->post('/profile/two-factor/disable', [
            'current_password' => 'password123',
        ]);

        $disableResponse->assertSessionHasNoErrors();
        $this->assertFalse($user->fresh()->two_factor_enabled);
    }

    public function test_new_user_requires_admin_approval_via_email_and_can_reset_sample_workspace_while_keeping_profile(): void
    {
        \Illuminate\Support\Facades\Mail::fake();

        $admin = User::where('role', 'owner')->first() ?? User::factory()->create([
            'role' => 'owner',
            'approval_status' => 'approved',
        ]);

        $uniqueEmail = 'saas_user_' . uniqid() . '@example.com';

        // 1. Register new SaaS account -> placed in pending status, sends email to Admin, & redirects to /pending-approval
        $registerResponse = $this->post('/register', [
            'name' => 'Ama Boateng',
            'email' => $uniqueEmail,
            'currency' => '$',
            'overall_monthly_budget' => '4000.00',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $registerResponse->assertRedirect('/pending-approval');
        $this->assertGuest();

        $newUser = User::where('email', $uniqueEmail)->first();
        $this->assertNotNull($newUser);
        $this->assertEquals('pending', $newUser->approval_status);
        $this->assertEquals(0, $newUser->transactions()->count());

        // Verify AdminNewUserApprovalMail was sent with signed approve/reject URLs
        $signedApproveUrl = null;
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\AdminNewUserApprovalMail::class, function ($mail) use ($newUser, &$signedApproveUrl) {
            $signedApproveUrl = $mail->approveUrl;
            return $mail->registeredUser->id === $newUser->id && !empty($mail->approveUrl);
        });

        // Verify an unread in-app notification was created for the Admin in the header Notification Bell
        $adminUser = User::where('email', 'selase@cediledger.gh')->firstOrFail();
        $adminNotif = \App\Models\AppNotification::where('user_id', $adminUser->id)
            ->where('type', 'new_user')
            ->where('message', 'like', '%' . $uniqueEmail . '%')
            ->first();
        $this->assertNotNull($adminNotif);
        $this->assertFalse($adminNotif->is_read);

        // Verify Admin sees the notification bell with Unread and Read tabs and Clear All button on /dashboard
        $adminDashboard = $this->actingAs($adminUser)->get('/dashboard');
        $adminDashboard->assertStatus(200);
        $adminDashboard->assertSee('Notifications Center');
        $adminDashboard->assertSee('New User Registration: Ama Boateng');
        $adminDashboard->assertSee('Clear All');

        // Verify Admin can toggle notification between Unread and Read
        $this->actingAs($adminUser)->post("/notifications/{$adminNotif->id}/toggle-read")->assertRedirect();
        $adminNotif->refresh();
        $this->assertTrue($adminNotif->is_read);

        // Toggle back to unread, then clear all notifications
        $this->actingAs($adminUser)->post("/notifications/{$adminNotif->id}/toggle-read")->assertRedirect();
        $adminNotif->refresh();
        $this->assertFalse($adminNotif->is_read);

        $this->actingAs($adminUser)->post('/notifications/clear-all', ['scope' => 'all'])->assertRedirect();
        $this->assertEquals(0, \App\Models\AppNotification::where('user_id', $adminUser->id)->count());
        auth()->logout();

        // 2. Attempting to log in while still pending redirects to /pending-approval
        $pendingLogin = $this->post('/login', [
            'email' => $uniqueEmail,
            'password' => 'Password123!',
        ]);
        $pendingLogin->assertRedirect('/pending-approval');
        $this->assertGuest();

        // 3. Admin clicks the signed "Approve & Setup Workspace" link directly from the email
        $emailApproveResponse = $this->get($signedApproveUrl);
        $emailApproveResponse->assertStatus(200);
        $emailApproveResponse->assertSee('User Account Approved');

        // Verify welcome email was sent to the user
        \Illuminate\Support\Facades\Mail::assertSent(\App\Mail\UserAccountStatusMail::class, function ($mail) use ($newUser) {
            return $mail->user->id === $newUser->id && $mail->status === 'approved';
        });

        $newUser->refresh();
        $this->assertEquals('approved', $newUser->approval_status);
        $this->assertTrue($newUser->has_sample_data);
        $this->assertEquals(21, $newUser->categories()->count());
        $this->assertEquals(5, $newUser->paymentMethods()->count());
        $this->assertGreaterThan(50, $newUser->transactions()->count());

        // 4. User updates profile details, then resets sample database to start fresh
        $newUser->update([
            'phone' => '+233 24 999 8888',
            'job_title' => 'Product Director',
            'bio' => 'Building long-term capital reserves.',
        ]);

        $resetResponse = $this->actingAs($newUser)->post('/workspace/reset');
        $resetResponse->assertRedirect('/dashboard');

        $newUser->refresh();
        $this->assertFalse($newUser->has_sample_data);
        $this->assertTrue($newUser->tour_completed);
        $this->assertEquals(0, $newUser->transactions()->count());
        $this->assertEquals(0, $newUser->savingsRecords()->count());
        $this->assertEquals(0, $newUser->financialGoals()->count());

        // Verify profile details were NOT erased, while sample categories & payment methods were cleared so user starts from Step 1
        $this->assertEquals('Ama Boateng', $newUser->name);
        $this->assertEquals($uniqueEmail, $newUser->email);
        $this->assertEquals('+233 24 999 8888', $newUser->phone);
        $this->assertEquals('Product Director', $newUser->job_title);
        $this->assertEquals('Building long-term capital reserves.', $newUser->bio);
        $this->assertEquals('$', $newUser->currency);
        $this->assertEquals(0, $newUser->categories()->count());
        $this->assertEquals(0, $newUser->paymentMethods()->count());

        // Verify the Chronological Setup Roadmap guide is displayed on the dashboard after reset
        $dashboardAfterReset = $this->actingAs($newUser)->get('/dashboard');
        $dashboardAfterReset->assertStatus(200);
        $dashboardAfterReset->assertSee('Chronological Setup Roadmap');
        $dashboardAfterReset->assertSee('Create Expense & Income Categories');
        $dashboardAfterReset->assertSee('Add Payment Methods');
        $dashboardAfterReset->assertSee('Set Monthly, Weekly & Category Budgets');

        // Cleanup test user
        $newUser->delete();
    }
}
