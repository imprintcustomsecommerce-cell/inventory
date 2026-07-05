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
        'credentials',
        'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'credentials' => 'encrypted:array',
    ];

    /** Developer app keys are saved (may still need shop authorization). */
    public function hasApiCredentials(): bool
    {
        $c = $this->credentials ?? [];

        return match ($this->platform) {
            'shopee' => !empty($c['partner_id']) && !empty($c['partner_key']),
            'lazada', 'tiktok' => !empty($c['app_key']) && !empty($c['app_secret']),
            default => false,
        };
    }

    /** Fully authorized against the real marketplace API. */
    public function isLive(): bool
    {
        return $this->hasApiCredentials() && !empty($this->credentials['access_token']);
    }

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
