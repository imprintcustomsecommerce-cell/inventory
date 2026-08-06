<?php

namespace App\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

/**
 * Gives a model a single uploaded image stored as bytes in the database.
 *
 * The owning row keeps an `image_mime` marker so that imageUrl() can build a
 * URL without touching the media table — otherwise every product on an index
 * page would cost an extra query.
 *
 * Models that used to write to disk keep working: if `image_mime` is empty but
 * the legacy `image_path` is set, the file is still served from storage. That
 * lets existing LAN installations keep their images without a data migration.
 */
trait HasImageBlob
{
    /** Slug used in the media URL, e.g. "product" -> /media/product/12. */
    abstract public static function mediaType(): string;

    public function image(): MorphOne
    {
        return $this->morphOne(Media::class, 'mediable')->where('collection', 'image');
    }

    /**
     * Replace this model's image with an uploaded file.
     */
    public function setImageFromUpload(UploadedFile $file): void
    {
        $this->setImageFromBytes(
            (string) file_get_contents($file->getRealPath()),
            (string) $file->getMimeType(),
            $file->getClientOriginalName(),
        );
    }

    /**
     * Replace this model's image with raw bytes (used by the spreadsheet and
     * URL importers, which never produce an UploadedFile).
     */
    public function setImageFromBytes(string $bytes, string $mime, ?string $originalName = null): void
    {
        Media::updateOrCreate(
            [
                'mediable_type' => static::class,
                'mediable_id' => $this->getKey(),
                'collection' => 'image',
            ],
            [
                'mime' => $mime,
                'original_name' => $originalName,
                'size' => strlen($bytes),
                'data' => $bytes,
            ],
        );

        // Written directly so callers don't have to remember a second save().
        $this->forceFill(['image_mime' => $mime])->save();
    }

    public function deleteImage(): void
    {
        Media::where('mediable_type', static::class)
            ->where('mediable_id', $this->getKey())
            ->where('collection', 'image')
            ->delete();

        if ($this->image_path) {
            Storage::disk('public')->delete($this->image_path);
        }

        $this->forceFill(['image_mime' => null, 'image_path' => null])->save();
    }

    public function hasImage(): bool
    {
        return (bool) ($this->image_mime || $this->image_path);
    }

    public function imageUrl(): ?string
    {
        if ($this->image_mime) {
            return route('media.show', [static::mediaType(), $this->getKey()]);
        }

        // Legacy on-disk image from an older LAN install.
        return $this->image_path ? Storage::url($this->image_path) : null;
    }
}
