@extends('layouts.app')

@section('title', 'Movement Report')

@section('content')

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Movement report</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $movements->total() }} movement{{ $movements->total() === 1 ? '' : 's' }} recorded.</p>
    </div>
    <a href="{{ route('inventory.movements.export', request()->query()) }}" class="btn btn-ghost">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Export Excel
    </a>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('inventory.allMovements') }}" class="grid grid-cols-1 gap-3 border-b border-zinc-200 p-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="relative sm:col-span-2 lg:col-span-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search…" class="input pl-9">
        </div>
        <select name="type" class="select">
            <option value="">All types</option>
            <option value="stock_in" {{ request('type') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
            <option value="stock_out" {{ request('type') == 'stock_out' ? 'selected' : '' }}>Stock Out</option>
            <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>Adjustment</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="input">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="input">
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark flex-1">Filter</button>
            @if(request()->hasAny(['search','type','date_from','date_to']))
                <a href="{{ route('inventory.allMovements') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($movements->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Item</th>
                        <th>Category</th>
                        <th>Type</th>
                        <th>Quantity</th>
                        <th>By</th>
                        <th>Reference</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($movements as $movement)
                        <tr>
                            <td class="whitespace-nowrap text-zinc-500">{{ $movement->created_at->format('M d, Y') }}</td>
                            <td class="font-medium text-zinc-900">{{ $movement->item->displayName() }}</td>
                            <td>
                                @if($movement->item->category)
                                    <span class="badge badge-zinc">{{ $movement->item->category }}</span>
                                @else <span class="text-zinc-300">—</span> @endif
                            </td>
                            <td>
                                <span class="badge {{ $movement->type === 'stock_in' ? 'badge-green' : ($movement->type === 'stock_out' ? 'badge-red' : 'badge-amber') }}">{{ $movement->getTypeLabel() }}</span>
                            </td>
                            <td class="font-semibold text-zinc-900">{{ $movement->quantity }} <span class="text-xs font-normal text-zinc-400">{{ $movement->item->unit }}</span></td>
                            <td>
                                @if($movement->user)
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-zinc-200 text-[10px] font-bold text-zinc-600">{{ strtoupper(substr($movement->user->name, 0, 1)) }}</span>
                                        <span class="text-zinc-700">{{ $movement->user->name }}</span>
                                    </div>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="text-zinc-500">{{ $movement->reference ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $movements->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No movements found</p>
            <p class="mt-1 text-sm text-zinc-500">Try adjusting your filters.</p>
        </div>
    @endif
</div>

@endsection
