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
        'category',
        'brand',
        'material',
        'retail_price',
        'cost_price',
        'description',
        'image_path',
    ];

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

    /** Limit a query to products a given user may see (admins see all). */
    public function scopeVisibleTo($query, User $user)
    {
        if (!$user->isAdmin() && $user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        return $query;
    }
}
