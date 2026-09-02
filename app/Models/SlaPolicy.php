<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SlaPolicy extends Model
{
    protected $fillable = [
        'name',
        'description',
        'maintenance_type',
        'priority',
        'category_id',
        'response_time_hours',
        'resolution_time_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    /**
     * Human-readable description of what this policy applies to,
     * e.g. "Critical / Corrective / Laptop / Desktop" or
     * "Critical / Any type / Any category" for a broad policy.
     */
    public function scopeLabel(): string
    {
        $type = $this->maintenance_type ? ucfirst($this->maintenance_type) : 'Any type';
        $category = $this->category->name ?? 'Any category';

        return ucfirst($this->priority) . " / {$type} / {$category}";
    }
}
