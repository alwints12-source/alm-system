<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verify it's you - ALM System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/prototype.css') }}">
</head>
<body>

<div class="login-wrap">
    <div class="login-card">
        <div class="login-head">
            <div class="login-icon"><i class="ti ti-shield-lock"></i></div>
            <div class="login-title">Verify it's you</div>
            <div class="login-sub">Enter the 6-digit code from your authenticator app</div>
            <div class="login-divider"></div>
        </div>

        @if ($errors->any())
            <div style="background:#fff0f0;border:1px solid #fbc5c5;color:#a32d2d;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">
                @foreach ($errors->all() as $error)
                    {{ $error }}
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('mfa.verify') }}" class="login-form">
            @csrf
            <div>
                <label for="code">Authentication code</label>
                <input id="code" type="text" name="code" placeholder="000000" maxlength="6" pattern="[0-9]{6}"
                       required autofocus autocomplete="one-time-code"
                       style="letter-spacing:6px;font-size:20px;text-align:center">
            </div>

            <button type="submit" class="login-btn">
                <i class="ti ti-check" style="font-size:16px"></i> Verify and continue
            </button>
        </form>

        <p style="text-align:center;font-size:11px;color:#9aa3b2;margin-top:18px">
            <a href="{{ route('login') }}" style="color:#9aa3b2">Cancel and return to login</a>
        </p>
    </div>
</div>

</body>
</html>
