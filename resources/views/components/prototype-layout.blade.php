<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard' }} - ALM System</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/prototype.css') }}">
</head>
<body>
    <div class="app">

        {{-- SIDEBAR --}}
        <div class="sidebar">
            <div class="sb-logo">
                <div class="sb-logo-icon"><i class="ti ti-building-bank"></i></div>
                <div>
                    <div class="sb-org">DOST-PES</div>
                    <div class="sb-sys">Asset Lifecycle<br>Management System</div>
                </div>
            </div>

            <nav>
                <div class="sb-sect">Menu</div>
                <div class="sb-item active">
                    <i class="ti ti-layout-dashboard"></i><span>Dashboard</span>
                </div>

                @php
                    $role = auth()->user()->role;
                @endphp

                @if($role === 'administrative_admin')
                    {{-- Admin manages the asset lifecycle: register, inventory, transfers, disposal, reports --}}
                    <a href="{{ route('admin.assets.create') }}" class="sb-item"><i class="ti ti-plus"></i><span>Register Asset</span></a>
                    <a href="{{ route('admin.assets.index') }}" class="sb-item"><i class="ti ti-package"></i><span>Asset Inventory</span></a>
                    <a href="{{ route('admin.requests.index') }}" class="sb-item"><i class="ti ti-send-2"></i><span>Requests</span></a>
                    <a href="{{ route('admin.asset-conditions.index') }}" class="sb-item"><i class="ti ti-activity"></i><span>Asset Conditions</span></a>
                    <div class="sb-item"><i class="ti ti-arrows-exchange"></i><span>Transfers</span></div>
                    <div class="sb-item"><i class="ti ti-trash"></i><span>Disposal</span></div>
                    <div class="sb-item"><i class="ti ti-chart-bar"></i><span>Reports</span></div>
                @elseif($role === 'technical_admin')
                    {{-- Tech Admin manages system users and roles --}}
                    <a href="{{ route('techadmin.users.index') }}" class="sb-item"><i class="ti ti-users"></i><span>Users</span></a>
                    <div class="sb-item"><i class="ti ti-shield-lock"></i><span>Roles</span></div>
                @elseif($role === 'asset_holder')
                    <a href="{{ route('holder.assets.index') }}" class="sb-item"><i class="ti ti-package"></i><span>My Assets</span></a>
                    <a href="{{ route('holder.requests.index') }}" class="sb-item"><i class="ti ti-clipboard-list"></i><span>Requests</span></a>
                @elseif($role === 'technician')
                    <a href="{{ route('technician.workorders.index') }}" class="sb-item"><i class="ti ti-tool"></i><span>Work Orders</span></a>
                    <a href="{{ route('holder.assets.index') }}" class="sb-item"><i class="ti ti-package"></i><span>My Assets</span></a>
                    <div class="sb-item"><i class="ti ti-calendar-time"></i><span>Maintenance Schedule</span></div>
                @endif

                <div class="sb-sect">General</div>
                <div class="sb-item"><i class="ti ti-bell"></i><span>Notifications</span></div>
                <div class="sb-item"><i class="ti ti-settings"></i><span>Settings</span></div>
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
                <div class="srch">
                    <i class="ti ti-search" style="font-size:13px;color:#9aa3b2"></i>
                    <input type="text" placeholder="Search assets, users...">
                </div>
                <button class="ic-btn" aria-label="Notifications">
                    <i class="ti ti-bell" style="font-size:15px"></i>
                </button>
                <button class="ic-btn" aria-label="Profile">
                    <i class="ti ti-user" style="font-size:15px"></i>
                </button>
            </div>

            <div class="content">
                {{ $slot }}
            </div>
        </div>

    </div>
</body>
</html>
