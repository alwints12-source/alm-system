<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\Notification;
use App\Models\WorkOrder;

class HolderDashboardController extends Controller
{
    public function index($pendingAssignments)
    {
        $userId = auth()->id();

        $myAssets = AssetAssignment::with('asset.category', 'asset.primaryPhoto')
            ->where('holder_id', $userId)
            ->where('status', 'acknowledged')
            ->orderBy('acknowledged_at', 'desc')
            ->get();

        $openRequests = WorkOrder::where('requested_by', $userId)
            ->whereIn('status', ['pending', 'assigned', 'in_progress'])
            ->count();

        $recentRequests = WorkOrder::with('asset')
            ->where('requested_by', $userId)
            ->orderByRaw('COALESCE(reported_at, created_at) DESC')
            ->limit(5)
            ->get();

        $recentNotifications = Notification::where('recipient_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('holder.dashboard', compact(
            'pendingAssignments', 'myAssets', 'openRequests', 'recentRequests', 'recentNotifications'
        ));
    }
}
