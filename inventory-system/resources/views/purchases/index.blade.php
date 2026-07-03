@extends('layouts.app')

@section('title', 'Purchase Orders')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Purchase orders</h1>
        <p class="mt-1 text-sm text-zinc-500">Order materials from suppliers and receive them into stock.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('purchases.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Order
        </a>
    </div>
</div>

<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $cards = [
            ['label' => 'Total', 'value' => $stats['total'], 'accent' => 'text-zinc-900'],
            ['label' => 'Open', 'value' => $stats['open'], 'accent' => 'text-amber-600'],
            ['label' => 'Overdue', 'value' => $stats['overdue'], 'accent' => 'text-red-600'],
            ['label' => 'Open value', 'value' => '₱' . number_format($stats['open_value'], 0), 'accent' => 'text-zinc-900'],
        ];
    @endphp
    @foreach($cards as $i => $c)
        @php $cp = \App\Support\CardPalette::at($i); @endphp
        <div class="rounded-xl p-5 shadow-sm transition hover:shadow-md {{ $cp['bg'] }}">
            <p class="text-sm font-medium {{ $cp['label'] }}">{{ $c['label'] }}</p>
            <p class="mt-2 text-3xl font-bold {{ $cp['value'] }}">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO number or supplier…" class="input pl-9">
        </div>
        <select name="status" class="select sm:w-52">
            <option value="">All statuses</option>
            @foreach(\App\Models\PurchaseOrder::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('purchases.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>PO #</th>
                        <th>Supplier</th>
                        <th>Status</th>
                        <th>Expected</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $order->po_number }}</td>
                            <td class="text-zinc-500">{{ $order->supplier?->name ?? '—' }}</td>
                            <td><span class="badge {{ $order->getStatusBadgeClass() }}">{{ $order->status }}</span></td>
                            <td>
                                @if($order->expected_date)
                                    <span class="{{ $order->isOverdue() ? 'font-medium text-red-600' : 'text-zinc-500' }}">{{ $order->expected_date->format('M d, Y') }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="text-right text-zinc-700">₱{{ number_format($order->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('purchases.show', $order) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $orders->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 10-7.5 0v4.5m11.356-1.993l1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 01-1.12-1.243l1.264-12A1.125 1.125 0 015.513 7.5h12.974c.576 0 1.059.435 1.119 1.007zM8.625 10.5a.375.375 0 11-.75 0 .375.375 0 01.75 0zm7.5 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No purchase orders found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') || request('status') ? 'Try adjusting your filters.' : 'Create your first order to restock materials.' }}</p>
            <a href="{{ route('purchases.create') }}" class="btn btn-primary mt-4">New Order</a>
        </div>
    @endif
</div>

@endsection
