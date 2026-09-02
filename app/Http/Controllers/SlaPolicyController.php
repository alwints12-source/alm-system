<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class SlaPolicyController extends Controller
{
    public function index()
    {
        $policies = SlaPolicy::with('category')
            ->orderByRaw("CASE priority WHEN 'critical' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 WHEN 'low' THEN 4 END")
            ->get();

        return view('admin.sla-policies.index', compact('policies'));
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
