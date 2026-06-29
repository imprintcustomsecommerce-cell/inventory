@extends('layouts.app')

@section('title', 'Commission ' . $run->periodLabel())

@section('content')

<a href="{{ route('commissions.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to commissions
</a>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $run->periodLabel() }}</h1>
            <span class="badge {{ $run->getStatusBadgeClass() }}">{{ $run->status }}</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $run->items->count() }} {{ Str::plural('seller', $run->items->count()) }}
            @if($run->notes) · {{ $run->notes }} @endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @unless($run->isFinalized())
            <form action="{{ route('commissions.finalize', $run) }}" method="POST" onsubmit="return confirm('Finalize this run?');">
                @csrf
                <button type="submit" class="btn btn-primary">Finalize</button>
            </form>
        @endunless
        <form action="{{ route('commissions.destroy', $run) }}" method="POST" onsubmit="return confirm('Delete this commission run?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-ghost">Delete</button>
        </form>
    </div>
</div>

<div class="mb-6 card p-5 sm:max-w-xs">
    <p class="text-sm font-medium text-zinc-500">Total commission</p>
    <p class="mt-2 text-2xl font-bold text-emerald-600">₱{{ number_format($run->totalCommission(), 2) }}</p>
</div>

<div class="card overflow-hidden">
    <div class="border-b border-zinc-200 px-6 py-4">
        <h2 class="text-sm font-semibold text-zinc-900">Payouts</h2>
    </div>
    @if($run->items->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Seller</th>
                        <th class="text-right">Sales</th>
                        <th class="text-right">Sales total</th>
                        <th class="text-right">Rate</th>
                        <th class="text-right">Commission</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($run->items as $item)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $item->employee_name }}</td>
                            <td class="text-right text-zinc-700">{{ $item->sales_count }}</td>
                            <td class="text-right text-zinc-700">₱{{ number_format($item->sales_total, 2) }}</td>
                            <td class="text-right text-zinc-700">{{ rtrim(rtrim(number_format($item->rate, 2), '0'), '.') }}%</td>
                            <td class="text-right font-semibold text-zinc-900">₱{{ number_format($item->commission, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No payouts</p>
            <p class="mt-1 text-sm text-zinc-500">No commissionable sales in this period.</p>
        </div>
    @endif
</div>

@endsection
