<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Material extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'name',
        'category',
        'unit',
        'current_stock',
        'minimum_stock',
        'unit_cost',
        'supplier',
        'remarks',
    ];

    protected $casts = [
        'current_stock' => 'float',
        'minimum_stock' => 'float',
        'unit_cost' => 'decimal:2',
    ];

    public const CATEGORIES = [
        'Fabric', 'Thread', 'Zipper', 'Button', 'Collar', 'Cuffs',
        'Label', 'Ink', 'Packaging', 'Other',
    ];

    public const UNITS = ['pcs', 'yards', 'meters', 'rolls', 'spools', 'packs', 'boxes', 'kg', 'liters'];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(MaterialMovement::class);
    }

    public function isLowStock(): bool
    {
        return $this->current_stock <= $this->minimum_stock && $this->current_stock > 0;
    }

    public function isOutOfStock(): bool
    {
        return $this->current_stock <= 0;
    }

    public function getStatusLabel(): string
    {
        return $this->isOutOfStock() ? 'Out of Stock' : ($this->isLowStock() ? 'Low Stock' : 'In Stock');
    }

    public function getStatusBadgeClass(): string
    {
        return $this->isOutOfStock() ? 'badge-red' : ($this->isLowStock() ? 'badge-amber' : 'badge-green');
    }

    public function scopeVisibleTo($query, User $user)
    {
        if (!$user->isAdmin() && $user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        return $query;
    }
}
