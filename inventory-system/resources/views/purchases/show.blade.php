@extends('layouts.app')

@section('title', $purchase->po_number)

@section('content')

@php
    $received = (float) $purchase->items->sum('quantity_received');
    $ordered = (float) $purchase->items->sum('quantity_ordered');
@endphp

<a href="{{ route('purchases.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to purchase orders
</a>

<!-- Header -->
<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $purchase->po_number }}</h1>
            <span class="badge {{ $purchase->getStatusBadgeClass() }}">{{ $purchase->status }}</span>
            @if($purchase->isOverdue())<span class="badge badge-red">Overdue</span>@endif
        </div>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $purchase->supplier?->name ?? 'No supplier' }}@if($purchase->warehouse) · to {{ $purchase->warehouse->name }}@endif
        </p>
    </div>
    <div class="flex flex-wrap gap-2">
        @if($purchase->status === 'Draft')
            <form action="{{ route('purchases.markOrdered', $purchase) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-dark">Mark as placed</button>
            </form>
        @endif
        @if($purchase->canReceive() && $received < $ordered)
            <form action="{{ route('purchases.receiveAll', $purchase) }}" method="POST"
                  onsubmit="return confirm('Receive all outstanding items into stock?');">
                @csrf
                <button type="submit" class="btn btn-primary">Receive all</button>
            </form>
        @endif
        @if(!in_array($purchase->status, ['Received', 'Cancelled']))
            <form action="{{ route('purchases.cancel', $purchase) }}" method="POST" onsubmit="return confirm('Cancel this purchase order?');">
                @csrf
                <button type="submit" class="btn btn-ghost text-red-600 hover:bg-red-50">Cancel</button>
            </form>
        @endif
        <a href="{{ route('purchases.pdf', $purchase) }}" target="_blank" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
            PDF
        </a>
        @if($purchase->isEditable())
            <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-ghost">Edit</a>
        @endif
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Details -->
    <div class="space-y-6 lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Details</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Supplier</dt>
                    <dd class="text-right text-zinc-900">
                        @if($purchase->supplier)
                            <a href="{{ route('suppliers.show', $purchase->supplier) }}" class="underline-offset-2 hover:underline">{{ $purchase->supplier->name }}</a>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Deliver to</dt>
                    <dd class="text-zinc-900">{{ $purchase->warehouse?->name ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Order date</dt>
                    <dd class="text-zinc-900">{{ $purchase->order_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Expected</dt>
                    <dd class="{{ $purchase->isOverdue() ? 'font-medium text-red-600' : 'text-zinc-900' }}">{{ $purchase->expected_date?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4 border-t border-zinc-100 pt-3">
                    <dt class="text-zinc-500">Order total</dt>
                    <dd class="text-lg font-bold text-zinc-900">₱{{ number_format($purchase->total, 2) }}</dd>
                </div>
            </dl>
            @if($purchase->notes)
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">Notes</p>
                    <p class="mt-1 text-sm text-zinc-700">{{ $purchase->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Items -->
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-900">Items</h2>
                    <p class="text-xs text-zinc-500">Linked materials are restocked when received.</p>
                </div>
                @unless($purchase->isEditable())<span class="badge {{ $purchase->getStatusBadgeClass() }}">{{ $purchase->status }}</span>@endunless
            </div>

            @if($purchase->isEditable())
                <form action="{{ route('purchases.items.add', $purchase) }}" method="POST" class="flex flex-col gap-3 border-b border-zinc-200 bg-zinc-50 p-4">
                    @csrf
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="flex-1">
                            <label class="label">Material <span class="font-normal text-zinc-400">(optional)</span></label>
                            <select name="material_id" class="select" onchange="const o=this.selectedOptions[0]; const f=this.form; if(o.dataset.name){f.description.value=o.dataset.name;} if(o.dataset.cost && o.dataset.cost!=='0.00'){f.unit_cost.value=o.dataset.cost;}">
                                <option value="">One-off (no material link)</option>
                                @foreach($materials as $m)
                                    <option value="{{ $m->id }}" data-name="{{ $m->name }}" data-cost="{{ number_format((float) $m->unit_cost, 2, '.', '') }}">{{ $m->name }} ({{ $m->current_stock }} {{ $m->unit }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1">
                            <label class="label">Description</label>
                            <input type="text" name="description" required placeholder="What you're ordering" class="input">
                        </div>
                    </div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                        <div class="w-full sm:w-28">
                            <label class="label">Qty</label>
                            <input type="number" name="quantity_ordered" min="1" step="1" value="1" required class="input">
                        </div>
                        <div class="w-full sm:w-36">
                            <label class="label">Unit cost</label>
                            <input type="number" name="unit_cost" min="0" step="0.01" required class="input">
                        </div>
                        <button type="submit" class="btn btn-primary">Add item</button>
                    </div>
                </form>
            @endif

            @if($purchase->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th class="text-right">Ordered</th>
                                <th class="text-right">Received</th>
                                <th class="text-right">Unit cost</th>
                                <th class="text-right">Total</th>
                                @if($purchase->canReceive())<th>Receive</th>@endif
                                @if($purchase->isEditable())<th class="text-right">Remove</th>@endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->items as $item)
                                <tr>
                                    <td class="font-medium text-zinc-900">
                                        {{ $item->description }}
                                        @if($item->material)<div class="text-xs text-zinc-400">↳ {{ $item->material->name }}</div>@endif
                                    </td>
                                    <td class="text-right text-zinc-700">{{ rtrim(rtrim(number_format($item->quantity_ordered, 2), '0'), '.') }}</td>
                                    <td class="text-right">
                                        @if($item->isFullyReceived())
                                            <span class="badge badge-green">{{ rtrim(rtrim(number_format($item->quantity_received, 2), '0'), '.') }}</span>
                                        @else
                                            <span class="text-zinc-700">{{ rtrim(rtrim(number_format($item->quantity_received, 2), '0'), '.') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-right text-zinc-700">₱{{ number_format($item->unit_cost, 2) }}</td>
                                    <td class="text-right font-medium text-zinc-900">₱{{ number_format($item->total, 2) }}</td>
                                    @if($purchase->canReceive())
                                        <td>
                                            @if($item->outstanding() > 0)
                                                <form action="{{ route('purchases.items.receive', [$purchase, $item]) }}" method="POST" class="flex items-center gap-2">
                                                    @csrf
                                                    <input type="number" name="quantity" min="0.01" step="1" value="{{ rtrim(rtrim(number_format($item->outstanding(), 2), '0'), '.') }}" class="input h-9 w-20 py-1">
                                                    <button type="submit" class="btn btn-dark btn-sm">Receive</button>
                                                </form>
                                            @else
                                                <span class="text-xs text-emerald-600">Complete</span>
                                            @endif
                                        </td>
                                    @endif
                                    @if($purchase->isEditable())
                                        <td class="text-right">
                                            <form action="{{ route('purchases.items.remove', [$purchase, $item]) }}" method="POST" onsubmit="return confirm('Remove this item?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 py-12 text-center text-sm text-zinc-500">No items yet. Add what you're ordering above.</div>
            @endif
        </div>
    </div>
</div>

@endsection
