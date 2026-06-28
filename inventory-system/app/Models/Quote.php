<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Quote extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'quote_number',
        'customer_id',
        'user_id',
        'title',
        'status',
        'valid_until',
        'subtotal',
        'discount',
        'total',
        'notes',
        'terms',
        'project_id',
        'converted_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'converted_at' => 'datetime',
    ];

    public const STATUSES = [
        'Draft',
        'Sent',
        'Approved',
        'Rejected',
        'Expired',
        'Converted',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Approved', 'Converted' => 'badge-green',
            'Sent' => 'badge-amber',
            'Rejected', 'Expired' => 'badge-red',
            default => 'badge-zinc',
        };
    }

    /** Whether the quote can still be edited (items / details). */
    public function isEditable(): bool
    {
        return !in_array($this->status, ['Converted'], true);
    }

    /** A quote past its validity date that has not been acted on. */
    public function isExpired(): bool
    {
        return $this->valid_until
            && !in_array($this->status, ['Approved', 'Rejected', 'Converted'], true)
            && $this->valid_until->isPast();
    }
}
