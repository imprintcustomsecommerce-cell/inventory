<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StockRequest extends Model
{
    protected $fillable = [
        'warehouse_id',
        'requested_by_id',
        'handled_by_id',
        'status',
        'note',
        'handled_at',
    ];

    protected $casts = [
        'handled_at' => 'datetime',
    ];

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockRequestItem::class);
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'fulfilled' => 'badge-green',
            'rejected', 'cancelled' => 'badge-red',
            default => 'badge-amber',
        };
    }

    public function scopeVisibleTo($query, User $user)
    {
        // Stockroom staff + admins see all; everyone else sees their own location's requests.
        if (!$user->isAdmin() && !($user->warehouse && $user->warehouse->can_create_items) && $user->warehouse_id) {
            $query->where('warehouse_id', $user->warehouse_id);
        }

        return $query;
    }
}
