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
            @if($request->note) · “{{ $request->note }}”@endif
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
                        <option value="{{ $it->id }}">{{ $it->name }}{{ $it->size ? " ({$it->size})" : '' }} — {{ $it->current_stock }} in {{ $it->warehouse?->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full sm:w-32">
                <label class="label">Quantity</label>
                <input type="number" name="quantity" min="1" step="1" required class="input">
            </div>
            <button type="submit" class="btn btn-primary">Add</button>
        </form>
    @endif

    @if($request->items->count() > 0)
        <table class="data-table">
            <thead>
                <tr><th>Item</th><th class="text-right">In Stock</th><th class="text-right">Requested</th><th class="text-right">Fulfilled</th>@if($request->isPending() && !$canFulfill)<th class="text-right">Remove</th>@endif</tr>
            </thead>
            <tbody>
                @foreach($request->items as $line)
                    <tr>
                        <td class="font-medium text-zinc-900">{{ $line->item_label }}</td>
                        <td class="text-zinc-700">{{ rtrim(rtrim(number_format($line->quantity, 2), '0'), '.') }}</td>
                        <td class="text-zinc-500">{{ $line->fulfilled_quantity > 0 ? rtrim(rtrim(number_format($line->fulfilled_quantity, 2), '0'), '.') : '—' }}</td>
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
