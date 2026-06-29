<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayrollRun extends Model
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

    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFinalized(): bool
    {
        return $this->status === 'Finalized';
    }

    public function totalNet(): float
    {
        return (float) $this->payslips->sum('net_pay');
    }

    public function totalGross(): float
    {
        return (float) $this->payslips->sum('gross_pay');
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
