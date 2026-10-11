<?php

namespace App\Http\Controllers;

use App\Models\AssetAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class AssignmentController extends Controller
{
    public function index()
    {
        $assignments = AssetAssignment::with([
            'asset.category',
            'asset.location',
            'asset.primaryPhoto',
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

        $allUsers = User::where('is_active', true)->orderBy('first_name')->get();

        return view('holder.assets.index', compact('assignments', 'allUsers'));
    }

    public function show(AssetAssignment $assignment)
    {
        abort_if($assignment->holder_id !== auth()->id(), 403);
        abort_if($assignment->status !== 'acknowledged', 404);

        $assignment->load([
            'asset.category',
            'asset.location',
            'asset.media',
            'asset.workOrders' => function ($query) {
                $query->where('status', 'completed')
                      ->with('assignedTo')
                      ->orderBy('completed_at', 'desc');
            },
        ]);

        return view('holder.assets.show', compact('assignment'));
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
