<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSchedule extends Model
{
    protected $fillable = [
        'asset_id',
        'title',
        'description',
        'maintenance_type',
        'frequency',
        'interval_days',
        'start_date',
        'end_date',
        'next_due_date',
        'last_generated_at',
        'default_assignee_id',
        'estimated_duration_hrs',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date'         => 'date',
            'end_date'           => 'date',
            'next_due_date'      => 'date',
            'last_generated_at'  => 'datetime',
            'is_active'          => 'boolean',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function defaultAssignee()
    {
        return $this->belongsTo(User::class, 'default_assignee_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class, 'schedule_id');
    }

    public function isDue(): bool
    {
        return $this->is_active
            && $this->next_due_date->lessThanOrEqualTo(now()->startOfDay())
            && (!$this->end_date || $this->end_date->greaterThanOrEqualTo(now()->startOfDay()));
    }
}
