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
        'approved_at',
        'due_date',
        'started_at',
        'completed_at',
        'estimated_cost',
        'actual_cost',
        'resolution_notes',
        'technician_notes',
        'sla_id',
        'sla_breached',
        'sla_breached_at',
    ];

    protected function casts(): array
    {
        return [
            'reported_at'     => 'datetime',
            'approved_at'     => 'datetime',
            'due_date'        => 'datetime',
            'started_at'      => 'datetime',
            'completed_at'    => 'datetime',
            'estimated_cost'  => 'decimal:2',
            'actual_cost'     => 'decimal:2',
            'sla_breached'    => 'boolean',
            'sla_breached_at' => 'datetime',
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

    public function checklistItems()
    {
        return $this->hasMany(WorkOrderChecklistItem::class)->orderBy('sort_order');
    }

    public function activityLog()
    {
        return $this->hasMany(WorkOrderActivityLog::class)->orderBy('created_at', 'desc');
    }

    public function slaPolicy()
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_id');
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && !in_array($this->status, ['completed', 'cancelled']);
    }

    public function checklistProgress(): array
    {
        $total = $this->checklistItems->count();
        $done = $this->checklistItems->where('is_completed', true)->count();
        return [
            'done'    => $done,
            'total'   => $total,
            'percent' => $total > 0 ? round(($done / $total) * 100) : 0,
        ];
    }

    public function matchingSlaPolicy(): ?SlaPolicy
    {
        $categoryId = $this->asset->category_id ?? null;

        return SlaPolicy::where('priority', $this->priority)
            ->where('is_active', true)
            ->where(function ($query) use ($categoryId) {
                $query->where(function ($q) use ($categoryId) {
                    $q->where('maintenance_type', $this->maintenance_type)
                      ->where('category_id', $categoryId);
                })
                ->orWhere(function ($q) {
                    $q->where('maintenance_type', $this->maintenance_type)
                      ->whereNull('category_id');
                })
                ->orWhere(function ($q) use ($categoryId) {
                    $q->whereNull('maintenance_type')
                      ->where('category_id', $categoryId);
                })
                ->orWhere(function ($q) {
                    $q->whereNull('maintenance_type')
                      ->whereNull('category_id');
                });
            })
            ->orderByRaw("
                (maintenance_type IS NOT NULL)::int +
                (category_id IS NOT NULL)::int DESC
            ")
            ->first();
    }

    /**
     * Response target is always measured from when the issue was
     * reported — this deadline is about how long the Holder waited
     * to be acknowledged at all, before anyone owned the work.
     */
    public function responseDueAt(): ?\Carbon\Carbon
    {
        $policy = $this->slaPolicy ?? $this->matchingSlaPolicy();
        if (!$policy) return null;

        return $this->reported_at->copy()->addHours($policy->response_time_hours);
    }

    /**
     * Resolution target is measured from APPROVAL, not report — this
     * is the Technician's actual working-time budget, not the
     * Holder's total wait including queue time. Falls back to
     * reported_at for any record approved before this column existed.
     */
    public function resolutionDueAt(): ?\Carbon\Carbon
    {
        $policy = $this->slaPolicy ?? $this->matchingSlaPolicy();
        if (!$policy) return null;

        $startPoint = $this->approved_at ?? $this->reported_at;

        return $startPoint->copy()->addHours($policy->resolution_time_hours);
    }
}
