<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    /**
     * All work orders ever raised against this asset — the source of
     * truth for condition history, since condition only ever changes
     * when a work order is completed.
     */
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
