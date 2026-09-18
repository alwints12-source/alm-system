<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Asset extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'asset_tag',
        'serial_number',
        'name',
        'description',
        'category_id',
        'location_id',
        'manufacturer',
        'model',
        'status',
        'condition',
        'acquisition_date',
        'acquisition_cost',
        'supplier',
        'purchase_order_ref',
        'useful_life_years',
        'salvage_value',
        'warranty_expiry_date',
        'created_by',
        'disposal_date',
    ];

    protected function casts(): array
    {
        return [
            'acquisition_date'     => 'date',
            'warranty_expiry_date' => 'date',
            'acquisition_cost'     => 'decimal:2',
            'salvage_value'        => 'decimal:2',
        ];
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class, 'location_id');
    }

    public function assignments()
    {
        return $this->hasMany(AssetAssignment::class);
    }

    public function currentAssignment()
    {
        return $this->hasOne(AssetAssignment::class)
            ->whereIn('status', ['pending_acknowledgement', 'acknowledged'])
            ->latest('assigned_at');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }

    // ================================================================
    // DEPRECIATION — straight-line, calculated live from real inputs.
    // No stored history needed for "current" figures; a monthly
    // snapshot job (separate, for audit trail) writes permanent
    // records to depreciation_records going forward from whenever
    // it's first run.
    // ================================================================

    public function isDepreciable(): bool
    {
        return $this->acquisition_cost !== null
            && $this->acquisition_date !== null
            && $this->useful_life_years !== null
            && $this->useful_life_years > 0;
    }

    public function monthlyDepreciation(): float
    {
        if (!$this->isDepreciable()) return 0;

        $depreciableAmount = $this->acquisition_cost - ($this->salvage_value ?? 0);
        $totalMonths = $this->useful_life_years * 12;

        return $totalMonths > 0 ? round($depreciableAmount / $totalMonths, 2) : 0;
    }

    /**
     * Whole months between acquisition and today, capped at the asset's
     * full useful life — can't accumulate more depreciation than exists.
     */
     public function monthsElapsed(): int
    {
        if (!$this->isDepreciable()) return 0;

        $elapsed = (int) floor($this->acquisition_date->diffInMonths(now()));
        $maxMonths = $this->useful_life_years * 12;

        return min($elapsed, $maxMonths);
    }

    public function accumulatedDepreciation(): float
    {
        if (!$this->isDepreciable()) return 0;

        return round($this->monthsElapsed() * $this->monthlyDepreciation(), 2);
    }

    /**
     * Live current book value — never below salvage value, regardless
     * of how long past its useful life the asset has continued in use.
     */
    public function currentBookValue(): float
    {
        if (!$this->isDepreciable()) return (float) $this->acquisition_cost;

        $value = $this->acquisition_cost - $this->accumulatedDepreciation();
        $floor = (float) ($this->salvage_value ?? 0);

        return max($value, $floor);
    }

    public function isFullyDepreciated(): bool
    {
        return $this->isDepreciable() && $this->currentBookValue() <= ($this->salvage_value ?? 0) + 0.01;
    }

    public function remainingLifeLabel(): string
    {
        if (!$this->isDepreciable()) return '—';

        $remainingMonths = max(($this->useful_life_years * 12) - $this->monthsElapsed(), 0);
        $years = intdiv($remainingMonths, 12);
        $months = $remainingMonths % 12;

        if ($remainingMonths === 0) return 'Fully depreciated';

        return "{$years}y {$months}m";
    }

    /**
     * How much of this asset's depreciation fell within a specific
     * calendar year — real math from acquisition date and monthly
     * rate, bounded by both the asset's useful life and today (can't
     * count depreciation that hasn't happened yet).
     */
    public function depreciationInYear(int $year): float
    {
        if (!$this->isDepreciable()) return 0;

        $monthlyDep = $this->monthlyDepreciation();
        $depreciationStart = $this->acquisition_date->copy()->startOfMonth();
        $lifeEndCap = $this->acquisition_date->copy()->addMonths($this->useful_life_years * 12);
        $today = now();
        $depreciationEnd = $lifeEndCap->lessThan($today) ? $lifeEndCap : $today;

        $yearStart = Carbon::createFromDate($year, 1, 1)->startOfMonth();
        $yearEnd = Carbon::createFromDate($year, 12, 31)->endOfMonth();

        $overlapStart = $depreciationStart->greaterThan($yearStart) ? $depreciationStart : $yearStart;
        $overlapEnd = $depreciationEnd->lessThan($yearEnd) ? $depreciationEnd : $yearEnd;

        if ($overlapEnd->lessThan($overlapStart)) return 0;
        $months = (int) floor(max($overlapStart->diffInMonths($overlapEnd), 0)); 

        return round($months * $monthlyDep, 2);
    }
}
