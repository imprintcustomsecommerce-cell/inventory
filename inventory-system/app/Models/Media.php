<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Raw bytes of an uploaded file, kept in the database so that uploads survive
 * on hosts with an ephemeral filesystem. See the create_media_table migration.
 */
class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'mediable_type',
        'mediable_id',
        'collection',
        'mime',
        'original_name',
        'size',
        'data',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function mediable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Every column except the blob.
     *
     * Selecting `data` pulls the whole file into memory, so any query that
     * isn't actually serving the file should use this instead of `*`.
     *
     * @return array<int, string>
     */
    public static function columnsWithoutData(): array
    {
        return ['id', 'mediable_type', 'mediable_id', 'collection', 'mime', 'original_name', 'size', 'created_at', 'updated_at'];
    }
}
