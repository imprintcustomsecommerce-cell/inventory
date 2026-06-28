@extends('layouts.app')

@section('title', 'Sales')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Sales</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $count }} sale{{ $count === 1 ? '' : 's' }} · revenue <span class="font-semibold text-emerald-600">₱{{ number_format($revenue, 2) }}</span>@if($showProfit) · profit <span class="font-semibold text-emerald-600">₱{{ number_format($profit, 2) }}</span>@endif</p>
    </div>
    <a href="{{ route('sales.export', request()->query()) }}" class="btn btn-ghost">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Export Excel
    </a>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('sales.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input sm:w-44">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="input sm:w-44">
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request()->hasAny(['date_from','date_to']))
                <a href="{{ route('sales.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($sales->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Location</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        @if($showProfit)<th>Profit</th>@endif
                        <th>Sold By</th>
                        <th class="text-right">Receipt</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($sales as $sale)
                        <tr>
                            <td class="whitespace-nowrap text-zinc-500">{{ $sale->created_at->format('M d, Y · h:i A') }}</td>
                            <td class="font-medium text-zinc-900">{{ $sale->item_label }}</td>
                            <td><span class="badge badge-zinc">{{ $sale->warehouse?->name ?? '—' }}</span></td>
                            <td class="text-zinc-700">{{ $sale->quantity }}</td>
                            <td class="text-zinc-500">₱{{ number_format($sale->unit_price, 2) }}</td>
                            <td class="font-semibold text-zinc-900">₱{{ number_format($sale->total, 2) }}</td>
                            @if($showProfit)<td class="font-medium {{ $sale->profit() >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₱{{ number_format($sale->profit(), 2) }}</td>@endif
                            <td class="text-zinc-500">{{ $sale->user?->name ?? '—' }}</td>
                            <td class="text-right">
                                <a href="{{ route('sales.receipt', $sale) }}" target="_blank" class="btn btn-ghost btn-sm">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 3h6m2.25 6H6.75A2.25 2.25 0 014.5 18.75V5.25A2.25 2.25 0 016.75 3h7.5l5.25 5.25v10.5A2.25 2.25 0 0117.25 21z"/></svg>
                                    Receipt
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())<div class="border-t border-zinc-200 px-5 py-3">{{ $sales->links() }}</div>@endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No sales yet</p>
            <p class="mt-1 text-sm text-zinc-500">Sales appear here when stock is sold from a store or event.</p>
        </div>
    @endif
</div>

@endsection
