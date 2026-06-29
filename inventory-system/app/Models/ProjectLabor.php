<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLabor extends Model
{
    protected $table = 'project_labor';

    protected $fillable = [
        'project_id',
        'user_id',
        'worker_name',
        'task',
        'hours',
        'hourly_rate',
        'logged_at',
        'remarks',
    ];

    protected $casts = [
        'hours' => 'decimal:2',
        'hourly_rate' => 'decimal:2',
        'logged_at' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The name to show for whoever did the work.
     */
    public function workerLabel(): string
    {
        return $this->user?->name ?? $this->worker_name ?? 'Unassigned';
    }

    /**
     * Cost of this labor entry: hours × hourly rate.
     */
    public function cost(): float
    {
        return (float) $this->hours * (float) $this->hourly_rate;
    }
}
