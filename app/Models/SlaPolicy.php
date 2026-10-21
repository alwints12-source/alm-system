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
            'response_time_hours' => 'float',
            'resolution_time_hours' => 'float',
        ];
    }

    /** Formats a number of hours as "30 min", "4h" or "1h 30 min". */
    public static function formatHours(float $hours): string
    {
        $minutes = (int) round($hours * 60);
        if ($minutes < 60) {
            return $minutes . ' min';
        }
        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return $m === 0 ? $h . 'h' : $h . 'h ' . $m . ' min';
    }

    public function getResponseLabelAttribute(): string
    {
        return self::formatHours((float) $this->response_time_hours);
    }

    public function getResolutionLabelAttribute(): string
    {
        return self::formatHours((float) $this->resolution_time_hours);
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
