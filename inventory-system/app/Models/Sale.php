<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sale extends Model
{
    protected $fillable = [
        'warehouse_id',
        'inventory_item_id',
        'product_id',
        'user_id',
        'item_label',
        'quantity',
        'unit_price',
        'unit_cost',
        'total',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'float',
        'unit_price' => 'decimal:2',
        'unit_cost' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /** Gross profit for this sale line. */
    public function profit(): float
    {
        return (float) $this->total - ((float) $this->unit_cost * (float) $this->quantity);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeVisibleTo($query, User $user)
    {
        if (!$user->isAdmin() && $user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        return $query;
    }
}
