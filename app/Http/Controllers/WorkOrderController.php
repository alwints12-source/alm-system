<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\Notification;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderActivityLog;
use App\Models\WorkOrderChecklistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkOrderController extends Controller
{
    private const DEFAULT_CHECKLIST = [
        'Inspect asset and confirm reported issue',
        'Diagnose root cause',
        'Perform repair or replacement',
        'Test asset functionality',
        'Document work and close out',
    ];

    private function logActivity(WorkOrder $workOrder, string $description): void
    {
        WorkOrderActivityLog::create([
            'work_order_id'      => $workOrder->id,
            'event_description'  => $description,
            'created_by'         => auth()->id(),
        ]);
    }

    // ============================================================
    // ASSET HOLDER
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

        $workOrder = WorkOrder::create([
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

        $this->logActivity($workOrder, 'Issue reported by ' . auth()->user()->name);

        return redirect()->route('holder.assets.index')
            ->with('status', 'Issue reported. Admin will review and assign a technician.');
    }

    public function myRequests()
    {
        $requests = WorkOrder::with('asset', 'assignedTo')
            ->where('requested_by', auth()->id())
            ->orderBy('reported_at', 'desc')
            ->get();

        return view('holder.requests.index', compact('requests'));
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN
    // ============================================================

    public function index()
    {
        $requests = WorkOrder::with('asset.category', 'requestedBy', 'assignedTo', 'slaPolicy')
            ->orderBy('reported_at', 'desc')
            ->get();

        $technicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['assignedWorkOrders as open_jobs' => function ($query) {
                $query->whereIn('status', ['assigned', 'in_progress']);
            }])
            ->orderBy('first_name')
            ->get();

        return view('admin.requests.index', compact('requests', 'technicians'));
    }

    public function approve(Request $request, WorkOrder $workOrder)
    {
        $validated = $request->validate([
            'assigned_to'       => ['required', 'exists:users,id'],
            'due_date'          => ['required', 'date'],
            'due_time'          => ['required'],
            'maintenance_type'  => ['required', 'in:preventive,corrective,predictive,emergency'],
            'estimated_cost'    => ['nullable', 'numeric', 'min:0'],
            'technician_notes'  => ['nullable', 'string'],
        ]);

        $dueDateTime = $validated['due_date'] . ' ' . $validated['due_time'];
        $approvedAt = now();

        DB::transaction(function () use ($workOrder, $validated, $dueDateTime, $approvedAt) {
            $workOrder->update([
                'assigned_to'      => $validated['assigned_to'],
                'approved_by'      => auth()->id(),
                'approved_at'      => $approvedAt,
                'due_date'         => $dueDateTime,
                'maintenance_type' => $validated['maintenance_type'],
                'estimated_cost'   => $validated['estimated_cost'] ?? null,
                'technician_notes' => $validated['technician_notes'] ?? null,
                'status'           => 'assigned',
            ]);

            // SLA: find the applicable policy, lock it in. Response
            // breach is checked against reported_at (how long the
            // Holder waited to be acknowledged at all) — unaffected
            // by today's change, which only touches resolution timing.
            $policy = $workOrder->fresh('asset')->matchingSlaPolicy();

            if ($policy) {
                $responseDeadline = $workOrder->reported_at->copy()->addHours($policy->response_time_hours);
                $responseBreached = $approvedAt->greaterThan($responseDeadline);

                $workOrder->update([
                    'sla_id'          => $policy->id,
                    'sla_breached'    => $responseBreached,
                    'sla_breached_at' => $responseBreached ? $approvedAt : null,
                ]);

                $this->logActivity(
                    $workOrder,
                    $responseBreached
                        ? "SLA response target missed ({$policy->response_time_hours}h target)"
                        : "SLA policy applied: {$policy->name}"
                );
            }

            foreach (self::DEFAULT_CHECKLIST as $index => $description) {
                WorkOrderChecklistItem::create([
                    'work_order_id' => $workOrder->id,
                    'description'   => $description,
                    'sort_order'    => $index,
                ]);
            }

            $technicianName = User::find($validated['assigned_to'])->name;
            $this->logActivity($workOrder, "Approved and assigned to {$technicianName}");
        });

        Notification::create([
            'recipient_id' => $workOrder->requested_by,
            'type'         => 'work_order.assigned',
            'channel'      => 'in_app',
            'title'        => 'Your maintenance request was approved',
            'body'         => "{$workOrder->title} has been assigned to a technician, scheduled for " . \Carbon\Carbon::parse($dueDateTime)->format('M j, Y g:i A') . '.',
            'related_type' => 'work_order',
            'related_id'   => $workOrder->id,
        ]);

        Notification::create([
            'recipient_id' => $workOrder->assigned_to,
            'type'         => 'work_order.new_assignment',
            'channel'      => 'in_app',
            'title'        => 'New work order assigned to you',
            'body'         => "{$workOrder->title} ({$workOrder->asset->asset_tag}) has been assigned to you, due " . \Carbon\Carbon::parse($dueDateTime)->format('M j, Y g:i A') . '.',
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

        $this->logActivity($workOrder, 'Rejected by ' . auth()->user()->name);

        return redirect()->route('admin.requests.index')
            ->with('status', 'Request rejected.');
    }

    // ============================================================
    // TECHNICIAN
    // ============================================================

    public function technicianIndex()
    {
        $workOrders = WorkOrder::with('asset', 'requestedBy', 'slaPolicy')
            ->where('assigned_to', auth()->id())
            ->orderBy('due_date')
            ->get();

        return view('technician.workorders.index', compact('workOrders'));
    }

    public function show(WorkOrder $workOrder)
    {
        abort_if($workOrder->assigned_to !== auth()->id(), 403);

        $workOrder->load('asset', 'requestedBy', 'checklistItems', 'activityLog.createdBy', 'slaPolicy');

        return view('technician.workorders.show', compact('workOrder'));
    }

    public function toggleChecklistItem(WorkOrderChecklistItem $item)
    {
        $workOrder = $item->workOrder;
        abort_if($workOrder->assigned_to !== auth()->id(), 403);

        $item->update([
            'is_completed' => ! $item->is_completed,
            'completed_at' => ! $item->is_completed ? now() : null,
        ]);

        $this->logActivity(
            $workOrder,
            ($item->is_completed ? 'Checked: ' : 'Unchecked: ') . $item->description
        );

        return redirect()->route('technician.workorders.show', $workOrder);
    }

    public function startWork(WorkOrder $workOrder)
    {
        abort_if($workOrder->assigned_to !== auth()->id(), 403);

        $workOrder->update([
            'status'     => 'in_progress',
            'started_at' => now(),
        ]);

        $this->logActivity($workOrder, 'Work started');

        Notification::create([
            'recipient_id' => $workOrder->requested_by,
            'type'         => 'work_order.started',
            'channel'      => 'in_app',
            'title'        => 'Work has started on your request',
            'body'         => "{$workOrder->title} is now being worked on.",
            'related_type' => 'work_order',
            'related_id'   => $workOrder->id,
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
            $completedAt = now();

            $workOrder->update([
                'status'           => 'completed',
                'completed_at'     => $completedAt,
                'resolution_notes' => $validated['resolution_notes'],
                'actual_cost'      => $validated['actual_cost'] ?? null,
            ]);

            // SLA resolution check — measured from approval, not
            // report, per the design change: this is the Technician's
            // working-time budget, not the Holder's total wait.
            if ($workOrder->slaPolicy && !$workOrder->sla_breached) {
                $resolutionStartPoint = $workOrder->approved_at ?? $workOrder->reported_at;
                $resolutionDeadline = $resolutionStartPoint->copy()->addHours($workOrder->slaPolicy->resolution_time_hours);
                $resolutionBreached = $completedAt->greaterThan($resolutionDeadline);

                if ($resolutionBreached) {
                    $workOrder->update([
                        'sla_breached'    => true,
                        'sla_breached_at' => $completedAt,
                    ]);
                    $this->logActivity($workOrder, "SLA resolution target missed ({$workOrder->slaPolicy->resolution_time_hours}h target)");
                } else {
                    $this->logActivity($workOrder, 'Resolved within SLA target');
                }
            }

            $workOrder->asset->update([
                'condition' => $validated['condition'],
                'status'    => 'active',
            ]);

            $this->logActivity($workOrder, 'Marked complete — asset condition set to ' . ucfirst($validated['condition']));

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
