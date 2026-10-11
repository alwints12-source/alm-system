<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        's3_bucket',
        's3_key',
        's3_region',
        'original_filename',
        'mime_type',
        'file_size_bytes',
        'checksum_sha256',
        'width_px',
        'height_px',
        'is_primary',
        'caption',
        'sort_order',
        'uploaded_by',
    ];

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    public function mediable()
    {
        return $this->morphTo();
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Public URL for this media file.
     *
     * Currently resolves against the 'public' local disk (s3_key holds the
     * relative path, e.g. "assets/3/photo.jpg"). When DOST's cloud storage
     * is wired in at deployment, this is the only place that needs to
     * change — swap the disk name (and build the URL from s3_bucket/
     * s3_region if the provider needs a different URL shape).
     */
    public function url(): string
    {
        return Storage::disk('public')->url($this->s3_key);
    }
}
