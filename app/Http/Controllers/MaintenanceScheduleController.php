<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\MaintenanceSchedule;
use App\Models\User;
use Illuminate\Http\Request;

class MaintenanceScheduleController extends Controller
{
    /**
     * Frequency label -> number of days, used to compute interval_days
     * automatically so Admin thinks in terms of "quarterly," not a
     * raw day count.
     */
    private const FREQUENCY_DAYS = [
        'daily'       => 1,
        'weekly'      => 7,
        'monthly'     => 30,
        'quarterly'   => 90,
        'semi_annual' => 182,
        'annual'      => 365,
    ];

    // ============================================================
    // ADMINISTRATIVE ADMIN
    // ============================================================

    public function index()
    {
        $schedules = MaintenanceSchedule::with('asset', 'defaultAssignee')
            ->orderBy('next_due_date')
            ->get();

        return view('admin.maintenance-schedules.index', compact('schedules'));
    }

    public function create()
    {
        $assets = Asset::orderBy('asset_tag')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->orderBy('first_name')->get();

        return view('admin.maintenance-schedules.create', compact('assets', 'technicians'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSchedule($request);

        MaintenanceSchedule::create([
            ...$validated,
            'interval_days'   => self::FREQUENCY_DAYS[$validated['frequency']],
            'next_due_date'   => $validated['start_date'],
            'maintenance_type' => 'preventive',
            'is_active'       => true,
            'created_by'      => auth()->id(),
        ]);

        return redirect()->route('admin.maintenance-schedules.index')
            ->with('status', 'Maintenance schedule created.');
    }

    public function edit(MaintenanceSchedule $maintenanceSchedule)
    {
        $assets = Asset::orderBy('asset_tag')->get();
        $technicians = User::where('role', 'technician')->where('is_active', true)->orderBy('first_name')->get();

        return view('admin.maintenance-schedules.edit', [
            'schedule'    => $maintenanceSchedule,
            'assets'      => $assets,
            'technicians' => $technicians,
        ]);
    }

    public function update(Request $request, MaintenanceSchedule $maintenanceSchedule)
    {
        $validated = $this->validateSchedule($request);

        $maintenanceSchedule->update([
            ...$validated,
            'interval_days' => self::FREQUENCY_DAYS[$validated['frequency']],
        ]);

        return redirect()->route('admin.maintenance-schedules.index')
            ->with('status', 'Maintenance schedule updated.');
    }

    public function toggleActive(MaintenanceSchedule $maintenanceSchedule)
    {
        $maintenanceSchedule->update(['is_active' => ! $maintenanceSchedule->is_active]);

        return redirect()->route('admin.maintenance-schedules.index')
            ->with('status', $maintenanceSchedule->is_active ? 'Schedule activated.' : 'Schedule deactivated.');
    }

    // ============================================================
    // TECHNICIAN — read-only, scoped to their own assigned schedules
    // ============================================================

    public function technicianIndex()
    {
        $schedules = MaintenanceSchedule::with('asset')
            ->where('default_assignee_id', auth()->id())
            ->where('is_active', true)
            ->orderBy('next_due_date')
            ->get();

        return view('technician.maintenance-schedule.index', compact('schedules'));
    }

    private function validateSchedule(Request $request): array
    {
        return $request->validate([
            'asset_id'               => ['required', 'exists:assets,id'],
            'title'                  => ['required', 'string', 'max:200'],
            'description'            => ['nullable', 'string'],
            'frequency'              => ['required', 'in:daily,weekly,monthly,quarterly,semi_annual,annual'],
            'start_date'             => ['required', 'date'],
            'end_date'               => ['nullable', 'date', 'after_or_equal:start_date'],
            'default_assignee_id'    => ['required', 'exists:users,id'],
            'estimated_duration_hrs' => ['nullable', 'numeric', 'min:0'],
        ]);
    }
}
