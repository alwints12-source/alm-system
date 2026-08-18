<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'holder_id',
        'assigned_by',
        'status',
        'assigned_at',
        'acknowledged_at',
        'returned_at',
        'assignment_notes',
        'return_condition',
        'return_notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at'     => 'datetime',
            'acknowledged_at' => 'datetime',
            'returned_at'     => 'datetime',
        ];
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function holder()
    {
        return $this->belongsTo(User::class, 'holder_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
