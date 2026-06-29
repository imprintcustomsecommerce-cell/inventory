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
        'public_token',
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

    public function labor(): HasMany
    {
        return $this->hasMany(ProjectLabor::class)->latest('logged_at');
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(ProjectProof::class)->orderByDesc('version');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(ProjectDelivery::class)->latest();
    }

    public function issues(): HasMany
    {
        return $this->hasMany(ProjectIssue::class)->latest();
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(ProjectFeedback::class)->latest();
    }

    /** Generate the public token if missing, then return it. */
    public function ensurePublicToken(): string
    {
        if (!$this->public_token) {
            $this->update(['public_token' => \Illuminate\Support\Str::random(48)]);
        }

        return $this->public_token;
    }

    public function portalUrl(): ?string
    {
        return $this->public_token ? route('portal.show', $this->public_token) : null;
    }

    public function openIssuesCount(): int
    {
        return $this->issues->whereIn('status', ['Open', 'In Progress'])->count();
    }

    public function latestDelivery(): ?ProjectDelivery
    {
        return $this->deliveries->first();
    }

    public function latestProof(): ?ProjectProof
    {
        return $this->proofs->first();
    }

    public function hasApprovedProof(): bool
    {
        return $this->proofs->contains(fn ($p) => $p->status === 'Approved');
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

    /**
     * Total labor cost: hours × hourly rate across all logged entries.
     */
    public function laborCost(): float
    {
        return (float) $this->labor->sum(
            fn ($l) => (float) $l->hours * (float) $l->hourly_rate
        );
    }

    /**
     * Total hours logged against this project.
     */
    public function totalHours(): float
    {
        return (float) $this->labor->sum(fn ($l) => (float) $l->hours);
    }

    /**
     * Full production cost: materials + labor.
     */
    public function totalCost(): float
    {
        return $this->materialsCost() + $this->laborCost();
    }

    public function margin(): ?float
    {
        if ($this->quoted_price === null) {
            return null;
        }

        return (float) $this->quoted_price - $this->totalCost();
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
