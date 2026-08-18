<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'building',
        'floor',
        'room',
        'address',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'location_id');
    }
}
