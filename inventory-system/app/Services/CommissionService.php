<?php

namespace App\Services;

use App\Models\CommissionRun;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CommissionService
{
    /**
     * Create a commission run, computing each seller's commission from the
     * sales they made in the period × their commission rate.
     */
    public function generate(string $periodStart, string $periodEnd, ?string $notes = null): CommissionRun
    {
        return DB::transaction(function () use ($periodStart, $periodEnd, $notes) {
            $run = CommissionRun::create([
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'Draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // Sales grouped by seller within the period.
            $sales = Sale::query()
                ->with('user')
                ->whereNotNull('user_id')
                ->whereBetween('created_at', [$periodStart . ' 00:00:00', $periodEnd . ' 23:59:59'])
                ->get()
                ->groupBy('user_id');

            $rates = User::whereIn('id', $sales->keys())->pluck('commission_rate', 'id');

            foreach ($sales as $userId => $rows) {
                $rate = (float) ($rates[$userId] ?? 0);
                if ($rate <= 0) {
                    continue; // no commission scheme for this seller
                }

                $total = (float) $rows->sum('total');

                $run->items()->create([
                    'user_id' => $userId,
                    'employee_name' => $rows->first()->user?->name ?? 'Employee #' . $userId,
                    'sales_count' => $rows->count(),
                    'sales_total' => $total,
                    'rate' => $rate,
                    'commission' => round($total * $rate / 100, 2),
                ]);
            }

            return $run;
        });
    }
}
