<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'warehouse_id',
        'sku',
        'name',
        'status',
        'category',
        'brand',
        'material',
        'retail_price',
        'cost_price',
        'description',
        'image_path',
    ];

    public function isActive(): bool
    {
        return ($this->status ?? 'active') === 'active';
    }

    public function scopeStatus($query, ?string $status)
    {
        return $status ? $query->where('status', $status) : $query;
    }

    protected $casts = [
        'retail_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function totalStock(): float
    {
        return (float) $this->variants->sum('current_stock');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? \Illuminate\Support\Facades\Storage::url($this->image_path) : null;
    }

    public function margin(): float
    {
        return (float) $this->retail_price - (float) $this->cost_price;
    }

    /** Warehouses that currently hold this product (have a size variant there). */
    public function stockWarehouseNames(): array
    {
        return $this->variants
            ->map(fn ($v) => $v->warehouse?->name)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Total stock per warehouse, e.g. ['Inventory' => 40, 'Store' => 12].
     *
     * @return array<string, float>
     */
    public function stockByWarehouse(): array
    {
        return $this->variants
            ->groupBy(fn ($v) => $v->warehouse?->name ?? '—')
            ->map(fn ($group) => (float) $group->sum('current_stock'))
            ->all();
    }

    /**
     * Limit to products a user may see (admins all). A product is visible to a
     * warehouse if it has a size variant stored there.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if (!$user->isAdmin() && $user->warehouse_id) {
            $query->whereHas('variants', fn ($q) => $q->where('warehouse_id', $user->warehouse_id));
        }

        return $query;
    }
}
