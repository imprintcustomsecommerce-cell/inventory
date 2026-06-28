<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialMovement extends Model
{
    protected $fillable = [
        'material_id',
        'user_id',
        'type',
        'quantity',
        'reference',
        'remarks',
    ];

    protected $casts = [
        'quantity' => 'float',
    ];

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'stock_in' => 'Stock In',
            'stock_out' => 'Stock Out',
            'adjustment' => 'Adjustment',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    public function getTypeBadgeClass(): string
    {
        return match ($this->type) {
            'stock_in' => 'badge-green',
            'stock_out' => 'badge-red',
            'adjustment' => 'badge-amber',
            default => 'badge-zinc',
        };
    }
}
