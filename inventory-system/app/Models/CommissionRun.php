<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRun extends Model
{
    protected $fillable = [
        'period_start',
        'period_end',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(CommissionItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFinalized(): bool
    {
        return $this->status === 'Finalized';
    }

    public function totalCommission(): float
    {
        return (float) $this->items->sum('commission');
    }

    public function getStatusBadgeClass(): string
    {
        return $this->isFinalized() ? 'badge-green' : 'badge-amber';
    }

    public function periodLabel(): string
    {
        return $this->period_start->format('M d') . ' – ' . $this->period_end->format('M d, Y');
    }
}
