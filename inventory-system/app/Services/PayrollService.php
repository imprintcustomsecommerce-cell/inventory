<?php

namespace App\Services;

use App\Models\PayrollRun;
use App\Models\ProjectLabor;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    /**
     * Create a payroll run and build one payslip per employee from the
     * project labor logged within the period.
     */
    public function generate(string $periodStart, string $periodEnd, ?string $notes = null): PayrollRun
    {
        return DB::transaction(function () use ($periodStart, $periodEnd, $notes) {
            $run = PayrollRun::create([
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
                'status' => 'Draft',
                'notes' => $notes,
                'created_by' => auth()->id(),
            ]);

            // Only labor tied to a staff account counts toward payroll.
            $labor = ProjectLabor::with('user')
                ->whereNotNull('user_id')
                ->whereBetween('logged_at', [$periodStart, $periodEnd])
                ->get()
                ->groupBy('user_id');

            foreach ($labor as $userId => $entries) {
                $hours = (float) $entries->sum(fn ($e) => (float) $e->hours);
                $gross = (float) $entries->sum(fn ($e) => (float) $e->hours * (float) $e->hourly_rate);

                $run->payslips()->create([
                    'user_id' => $userId,
                    'employee_name' => $entries->first()->user?->name ?? 'Employee #' . $userId,
                    'total_hours' => $hours,
                    'gross_pay' => $gross,
                    'deductions' => 0,
                    'net_pay' => $gross,
                ]);
            }

            return $run;
        });
    }
}
