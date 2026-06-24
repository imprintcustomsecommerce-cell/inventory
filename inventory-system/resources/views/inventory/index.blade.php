@extends('layouts.app')

@section('title', 'Inventory')

@section('content')

<!-- Page header -->
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Inventory</h1>
        <p class="mt-1 text-sm text-zinc-500">Track materials, supplies, and stock levels.</p>
    </div>
    <div class="flex gap-2">
        @if(auth()->user()->isAdmin())
            <a href="{{ route('inventory.trash') }}" class="btn btn-ghost">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                Trash
            </a>
            <a href="{{ route('inventory.importForm') }}" class="btn btn-ghost">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Import
            </a>
        @endif
        <a href="{{ route('inventory.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        <a href="{{ route('inventory.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add Item
        </a>
    </div>
</div>

<!-- Stats -->
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $cards = [
            ['label' => 'Total Items', 'value' => $stats['total_items'], 'accent' => 'text-zinc-900', 'ring' => 'bg-zinc-100 text-zinc-600', 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z'],
            ['label' => 'Low Stock', 'value' => $stats['low_stock_items'], 'accent' => 'text-amber-600', 'ring' => 'bg-amber-50 text-amber-600', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'],
            ['label' => 'Out of Stock', 'value' => $stats['out_of_stock_items'], 'accent' => 'text-red-600', 'ring' => 'bg-red-50 text-red-600', 'icon' => 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'],
            ['label' => 'Movements', 'value' => $stats['total_movements'], 'accent' => 'text-emerald-600', 'ring' => 'bg-emerald-50 text-emerald-600', 'icon' => 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75z'],
        ];
    @endphp
    @foreach($cards as $c)
        <div class="card p-5">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium text-zinc-500">{{ $c['label'] }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $c['ring'] }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $c['icon'] }}"/></svg>
                </span>
            </div>
            <p class="mt-3 text-3xl font-bold {{ $c['accent'] }}">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<!-- Table card -->
<div class="card overflow-hidden">
    <!-- Filters -->
    <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search items, categories, remarks…" class="input pl-9">
        </div>
        <select name="category" class="select sm:w-44">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @if($warehouses->isNotEmpty())
            <select name="warehouse" class="select sm:w-44">
                <option value="">All warehouses</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ request('warehouse') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Search</button>
            @if(request('search') || request('category') || request('warehouse'))
                <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($items->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        @if(auth()->user()->isAdmin())<th>Warehouse</th>@endif
                        <th>Category</th>
                        <th>Size</th>
                        <th>Stock</th>
                        <th>Minimum</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($items as $item)
                        <tr>
                            <td>
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50">
                                        @if($item->imageUrl())
                                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                                        @else
                                            <svg class="h-5 w-5 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900">{{ $item->name }}</div>
                                        @if($item->remarks)
                                            <div class="text-xs text-zinc-400">{{ \Illuminate\Support\Str::limit($item->remarks, 40) }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            @if(auth()->user()->isAdmin())
                                <td><span class="badge badge-zinc">{{ $item->warehouse?->name ?? '—' }}</span></td>
                            @endif
                            <td>
                                @if($item->category)
                                    <span class="badge badge-zinc">{{ $item->category }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td>
                                @if($item->size)
                                    <span class="badge badge-zinc">{{ $item->size }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-semibold text-zinc-900">{{ $item->current_stock }}</span>
                                <span class="text-xs text-zinc-400">{{ $item->unit }}</span>
                            </td>
                            <td class="text-zinc-500">{{ $item->minimum_stock }} {{ $item->unit }}</td>
                            <td>
                                <span class="badge {{ $item->isOutOfStock() ? 'badge-red' : ($item->isLowStock() ? 'badge-amber' : 'badge-green') }}">
                                    <span class="h-1.5 w-1.5 rounded-full {{ $item->isOutOfStock() ? 'bg-red-500' : ($item->isLowStock() ? 'bg-amber-500' : 'bg-emerald-500') }}"></span>
                                    {{ $item->getStatusLabel() }}
                                </span>
                            </td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    @php
                                        $actions = [
                                            ['route' => route('inventory.stockInForm', $item->id), 'label' => 'Stock in', 'hover' => 'hover:bg-emerald-50 hover:text-emerald-600', 'icon' => 'M12 4.5v15m7.5-7.5h-15'],
                                            ['route' => route('inventory.stockOutForm', $item->id), 'label' => 'Stock out', 'hover' => 'hover:bg-red-50 hover:text-red-600', 'icon' => 'M19.5 12h-15'],
                                            ['route' => route('inventory.adjustForm', $item->id), 'label' => 'Adjust', 'hover' => 'hover:bg-amber-50 hover:text-amber-600', 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a6.759 6.759 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281z'],
                                            ['route' => route('inventory.transferForm', $item->id), 'label' => 'Transfer', 'hover' => 'hover:bg-blue-50 hover:text-blue-600', 'icon' => 'M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5'],
                                            ['route' => route('inventory.movements', $item->id), 'label' => 'History', 'hover' => 'hover:bg-blue-50 hover:text-blue-600', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                                            ['route' => route('inventory.edit', $item->id), 'label' => 'Edit', 'hover' => 'hover:bg-zinc-100 hover:text-zinc-900', 'icon' => 'M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z'],
                                        ];
                                    @endphp
                                    @foreach($actions as $a)
                                        <a href="{{ $a['route'] }}" title="{{ $a['label'] }}" class="rounded-lg p-2 text-zinc-400 transition {{ $a['hover'] }}">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $a['icon'] }}"/></svg>
                                        </a>
                                    @endforeach
                                </div>
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
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0a48.667 48.667 0 00-16.5 0"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No items found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') || request('category') ? 'Try adjusting your filters.' : 'Get started by adding your first inventory item.' }}</p>
            <a href="{{ route('inventory.create') }}" class="btn btn-primary mt-4">Add Item</a>
        </div>
    @endif
</div>

@endsection
