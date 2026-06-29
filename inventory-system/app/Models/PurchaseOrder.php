<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'po_number',
        'supplier_id',
        'warehouse_id',
        'user_id',
        'status',
        'order_date',
        'expected_date',
        'total',
        'notes',
    ];

    protected $casts = [
        'order_date' => 'date',
        'expected_date' => 'date',
        'total' => 'decimal:2',
    ];

    public const STATUSES = [
        'Draft',
        'Ordered',
        'Partially Received',
        'Received',
        'Cancelled',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Received' => 'badge-green',
            'Partially Received', 'Ordered' => 'badge-amber',
            'Cancelled' => 'badge-zinc',
            default => 'badge-zinc',
        };
    }

    /** Items / receiving can happen while the order is open. */
    public function isEditable(): bool
    {
        return !in_array($this->status, ['Received', 'Cancelled'], true);
    }

    /** Whether stock can still be received against this order. */
    public function canReceive(): bool
    {
        return in_array($this->status, ['Ordered', 'Partially Received'], true);
    }

    public function isOverdue(): bool
    {
        return $this->expected_date
            && in_array($this->status, ['Ordered', 'Partially Received'], true)
            && $this->expected_date->isPast();
    }
}
