<?php

namespace App\Http\Controllers;

use App\Models\CommissionRun;
use App\Services\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function __construct(private CommissionService $commissions)
    {
    }

    public function index()
    {
        $runs = CommissionRun::withCount('items')
            ->withSum('items as commission_total', 'commission')
            ->latest()
            ->paginate(20);

        return view('commissions.index', compact('runs'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string|max:500',
        ]);

        $run = $this->commissions->generate($data['period_start'], $data['period_end'], $data['notes'] ?? null);

        if ($run->items()->count() === 0) {
            return redirect()->route('commissions.show', $run)
                ->with('error', 'No commissionable sales in that period (check seller commission rates).');
        }

        return redirect()->route('commissions.show', $run)
            ->with('success', 'Commission run generated from sales.');
    }

    public function show(CommissionRun $commission)
    {
        $commission->load('items.user', 'creator');

        return view('commissions.show', ['run' => $commission]);
    }

    public function finalize(CommissionRun $commission)
    {
        $commission->update(['status' => 'Finalized']);

        return back()->with('success', 'Commission run finalized.');
    }

    public function destroy(CommissionRun $commission)
    {
        $commission->delete();

        return redirect()->route('commissions.index')->with('success', 'Commission run deleted.');
    }
}
