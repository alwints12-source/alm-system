<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetTransfer;
use App\Models\Location;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetTransferController extends Controller
{
    public function index()
    {
        $transfers = AssetTransfer::with('asset', 'fromHolder', 'toHolder', 'toLocation', 'requestedBy')
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('admin.transfers.index', compact('transfers'));
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN — direct initiation (offboarding,
    // reorganization — cases where the holder isn't the one asking)
    // ============================================================

    public function create()
    {
        $assignedAssets = Asset::whereHas('currentAssignment')
            ->with(['currentAssignment.holder', 'category'])
            ->orderBy('asset_tag')
            ->get();

        $users = User::where('is_active', true)->orderBy('first_name')->get();
        $locations = Location::orderBy('name')->get();

        return view('admin.transfers.create', compact('assignedAssets', 'users', 'locations'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id'      => ['required', 'exists:assets,id'],
            'to_holder_id'  => ['required', 'exists:users,id'],
            'to_location_id' => ['nullable', 'exists:locations,id'],
            'reason'        => ['required', 'string'],
        ]);

        $asset = Asset::with('currentAssignment')->findOrFail($validated['asset_id']);
        $currentAssignment = $asset->currentAssignment;

        abort_if(!$currentAssignment, 422, 'This asset has no current holder to transfer from.');
        abort_if($currentAssignment->holder_id == $validated['to_holder_id'], 422, 'Asset is already held by this user.');

        AssetTransfer::create([
            'asset_id'         => $asset->id,
            'from_holder_id'   => $currentAssignment->holder_id,
            'to_holder_id'     => $validated['to_holder_id'],
            'from_location_id' => $asset->location_id,
            'to_location_id'   => $validated['to_location_id'] ?? $asset->location_id,
            'reason'           => $validated['reason'],
            'status'           => 'pending',
            'requested_by'     => auth()->id(),
            'requested_at'     => now(),
        ]);

        return redirect()->route('admin.transfers.index')
            ->with('status', 'Transfer request created.');
    }

    // ============================================================
    // ASSET HOLDER — requesting a transfer on their own asset
    // ============================================================

    public function holderStore(Request $request, AssetAssignment $assignment)
    {
        abort_if($assignment->holder_id !== auth()->id(), 403);
        abort_if($assignment->status !== 'acknowledged', 403, 'You can only request a transfer on an asset you have acknowledged.');

        $validated = $request->validate([
            'to_holder_id' => ['required', 'exists:users,id'],
            'reason'       => ['required', 'string'],
        ]);

        abort_if($validated['to_holder_id'] == auth()->id(), 422, 'Cannot transfer an asset to yourself.');

        AssetTransfer::create([
            'asset_id'         => $assignment->asset_id,
            'from_holder_id'   => auth()->id(),
            'to_holder_id'     => $validated['to_holder_id'],
            'from_location_id' => $assignment->asset->location_id,
            'to_location_id'   => $assignment->asset->location_id,
            'reason'           => $validated['reason'],
            'status'           => 'pending',
            'requested_by'     => auth()->id(),
            'requested_at'     => now(),
        ]);

        return redirect()->route('holder.assets.index')
            ->with('status', 'Transfer request submitted. Admin will review it.');
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN — approve / reject (unchanged — works
    // identically regardless of who originally requested it)
    // ============================================================

    public function approve(AssetTransfer $transfer)
    {
        abort_if($transfer->status !== 'pending', 422, 'This transfer has already been processed.');

        DB::transaction(function () use ($transfer) {
            $currentAssignment = AssetAssignment::where('asset_id', $transfer->asset_id)
                ->whereIn('status', ['pending_acknowledgement', 'acknowledged'])
                ->first();

            if ($currentAssignment) {
                $currentAssignment->update([
                    'status'      => 'transferred',
                    'returned_at' => now(),
                ]);
            }

            AssetAssignment::create([
                'asset_id'    => $transfer->asset_id,
                'holder_id'   => $transfer->to_holder_id,
                'assigned_by' => auth()->id(),
                'status'      => 'pending_acknowledgement',
                'assigned_at' => now(),
            ]);

            if ($transfer->to_location_id) {
                $transfer->asset->update(['location_id' => $transfer->to_location_id]);
            }

            $transfer->update([
                'status'      => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
        });

        Notification::create([
            'recipient_id' => $transfer->from_holder_id,
            'type'         => 'asset.transferred_away',
            'channel'      => 'in_app',
            'title'        => 'Asset transferred',
            'body'         => "{$transfer->asset->name} has been transferred to another user.",
            'related_type' => 'asset_transfer',
            'related_id'   => $transfer->id,
        ]);

        Notification::create([
            'recipient_id' => $transfer->to_holder_id,
            'type'         => 'asset.assigned',
            'channel'      => 'in_app',
            'title'        => 'Asset transferred to you',
            'body'         => "{$transfer->asset->name} has been transferred to you. Please acknowledge receipt from your dashboard.",
            'related_type' => 'asset_transfer',
            'related_id'   => $transfer->id,
        ]);

        return redirect()->route('admin.transfers.index')
            ->with('status', 'Transfer approved. New holder must acknowledge receipt.');
    }

    public function reject(Request $request, AssetTransfer $transfer)
    {
        abort_if($transfer->status !== 'pending', 422, 'This transfer has already been processed.');

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $transfer->update([
            'status'            => 'rejected',
            'approved_by'       => auth()->id(),
            'approved_at'       => now(),
            'rejection_reason'  => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('admin.transfers.index')
            ->with('status', 'Transfer rejected.');
    }
}
