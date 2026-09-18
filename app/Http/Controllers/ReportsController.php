<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\WorkOrder;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportsController extends Controller
{
    /**
     * All four report sections computed from one shared filter set —
     * used identically by the page, the PDF, and the CSV, so all
     * three always agree with each other.
     */
    private function getReportData(Request $request): array
    {
        $dateFrom = $request->filled('date_from') ? Carbon::parse($request->input('date_from'))->startOfDay() : null;
        $dateTo = $request->filled('date_to') ? Carbon::parse($request->input('date_to'))->endOfDay() : now();
        $departmentId = $request->input('department_id');
        $categoryId = $request->input('category_id');

        // Active (non-disposed) assets matching filters — the base set
        // for utilization, depreciation, and most insights
        $assets = Asset::where('status', '!=', 'disposed')
            ->when($departmentId, fn ($q) => $q->where('location_id', $departmentId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->with('category', 'location', 'currentAssignment')
            ->get();

        // Every asset regardless of status — needed for status distribution
        $allAssets = Asset::when($departmentId, fn ($q) => $q->where('location_id', $departmentId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->get();

        // ============================================================
        // 1. Asset utilization by department — live snapshot, not
        // affected by date range (there's no historical utilization
        // record to look back at, only current assignment state)
        // ============================================================
        $utilization = Location::orderBy('name')->get()->map(function ($loc) use ($categoryId) {
            $inLocation = Asset::where('location_id', $loc->id)
                ->where('status', '!=', 'disposed')
                ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
                ->with('currentAssignment')
                ->get();

            $total = $inLocation->count();
            $utilized = $inLocation->filter(fn ($a) => $a->currentAssignment)->count();

            return [
                'name' => $loc->name,
                'rate' => $total > 0 ? round(($utilized / $total) * 100) : 0,
            ];
        });

        // ============================================================
        // 2. Depreciation summary by year — real span, bounded by the
        // date filter if one is set
        // ============================================================
        $depreciable = $assets->filter(fn ($a) => $a->isDepreciable());
        $earliestYear = $depreciable->min(fn ($a) => $a->acquisition_date->year) ?? now()->year;
        $startYear = $dateFrom ? max($dateFrom->year, $earliestYear) : $earliestYear;
        $endYear = $dateTo->year;

        $depreciationByYear = collect(range($startYear, $endYear))->map(fn ($year) => [
            'year'   => $year,
            'amount' => round($depreciable->sum(fn ($a) => $a->depreciationInYear($year)), 2),
        ]);

        // ============================================================
        // 3. Maintenance costs by quarter — within the date range,
        // defaulting to the current year if no range given
        // ============================================================
        $workOrdersQuery = WorkOrder::where('status', 'completed')
            ->whereNotNull('actual_cost')
            ->whereHas('asset', function ($q) use ($departmentId, $categoryId) {
                $q->when($departmentId, fn ($qq) => $qq->where('location_id', $departmentId));
                $q->when($categoryId, fn ($qq) => $qq->where('category_id', $categoryId));
            })
            ->where('completed_at', '<=', $dateTo);

        if ($dateFrom) {
            $workOrdersQuery->where('completed_at', '>=', $dateFrom);
        }

        $completedOrders = $workOrdersQuery->get();

        $costByQuarter = collect([1, 2, 3, 4])->map(fn ($q) => [
            'quarter' => "Q{$q}",
            'amount'  => round($completedOrders->filter(fn ($wo) => $wo->completed_at->quarter === $q)->sum('actual_cost'), 2),
        ]);

        // ============================================================
        // 4. Asset status distribution
        // ============================================================
        $totalAssetsCount = $allAssets->count();
        $statusCounts = $allAssets->groupBy('status')->map->count();

        // ============================================================
        // Data insights
        // ============================================================
        $depreciationByCategory = $depreciable->groupBy(fn ($a) => $a->category->name ?? 'Uncategorised')
            ->map(fn ($group) => $group->sum(fn ($a) => $a->accumulatedDepreciation()))
            ->sortDesc();

        $mostMaintained = Asset::withCount(['workOrders' => fn ($q) => $q->where('status', 'completed')])
            ->when($departmentId, fn ($q) => $q->where('location_id', $departmentId))
            ->when($categoryId, fn ($q) => $q->where('category_id', $categoryId))
            ->orderByDesc('work_orders_count')
            ->first();

        $avgLifespan = $depreciable->avg('useful_life_years');

        return [
            'utilization'             => $utilization,
            'depreciationByYear'      => $depreciationByYear,
            'costByQuarter'           => $costByQuarter,
            'statusCounts'            => $statusCounts,
            'totalAssetsCount'        => $totalAssetsCount,
            'highestDepCategoryName'  => $depreciationByCategory->keys()->first(),
            'highestDepCategoryValue' => $depreciationByCategory->first(),
            'mostMaintained'          => $mostMaintained,
            'avgLifespan'             => $avgLifespan,
            'dateFrom'                => $dateFrom,
            'dateTo'                  => $dateTo,
            'departmentId'            => $departmentId,
            'categoryId'              => $categoryId,
            'assets'                  => $assets,
        ];
    }

    public function index(Request $request)
    {
        $data = $this->getReportData($request);
        $data['categories'] = AssetCategory::orderBy('name')->get();
        $data['locations'] = Location::orderBy('name')->get();

        return view('admin.reports.index', $data);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getReportData($request);

        $pdf = Pdf::loadView('admin.reports.pdf', $data);

        return $pdf->download('reports-analytics-' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getReportData($request);
        $filename = 'reports-analytics-' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Asset ID', 'Name', 'Category', 'Location', 'Status', 'Currently Assigned', 'Book Value', 'Accumulated Depreciation']);

            foreach ($data['assets'] as $asset) {
                fputcsv($handle, [
                    $asset->asset_tag,
                    $asset->name,
                    $asset->category->name ?? '—',
                    $asset->location->name ?? '—',
                    ucfirst($asset->status),
                    $asset->currentAssignment ? 'Yes' : 'No',
                    $asset->isDepreciable() ? number_format($asset->currentBookValue(), 2) : 'N/A',
                    $asset->isDepreciable() ? number_format($asset->accumulatedDepreciation(), 2) : 'N/A',
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, ['Content-Type' => 'text/csv']);
    }
}
