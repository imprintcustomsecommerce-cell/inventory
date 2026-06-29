@extends('layouts.app')

@section('title', 'Payroll')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Payroll</h1>
    <p class="mt-1 text-sm text-zinc-500">Generate payslips from project labor logged in a pay period.</p>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- New run -->
    <div class="lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">New payroll run</h2>
            <form action="{{ route('payroll.store') }}" method="POST" class="mt-4 space-y-3">
                @csrf
                <div>
                    <label class="label">Period start</label>
                    <input type="date" name="period_start" value="{{ old('period_start', now()->startOfMonth()->toDateString()) }}" required class="input">
                </div>
                <div>
                    <label class="label">Period end</label>
                    <input type="date" name="period_end" value="{{ old('period_end', now()->toDateString()) }}" required class="input">
                </div>
                <div>
                    <label class="label">Notes (optional)</label>
                    <input type="text" name="notes" value="{{ old('notes') }}" class="input" placeholder="e.g. June 2nd half">
                </div>
                <button type="submit" class="btn btn-primary w-full">Generate</button>
                @error('period_end')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </form>
            <p class="mt-3 text-xs text-zinc-400">Aggregates hours × rate from labor logged by staff accounts in the period.</p>
        </div>
    </div>

    <!-- Runs list -->
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            @if($runs->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Period</th>
                            <th>Status</th>
                            <th class="text-right">Employees</th>
                            <th class="text-right">Net total</th>
                            <th class="text-right">Open</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($runs as $run)
                            <tr>
                                <td class="font-medium text-zinc-900">{{ $run->periodLabel() }}</td>
                                <td><span class="badge {{ $run->getStatusBadgeClass() }}">{{ $run->status }}</span></td>
                                <td class="text-right text-zinc-700">{{ $run->payslips_count }}</td>
                                <td class="text-right font-medium text-zinc-900">₱{{ number_format((float) $run->net_total, 2) }}</td>
                                <td class="text-right">
                                    <a href="{{ route('payroll.show', $run) }}" class="btn btn-ghost btn-sm">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($runs->hasPages())
                    <div class="border-t border-zinc-200 px-5 py-3">{{ $runs->links() }}</div>
                @endif
            @else
                <div class="px-6 py-16 text-center">
                    <p class="text-sm font-medium text-zinc-900">No payroll runs yet</p>
                    <p class="mt-1 text-sm text-zinc-500">Generate your first run from the form on the left.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
