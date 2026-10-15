<?php

namespace App\Http\Controllers;

use App\Models\User;

class TechAdminDashboardController extends Controller
{
    public function index($pendingAssignments)
    {
        $total    = User::count();
        $active   = User::where('is_active', true)->count();
        $inactive = $total - $active;
        $mfa      = User::where('mfa_enabled', true)->count();
        $mfaPct   = $total > 0 ? round($mfa / $total * 100) : 0;

        $byRole = User::selectRaw('role, count(*) as total')
            ->groupBy('role')->pluck('total', 'role');

        $recentUsers  = User::orderBy('created_at', 'desc')->limit(5)->get();
        $recentLogins = User::whereNotNull('last_login_at')
            ->orderBy('last_login_at', 'desc')->limit(5)->get();

        return view('techadmin.dashboard', compact(
            'pendingAssignments', 'total', 'active', 'inactive',
            'mfa', 'mfaPct', 'byRole', 'recentUsers', 'recentLogins'
        ));
    }
}
