<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProjectProof;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves uploaded files back out of the database.
 *
 * This is the read side of HasImageBlob: the bytes live in the `media` table,
 * and the browser fetches them through /media/{type}/{id}.
 */
class MediaController extends Controller
{
    /** URL slug => owning model. */
    private const TYPES = [
        'product' => Product::class,
        'item' => InventoryItem::class,
        'proof' => ProjectProof::class,
    ];

    public function show(string $type, int $id): Response
    {
        abort_unless(isset(self::TYPES[$type]), 404);

        $media = Media::where('mediable_type', self::TYPES[$type])
            ->where('mediable_id', $id)
            ->first();

        abort_if($media === null, 404);

        return response($media->data, 200, [
            'Content-Type' => $media->mime ?: 'application/octet-stream',
            'Content-Length' => (string) strlen((string) $media->data),
            // Files are immutable once uploaded — a replacement writes a new
            // row and the owner's updated_at changes, so caching hard is safe.
            'Cache-Control' => 'private, max-age=31536000',
            'Content-Disposition' => 'inline; filename="' . addslashes((string) ($media->original_name ?: $type)) . '"',
        ]);
    }
}
