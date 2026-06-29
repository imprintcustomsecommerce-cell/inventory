<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'customer_id',
        'quote_id',
        'project_id',
        'user_id',
        'title',
        'status',
        'issue_date',
        'due_date',
        'subtotal',
        'discount',
        'total',
        'amount_paid',
        'notes',
        'terms',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public const STATUSES = [
        'Unpaid',
        'Partial',
        'Paid',
        'Cancelled',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest('paid_at');
    }

    /** Remaining balance due. */
    public function balance(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Paid' => 'badge-green',
            'Partial' => 'badge-amber',
            'Cancelled' => 'badge-zinc',
            default => 'badge-red',
        };
    }

    /** Items / payments can still be edited unless settled or cancelled. */
    public function isEditable(): bool
    {
        return !in_array($this->status, ['Cancelled'], true);
    }

    /** An unpaid/partial invoice past its due date. */
    public function isOverdue(): bool
    {
        return $this->due_date
            && !in_array($this->status, ['Paid', 'Cancelled'], true)
            && $this->due_date->isPast();
    }
}
