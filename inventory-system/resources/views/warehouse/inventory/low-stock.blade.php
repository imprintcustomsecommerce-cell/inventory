@extends('shared.layouts.app')

@section('title', 'Low Stock')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Low stock</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $items->total() }} item{{ $items->total() === 1 ? '' : 's' }} at or below the minimum level.</p>
    </div>
    @if(!auth()->user()->canCreateItems() && auth()->user()->warehouse_id && $items->total() > 0)
        <form action="{{ route('requests.restockLow') }}" method="POST" onsubmit="return confirm('Create a stock request for all low items?');">
            @csrf
            <button type="submit" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h9.75m4.5-4.5v12m0 0l-3.75-3.75M17.25 21L21 17.25"/></svg>
                Request all
            </button>
        </form>
    @endif
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('inventory.lowStock') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items…" class="input pl-9">
        </div>
        <select name="status" class="select sm:w-48">
            <option value="">All low stock</option>
            <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low stock only</option>
            <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Out of stock only</option>
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('inventory.lowStock') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($items->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Size</th>
                        <th>Your Stock</th>
                        <th>Minimum</th>
                        @if(!auth()->user()->canCreateItems() && auth()->user()->warehouse_id)
                            <th>Available in Warehouse</th>
                        @endif
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        @php
                            $key = $item->name . '|' . ($item->size ?? '');
                            $stockroomItem = isset($warehouseStock) ? $warehouseStock->get($key) : null;
                            $suggestedQty = max(1, (int) ceil((float) $item->minimum_stock - (float) $item->current_stock));
                        @endphp
                        <tr>
                            <td class="font-medium text-zinc-900">
                                {{ $item->name }}
                                @if($item->category)<div class="text-xs text-zinc-400">{{ $item->category }}</div>@endif
                            </td>
                            <td>
                                @if($item->size)<span class="badge badge-zinc">{{ $item->size }}</span>@else <span class="text-zinc-300">—</span> @endif
                            </td>
                            <td>
                                <span class="font-semibold {{ $item->current_stock <= 0 ? 'text-red-600' : 'text-zinc-900' }}">{{ $item->current_stock }}</span>
                                <span class="text-xs text-zinc-400">{{ $item->unit }}</span>
                            </td>
                            <td class="text-zinc-500">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                            @if(!auth()->user()->canCreateItems() && auth()->user()->warehouse_id)
                                <td>
                                    @if($stockroomItem)
                                        <span class="font-semibold {{ $stockroomItem->current_stock > 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ $stockroomItem->current_stock }}</span>
                                        <span class="text-xs text-zinc-400">{{ $stockroomItem->unit }}</span>
                                        <div class="text-xs text-zinc-400">{{ $stockroomItem->warehouse?->name }}</div>
                                    @else
                                        <span class="text-xs text-zinc-400">Not in warehouse</span>
                                    @endif
                                </td>
                            @endif
                            <td>
                                <span class="badge {{ $item->isOutOfStock() ? 'badge-red' : 'badge-amber' }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $item->isOutOfStock() ? 'bg-red-500' : 'bg-amber-500' }}"></span>
                                    {{ $item->getStatusLabel() }}
                                </span>
                            </td>
                            <td class="text-right">
                                @if(auth()->user()->canCreateItems() && !auth()->user()->isAdmin())
                                    <a href="{{ route('inventory.stockInForm', $item->id) }}" class="btn btn-primary btn-sm">
                                        Restock
                                    </a>
                                @elseif(!auth()->user()->isAdmin() && auth()->user()->warehouse_id)
                                    <form action="{{ route('requests.restockItem', $item->id) }}" method="POST" class="inline-flex items-center gap-2">
                                        @csrf
                                        <input type="number" name="quantity" min="1" step="1" value="{{ $suggestedQty }}" required
                                            class="input w-20 py-1.5 text-center text-sm" title="How many do you need?">
                                        <button type="submit" class="btn btn-primary btn-sm whitespace-nowrap">
                                            Request
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $items->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50">
                <svg class="h-6 w-6 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">All stocked up</p>
            <p class="mt-1 text-sm text-zinc-500">No items are below their minimum level.</p>
        </div>
    @endif
</div>

@endsection
