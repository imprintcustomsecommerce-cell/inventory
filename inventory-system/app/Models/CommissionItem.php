<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionItem extends Model
{
    protected $fillable = [
        'commission_run_id',
        'user_id',
        'employee_name',
        'sales_count',
        'sales_total',
        'rate',
        'commission',
    ];

    protected $casts = [
        'sales_count' => 'integer',
        'sales_total' => 'decimal:2',
        'rate' => 'decimal:2',
        'commission' => 'decimal:2',
    ];

    public function run(): BelongsTo
    {
        return $this->belongsTo(CommissionRun::class, 'commission_run_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
