<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['name', 'location', 'can_create_items'];

    protected $casts = [
        'can_create_items' => 'boolean',
    ];

    public function scopeStockrooms($query)
    {
        return $query->where('can_create_items', true);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryItem::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
