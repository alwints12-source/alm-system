<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkOrderActivityLog extends Model
{
    protected $table = 'work_order_activity_log';
    public $timestamps = false;

    protected $fillable = [
        'work_order_id',
        'event_description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
