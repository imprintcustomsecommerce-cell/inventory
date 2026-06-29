<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'address',
        'notes',
    ];

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class)->latest();
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class)->latest();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest();
    }

    /** Customer name with company shown when present. */
    public function displayName(): string
    {
        return $this->company
            ? "{$this->name} ({$this->company})"
            : $this->name;
    }
}
