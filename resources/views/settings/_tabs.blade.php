@php
    $tabs = [
        'profile'  => ['Profile', 'settings.profile', 'ti-user'],
        'password' => ['Password', 'settings.password', 'ti-lock'],
        'security' => ['Security (MFA)', 'settings.mfa', 'ti-shield-check'],
    ];
@endphp
<div style="display:flex;gap:6px;margin-bottom:18px;border-bottom:1px solid #e2e8f0">
    @foreach ($tabs as $key => [$label, $routeName, $icon])
        <a href="{{ route($routeName) }}"
           style="padding:9px 14px;font-size:12.5px;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;border-bottom:2px solid {{ $active === $key ? '#0f2d5e' : 'transparent' }};color:{{ $active === $key ? '#0f2d5e' : '#64748b' }}">
            <i class="ti {{ $icon }}" style="font-size:14px"></i> {{ $label }}
        </a>
    @endforeach
</div>
