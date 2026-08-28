<x-prototype-layout title="Security settings">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Settings</div>
            <div class="pt">Multi-factor authentication</div>
        </div>
    </div>

    @if (session('status'))
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            {{ session('status') }}
        </div>
    @endif

    @if ($user->mfa_enabled)

        <div class="card" style="background:#f0fdf4;border-color:#c0dd97;max-width:520px">
            <div style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:700;color:#2d7d32;margin-bottom:8px">
                <i class="ti ti-shield-check" style="font-size:18px"></i> MFA is active
            </div>
            <div style="font-size:12.5px;color:#334155;margin-bottom:16px">
                Enabled since {{ $user->mfa_confirmed_at->format('M j, Y g:i A') }}. You'll be asked for a
                6-digit code from your authenticator app each time you log in.
            </div>

            <form method="POST" action="{{ route('settings.mfa.disable') }}" onsubmit="return confirm('Are you sure? This will remove the extra security step from your account.')">
                @csrf
                @method('DELETE')

                @if ($errors->any())
                    <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <div class="fg">
                    <label>Confirm your password to disable MFA</label>
                    <input type="password" name="password" placeholder="Current password" required>
                </div>

                <button type="submit" class="btn sm" style="border-color:#e24b4a;color:#e24b4a">
                    <i class="ti ti-shield-off" style="font-size:13px"></i> Disable MFA
                </button>
            </form>
        </div>

    @else

        <div class="card" style="max-width:520px">
            <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:8px">Set up multi-factor authentication</div>
            <div style="font-size:12.5px;color:#64748b;margin-bottom:16px">
                Scan this QR code with an authenticator app (Google Authenticator, Authy, Microsoft Authenticator),
                then enter the 6-digit code it generates below to confirm setup.
            </div>

            <div style="text-align:center;background:#f8fafc;border-radius:8px;padding:20px;margin-bottom:16px">
                {!! QrCode::size(200)->generate($qrCodeUrl) !!}
            </div>

            <div style="font-size:11.5px;color:#94a3b8;text-align:center;margin-bottom:20px">
                Can't scan? Enter this key manually: <strong style="color:#334155;letter-spacing:1px">{{ $secret }}</strong>
            </div>

            @if ($errors->any())
                <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('settings.mfa.enable') }}">
                @csrf
                <div class="fg">
                    <label>Enter the 6-digit code from your app</label>
                    <input type="text" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}" required autofocus style="letter-spacing:4px;font-size:18px;text-align:center">
                </div>
                <button type="submit" class="btn pri sm">
                    <i class="ti ti-check" style="font-size:13px"></i> Verify and enable
                </button>
            </form>
        </div>

    @endif

</x-prototype-layout>
