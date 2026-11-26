<?php

namespace App\Http\Controllers;

use App\Services\TwoFactorAuthService;
use App\Services\WorkspaceOnboardingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService $twoFactorService,
        private readonly WorkspaceOnboardingService $onboardingService
    ) {}

    /**
     * Display the User Profile & 2FA Security Center.
     */
    public function index(Request $request): View
    {
        $user = $this->currentUser();

        // If 2FA is not yet enabled, prepare or retrieve a pending setup secret in session
        $pendingSecret = $request->session()->get('two_factor_setup_secret');
        if (!$user->two_factor_enabled && !$pendingSecret) {
            $pendingSecret = $this->twoFactorService->generateSecretKey();
            $request->session()->put('two_factor_setup_secret', $pendingSecret);
        }

        $activeSecret = $user->two_factor_enabled
            ? $user->two_factor_secret
            : $pendingSecret;

        $otpAuthUrl = $activeSecret
            ? $this->twoFactorService->getOtpAuthUrl('CediLedger', $user->email, $activeSecret)
            : '';

        $currentLiveCode = $activeSecret
            ? $this->twoFactorService->getCode($activeSecret)
            : '';

        return view('profile.index', compact(
            'user',
            'pendingSecret',
            'activeSecret',
            'otpAuthUrl',
            'currentLiveCode'
        ));
    }

    /**
     * Update personal profile details and optional avatar photo.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $this->currentUser();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:40'],
            'job_title' => ['nullable', 'string', 'max:120'],
            'location' => ['nullable', 'string', 'max:120'],
            'bio' => ['nullable', 'string', 'max:500'],
            'currency' => ['required', 'string', 'max:10'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'job_title' => $validated['job_title'] ?? null,
            'location' => $validated['location'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'currency' => $validated['currency'],
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Your profile details have been updated.');
    }

    /**
     * Update account password.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $this->currentUser();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'The current password you entered is incorrect.',
            ]);
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your account password has been changed.');
    }

    /**
     * Confirm and enable Two-Factor Authentication (2FA).
     */
    public function enableTwoFactor(Request $request): RedirectResponse
    {
        $user = $this->currentUser();

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $secret = $request->session()->get('two_factor_setup_secret') ?: $user->two_factor_secret;
        if (!$secret) {
            $secret = $this->twoFactorService->generateSecretKey();
            $request->session()->put('two_factor_setup_secret', $secret);
        }

        if (!$this->twoFactorService->verifyCode($secret, $validated['code'])) {
            return back()->withErrors([
                'code' => 'Invalid 6-digit verification code. Please check your authenticator app and try again.',
            ]);
        }

        $recoveryCodes = $this->twoFactorService->generateRecoveryCodes();

        $user->update([
            'two_factor_enabled' => true,
            'two_factor_secret' => $secret,
            'two_factor_recovery_codes' => $recoveryCodes,
            'two_factor_confirmed_at' => now(),
        ]);

        $request->session()->forget('two_factor_setup_secret');

        return back()->with('success', 'Two-Factor Authentication (2FA) is now enabled on your account.');
    }

    /**
     * Disable Two-Factor Authentication (2FA).
     */
    public function disableTwoFactor(Request $request): RedirectResponse
    {
        $user = $this->currentUser();

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        if (!Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors([
                'current_password' => 'Please enter your valid account password to disable 2FA.',
            ]);
        }

        $user->update([
            'two_factor_enabled' => false,
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);

        $request->session()->forget('two_factor_setup_secret');

        return back()->with('success', 'Two-Factor Authentication (2FA) has been disabled.');
    }

    /**
     * Regenerate 2FA recovery codes.
     */
    public function regenerateRecoveryCodes(): RedirectResponse
    {
        $user = $this->currentUser();

        if (!$user->two_factor_enabled) {
            return back()->withErrors(['code' => '2FA is not currently enabled.']);
        }

        $user->update([
            'two_factor_recovery_codes' => $this->twoFactorService->generateRecoveryCodes(),
        ]);

        return back()->with('success', 'New 2FA recovery codes have been generated.');
    }

    /**
     * Mark the interactive Guided Platform Tour as completed.
     */
    public function completeTour(): RedirectResponse
    {
        $user = $this->currentUser();
        $user->update(['tour_completed' => true]);

        return back()->with('success', 'Guided tour completed! Whenever you are ready, you can reset the sample database to start fresh with your own records.');
    }

    /**
     * Reset the user's financial database (transactions, categories, payment methods, savings, goals, budgets, recurring bills)
     * so they can start fresh after exploring the sample data, while strictly preserving their
     * profile details, credentials, and 2FA settings.
     */
    public function resetWorkspace(): RedirectResponse
    {
        $user = $this->currentUser();

        $this->onboardingService->resetWorkspaceToFreshStart($user);

        return redirect()->route('dashboard')
            ->with('fresh_start_initiated', true)
            ->with('success', 'Database reset complete! Your personal profile was preserved. Follow the chronological Setup Roadmap below to build your ledger.');
    }
}
