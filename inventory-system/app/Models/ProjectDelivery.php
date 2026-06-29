<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectDelivery extends Model
{
    protected $fillable = [
        'project_id',
        'status',
        'method',
        'courier',
        'tracking_number',
        'recipient_name',
        'recipient_contact',
        'address',
        'scheduled_date',
        'dispatched_at',
        'delivered_at',
        'received_by',
        'fee',
        'remarks',
        'created_by',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'dispatched_at' => 'datetime',
        'delivered_at' => 'datetime',
        'fee' => 'decimal:2',
    ];

    public const STATUSES = [
        'Scheduled',
        'Out for Delivery',
        'Delivered',
        'Failed',
        'Returned',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['Scheduled', 'Out for Delivery']);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Delivered' => 'badge-green',
            'Out for Delivery' => 'badge-amber',
            'Failed', 'Returned' => 'badge-red',
            default => 'badge-zinc',
        };
    }
}
