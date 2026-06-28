<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockRequestItem extends Model
{
    protected $fillable = [
        'stock_request_id',
        'inventory_item_id',
        'item_label',
        'quantity',
        'fulfilled_quantity',
    ];

    protected $casts = [
        'quantity' => 'float',
        'fulfilled_quantity' => 'float',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(StockRequest::class, 'stock_request_id');
    }

    public function inventoryItem(): BelongsTo
    {
        return $this->belongsTo(InventoryItem::class);
    }
}
