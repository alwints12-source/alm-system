<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    // ============================================================
    // ASSET HOLDER — report an issue, and track requests already made
    // ============================================================

    public function store(Request $request, AssetAssignment $assignment)
    {
        abort_if($assignment->holder_id !== auth()->id(), 403);
        abort_if($assignment->status !== 'acknowledged', 403, 'You can only report issues on assets you have acknowledged.');

        $validated = $request->validate([
            'title'       => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'priority'    => ['required', 'in:critical,high,medium,low'],
        ]);

        $nextNumber = (WorkOrder::max('id') ?? 0) + 1;
        $workOrderNumber = 'WO-' . now()->year . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        WorkOrder::create([
            'work_order_number' => $workOrderNumber,
            'asset_id'          => $assignment->asset_id,
            'title'             => $validated['title'],
            'description'       => $validated['description'],
            'priority'          => $validated['priority'],
            'maintenance_type'  => 'corrective',
            'status'            => 'pending',
            'requested_by'      => auth()->id(),
            'reported_at'       => now(),
        ]);

        return redirect()->route('holder.assets.index')
            ->with('status', 'Issue reported. Admin will review and assign a technician.');
    }

    /**
     * "My Requests" — everything this holder has ever reported, with
     * live status and resolution notes once closed.
     */
    public function myRequests()
    {
        $requests = WorkOrder::with('asset', 'assignedTo')
            ->where('requested_by', auth()->id())
            ->orderBy('reported_at', 'desc')
            ->get();

        return view('holder.requests.index', compact('requests'));
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN — review requests, approve & assign, or reject
    // ============================================================

    public function index()
    {
        $requests = WorkOrder::with('asset', 'requestedBy', 'assignedTo')
            ->orderBy('reported_at', 'desc')
            ->get();

        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('admin.requests.index', compact('requests', 'technicians'));
    }

    public function approve(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'assigned_to'      => ['required', 'exists:users,id'],
            'due_date'         => ['required', 'date'],
            'due_time'         => ['required'],
            'maintenance_type' => ['required', 'in:preventive,corrective,predictive,emergency'],
            'estimated_cost'   => ['nullable', 'numeric', 'min:0'],
        ]);

        $dueDateTime = $validated['due_date'] . ' ' . $validated['due_time'];

        $workOrder->update([
            'assigned_to'      => $validated['assigned_to'],
            'approved_by'      => auth()->id(),
            'due_date'         => $dueDateTime,
            'maintenance_type' => $validated['maintenance_type'],
            'estimated_cost'   => $validated['estimated_cost'] ?? null,
            'status'           => 'assigned',
        ]);

        Notification::create([
            'recipient_id' => $workOrder->requested_by,
            'type'         => 'work_order.assigned',
            'channel'      => 'in_app',
            'title'        => 'Your maintenance request was approved',
            'body'         => "{$workOrder->title} has been assigned to a technician, scheduled for " . \Carbon\Carbon::parse($dueDateTime)->format('M j, Y g:i A') . '.',
            'related_type' => 'work_order',
            'related_id'   => $workOrder->id,
        ]);

        return redirect()->route('admin.requests.index')
            ->with('status', 'Request approved and assigned.');
    }

    public function reject(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'resolution_notes' => ['nullable', 'string'],
        ]);

        $workOrder->update([
            'status'           => 'cancelled',
            'approved_by'      => auth()->id(),
            'resolution_notes' => $validated['resolution_notes'] ?? null,
        ]);

        return redirect()->route('admin.requests.index')
            ->with('status', 'Request rejected.');
    }

    // ============================================================
    // TECHNICIAN — see assigned work, execute, complete
    // ============================================================

    public function technicianIndex()
    {
        $workOrders = WorkOrder::with('asset', 'requestedBy')
            ->where('assigned_to', auth()->id())
            ->orderBy('due_date')
            ->get();

        return view('technician.workorders.index', compact('workOrders'));
    }

    public function show(WorkOrder $workOrder)
    {
        abort_if($workOrder->assigned_to !== auth()->id(), 403);

        $workOrder->load('asset', 'requestedBy');

        return view('technician.workorders.show', compact('workOrder'));
    }

    public function startWork(WorkOrder $workOrder)
    {
        abort_if($workOrder->assigned_to !== auth()->id(), 403);

        $workOrder->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        return redirect()->route('technician.workorders.show', $workOrder)
            ->with('status', 'Work order started.');
    }

    public function complete(Request $request, WorkOrder $workOrder)
    {
        abort_if($workOrder->assigned_to !== auth()->id(), 403);

        $validated = $request->validate([
            'resolution_notes' => ['required', 'string'],
            'condition'        => ['required', 'in:excellent,good,fair,poor,unserviceable'],
            'actual_cost'      => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($workOrder, $validated) {
            $workOrder->update([
                'status'           => 'completed',
                'completed_at'     => now(),
                'resolution_notes' => $validated['resolution_notes'],
                'actual_cost'      => $validated['actual_cost'] ?? null,
            ]);

            $workOrder->asset->update([
                'condition' => $validated['condition'],
                'status'    => 'active',
            ]);

            Notification::create([
                'recipient_id' => $workOrder->requested_by,
                'type'         => 'work_order.completed',
                'channel'      => 'in_app',
                'title'        => 'Your maintenance request was resolved',
                'body'         => "{$workOrder->title} has been completed.",
                'related_type' => 'work_order',
                'related_id'   => $workOrder->id,
            ]);
        });

        return redirect()->route('technician.workorders.index')
            ->with('status', 'Work order marked complete. Asset condition updated.');
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN — read-only condition overview
    // ============================================================

    public function assetConditions()
    {
        $assets = \App\Models\Asset::with(['workOrders' => function ($query) {
            $query->where('status', 'completed')->with('assignedTo');
        }])->orderBy('asset_tag')->get();

        return view('admin.asset-conditions.index', compact('assets'));
    }
}
