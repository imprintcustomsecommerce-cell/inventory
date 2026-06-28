<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['name', 'type', 'location', 'event_date', 'can_create_items'];

    protected $casts = [
        'can_create_items' => 'boolean',
        'event_date' => 'date',
    ];

    public function scopeStockrooms($query)
    {
        return $query->where('can_create_items', true);
    }

    public function scopeEvents($query)
    {
        return $query->where('type', 'event');
    }

    public function isEvent(): bool
    {
        return $this->type === 'event';
    }

    /** Selling happens at stores and events (not the stockroom). */
    public function sellsStock(): bool
    {
        return in_array($this->type, ['store', 'event']);
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
