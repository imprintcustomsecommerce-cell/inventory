<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'project_name',
        'customer_id',
        'customer_name',
        'product_type',
        'quantity',
        'quoted_price',
        'status',
        'due_date',
        'remarks',
        'materials_deducted',
        'started_production_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'quoted_price' => 'decimal:2',
        'due_date' => 'date',
        'materials_deducted' => 'boolean',
        'started_production_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public const STATUSES = [
        'Pending',
        'For Design',
        'For Sample',
        'For Approval',
        'For Production',
        'Completed',
        'Cancelled',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(ProjectStatusLog::class)->latest();
    }

    /**
     * Total material cost based on quantity needed × each item's unit cost.
     */
    public function materialsCost(): float
    {
        return (float) $this->materials->sum(
            fn ($m) => (float) $m->quantity_needed * (float) ($m->inventoryItem->unit_cost ?? 0)
        );
    }

    public function margin(): ?float
    {
        if ($this->quoted_price === null) {
            return null;
        }

        return (float) $this->quoted_price - $this->materialsCost();
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Completed' => 'badge-green',
            'For Production' => 'badge-amber',
            'Cancelled' => 'badge-red',
            default => 'badge-zinc',
        };
    }

    public function isOverdue(): bool
    {
        return $this->due_date
            && !in_array($this->status, ['Completed', 'Cancelled'])
            && $this->due_date->isPast();
    }
}
