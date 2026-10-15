<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceSchedule;
use App\Models\WorkOrder;

class TechnicianDashboardController extends Controller
{
    public function index($pendingAssignments)
    {
        $uid  = auth()->id();
        $open = ['assigned', 'in_progress'];
        $mine = fn () => WorkOrder::where('assigned_to', $uid);

        $openCount      = $mine()->whereIn('status', $open)->count();
        $inProgress     = $mine()->where('status', 'in_progress')->count();
        $dueToday       = $mine()->whereIn('status', $open)->whereDate('due_date', today())->count();
        $overdue        = $mine()->whereIn('status', $open)->whereDate('due_date', '<', today())->count();
        $completedMonth = $mine()->where('status', 'completed')
            ->whereBetween('completed_at', [now()->startOfMonth(), now()->endOfMonth()])->count();

        $nextJobs = $mine()->with('asset.primaryPhoto')->whereIn('status', $open)
            ->orderByRaw('due_date ASC NULLS LAST')->limit(6)->get();

        $upcoming = MaintenanceSchedule::with('asset')->where('is_active', true)
            ->where('default_assignee_id', $uid)->whereNotNull('next_due_date')
            ->orderBy('next_due_date')->limit(5)->get();

        return view('technician.dashboard', compact(
            'pendingAssignments', 'openCount', 'inProgress', 'dueToday',
            'overdue', 'completedMonth', 'nextJobs', 'upcoming'
        ));
    }
}
