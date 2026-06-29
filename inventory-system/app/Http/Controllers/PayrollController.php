<?php

namespace App\Http\Controllers;

use App\Models\PayrollRun;
use App\Models\Payslip;
use App\Services\PayrollService;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function __construct(private PayrollService $payroll)
    {
    }

    public function index()
    {
        $runs = PayrollRun::withCount('payslips')
            ->withSum('payslips as net_total', 'net_pay')
            ->latest()
            ->paginate(20);

        return view('payroll.index', compact('runs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string|max:500',
        ]);

        $run = $this->payroll->generate($data['period_start'], $data['period_end'], $data['notes'] ?? null);

        if ($run->payslips()->count() === 0) {
            return redirect()->route('payroll.show', $run)
                ->with('error', 'No labor was logged in that period — the run is empty.');
        }

        return redirect()->route('payroll.show', $run)
            ->with('success', 'Payroll run generated from logged labor.');
    }

    public function show(PayrollRun $payroll)
    {
        $payroll->load('payslips.user', 'creator');

        return view('payroll.show', ['run' => $payroll]);
    }

    public function updatePayslip(Request $request, PayrollRun $payroll, Payslip $payslip)
    {
        if ($payroll->isFinalized()) {
            return back()->with('error', 'This run is finalized and can no longer be edited.');
        }

        $data = $request->validate([
            'deductions' => 'required|numeric|min:0',
        ]);

        $payslip->update([
            'deductions' => $data['deductions'],
            'net_pay' => max(0, (float) $payslip->gross_pay - (float) $data['deductions']),
        ]);

        return back()->with('success', "Updated payslip for {$payslip->employee_name}.");
    }

    public function finalize(PayrollRun $payroll)
    {
        $payroll->update(['status' => 'Finalized']);

        return back()->with('success', 'Payroll run finalized.');
    }

    public function destroy(PayrollRun $payroll)
    {
        $payroll->delete();

        return redirect()->route('payroll.index')->with('success', 'Payroll run deleted.');
    }
}
