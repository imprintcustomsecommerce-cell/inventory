<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnlineOrder extends Model
{
    protected $fillable = [
        'sales_channel_id',
        'external_ref',
        'buyer_name',
        'buyer_contact',
        'item_label',
        'quantity',
        'amount',
        'order_type',
        'status',
        'routed_type',
        'routed_id',
        'notes',
        'ordered_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'amount' => 'decimal:2',
        'ordered_at' => 'datetime',
    ];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class, 'sales_channel_id');
    }

    public function isNew(): bool
    {
        return $this->status === 'New';
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Routed' => 'badge-green',
            'Ignored' => 'badge-zinc',
            default => 'badge-amber',
        };
    }

    /** Link to the record this order became, if routed. */
    public function routedUrl(): ?string
    {
        if ($this->routed_type === 'project' && $this->routed_id) {
            return route('projects.show', $this->routed_id);
        }
        if ($this->routed_type === 'sale' && $this->routed_id) {
            return route('sales.index');
        }

        return null;
    }
}
