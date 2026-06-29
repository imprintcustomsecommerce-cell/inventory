<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectIssue extends Model
{
    protected $fillable = [
        'project_id',
        'type',
        'status',
        'reason',
        'description',
        'quantity_affected',
        'rework_cost',
        'reported_by',
        'reported_at',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'quantity_affected' => 'integer',
        'rework_cost' => 'decimal:2',
        'reported_at' => 'date',
        'resolved_at' => 'datetime',
    ];

    public const TYPES = ['Defect', 'Reprint', 'Return', 'Complaint'];

    public const STATUSES = ['Open', 'In Progress', 'Resolved', 'Rejected'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['Open', 'In Progress']);
    }

    public function getStatusBadgeClass(): string
    {
        return match ($this->status) {
            'Resolved' => 'badge-green',
            'In Progress' => 'badge-amber',
            'Rejected' => 'badge-zinc',
            default => 'badge-red',
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'Reprint' => 'badge-amber',
            'Return' => 'badge-red',
            'Complaint' => 'badge-zinc',
            default => 'badge-zinc',
        };
    }
}
