@extends('shared.layouts.app')

@section('title', 'Request #' . str_pad($request->id, 4, '0', STR_PAD_LEFT))

@section('content')

<a href="{{ route('requests.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to requests
</a>

<div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Request #{{ str_pad($request->id, 4, '0', STR_PAD_LEFT) }}</h1>
            <span class="badge {{ $request->getStatusBadgeClass() }}">{{ ucfirst($request->status) }}</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            For <strong>{{ $request->warehouse?->name }}</strong> · by {{ $request->requestedBy?->name ?? '—' }} · {{ $request->created_at->format('M d, Y') }}
            @if($request->note) · "{{ $request->note }}"@endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($request->isPending() && $canFulfill)
            <form action="{{ route('requests.fulfill', $request) }}" method="POST" onsubmit="return confirm('Fulfill this request and transfer the stock to {{ $request->warehouse?->name }}?');">
                @csrf
                <button class="btn btn-primary">Fulfill &amp; transfer</button>
            </form>
            <form action="{{ route('requests.reject', $request) }}" method="POST">
                @csrf
                <button class="btn btn-ghost">Reject</button>
            </form>
        @elseif($request->isPending())
            <form action="{{ route('requests.cancel', $request) }}" method="POST" onsubmit="return confirm('Cancel this request?');">
                @csrf
                <button class="btn btn-ghost">Cancel request</button>
            </form>
        @endif
    </div>
</div>

{{-- Summary cards for warehouse users --}}
@if($canFulfill && $request->items->count() > 0)
    @php
        $totalRequested = $request->items->sum('quantity');
        $totalAvailable = $request->items->sum(fn ($line) => $line->inventoryItem ? $line->inventoryItem->current_stock : 0);
        $shortItems = $request->items->filter(fn ($line) => $line->inventoryItem && $line->inventoryItem->current_stock < $line->quantity)->count();
    @endphp
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Total Requested</p>
            <p class="mt-1 text-2xl font-bold text-zinc-900">{{ rtrim(rtrim(number_format($totalRequested, 2), '0'), '.') }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">across {{ $request->items->count() }} item(s)</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Available in Warehouse</p>
            <p class="mt-1 text-2xl font-bold {{ $totalAvailable >= $totalRequested ? 'text-emerald-600' : 'text-amber-600' }}">{{ rtrim(rtrim(number_format($totalAvailable, 2), '0'), '.') }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">total stock on hand</p>
        </div>
        <div class="rounded-xl border {{ $shortItems > 0 ? 'border-red-200 bg-red-50' : 'border-emerald-200 bg-emerald-50' }} p-4">
            <p class="text-xs font-medium uppercase tracking-wider text-zinc-500">Shortage</p>
            <p class="mt-1 text-2xl font-bold {{ $shortItems > 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ $shortItems }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">{{ $shortItems > 0 ? 'item(s) insufficient stock' : 'all items can be fulfilled' }}</p>
        </div>
    </div>
@endif

<div class="card overflow-hidden">
    <div class="border-b border-zinc-200 px-5 py-3"><h2 class="text-sm font-semibold text-zinc-900">Requested items</h2></div>

    @if($request->isPending() && !$canFulfill)
        <form action="{{ route('requests.items.add', $request) }}" method="POST" class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 p-4 sm:flex-row sm:items-end">
            @csrf
            <div class="flex-1">
                <label class="label">Item (from Inventory)</label>
                <select name="inventory_item_id" required class="select">
                    <option value="">Select item…</option>
                    @foreach($available as $it)
                        <option value="{{ $it->id }}">{{ $it->name }}{{ $it->size ? " ({$it->size})" : '' }} — {{ $it->current_stock }} available in {{ $it->warehouse?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-32">
                <label class="label">Quantity</label>
                <input type="number" name="quantity" min="1" step="1" required class="input" placeholder="How many?">
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    @endif

    @if($request->items->count() > 0)
        <table class="data-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Requested Qty</th>
                    <th class="text-right">In Warehouse</th>
                    <th class="text-center">Can Fulfill?</th>
                    <th class="text-right">Fulfilled</th>
                    @if($request->isPending() && !$canFulfill)<th class="text-right">Remove</th>@endif
                </tr>
            </thead>
            <tbody>
                @foreach($request->items as $line)
                    @php
                        $available_stock = $line->inventoryItem ? $line->inventoryItem->current_stock : 0;
                        $canCover = $available_stock >= $line->quantity;
                    @endphp
                    <tr class="{{ !$canCover && $request->isPending() ? 'bg-red-50/50' : '' }}">
                        <td>
                            <span class="font-medium text-zinc-900">{{ $line->item_label }}</span>
                            @if($line->inventoryItem)
                                <div class="text-xs text-zinc-400">from {{ $line->inventoryItem->warehouse?->name }}</div>
                            @endif
                        </td>
                        <td class="text-right">
                            <span class="text-lg font-bold text-zinc-900">{{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }}</span>
                        </td>
                        <td class="text-right">
                            @if($line->inventoryItem)
                                <span class="text-lg font-semibold {{ $canCover ? 'text-emerald-600' : 'text-red-600' }}">{{ rtrim(rtrim(number_format($available_stock, 2), '0'), '.') }}</span>
                                <div class="text-xs text-zinc-400">{{ $line->inventoryItem->unit ?? '' }}</div>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($line->inventoryItem)
                                @if($canCover)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        Yes
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-1 text-xs font-medium text-red-700">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Short {{ rtrim(rtrim(number_format($line->quantity - $available_stock, 2), '0'), '.') }}
                                    </span>
                                @endif
                            @else
                                <span class="text-xs text-zinc-400">N/A</span>
                            @endif
                        </td>
                        <td class="text-right text-zinc-500">{{ $line->fulfilled_quantity > 0 ? rtrim(rtrim(number_format($line->fulfilled_quantity, 2), '0'), '.') : '—' }}</td>
                        @if($request->isPending() && !$canFulfill)
                            <td class="text-right">
                                <form action="{{ route('requests.items.remove', [$request, $line]) }}" method="POST" onsubmit="return confirm('Remove this item?');">
                                    @csrf @method('DELETE')
                                    <button class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="px-5 py-10 text-center text-sm text-zinc-500">No items yet.@if($request->isPending() && !$canFulfill) Add what you need above.@endif</p>
    @endif
</div>

@endsection
