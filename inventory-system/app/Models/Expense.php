<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'category',
        'description',
        'amount',
        'expense_date',
        'payment_method',
        'supplier_id',
        'user_id',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public const CATEGORIES = [
        'Rent',
        'Utilities',
        'Salaries',
        'Materials',
        'Supplies',
        'Equipment',
        'Marketing',
        'Transportation',
        'Repairs',
        'Taxes & Fees',
        'Other',
    ];

    public const PAYMENT_METHODS = [
        'Cash',
        'GCash',
        'Bank Transfer',
        'Check',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
