<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = [
        'project_name',
        'customer_name',
        'product_type',
        'quantity',
        'status',
        'due_date',
        'remarks',
        'materials_deducted',
        'started_production_at',
        'completed_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
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

    public function materials(): HasMany
    {
        return $this->hasMany(ProjectMaterial::class);
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
