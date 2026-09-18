<?php

namespace App\Http\Controllers;

use App\Models\Asset;

class WarrantyController extends Controller
{
    private const EXPIRING_SOON_DAYS = 30;

    public function index()
    {
        $trackedAssets = Asset::whereNotNull('warranty_expiry_date')
            ->with('category', 'location')
            ->orderBy('warranty_expiry_date')
            ->get();

        $today = now()->startOfDay();
        $expiringSoonCutoff = $today->copy()->addDays(self::EXPIRING_SOON_DAYS);

        $expired = $trackedAssets->filter(fn ($a) => $a->warranty_expiry_date->lessThan($today));
        $expiringSoon = $trackedAssets->filter(fn ($a) =>
            $a->warranty_expiry_date->greaterThanOrEqualTo($today) &&
            $a->warranty_expiry_date->lessThanOrEqualTo($expiringSoonCutoff)
        );
        $safe = $trackedAssets->filter(fn ($a) => $a->warranty_expiry_date->greaterThan($expiringSoonCutoff));

        return view('admin.warranty.index', compact('expired', 'expiringSoon', 'safe', 'trackedAssets'));
    }
}
