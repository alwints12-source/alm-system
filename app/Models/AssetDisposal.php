<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetDisposal extends Model
{
    protected $fillable = [
        'asset_id',
        'reason',
        'requested_by',
        'requested_at',
        'status',
        'approved_by',
        'approved_at',
        'disposal_method',
        'rejection_reason',
        'source_work_order_id',
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

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function sourceWorkOrder()
    {
        return $this->belongsTo(WorkOrder::class, 'source_work_order_id');
    }
}
