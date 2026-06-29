<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    protected $fillable = [
        'code',
        'type',
        'value',
        'min_subtotal',
        'max_uses',
        'used_count',
        'expires_at',
        'active',
        'description',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_subtotal' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'expires_at' => 'date',
        'active' => 'boolean',
    ];

    /**
     * Why this code can't be applied to the given subtotal, or null if it can.
     */
    public function invalidReason(float $subtotal): ?string
    {
        if (!$this->active) {
            return 'This code is inactive.';
        }
        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'This code has expired.';
        }
        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return 'This code has reached its usage limit.';
        }
        if ($this->min_subtotal !== null && $subtotal < (float) $this->min_subtotal) {
            return 'Subtotal is below this code\'s minimum of ₱' . number_format($this->min_subtotal, 2) . '.';
        }

        return null;
    }

    /**
     * The discount amount this code yields for a subtotal (capped at subtotal).
     */
    public function discountFor(float $subtotal): float
    {
        $raw = $this->type === 'percent'
            ? $subtotal * (float) $this->value / 100
            : (float) $this->value;

        return round(min($raw, $subtotal), 2);
    }

    public function label(): string
    {
        return $this->type === 'percent'
            ? rtrim(rtrim(number_format($this->value, 2), '0'), '.') . '% off'
            : '₱' . number_format($this->value, 2) . ' off';
    }
}
