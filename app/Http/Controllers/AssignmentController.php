<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = AssetAssignment::with([
            'asset.category',
            'asset.location',
            'asset.workOrders' => function ($query) {
                $query->where('status', 'completed')
                      ->with('assignedTo')
                      ->orderBy('completed_at', 'desc');
            },
        ])
            ->where('holder_id', auth()->id())
            ->where('status', 'acknowledged')
            ->orderBy('acknowledged_at', 'desc')
            ->get();

        return view('holder.assets.index', compact('assignments'));
    }

    public function acknowledge(AssetAssignment $assignment)
    {
        abort_if($assignment->holder_id !== auth()->id(), 403);

        $assignment->update([
            'status'          => 'acknowledged',
            'acknowledged_at' => now(),
        ]);

        return redirect()->route('dashboard')
            ->with('status', 'Asset receipt acknowledged. It now appears under My Assets.');
    }
}
