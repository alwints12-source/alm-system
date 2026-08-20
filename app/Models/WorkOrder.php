<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WorkOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_number',
        'asset_id',
        'title',
        'description',
        'maintenance_type',
        'priority',
        'status',
        'requested_by',
        'assigned_to',
        'approved_by',
        'reported_at',
        'due_date',
        'started_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'reported_at'   => 'datetime',
            'due_date'      => 'datetime',
            'started_at'    => 'datetime',
            'completed_at'  => 'datetime',
            'estimated_cost' => 'decimal:2',
            'actual_cost'    => 'decimal:2',
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

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }
}
