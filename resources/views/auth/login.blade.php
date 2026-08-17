<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - ALM System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/prototype.css') }}">
</head>
<body>

<div class="login-wrap">
    <div class="login-card">
        <div class="login-head">
            <div class="login-icon"><i class="ti ti-building-bank"></i></div>
            <div class="login-title">Asset Lifecycle Management System</div>
            <div class="login-sub">Department of Science and Technology — PES</div>
            <div class="login-divider"></div>
        </div>

        {{-- Session status (e.g. password reset confirmation) --}}
        @if (session('status'))
            <div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
                {{ session('status') }}
            </div>
        @endif

        {{-- Validation errors --}}
        @if ($errors->any())
            <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="login-form">
            @csrf

            <div>
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}"
                       placeholder="Enter your email" required autofocus autocomplete="username">
            </div>

            <div>
                <label for="password">Password</label>
                <input id="password" type="password" name="password"
                       placeholder="Enter your password" required autocomplete="current-password">
            </div>

            <div class="login-extra">
                <label style="display:flex;gap:6px;align-items:center;cursor:pointer">
                    <input type="checkbox" name="remember" style="accent-color:#0f2d5e;width:14px;height:14px">
                    Remember me
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="login-btn">
                <i class="ti ti-lock" style="font-size:16px"></i> Secure Login
            </button>
        </form>

        <p style="text-align:center;font-size:11px;color:#9aa3b2;margin-top:18px">
            DOST-PES Internal System &nbsp;·&nbsp; Authorized Personnel Only &nbsp;·&nbsp; v2.4.1
        </p>
    </div>
</div>

</body>
</html>
