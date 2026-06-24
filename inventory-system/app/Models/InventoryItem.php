<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class InventoryItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'name',
        'category',
        'size',
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
        return $this->size ? "{$this->name} ({$this->size})" : $this->name;
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::url($this->image_path) : null;
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
