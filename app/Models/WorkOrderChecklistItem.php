<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderChecklistItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'work_order_id',
        'description',
        'is_completed',
        'completed_at',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
