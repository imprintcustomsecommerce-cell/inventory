<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Activity extends Model
{
    protected $fillable = [
        'type',
        'title',
        'description',
        'url',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record an activity. Safe to call from anywhere; the current user is
     * attributed automatically.
     */
    public static function log(string $type, string $title, ?string $description = null, ?string $url = null): void
    {
        static::create([
            'type' => $type,
            'title' => $title,
            'description' => $description,
            'url' => $url,
            'user_id' => auth()->id(),
        ]);
    }

    public function iconColor(): string
    {
        return match ($this->type) {
            'order' => 'bg-sky-100 text-sky-700',
            'proof' => 'bg-violet-100 text-violet-700',
            'delivery' => 'bg-emerald-100 text-emerald-700',
            'quality' => 'bg-red-100 text-red-700',
            'feedback' => 'bg-amber-100 text-amber-700',
            'project' => 'bg-brand-100 text-zinc-700',
            default => 'bg-zinc-100 text-zinc-600',
        };
    }
}
