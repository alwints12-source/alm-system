<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    const UPDATED_AT = null;

    protected $fillable = [
        'recipient_id',
        'type',
        'channel',
        'title',
        'body',
        'related_type',
        'related_id',
        'is_read',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
            'read_at' => 'datetime',
        ];
    }

    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }
}
