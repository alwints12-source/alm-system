<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\AssetDisposal;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetDisposalController extends Controller
{
    public function index()
    {
        $disposals = AssetDisposal::with('asset', 'requestedBy', 'approvedBy')
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('admin.disposals.index', compact('disposals'));
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN — direct initiation (obsolescence, budget
    // cleanup — no repair history needed to justify this one)
    // ============================================================

    public function create()
    {
        $assets = Asset::where('status', '!=', 'disposed')->orderBy('asset_tag')->get();

        return view('admin.disposals.create', compact('assets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => ['required', 'exists:assets,id'],
            'reason'   => ['required', 'string'],
        ]);

        AssetDisposal::create([
            'asset_id'     => $validated['asset_id'],
            'reason'       => $validated['reason'],
            'requested_by' => auth()->id(),
            'requested_at' => now(),
            'status'       => 'pending',
        ]);

        return redirect()->route('admin.disposals.index')
            ->with('status', 'Disposal request created.');
    }

    // ============================================================
    // ADMINISTRATIVE ADMIN — approve / reject
    // ============================================================

    public function approve(Request $request, AssetDisposal $disposal)
    {
        abort_if($disposal->status !== 'pending', 422, 'This disposal request has already been processed.');

        $validated = $request->validate([
            'disposal_method' => ['required', 'in:scrapped,donated,sold,recycled'],
        ]);

        DB::transaction(function () use ($disposal, $validated) {
            $currentAssignment = AssetAssignment::where('asset_id', $disposal->asset_id)
                ->whereIn('status', ['pending_acknowledgement', 'acknowledged'])
                ->first();

            if ($currentAssignment) {
                $currentAssignment->update([
                    'status'      => 'returned',
                    'returned_at' => now(),
                ]);

                Notification::create([
                    'recipient_id' => $currentAssignment->holder_id,
                    'type'         => 'asset.disposed',
                    'channel'      => 'in_app',
                    'title'        => 'Asset removed from inventory',
                    'body'         => "{$disposal->asset->name} has been disposed and is no longer assigned to you.",
                    'related_type' => 'asset_disposal',
                    'related_id'   => $disposal->id,
                ]);
            }

            $disposal->asset->update([
                'status'        => 'disposed',
                'disposal_date' => now()->toDateString(),
            ]);

            $disposal->update([
                'status'          => 'approved',
                'approved_by'     => auth()->id(),
                'approved_at'     => now(),
                'disposal_method' => $validated['disposal_method'],
            ]);
        });

        return redirect()->route('admin.disposals.index')
            ->with('status', 'Disposal approved. Asset marked as disposed.');
    }

    public function reject(Request $request, AssetDisposal $disposal)
    {
        abort_if($disposal->status !== 'pending', 422, 'This disposal request has already been processed.');

        $validated = $request->validate([
            'rejection_reason' => ['nullable', 'string'],
        ]);

        $disposal->update([
            'status'           => 'rejected',
            'approved_by'      => auth()->id(),
            'approved_at'      => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        return redirect()->route('admin.disposals.index')
            ->with('status', 'Disposal request rejected.');
    }
}
