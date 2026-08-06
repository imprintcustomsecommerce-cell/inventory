<?php

namespace App\Models;

use App\Concerns\HasImageBlob;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use HasImageBlob;
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'product_id',
        'name',
        'category',
        'size',
        'color',
        'sku',
        'unit',
        'current_stock',
        'minimum_stock',
        'unit_cost',
        'status',
        'remarks',
        'image_path',
    ];

    /** Product categories for Imprint's apparel lines. */
    public const CATEGORIES = [
        'Jersey',
        'Polo Shirt',
        'Round Neck Shirt',
        'V-Neck Shirt',
        'Jacket / Hoodie',
        'Shorts',
        'Jogger Pants',
        'Cap',
        'Accessories',
        'Other',
    ];

    /** Common apparel sizes. */
    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', '2XL', '3XL', 'One Size'];

    public function displayName(): string
    {
        $variant = trim(implode(' / ', array_filter([$this->size, $this->color])));

        return $variant !== '' ? "{$this->name} ({$variant})" : $this->name;
    }

    /** Short size/color label for tables, e.g. "M / Navy". */
    public function variantLabel(): string
    {
        return trim(implode(' / ', array_filter([$this->size, $this->color]))) ?: '—';
    }

    public static function mediaType(): string
    {
        return 'item';
    }

    protected $casts = [
        'current_stock' => 'float',
        'minimum_stock' => 'float',
        'unit_cost' => 'decimal:2',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function warehouse(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Limit a query to the items a given user may see (admins see all). */
    public function scopeVisibleTo($query, User $user)
    {
        if (!$user->isAdmin() && $user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        return $query;
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock && $this->current_stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function getStatusBadgeClass(): string
    {
        if ($this->isOutOfStock()) {
            return 'bg-red-100 text-red-800';
        }
        if ($this->isLowStock()) {
            return 'bg-yellow-100 text-yellow-800';
        }
        return 'bg-green-100 text-green-800';
    }

    public function getStatusLabel(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of Stock';
        }
        if ($this->isLowStock()) {
            return 'Low Stock';
        }
        return 'In Stock';
    }
}
