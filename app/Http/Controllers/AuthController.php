<?php

namespace App\Http\Controllers;

use App\Mail\AdminNewUserApprovalMail;
use App\Models\AppNotification;
use App\Models\User;
use App\Services\TwoFactorAuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        private readonly TwoFactorAuthService $twoFactorService
    ) {}

    /**
     * Display the login screen.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isApproved()
                ? redirect()->route('dashboard')
                : redirect()->route('approval.notice');
        }

        $defaultUser = User::where('email', 'selase@cediledger.gh')->first() ?? User::first();

        return view('auth.login', [
            'defaultEmail' => $defaultUser->email ?? 'selase@cediledger.gh',
        ]);
    }

    /**
     * Display the SaaS account registration screen.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return Auth::user()->isApproved()
                ? redirect()->route('dashboard')
                : redirect()->route('approval.notice');
        }

        return view('auth.register');
    }

    /**
     * Register a new SaaS user account in PENDING status awaiting Admin Approval
     * before their financial workspace is provisioned.
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'currency' => ['required', 'string', 'max:10'],
            'overall_monthly_budget' => ['nullable', 'numeric', 'min:0'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->all());
        }

        $validated = $validator->validated();

        $monthlyBudget = isset($validated['overall_monthly_budget']) && (float) $validated['overall_monthly_budget'] > 0
            ? (float) $validated['overall_monthly_budget']
            : 5000.00;

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'member',
            'approval_status' => 'pending',
            'approved_at' => null,
            'currency' => $validated['currency'],
            'monthly_income_target' => number_format($monthlyBudget * 1.5, 2, '.', ''),
            'overall_monthly_budget' => number_format($monthlyBudget, 2, '.', ''),
            'weekly_spending_limit' => number_format($monthlyBudget / 4, 2, '.', ''),
            'yearly_spending_target' => number_format($monthlyBudget * 12, 2, '.', ''),
            'email_verified_at' => now(),
            'has_sample_data' => true,
            'tour_completed' => false,
        ]);

        $request->session()->put('pending_approval_email', $user->email);

        // Create an unread in-app notification for every Administrator / Owner
        $adminUsers = User::whereIn('role', ['owner', 'admin'])->orderBy('id')->get();
        foreach ($adminUsers as $adminReceiver) {
            AppNotification::create([
                'user_id' => $adminReceiver->id,
                'title' => "New User Registration: {$user->name}",
                'message' => "{$user->name} ({$user->email}) registered a new SaaS account ({$user->currency}) and is awaiting your approval.",
                'type' => 'new_user',
                'severity' => 'warning',
                'action_url' => '/admin/users',
                'is_read' => false,
                'read_at' => null,
            ]);
        }

        // Send instant email notification with one-click signed Approve / Reject links to the Administrator
        $adminUser = $adminUsers->first();
        $adminEmail = env('ADMIN_APPROVAL_EMAIL', $adminUser?->email ?? 'selase@cediledger.gh');

        try {
            Mail::to($adminEmail)->send(new AdminNewUserApprovalMail($user));
        } catch (\Throwable $e) {
            Log::warning('Failed to send admin approval email: ' . $e->getMessage());
        }

        return redirect()->route('approval.notice')
            ->with('success', 'Your account request has been submitted and an approval email has been sent to the administrator!');
    }

    /**
     * Display the Pending Admin Approval screen.
     */
    public function showApprovalNotice(Request $request): View|RedirectResponse
    {
        if (Auth::check() && Auth::user()->isApproved()) {
            return redirect()->route('dashboard');
        }

        $email = Auth::user()?->email ?? $request->session()->get('pending_approval_email');
        $pendingUser = $email ? User::where('email', $email)->first() : null;

        return view('auth.pending-approval', [
            'pendingUser' => $pendingUser,
        ]);
    }

    /**
     * Authenticate a user session (enforce Admin Approval check + optional 2FA challenge).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ])->withInput($request->only('email', 'password', 'remember'));
        }

        // Enforce Admin Approval before allowing workspace access
        if (!$user->isApproved()) {
            $request->session()->put('pending_approval_email', $user->email);

            return redirect()->route('approval.notice');
        }

        // If user has Two-Factor Authentication enabled, pause login and redirect to 2FA challenge
        if ($user->two_factor_enabled && $user->two_factor_secret) {
            $request->session()->put('two_factor:login:id', $user->id);
            $request->session()->put('two_factor:login:remember', $remember);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if (!$remember && $user->getRememberToken()) {
            $user->setRememberToken(null);
            $user->save();
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Welcome back to CediLedger, ' . $user->name . '!');
    }

    /**
     * Show the 2FA verification challenge screen during login.
     */
    public function showTwoFactorChallenge(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $userId = $request->session()->get('two_factor:login:id');
        $user = $userId ? User::find($userId) : null;

        if (!$user || !$user->two_factor_enabled) {
            return redirect()->route('login');
        }

        $localLiveCode = app()->isLocal() && $user->two_factor_secret
            ? $this->twoFactorService->getCode($user->two_factor_secret)
            : null;

        return view('auth.two-factor-challenge', [
            'user' => $user,
            'localLiveCode' => $localLiveCode,
        ]);
    }

    /**
     * Verify a 6-digit TOTP code or one-time recovery code to complete login.
     */
    public function verifyTwoFactorChallenge(Request $request): RedirectResponse
    {
        $userId = $request->session()->get('two_factor:login:id');
        $remember = (bool) $request->session()->get('two_factor:login:remember', false);
        $user = $userId ? User::find($userId) : null;

        if (!$user || !$user->two_factor_enabled || !$user->two_factor_secret) {
            return redirect()->route('login');
        }

        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        $inputCode = trim($validated['code']);
        $isValidTotp = $this->twoFactorService->verifyCode($user->two_factor_secret, $inputCode);
        $usedRecoveryCode = false;

        if (!$isValidTotp) {
            $recoveryCodes = $user->two_factor_recovery_codes ?? [];
            $normalizedInput = strtoupper($inputCode);

            if (in_array($normalizedInput, $recoveryCodes, true)) {
                $usedRecoveryCode = true;
                $remainingCodes = array_values(array_diff($recoveryCodes, [$normalizedInput]));
                $user->update(['two_factor_recovery_codes' => $remainingCodes]);
            }
        }

        if (!$isValidTotp && !$usedRecoveryCode) {
            return back()->withErrors([
                'code' => 'The two-factor authentication code or recovery code you entered is invalid.',
            ]);
        }

        $request->session()->forget(['two_factor:login:id', 'two_factor:login:remember']);

        Auth::login($user, $remember);
        $request->session()->regenerate();

        if (!$remember && $user->getRememberToken()) {
            $user->setRememberToken(null);
            $user->save();
        }

        return redirect()->intended(route('dashboard'))
            ->with('success', 'Two-factor authentication verified. Welcome back, ' . $user->name . '!');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('success', 'You have been securely signed out.');
    }
}
