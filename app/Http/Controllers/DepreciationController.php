<?php

namespace App\Http\Controllers;

use App\Models\Asset;

class DepreciationController extends Controller
{
    public function index()
    {
        $assets = Asset::where('status', '!=', 'disposed')
            ->whereNotNull('useful_life_years')
            ->with('category', 'location')
            ->orderBy('asset_tag')
            ->get();

        $totalAcquisitionValue = $assets->sum('acquisition_cost');
        $currentBookValue = $assets->sum(fn ($a) => $a->currentBookValue());
        $ytdDepreciation = $assets->sum(fn ($a) => $a->depreciationInYear((int) now()->year));
        $fullyDepreciatedCount = $assets->filter(fn ($a) => $a->isFullyDepreciated())->count();

        // Depreciation by department (location) — accumulated
        // depreciation summed per location, largest first
        $byDepartment = $assets->groupBy(fn ($a) => $a->location->name ?? 'Unassigned')
            ->map(fn ($group) => $group->sum(fn ($a) => $a->accumulatedDepreciation()))
            ->sortDesc();

        $maxDepartmentValue = $byDepartment->max() ?: 1;

        // Year-by-year schedule — real span, from the earliest actual
        // acquisition year in the data to the current year, not a
        // fixed/fabricated range
        $earliestYear = $assets->min(fn ($a) => $a->acquisition_date->year) ?? now()->year;
        $currentYear = (int) now()->year;
        $years = range($earliestYear, $currentYear);

        $yearlySchedule = collect($years)->map(fn ($year) => [
            'year'   => $year,
            'amount' => round($assets->sum(fn ($a) => $a->depreciationInYear($year)), 2),
        ]);

        return view('admin.depreciation.index', compact(
            'assets', 'totalAcquisitionValue', 'currentBookValue', 'ytdDepreciation',
            'fullyDepreciatedCount', 'byDepartment', 'maxDepartmentValue', 'yearlySchedule'
        ));
    }

    public function exportCsv()
    {
        $assets = Asset::where('status', '!=', 'disposed')
            ->whereNotNull('useful_life_years')
            ->orderBy('asset_tag')
            ->get();

        $filename = 'depreciation-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($assets) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Asset ID', 'Asset Name', 'Acquisition Cost', 'Book Value', 'Accumulated Depreciation', 'Method', 'Useful Life (yrs)', 'Remaining']);

            foreach ($assets as $asset) {
                fputcsv($handle, [
                    $asset->asset_tag,
                    $asset->name,
                    $asset->acquisition_cost,
                    number_format($asset->currentBookValue(), 2),
                    number_format($asset->accumulatedDepreciation(), 2),
                    'Straight-line',
                    $asset->useful_life_years,
                    $asset->remainingLifeLabel(),
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
