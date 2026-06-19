@extends('layouts.app')

@section('title', 'Stock History')

@section('content')

<a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to inventory
</a>

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Stock history</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $item->name }}</p>
    </div>
    <span class="badge {{ $item->isOutOfStock() ? 'badge-red' : ($item->isLowStock() ? 'badge-amber' : 'badge-green') }} self-start">
        {{ $item->current_stock }} {{ $item->unit }} in stock
    </span>
</div>

<div class="card overflow-hidden">
    @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>Reference</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap text-zinc-500">{{ $movement->created_at->format('M d, Y · h:i A') }}</td>
                            <td>
                                <span class="badge {{ $movement->type === 'stock_in' ? 'badge-green' : ($movement->type === 'stock_out' ? 'badge-red' : 'badge-amber') }}">{{ $movement->getTypeLabel() }}</span>
                            </td>
                            <td class="font-semibold text-zinc-900">{{ $movement->quantity }} <span class="text-xs font-normal text-zinc-400">{{ $item->unit }}</span></td>
                            <td class="text-zinc-500">{{ $movement->reference ?? '—' }}</td>
                            <td class="max-w-xs truncate text-zinc-500">{{ $movement->remarks ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $movements->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No movements yet</p>
            <p class="mt-1 text-sm text-zinc-500">Stock-ins, stock-outs, and adjustments will appear here.</p>
        </div>
    @endif
</div>

@endsection
