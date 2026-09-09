<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetTransfer extends Model
{
    protected $fillable = [
        'asset_id',
        'from_holder_id',
        'to_holder_id',
        'from_location_id',
        'to_location_id',
        'reason',
        'status',
        'requested_by',
        'requested_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'requested_at' => 'datetime',
            'approved_at'  => 'datetime',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function fromHolder()
    {
        return $this->belongsTo(User::class, 'from_holder_id');
    }

    public function toHolder()
    {
        return $this->belongsTo(User::class, 'to_holder_id');
    }

    public function fromLocation()
    {
        return $this->belongsTo(Location::class, 'from_location_id');
    }

    public function toLocation()
    {
        return $this->belongsTo(Location::class, 'to_location_id');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
