<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'code',
        'name',
        'description',
        'default_useful_life_yrs',
        'default_salvage_rate',
    ];

    public function assets()
    {
        return $this->hasMany(Asset::class, 'category_id');
    }
}
