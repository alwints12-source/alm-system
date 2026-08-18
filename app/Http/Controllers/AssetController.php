<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * Asset inventory list.
     */
    public function index()
    {
        $assets = Asset::with(['category', 'location', 'currentAssignment.holder'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.assets.index', compact('assets'));
    }

    /**
     * Show the Register Asset form.
     */
    public function create()
    {
        $categories = AssetCategory::orderBy('name')->get();
        $locations  = Location::orderBy('name')->get();

        // Any active user can be assigned an asset — not restricted to the
        // Asset Holder role, since Technicians/Admins can hold equipment too
        $holders = User::where('is_active', true)
            ->orderBy('first_name')
            ->get();

        return view('admin.assets.create', compact('categories', 'locations', 'holders'));
    }

    /**
     * Store a newly registered asset, optionally assigning it to a holder.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => ['required', 'string', 'max:200'],
            'category_id'           => ['required', 'exists:asset_categories,id'],
            'location_id'           => ['nullable', 'exists:locations,id'],
            'serial_number'         => ['nullable', 'string', 'max:120'],
            'acquisition_date'      => ['required', 'date'],
            'acquisition_cost'      => ['required', 'numeric', 'min:0'],
            'supplier'              => ['nullable', 'string', 'max:200'],
            'warranty_expiry_date'  => ['nullable', 'date', 'after_or_equal:acquisition_date'],
            'status'                => ['required', 'in:active,in_maintenance,in_storage,disposed'],
            'description'           => ['nullable', 'string'],
            'assign_to'             => ['nullable', 'exists:users,id'],
        ]);

        DB::transaction(function () use ($validated) {
            $nextNumber = Asset::withTrashed()->max('id') + 1;
            $assetTag = 'AST-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

            $asset = Asset::create([
                'asset_tag'             => $assetTag,
                'name'                  => $validated['name'],
                'category_id'           => $validated['category_id'],
                'location_id'           => $validated['location_id'] ?? null,
                'serial_number'         => $validated['serial_number'] ?? null,
                'acquisition_date'      => $validated['acquisition_date'],
                'acquisition_cost'      => $validated['acquisition_cost'],
                'supplier'              => $validated['supplier'] ?? null,
                'warranty_expiry_date'  => $validated['warranty_expiry_date'] ?? null,
                'status'                => $validated['status'],
                'condition'             => 'good',
                'description'           => $validated['description'] ?? null,
                'useful_life_years'     => 5,
                'salvage_value'         => 0,
                'created_by'            => auth()->id(),
            ]);

            if (!empty($validated['assign_to'])) {
                $assignment = AssetAssignment::create([
                    'asset_id'    => $asset->id,
                    'holder_id'   => $validated['assign_to'],
                    'assigned_by' => auth()->id(),
                    'status'      => 'pending_acknowledgement',
                    'assigned_at' => now(),
                ]);

                Notification::create([
                    'recipient_id' => $validated['assign_to'],
                    'type'         => 'asset.assigned',
                    'channel'      => 'in_app',
                    'title'        => 'New asset assigned to you',
                    'body'         => "You've been assigned {$asset->name} ({$asset->asset_tag}). Please acknowledge receipt from your dashboard.",
                    'related_type' => 'asset_assignment',
                    'related_id'   => $assignment->id,
                ]);
            }
        });

        return redirect()->route('admin.assets.index')
            ->with('status', 'Asset registered successfully.');
    }
}
