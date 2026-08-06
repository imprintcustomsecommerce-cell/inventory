<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Support\Facades\Storage;

class ProjectProof extends Model
{
    protected $fillable = [
        'project_id',
        'version',
        'file_path',
        'original_name',
        'mime',
        'size',
        'status',
        'uploaded_by',
        'decided_by',
        'decided_at',
        'feedback',
    ];

    protected $casts = [
        'version' => 'integer',
        'size' => 'integer',
        'decided_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function decider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'decided_by');
    }

    public function url(): string
    {
        // A proof with no path is stored as bytes in the media table; older
        // proofs from a LAN install still live on disk.
        return $this->file_path
            ? Storage::disk('public')->url($this->file_path)
            : route('media.show', ['proof', $this->getKey()]);
    }

    public function media(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable');
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    public function isPending(): bool
    {
        return $this->status === 'Pending';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Approved' => 'badge-green',
            'Revision Requested' => 'badge-red',
            default => 'badge-amber',
        };
    }

    /**
     * Human-readable file size.
     */
    public function humanSize(): string
    {
        $bytes = (int) $this->size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 1) . ' MB';
        }
        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 0) . ' KB';
        }

        return $bytes . ' B';
    }
}
