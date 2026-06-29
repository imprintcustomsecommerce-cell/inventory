<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectFeedback extends Model
{
    protected $table = 'project_feedback';

    protected $fillable = [
        'project_id',
        'customer_id',
        'rating',
        'would_recommend',
        'comment',
        'reviewer_name',
        'submitted_at',
    ];

    protected $casts = [
        'rating' => 'integer',
        'would_recommend' => 'boolean',
        'submitted_at' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /** Filled and empty stars for display. */
    public function stars(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}
