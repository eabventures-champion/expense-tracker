@extends('layouts.app')

@section('kicker', 'Profile & Security')
@section('page_title', 'Account Profile, Password & Two-Factor Authentication (2FA)')

@section('content')
<!-- PROFILE SUMMARY BANNER -->
<section class="section-block theme-ivory">
    <div class="section-body profile-banner-body" style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
        <div class="profile-banner-identity" style="display: flex; align-items: center; gap: 16px; min-width: 0; flex: 1;">
            @if($user->avatar_path)
                <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->name }}" class="profile-banner-avatar" style="width: 64px; height: 64px; border-radius: 50%; object-fit: cover; border: 2px solid var(--emerald); flex-shrink: 0;">
            @else
                <div class="profile-banner-avatar" style="width: 64px; height: 64px; border-radius: 50%; background-color: var(--plum); color: var(--mustard); display: flex; align-items: center; justify-content: center; font-size: 22px; font-weight: 800; border: 2px solid var(--ivory-border); flex-shrink: 0;">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
            @endif
            <div style="min-width: 0; flex: 1;">
                <h2 class="profile-banner-name" style="font-size: 19px; font-weight: 800; color: var(--text-dark); line-height: 1.25;">{{ $user->name }}</h2>
                <div class="profile-banner-meta" style="font-size: 12.5px; color: var(--text-muted); margin-top: 2px; word-break: break-word;">
                    {{ $user->email }}
                    @if($user->job_title) • {{ $user->job_title }} @endif
                    @if($user->location) • {{ $user->location }} @endif
                </div>
                <div class="profile-banner-badges" style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-top: 7px;">
                    <span class="mono" style="font-size: 10px; font-weight: 700; padding: 2.5px 9px; border-radius: 9999px; background-color: var(--ivory); border: 1px solid var(--ivory-border); color: var(--plum); text-transform: uppercase;">
                        {{ $user->role ?: 'Owner' }}
                    </span>
                    @if($user->two_factor_enabled)
                        <span class="mono" style="font-size: 10px; font-weight: 700; padding: 2.5px 9px; border-radius: 9999px; background-color: var(--emerald-soft); color: var(--emerald-dark);">
                            ✓ 2FA Protected
                        </span>
                    @else
                        <span class="mono" style="font-size: 10px; font-weight: 700; padding: 2.5px 9px; border-radius: 9999px; background-color: var(--coral-soft); color: var(--coral-dark);">
                            2FA Disabled
                        </span>
                    @endif
                </div>
                @if($user->bio)
                    <div style="font-size: 12px; color: var(--text-dark); margin-top: 6px; max-width: 680px;">{{ $user->bio }}</div>
                @endif
            </div>
        </div>

        <div class="profile-banner-actions" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
            <button type="button" class="btn btn-plum btn-sm" onclick="openGuidedTourModal(0)">✦ Launch Guided Tour</button>
            <button type="button" class="btn btn-mustard btn-sm" onclick="openResetWorkspaceModal()">↻ Reset &amp; Start Fresh</button>
            <button type="button" class="btn btn-ivory btn-sm" onclick="openPasswordModal()">Change Password</button>
            @if($user->two_factor_enabled)
                <button type="button" class="btn btn-coral btn-sm" onclick="openDisable2FAModal()">Disable 2FA</button>
            @else
                <button type="button" class="btn btn-emerald btn-sm" onclick="openEnable2FAModal()">Enable 2FA Security</button>
            @endif
        </div>
    </div>
</section>

<div class="grid-2">
    <!-- PERSONAL PROFILE INFORMATION FORM (COLLAPSED ON MOBILE BY DEFAULT) -->
    <section class="section-block theme-ivory mobile-collapsible-card" id="profileDetailsFormCard">
        <div class="section-header" onclick="toggleCollapsibleCard('profileDetailsFormCard', 'mobile')">
            <div style="display: flex; align-items: center; gap: 8px; min-width: 0;">
                <span class="collapsible-chevron-badge mono mobile-only-collapse-el">▸</span>
                <div>
                    <div style="font-size: 10.5px; color: var(--blue); text-transform: uppercase; font-weight: 700;">Identity &amp; Preferences</div>
                    <h3 class="section-title">Personal Profile Details</h3>
                </div>
            </div>
            <button type="button"
                    class="btn btn-ivory btn-sm collapsible-toggle-btn mobile-only-collapse-el"
                    data-expand-label="Edit ▾"
                    data-hide-label="Hide ▴"
                    style="padding: 3px 9px; font-size: 10.5px; border-radius: 9999px; font-weight: 700; flex-shrink: 0;">
                Edit ▾
            </button>
        </div>
        <div class="section-body collapsible-card-body">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control mono" placeholder="e.g., +233 24 000 0000">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role / Job Title</label>
                        <input type="text" name="job_title" value="{{ old('job_title', $user->job_title) }}" class="form-control" placeholder="e.g., Senior Systems Architect">
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Location / City</label>
                        <input type="text" name="location" value="{{ old('location', $user->location) }}" class="form-control" placeholder="e.g., Accra, Ghana">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Currency</label>
                        <select name="currency" class="form-control mono">
                            <option value="GH¢" {{ $user->currency === 'GH¢' ? 'selected' : '' }}>GH¢ — Ghana Cedi (Default)</option>
                            <option value="$" {{ $user->currency === '$' ? 'selected' : '' }}>$ — US Dollar (USD)</option>
                            <option value="£" {{ $user->currency === '£' ? 'selected' : '' }}>£ — British Pound (GBP)</option>
                            <option value="€" {{ $user->currency === '€' ? 'selected' : '' }}>€ — Euro (EUR)</option>
                            <option value="₦" {{ $user->currency === '₦' ? 'selected' : '' }}>₦ — Nigerian Naira (NGN)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Profile Photo (JPG, PNG, WEBP)</label>
                    <input type="file" name="avatar" class="form-control" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <div class="form-group">
                    <label class="form-label">Bio / Financial Objective Note</label>
                    <textarea name="bio" rows="3" class="form-control" placeholder="Short summary or financial mission statement...">{{ old('bio', $user->bio) }}</textarea>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn btn-plum">Save Profile Changes</button>
                </div>
            </form>
        </div>
    </section>

    <!-- TWO-FACTOR AUTHENTICATION (2FA) MANAGEMENT CARD -->
    <section class="section-block theme-ivory">
        <div class="section-header" style="flex-wrap: nowrap; gap: 8px;">
            <div style="min-width: 0;">
                <div style="font-size: 10.5px; color: var(--emerald); text-transform: uppercase; font-weight: 700;">Account Protection</div>
                <h3 class="section-title">Two-Factor Authentication (2FA)</h3>
            </div>
            @if($user->two_factor_enabled)
                <span class="mono" style="background-color: var(--emerald-soft); color: var(--emerald-dark); font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; flex-shrink: 0;">
                    ACTIVE
                </span>
            @else
                <span class="mono" style="background-color: var(--ivory); color: var(--text-muted); font-size: 11px; font-weight: 700; padding: 3px 10px; border-radius: 9999px; flex-shrink: 0;">
                    INACTIVE
                </span>
            @endif
        </div>
        <div class="section-body" style="display: flex; flex-direction: column; gap: 16px;">
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.55;">
                Two-Factor Authentication adds an extra layer of security to your CediLedger account. When enabled, signing in requires both your password and a time-based 6-digit verification code from your authenticator app (Google Authenticator, Authy, Microsoft Authenticator, or 1Password).
            </p>

            @if($user->two_factor_enabled)
                <div style="background-color: #F4FBF8; border: 1px solid rgba(45, 159, 120, 0.3); border-radius: 12px; padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 6px;">
                        <strong style="color: var(--emerald-dark); font-size: 14px;">✓ Two-Factor Authentication is Enabled</strong>
                        <span class="mono" style="font-size: 11px; color: var(--text-muted);">
                            Confirmed {{ $user->two_factor_confirmed_at ? $user->two_factor_confirmed_at->format('d M Y, H:i') : 'Active' }}
                        </span>
                    </div>
                    <div style="font-size: 12.5px; color: var(--text-muted);">
                        Your account is protected with TOTP verification. Keep your backup recovery codes in a safe place in case you lose access to your device.
                    </div>
                </div>

                <!-- RECOVERY CODES BOX -->
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
                        <div>
                            <strong style="font-size: 13.5px; color: var(--text-dark);">Backup Recovery Codes</strong>
                            <div style="font-size: 11.5px; color: var(--text-muted);">Each code can be used once to sign in if your authenticator app is unavailable.</div>
                        </div>
                        <form action="{{ route('profile.two-factor.recovery-codes') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-ivory btn-sm">↻ Regenerate Codes</button>
                        </form>
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
                        @foreach($user->two_factor_recovery_codes ?? [] as $rCode)
                            <div class="mono" style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); padding: 7px 10px; border-radius: 8px; font-size: 12.5px; font-weight: 700; text-align: center; color: var(--plum);">
                                {{ $rCode }}
                            </div>
                        @endforeach
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="button" class="btn btn-coral" onclick="openDisable2FAModal()">Disable Two-Factor Authentication</button>
                </div>
            @else
                <div style="background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 18px; display: flex; flex-direction: column; gap: 14px;">
                    <div>
                        <strong style="font-size: 14px; color: var(--text-dark);">Quick 2FA Setup</strong>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 3px;">
                            Click the button below to scan your setup QR code or enter your secret key into your authenticator app and confirm with a 6-digit code.
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-emerald" onclick="openEnable2FAModal()">
                            Configure & Enable 2FA →
                        </button>
                    </div>
                </div>
            @endif
        </div>
    </section>
</div>

<!-- MODAL 1: ENABLE TWO-FACTOR AUTHENTICATION (2FA) -->
<div id="enable2FAModal" class="modal-backdrop" onclick="if(event.target === this) closeEnable2FAModal()">
    <div class="modal-box" style="max-width: 540px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #5CE0B5; text-transform: uppercase; font-weight: 700;">Security Setup</div>
                <h3 style="font-size: 16px;">Enable Two-Factor Authentication (2FA)</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeEnable2FAModal()">✕ Close</button>
        </div>
        <div class="modal-body" style="display: flex; flex-direction: column; gap: 16px;">
            <div style="display: grid; grid-template-columns: 160px 1fr; gap: 16px; align-items: center; background-color: var(--ivory-alt); border: 1px solid var(--ivory-border); border-radius: 12px; padding: 14px;">
                <div style="background-color: #FFFFFF; padding: 10px; border-radius: 10px; border: 1px solid var(--ivory-border); display: flex; align-items: center; justify-content: center;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&data={{ urlencode($otpAuthUrl) }}"
                         alt="2FA QR Code"
                         width="136"
                         height="136"
                         style="display: block;"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                    <div style="display: none; font-size: 11px; color: var(--text-muted); text-align: center; padding: 14px;">
                        Use manual setup key on the right
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <div style="font-size: 12.5px; font-weight: 700; color: var(--text-dark);">1. Scan QR Code or Enter Setup Key</div>
                    <div style="font-size: 11.5px; color: var(--text-muted); line-height: 1.45;">
                        Open Google Authenticator, Authy, or 1Password and scan the QR code, or manually enter this Base32 secret key:
                    </div>
                    <div class="mono" style="background-color: #FFFFFF; border: 1px solid var(--ivory-border); padding: 7px 10px; border-radius: 8px; font-size: 13px; font-weight: 700; color: var(--plum); letter-spacing: 0.08em;">
                        {{ $activeSecret }}
                    </div>
                </div>
            </div>

            <form action="{{ route('profile.two-factor.enable') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <div class="form-group">
                    <label class="form-label">2. Enter 6-Digit Verification Code from App</label>
                    <input type="text" name="code" id="enable_2fa_code_input" class="form-control mono" placeholder="000000" maxlength="6" style="font-size: 17px; font-weight: 700; letter-spacing: 0.2em; text-align: center;" required>
                </div>

                @if(app()->isLocal() && $currentLiveCode)
                    <div style="padding: 10px 12px; background-color: var(--ivory); border: 1px solid var(--ivory-border); border-radius: 9px; display: flex; align-items: center; justify-content: space-between; font-size: 12px;">
                        <span>Local Dev Current TOTP Code: <strong class="mono" style="color: var(--plum);">{{ $currentLiveCode }}</strong></span>
                        <button type="button" class="btn btn-ivory btn-sm" style="padding: 3px 9px; font-size: 11px;" onclick="document.getElementById('enable_2fa_code_input').value='{{ $currentLiveCode }}'">
                            Use Code
                        </button>
                    </div>
                @endif

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-ivory" onclick="closeEnable2FAModal()">Cancel</button>
                    <button type="submit" class="btn btn-emerald">Verify & Activate 2FA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 2: DISABLE TWO-FACTOR AUTHENTICATION -->
<div id="disable2FAModal" class="modal-backdrop" onclick="if(event.target === this) closeDisable2FAModal()">
    <div class="modal-box" style="max-width: 440px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #FF8A73; text-transform: uppercase; font-weight: 700;">Security Confirmation</div>
                <h3 style="font-size: 16px;">Disable Two-Factor Authentication</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closeDisable2FAModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('profile.two-factor.disable') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                <p style="font-size: 12.5px; color: var(--text-muted); line-height: 1.5;">
                    Please confirm your current account password to disable Two-Factor Authentication on your account.
                </p>
                <div class="form-group">
                    <label class="form-label">Current Account Password</label>
                    <input type="password" name="current_password" id="disable_2fa_pwd_input" class="form-control" placeholder="••••••••••••" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-ivory" onclick="closeDisable2FAModal()">Cancel</button>
                    <button type="submit" class="btn btn-coral">Confirm & Disable 2FA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: CHANGE ACCOUNT PASSWORD -->
<div id="changePasswordModal" class="modal-backdrop" onclick="if(event.target === this) closePasswordModal()">
    <div class="modal-box" style="max-width: 460px;">
        <div class="modal-header">
            <div>
                <div style="font-size: 10.5px; color: #E5A93C; text-transform: uppercase; font-weight: 700;">Credentials Update</div>
                <h3 style="font-size: 16px;">Change Account Password</h3>
            </div>
            <button type="button" class="btn btn-coral btn-sm" onclick="closePasswordModal()">✕ Close</button>
        </div>
        <div class="modal-body">
            <form action="{{ route('profile.password.update') }}" method="POST" style="display: flex; flex-direction: column; gap: 14px;">
                @csrf
                @method('PUT')
                <div class="form-group">
                    <label class="form-label">Current Password</label>
                    <input type="password" name="current_password" id="current_pwd_input" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">New Password (Min 8 characters)</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" class="btn btn-ivory" onclick="closePasswordModal()">Cancel</button>
                    <button type="submit" class="btn btn-plum">Update Password</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openEnable2FAModal() {
        document.getElementById('enable2FAModal')?.classList.add('open');
        setTimeout(() => document.getElementById('enable_2fa_code_input')?.focus(), 50);
    }

    function closeEnable2FAModal() {
        document.getElementById('enable2FAModal')?.classList.remove('open');
    }

    function openDisable2FAModal() {
        document.getElementById('disable2FAModal')?.classList.add('open');
        setTimeout(() => document.getElementById('disable_2fa_pwd_input')?.focus(), 50);
    }

    function closeDisable2FAModal() {
        document.getElementById('disable2FAModal')?.classList.remove('open');
    }

    function openPasswordModal() {
        document.getElementById('changePasswordModal')?.classList.add('open');
        setTimeout(() => document.getElementById('current_pwd_input')?.focus(), 50);
    }

    function closePasswordModal() {
        document.getElementById('changePasswordModal')?.classList.remove('open');
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeEnable2FAModal();
            closeDisable2FAModal();
            closePasswordModal();
        }
    });
</script>
@endpush
