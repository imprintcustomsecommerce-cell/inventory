<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesChannel extends Model
{
    protected $fillable = [
        'platform',
        'name',
        'shop_name',
        'status',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(OnlineOrder::class);
    }

    public function isConnected(): bool
    {
        return $this->status === 'connected';
    }

    /** Brand accent colour for the platform badge. */
    public function color(): string
    {
        return match ($this->platform) {
            'shopee' => 'bg-orange-100 text-orange-700',
            'lazada' => 'bg-indigo-100 text-indigo-700',
            'tiktok' => 'bg-zinc-900 text-white',
            default => 'bg-zinc-100 text-zinc-700',
        };
    }
}
