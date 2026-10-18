<x-prototype-layout title="Settings">

    <div class="ph">
        <div class="ph-l">
            <div class="bc">Settings</div>
            <div class="pt">Change password</div>
        </div>
    </div>

    @include('settings._tabs', ['active' => 'password'])

    @if (session('status') === 'password-updated')
        <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
            Your password has been changed.
        </div>
    @endif

    <div class="card" style="max-width:520px">
        <div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:4px">Change your password</div>
        <div style="font-size:12.5px;color:#64748b;margin-bottom:16px">
            Use a long, unique password that you do not use anywhere else.
        </div>

        @if ($errors->updatePassword->any())
            <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:12px">
                @foreach ($errors->updatePassword->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            @method('PUT')

            <div class="fg"><label>Current password</label><input type="password" name="current_password" required autocomplete="current-password"></div>
            <div class="fg"><label>New password</label><input type="password" name="password" required autocomplete="new-password"></div>
            <div class="fg"><label>Confirm new password</label><input type="password" name="password_confirmation" required autocomplete="new-password"></div>

            <button type="submit" class="btn pri sm">
                <i class="ti ti-check" style="font-size:13px"></i> Update password
            </button>
        </form>
    </div>

</x-prototype-layout>
