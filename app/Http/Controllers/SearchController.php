<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $me   = auth()->user();
        $like = '%' . addcslashes($q, '%_\\') . '%';

        $assets = $workOrders = $users = $assignments = collect();

        if ($q !== '') {
            $assetMatch = fn ($w) => $w->where(fn ($x) => $x->where('name', 'ilike', $like)->orWhere('asset_tag', 'ilike', $like));
            $woMatch    = fn ($w) => $w->where(fn ($x) => $x->where('title', 'ilike', $like)->orWhere('work_order_number', 'ilike', $like));

            $assignments = AssetAssignment::with('asset')
                ->where('holder_id', $me->id)->where('status', 'acknowledged')
                ->whereHas('asset', $assetMatch)->limit(10)->get();

            if ($me->role === 'administrative_admin') {
                $assets = Asset::where($assetMatch)->limit(10)->get();
                $workOrders = WorkOrder::with('asset')->where($woMatch)
                    ->orderByRaw('COALESCE(reported_at, created_at) DESC')->limit(10)->get();
            } elseif ($me->role === 'technical_admin') {
                $users = User::where(fn ($w) => $w->where('first_name', 'ilike', $like)
                    ->orWhere('last_name', 'ilike', $like)->orWhere('email', 'ilike', $like))
                    ->limit(10)->get();
            } else {
                $workOrders = WorkOrder::with('asset')
                    ->where(fn ($w) => $w->where('assigned_to', $me->id)->orWhere('requested_by', $me->id))
                    ->where($woMatch)
                    ->orderByRaw('COALESCE(reported_at, created_at) DESC')->limit(10)->get();
            }
        }

        return view('search.index', compact('q', 'assets', 'workOrders', 'users', 'assignments'));
    }
}
