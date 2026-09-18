<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetDisposal;
use App\Models\AssetTransfer;
use App\Models\Location;
use App\Models\WorkOrder;

class AdminDashboardController extends Controller
{
    public function index($pendingAssignments)
    {
        $totalAssets = Asset::count();
        $activeAssets = Asset::where('status', 'active')->count();
        $activePercent = $totalAssets > 0 ? round(($activeAssets / $totalAssets) * 100) : 0;

        $newThisMonth = Asset::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $pendingTransfersCount = AssetTransfer::where('status', 'pending')->count();

        $currentYear = now()->year;
        $disposedThisYear = AssetDisposal::where('status', 'approved')
            ->whereYear('approved_at', $currentYear)
            ->count();

        // Monthly acquisitions, current year — all 12 months shown,
        // including honest zeros for quiet months
        $monthlyAcquisitions = collect(range(1, 12))->map(fn ($month) =>
            Asset::whereYear('acquisition_date', $currentYear)
                ->whereMonth('acquisition_date', $month)
                ->count()
        );

        // Department distribution — active fleet only
        $totalForDept = Asset::where('status', '!=', 'disposed')->count();
        $byDepartment = Location::withCount(['assets' => fn ($q) => $q->where('status', '!=', 'disposed')])
            ->get()
            ->map(fn ($loc) => [
                'name'  => $loc->name,
                'count' => $loc->assets_count,
                'pct'   => $totalForDept > 0 ? round(($loc->assets_count / $totalForDept) * 100) : 0,
            ])
            ->sortByDesc('count')
            ->values();

        $recentAssets = Asset::with('category', 'location')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Unassigned — active assets with no current holder, the
        // actionable panel at the bottom of the dashboard
        $unassignedAssets = Asset::where('status', '!=', 'disposed')
            ->whereDoesntHave('currentAssignment')
            ->with('category', 'location')
            ->limit(10)
            ->get();
        $unassignedCount = $unassignedAssets->count();

        $pendingAcknowledgementsCount = AssetAssignment::where('status', 'pending_acknowledgement')->count();

        $maintenanceDueThisWeek = WorkOrder::whereIn('status', ['assigned', 'in_progress'])
            ->whereBetween('due_date', [now(), now()->addDays(7)])
            ->count();

        $warrantyExpiringSoon = Asset::whereNotNull('warranty_expiry_date')
            ->whereBetween('warranty_expiry_date', [now(), now()->addDays(30)])
            ->count();

        return view('admin.dashboard', compact(
            'pendingAssignments', 'totalAssets', 'activeAssets', 'activePercent', 'newThisMonth',
            'pendingTransfersCount', 'disposedThisYear', 'monthlyAcquisitions', 'byDepartment',
            'recentAssets', 'unassignedAssets', 'unassignedCount', 'pendingAcknowledgementsCount',
            'maintenanceDueThisWeek', 'warrantyExpiringSoon'
        ));
    }
}
