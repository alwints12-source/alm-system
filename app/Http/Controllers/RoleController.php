<?php

namespace App\Http\Controllers;

use App\Models\User;

class RoleController extends Controller
{
    public function index()
    {
        $roles = [
            'administrative_admin' => ['label' => 'Administrative Admin', 'abbr' => 'AA', 'desc' => 'Full asset lifecycle control', 'color' => '#4a9eff'],
            'technical_admin'      => ['label' => 'Technical Admin', 'abbr' => 'TA', 'desc' => 'System and user management', 'color' => '#7c5cd6'],
            'asset_holder'         => ['label' => 'Asset Holder', 'abbr' => 'AH', 'desc' => 'View assets in custody and request maintenance', 'color' => '#2e9e6b'],
            'technician'           => ['label' => 'Technician', 'abbr' => 'TC', 'desc' => 'Carry out assigned work orders', 'color' => '#e8a33d'],
        ];

        $counts = User::selectRaw('role, count(*) as total, sum(case when is_active then 1 else 0 end) as active')
            ->groupBy('role')->get()->keyBy('role');

        $all = array_keys($roles);

        $permissions = [
            ['Register and manage assets', ['administrative_admin']],
            ['Assign assets to holders', ['administrative_admin']],
            ['Approve or reject maintenance requests', ['administrative_admin']],
            ['Approve transfers and disposals', ['administrative_admin']],
            ['Manage SLA policies and maintenance schedules', ['administrative_admin']],
            ['View reports and depreciation', ['administrative_admin']],
            ['Create and manage user accounts', ['technical_admin']],
            ['View roles and permissions', ['technical_admin']],
            ['Work on assigned work orders', ['technician']],
            ['View maintenance schedule', ['technician']],
            ['View assets in own custody', $all],
            ['Report an asset issue', $all],
            ['Request an asset transfer', $all],
            ['Acknowledge assigned assets', $all],
            ['Notifications and MFA settings', $all],
        ];

        return view('techadmin.roles.index', compact('roles', 'counts', 'permissions'));
    }
}
