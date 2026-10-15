<x-prototype-layout title="Technical Admin">
<div class="ph">
<div class="ph-l">
<div class="bc">Technical Admin</div>
<div class="pt">Dashboard overview</div>
</div>
<a href="{{ url('/techadmin/users') }}" class="btn pri"><i class="ti ti-users" style="font-size:14px"></i> Manage users</a>
</div>
@if (session('status'))
<div style="background:#f0fdf4;border:1px solid #c0dd97;color:#2d7d32;border-radius:8px;padding:10px 14px;margin-bottom:14px;font-size:12.5px">{{ session('status') }}</div>
@endif
@include('partials.pending-assignments-banner')
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:18px">
<a href="{{ url('/techadmin/users') }}" class="card" style="text-decoration:none;display:block">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-users" style="font-size:13px"></i> Total users</div>
<div style="font-size:26px;font-weight:700;color:#0f2d5e">{{ $total }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">All accounts</div>
</a>
<a href="{{ url('/techadmin/users?status=active') }}" class="card" style="text-decoration:none;display:block">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-user-check" style="font-size:13px"></i> Active users</div>
<div style="font-size:26px;font-weight:700;color:#2d7d32">{{ $active }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">Can sign in</div>
</a>
<a href="{{ url('/techadmin/users?status=inactive') }}" class="card" style="text-decoration:none;display:block">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-user-off" style="font-size:13px"></i> Inactive users</div>
<div style="font-size:26px;font-weight:700;color:#7a4a0a">{{ $inactive }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">Deactivated accounts</div>
</a>
<a href="{{ url('/techadmin/users?mfa=on') }}" class="card" style="text-decoration:none;display:block">
<div style="font-size:12px;color:#64748b;margin-bottom:6px"><i class="ti ti-shield-lock" style="font-size:13px"></i> MFA enabled</div>
<div style="font-size:26px;font-weight:700;color:#185fa5">{{ $mfa }}</div>
<div style="font-size:11px;color:#94a3b8;margin-top:4px">{{ $mfaPct }}% of users</div>
</a>
</div>
@php
$roleLabels = ['administrative_admin' => 'Administrative Admin', 'technical_admin' => 'Technical Admin', 'technician' => 'Technician', 'asset_holder' => 'Asset Holder'];
@endphp
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px">
<div class="card">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:14px">Users by role</div>
@foreach ($roleLabels as $key => $label)
@php $n = $byRole[$key] ?? 0; $pct = $total > 0 ? round($n / $total * 100) : 0; @endphp
<div style="margin-bottom:12px">
<div style="display:flex;justify-content:space-between;font-size:12.5px;color:#334155;margin-bottom:4px"><span>{{ $label }}</span><span style="font-weight:600">{{ $n }}</span></div>
<div style="height:7px;background:#f1f5f9;border-radius:4px"><div style="height:7px;width:{{ $pct }}%;background:#185fa5;border-radius:4px"></div></div>
</div>
@endforeach
</div>
<div class="card">
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
<div style="font-size:14px;font-weight:700;color:#0f2d5e">Recently created accounts</div>
<a href="{{ url('/techadmin/users') }}" style="font-size:12px;color:#185fa5">View all</a>
</div>
@foreach ($recentUsers as $u)
<div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-top:0.5px solid #e5e9f0">
<div>
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $u->name }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $roleLabels[$u->role] ?? ucfirst(str_replace('_',' ',$u->role)) }} · {{ $u->created_at->format('M j, Y') }}</div>
</div>
<span style="font-size:11px;padding:2px 8px;border-radius:10px;background:{{ $u->is_active ? '#f0fdf4' : '#fef2f2' }};color:{{ $u->is_active ? '#2d7d32' : '#b91c1c' }}">{{ $u->is_active ? 'Active' : 'Inactive' }}</span>
</div>
@endforeach
</div>
</div>
<div class="card">
<div style="font-size:14px;font-weight:700;color:#0f2d5e;margin-bottom:12px">Recent sign-ins</div>
@forelse ($recentLogins as $u)
<div style="display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-top:0.5px solid #e5e9f0">
<div>
<div style="font-size:13px;font-weight:600;color:#0f2d5e">{{ $u->name }}</div>
<div style="font-size:11.5px;color:#64748b">{{ $roleLabels[$u->role] ?? $u->role }}</div>
</div>
<div style="text-align:right">
<div style="font-size:12px;color:#334155">{{ \Carbon\Carbon::parse($u->last_login_at)->diffForHumans() }}</div>
<div style="font-size:11px;color:{{ $u->mfa_enabled ? '#2d7d32' : '#94a3b8' }}">{{ $u->mfa_enabled ? 'MFA on' : 'MFA off' }}</div>
</div>
</div>
@empty
<div style="text-align:center;color:#94a3b8;padding:20px;font-size:12.5px">No sign-ins recorded yet.</div>
@endforelse
</div>
</x-prototype-layout>
