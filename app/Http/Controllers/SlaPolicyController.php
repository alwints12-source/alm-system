<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\SlaPolicy;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $policies = SlaPolicy::with('category')
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END")
            ->get();

        $tracked = WorkOrder::whereNotNull('sla_id')
            ->whereIn('status', ['assigned', 'in_progress', 'completed'])
            ->with('slaPolicy')
            ->get();

        // ============================================================
        // RESPONSE compliance — known for every tracked order, since
        // the outcome is decided the moment approval happens (that's
        // literally when sla_id gets set)
        // ============================================================
        $responseResults = $tracked->map(function ($wo) {
            $start = $wo->reported_at;
            $approvedAt = $wo->approved_at ?? $wo->reported_at;
            $deadline = $start->copy()->addHours($wo->slaPolicy->response_time_hours);
            return [
                'priority' => $wo->priority,
                'met'      => $approvedAt->lessThanOrEqualTo($deadline),
            ];
        });

        $responseTotalTracked = $responseResults->count();
        $responseTotalMet = $responseResults->where('met', true)->count();
        $responseTotalBreached = $responseTotalTracked - $responseTotalMet;
        $responseComplianceRate = $responseTotalTracked > 0 ? round(($responseTotalMet / $responseTotalTracked) * 100) : 100;

        $responsePriorityBreakdown = [];
        foreach (['critical', 'high', 'medium', 'low'] as $priority) {
            $group = $responseResults->where('priority', $priority);
            $groupTotal = $group->count();
            $groupMet = $group->where('met', true)->count();
            $responsePriorityBreakdown[] = [
                'priority' => $priority,
                'total'    => $groupTotal,
                'met'      => $groupMet,
                'breached' => $groupTotal - $groupMet,
            ];
        }

        // ============================================================
        // RESOLUTION compliance — only knowable once a work order is
        // actually completed; anything still assigned/in_progress
        // hasn't had its resolution outcome decided yet
        // ============================================================
        $completedTracked = $tracked->where('status', 'completed');

        $resolutionResults = $completedTracked->map(function ($wo) {
            $start = $wo->approved_at ?? $wo->reported_at;
            $deadline = $start->copy()->addHours($wo->slaPolicy->resolution_time_hours);
            return [
                'priority' => $wo->priority,
                'met'      => $wo->completed_at->lessThanOrEqualTo($deadline),
            ];
        });

        $resolutionTotalTracked = $resolutionResults->count();
        $resolutionTotalMet = $resolutionResults->where('met', true)->count();
        $resolutionTotalBreached = $resolutionTotalTracked - $resolutionTotalMet;
        $resolutionComplianceRate = $resolutionTotalTracked > 0 ? round(($resolutionTotalMet / $resolutionTotalTracked) * 100) : 100;

        $resolutionPriorityBreakdown = [];
        foreach (['critical', 'high', 'medium', 'low'] as $priority) {
            $group = $resolutionResults->where('priority', $priority);
            $groupTotal = $group->count();
            $groupMet = $group->where('met', true)->count();
            $resolutionPriorityBreakdown[] = [
                'priority' => $priority,
                'total'    => $groupTotal,
                'met'      => $groupMet,
                'breached' => $groupTotal - $groupMet,
            ];
        }

        // Recent breaches list still uses the stored sla_breached flag
        // — correct here, since this is meant as "anything that needs
        // attention," response or resolution, combined into one list
        $recentBreaches = $tracked->where('sla_breached', true)
            ->sortByDesc('sla_breached_at')
            ->take(10)
            ->load('asset', 'requestedBy');

        // Technician resolution compliance — unchanged from before
        $completedWithSla = WorkOrder::whereNotNull('sla_id')
            ->where('status', 'completed')
            ->whereNotNull('assigned_to')
            ->with('slaPolicy', 'assignedTo', 'asset')
            ->get();

        $technicianStats = [];
        foreach ($completedWithSla->groupBy('assigned_to') as $orders) {
            $tech = $orders->first()->assignedTo;
            if (!$tech) continue;

            $techResolutionResults = $orders->map(function ($wo) {
                $start = $wo->approved_at ?? $wo->reported_at;
                $deadline = $start->copy()->addHours($wo->slaPolicy->resolution_time_hours);
                return [
                    'work_order' => $wo,
                    'met'        => $wo->completed_at->lessThanOrEqualTo($deadline),
                    'deadline'   => $deadline,
                ];
            });

            $total = $techResolutionResults->count();
            $met = $techResolutionResults->where('met', true)->count();

            $technicianStats[] = [
                'technician' => $tech,
                'total'      => $total,
                'met'        => $met,
                'breached'   => $total - $met,
                'rate'       => $total > 0 ? round(($met / $total) * 100) : null,
                'orders'     => $techResolutionResults,
            ];
        }

        return view('admin.sla-policies.index', compact(
            'policies',
            'responseTotalTracked', 'responseTotalMet', 'responseTotalBreached', 'responseComplianceRate', 'responsePriorityBreakdown',
            'resolutionTotalTracked', 'resolutionTotalMet', 'resolutionTotalBreached', 'resolutionComplianceRate', 'resolutionPriorityBreakdown',
            'recentBreaches', 'technicianStats'
        ));
    }

    public function create()
    {
        $categories = AssetCategory::orderBy('name')->get();
        return view('admin.sla-policies.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePolicy($request);

        try {
            SlaPolicy::create($validated);
        } catch (QueryException $e) {
            return back()->withInput()->withErrors([
                'priority' => 'A policy already exists for this exact combination of priority, type, and category. Edit the existing one instead, or narrow/broaden the scope.',
            ]);
        }

        return redirect()->route('admin.sla-policies.index')
            ->with('status', 'SLA policy created.');
    }

    public function edit(SlaPolicy $slaPolicy)
    {
        $categories = AssetCategory::orderBy('name')->get();
        return view('admin.sla-policies.edit', compact('slaPolicy', 'categories'));
    }

    public function update(Request $request, SlaPolicy $slaPolicy)
    {
        $validated = $this->validatePolicy($request);

        try {
            $slaPolicy->update($validated);
        } catch (QueryException $e) {
            return back()->withInput()->withErrors([
                'priority' => 'Another policy already exists for this exact combination of priority, type, and category.',
            ]);
        }

        return redirect()->route('admin.sla-policies.index')
            ->with('status', 'SLA policy updated.');
    }

    public function toggleActive(SlaPolicy $slaPolicy)
    {
        $slaPolicy->update(['is_active' => ! $slaPolicy->is_active]);

        return redirect()->route('admin.sla-policies.index')
            ->with('status', $slaPolicy->is_active ? 'Policy activated.' : 'Policy deactivated.');
    }

    private function validatePolicy(Request $request): array
    {
        return $request->validate([
            'name'                   => ['required', 'string', 'max:150'],
            'description'            => ['nullable', 'string'],
            'priority'               => ['required', 'in:critical,high,medium,low'],
            'maintenance_type'       => ['nullable', 'in:preventive,corrective,predictive,emergency'],
            'category_id'            => ['nullable', 'exists:asset_categories,id'],
            'response_time_hours'    => ['required', 'integer', 'min:1'],
            'resolution_time_hours'  => ['required', 'integer', 'gte:response_time_hours'],
        ]);
    }
}
