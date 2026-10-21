<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} - ALM System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/prototype.css') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/dost-logo.png') }}">
</head>
<body>
    <div class="app">

        {{-- SIDEBAR --}}
        <div class="sidebar">
            <div class="sb-logo">
                <img src="{{ asset('images/dost-logo.png') }}" alt="DOST logo" style="width:44px;height:44px;flex-shrink:0;display:block">
                <div>
                    <div class="sb-org">DOST-PES</div>
                    <div class="sb-sys">Asset Lifecycle<br>Management System</div>
                </div>
            </div>

            <nav>
                <div class="sb-sect">Menu</div>
                    @php
                    // Highlights the menu item that matches the current page.
                    $on = function (string $name, array $except = []) {
                        $pattern = preg_replace('/\.index$/', '.*', $name);
                        if (! request()->routeIs($pattern)) {
                            return '';
                        }
                        foreach ($except as $route) {
                            if (request()->routeIs($route)) {
                                return '';
                            }
                        }
                        return 'active';
                    };
                @endphp
                <a href="{{ route('dashboard') }}" class="sb-item {{ $on('dashboard') }}">
                        <i class="ti ti-layout-dashboard"></i><span>Dashboard</span>
                </a>
               

                @php
                    $role = auth()->user()->role;
                @endphp

                @if($role === 'administrative_admin')
                    {{-- Admin manages the asset lifecycle: register, inventory, transfers, disposal, reports --}}
                    <a href="{{ route('admin.assets.create') }}" class="sb-item {{ $on('admin.assets.create') }}"><i class="ti ti-plus"></i><span>Register Asset</span></a>
                    <a href="{{ route('admin.assets.index') }}" class="sb-item {{ $on('admin.assets.index', ['admin.assets.create']) }}"><i class="ti ti-package"></i><span>Asset Inventory</span></a>
                    <a href="{{ route('admin.requests.index') }}" class="sb-item {{ $on('admin.requests.index') }}"><i class="ti ti-send-2"></i><span>Requests</span></a>
                    <a href="{{ route('admin.asset-conditions.index') }}" class="sb-item {{ $on('admin.asset-conditions.index') }}"><i class="ti ti-activity"></i><span>Asset Conditions</span></a>
                    <a href="{{ route('admin.warranty.index') }}" class="sb-item {{ $on('admin.warranty.index') }}"><i class="ti ti-shield-check"></i><span>Warranty</span></a>
                    <a href="{{ route('admin.sla-policies.index') }}" class="sb-item {{ $on('admin.sla-policies.index') }}"><i class="ti ti-shield-check"></i><span>SLA & Compliance</span></a>
                    <a href="{{ route('admin.maintenance-schedules.index') }}" class="sb-item {{ $on('admin.maintenance-schedules.index') }}"><i class="ti ti-calendar-time"></i><span>Maintenance Schedule</span></a>
                    <a href="{{ route('admin.transfers.index') }}" class="sb-item {{ $on('admin.transfers.index') }}"><i class="ti ti-arrows-exchange"></i><span>Transfers</span></a>
                    <a href="{{ route('admin.disposals.index') }}" class="sb-item {{ $on('admin.disposals.index') }}"><i class="ti ti-trash"></i><span>Disposal</span></a>
                    <a href="{{ route('admin.depreciation.index') }}" class="sb-item {{ $on('admin.depreciation.index') }}"><i class="ti ti-chart-line"></i><span>Depreciation</span></a>
                    <a href="{{ route('admin.reports.index') }}" class="sb-item {{ $on('admin.reports.index') }}"><i class="ti ti-chart-bar"></i><span>Reports</span></a>
                    <a href="{{ route('holder.assets.index') }}" class="sb-item {{ $on('holder.assets.index') }}"><i class="ti ti-package"></i><span>My Assets</span></a>
                    <a href="{{ route('holder.requests.index') }}" class="sb-item {{ $on('holder.requests.index') }}"><i class="ti ti-clipboard-list"></i><span>My Requests</span></a>
                @elseif($role === 'technical_admin')
                    {{-- Tech Admin manages system users and roles --}}
                    <a href="{{ route('techadmin.users.index') }}" class="sb-item {{ $on('techadmin.users.index') }}"><i class="ti ti-users"></i><span>Users</span></a>
                    <a href="{{ route('techadmin.roles.index') }}" class="sb-item {{ $on('techadmin.roles.index') }}"><i class="ti ti-shield-lock"></i><span>Roles</span></a>
                    <a href="{{ route('holder.assets.index') }}" class="sb-item {{ $on('holder.assets.index') }}"><i class="ti ti-package"></i><span>My Assets</span></a>
                    <a href="{{ route('holder.requests.index') }}" class="sb-item {{ $on('holder.requests.index') }}"><i class="ti ti-clipboard-list"></i><span>My Requests</span></a>
                @elseif($role === 'asset_holder')
                    <a href="{{ route('holder.assets.index') }}" class="sb-item {{ $on('holder.assets.index') }}"><i class="ti ti-package"></i><span>My Assets</span></a>
                    <a href="{{ route('holder.requests.index') }}" class="sb-item {{ $on('holder.requests.index') }}"><i class="ti ti-clipboard-list"></i><span>Requests</span></a>
                @elseif($role === 'technician')
                    <a href="{{ route('technician.workorders.index') }}" class="sb-item {{ $on('technician.workorders.index') }}"><i class="ti ti-tool"></i><span>Work Orders</span></a>
                    <a href="{{ route('holder.assets.index') }}" class="sb-item {{ $on('holder.assets.index') }}"><i class="ti ti-package"></i><span>My Assets</span></a>
                    <a href="{{ route('technician.maintenance-schedule.index') }}" class="sb-item {{ $on('technician.maintenance-schedule.index') }}"><i class="ti ti-calendar-time"></i><span>Maintenance Schedule</span></a>
                @endif

                <div class="sb-sect">General</div>
                @php $unreadCount = \App\Models\Notification::where('recipient_id', auth()->id())->where('is_read', false)->count(); @endphp
                <a href="{{ route('notifications.index') }}" class="sb-item {{ $on('notifications.index') }}"><i class="ti ti-bell"></i><span>Notifications</span>@if ($unreadCount > 0)<span style="margin-left:auto;background:#dc2626;color:#fff;font-size:10px;font-weight:700;padding:1px 6px;border-radius:10px">{{ $unreadCount }}</span>@endif</a>
                <a href="{{ route('settings.profile') }}" class="sb-item {{ request()->routeIs('settings.*') ? 'active' : '' }}"><i class="ti ti-settings"></i><span>Settings</span></a>
            </nav>

            <div class="sb-footer">
                <div class="sb-user">
                    <div class="ava">{{ strtoupper(substr(auth()->user()->first_name, 0, 1) . substr(auth()->user()->last_name, 0, 1)) }}</div>
                    <div>
                        <div class="sb-uname">{{ auth()->user()->name }}</div>
                        <div class="sb-urole">{{ \Illuminate\Support\Str::headline($role) }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn sm" style="margin-top:10px;width:100%;justify-content:center;background:rgba(255,255,255,0.08);border-color:rgba(255,255,255,0.15);color:rgba(255,255,255,0.7);font-size:11.5px">
                        <i class="ti ti-logout" style="font-size:13px"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>

        {{-- MAIN AREA --}}
        <div class="main">
            <div class="topbar">
                <div class="topbar-title">{{ $title ?? 'Dashboard' }}</div>
                <form method="GET" action="{{ url('/search') }}" class="srch">
                    <i class="ti ti-search" style="font-size:13px;color:#9aa3b2"></i>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ $role === 'administrative_admin' ? 'Search assets, requests, users...' : ($role === 'technical_admin' ? 'Search users...' : 'Search my assets and requests...') }}">
                </form>
                <a href="{{ route('notifications.index') }}" class="ic-btn" aria-label="Notifications" style="position:relative;text-decoration:none">
                    <i class="ti ti-bell" style="font-size:15px"></i>
                    @if ($unreadCount > 0)<span style="position:absolute;top:-4px;right:-4px;background:#dc2626;color:#fff;font-size:9px;font-weight:700;min-width:15px;height:15px;line-height:15px;text-align:center;border-radius:8px">{{ $unreadCount }}</span>@endif
                </a>
                <div id="profile-wrap" style="position:relative">
                    <button type="button" class="ic-btn" aria-label="Profile" onclick="var m=document.getElementById('profile-menu');m.style.display=m.style.display==='block'?'none':'block'">
                        <i class="ti ti-user" style="font-size:15px"></i>
                    </button>
                    <div id="profile-menu" style="display:none;position:absolute;right:0;top:42px;width:220px;background:#fff;border:1px solid #e5e9f0;border-radius:10px;box-shadow:0 8px 24px rgba(15,45,94,.12);z-index:50;padding:6px">
                        <div style="padding:8px 10px 10px;border-bottom:1px solid #eef1f6;margin-bottom:4px">
                            <div style="font-size:13px;font-weight:700;color:#0f2d5e">{{ auth()->user()->name }}</div>
                            <div style="font-size:11.5px;color:#64748b">{{ \Illuminate\Support\Str::headline(auth()->user()->role) }}</div>
                        </div>
                        <a href="{{ route('settings.profile') }}" style="display:block;padding:8px 10px;font-size:12.5px;color:#334155;text-decoration:none;border-radius:6px">Profile</a>
                        <a href="{{ url('/settings/mfa') }}" style="display:block;padding:8px 10px;font-size:12.5px;color:#334155;text-decoration:none;border-radius:6px">Security (MFA)</a>
                        <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" style="width:100%;text-align:left;background:none;border:none;padding:8px 10px;font-size:12.5px;color:#b91c1c;cursor:pointer;border-radius:6px">Sign out</button></form>
                    </div>
                </div>
            </div>

            <div class="content">
                {{ $slot }}
            </div>
        </div>

    </div>
<script>document.addEventListener('click',function(e){if(!e.target.closest('#profile-wrap')){var m=document.getElementById('profile-menu');if(m){m.style.display='none';}}});</script>
</body>
</html>
