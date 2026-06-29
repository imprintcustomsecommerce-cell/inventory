@extends('layouts.app')

@section('title', 'Payroll ' . $run->periodLabel())

@section('content')

<a href="{{ route('payroll.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to payroll
</a>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $run->periodLabel() }}</h1>
            <span class="badge {{ $run->getStatusBadgeClass() }}">{{ $run->status }}</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $run->payslips->count() }} {{ Str::plural('employee', $run->payslips->count()) }}
            @if($run->notes) · {{ $run->notes }} @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @unless($run->isFinalized())
            <form action="{{ route('payroll.finalize', $run) }}" method="POST" onsubmit="return confirm('Finalize this run? Payslips can no longer be edited.');">
                @csrf
                <button type="submit" class="btn btn-primary">Finalize</button>
            </form>
        @endunless
        <form action="{{ route('payroll.destroy', $run) }}" method="POST" onsubmit="return confirm('Delete this payroll run?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-ghost">Delete</button>
        </form>
    </div>
</div>

<!-- Totals -->
<div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3">
    <div class="card p-5">
        <p class="text-sm font-medium text-zinc-500">Gross total</p>
        <p class="mt-2 text-2xl font-bold text-zinc-900">₱{{ number_format($run->totalGross(), 2) }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-zinc-500">Deductions</p>
        <p class="mt-2 text-2xl font-bold text-zinc-900">₱{{ number_format($run->totalGross() - $run->totalNet(), 2) }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-zinc-500">Net payable</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">₱{{ number_format($run->totalNet(), 2) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <div class="border-b border-zinc-200 px-6 py-4">
        <h2 class="text-sm font-semibold text-zinc-900">Payslips</h2>
    </div>
    @if($run->payslips->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th class="text-right">Hours</th>
                        <th class="text-right">Gross</th>
                        <th class="text-right">Deductions</th>
                        <th class="text-right">Net</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($run->payslips as $slip)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $slip->employee_name }}</td>
                            <td class="text-right text-zinc-700">{{ rtrim(rtrim(number_format($slip->total_hours, 2), '0'), '.') }}</td>
                            <td class="text-right text-zinc-700">₱{{ number_format($slip->gross_pay, 2) }}</td>
                            <td class="text-right">
                                @if($run->isFinalized())
                                    <span class="text-zinc-700">₱{{ number_format($slip->deductions, 2) }}</span>
                                @else
                                    <form action="{{ route('payroll.payslips.update', [$run, $slip]) }}" method="POST" class="flex items-center justify-end gap-1">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="deductions" min="0" step="0.01" value="{{ $slip->deductions }}" class="input w-28 text-right">
                                        <button type="submit" class="btn btn-ghost btn-sm">Save</button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-right font-semibold text-zinc-900">₱{{ number_format($slip->net_pay, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No payslips</p>
            <p class="mt-1 text-sm text-zinc-500">No staff labor was logged in this period.</p>
        </div>
    @endif
</div>

@endsection
