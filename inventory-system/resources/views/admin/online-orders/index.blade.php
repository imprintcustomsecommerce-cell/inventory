@extends('shared.layouts.app')

@section('title', 'Online Orders')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Online Orders</h1>
        <p class="mt-1 text-sm text-zinc-500">Marketplace orders waiting to be routed into production or sales.</p>
    </div>
    <div class="flex gap-2">
        <form action="{{ route('online-orders.simulate') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary">Simulate order</button>
        </form>
        <a href="{{ route('channels.index') }}" class="btn btn-ghost">Channels</a>
    </div>
</div>

<!-- Stats -->
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <div class="card p-5">
        <p class="text-sm font-medium text-zinc-500">New (unrouted)</p>
        <p class="mt-2 text-3xl font-bold text-amber-600">{{ $stats['new'] }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-zinc-500">Routed</p>
        <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $stats['routed'] }}</p>
    </div>
    <div class="card p-5">
        <p class="text-sm font-medium text-zinc-500">Routed revenue</p>
        <p class="mt-2 text-3xl font-bold text-zinc-900">₱{{ number_format($stats['revenue'], 2) }}</p>
    </div>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('online-orders.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <select name="channel" class="select sm:w-48" onchange="this.form.submit()">
            <option value="">All channels</option>
            @foreach($channels as $c)
                <option value="{{ $c->id }}" {{ (string) request('channel') === (string) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
            @endforeach
        </select>
        <select name="status" class="select sm:w-44" onchange="this.form.submit()">
            <option value="New" {{ $status === 'New' ? 'selected' : '' }}>New</option>
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
            <option value="Routed" {{ $status === 'Routed' ? 'selected' : '' }}>Routed</option>
            <option value="Ignored" {{ $status === 'Ignored' ? 'selected' : '' }}>Ignored</option>
        </select>
        <button type="submit" class="btn btn-dark">Filter</button>
    </form>

    @if($orders->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Channel</th>
                        <th>Order #</th>
                        <th>Buyer</th>
                        <th>Item</th>
                        <th>Type</th>
                        <th class="text-right">Qty</th>
                        <th class="text-right">Amount</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td><span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-semibold {{ $order->channel->color() }}">{{ $order->channel->name }}</span></td>
                            <td class="text-zinc-500">{{ $order->external_ref }}</td>
                            <td class="font-medium text-zinc-900">
                                {{ $order->buyer_name }}
                                @if($order->buyer_contact)<div class="text-xs text-zinc-400">{{ $order->buyer_contact }}</div>@endif
                            </td>
                            <td class="text-zinc-700">{{ $order->item_label }}</td>
                            <td>
                                <span class="badge {{ $order->order_type === 'custom' ? 'badge-amber' : 'badge-zinc' }}">{{ ucfirst($order->order_type) }}</span>
                            </td>
                            <td class="text-right text-zinc-700">{{ $order->quantity }}</td>
                            <td class="text-right font-medium text-zinc-900">₱{{ number_format($order->amount, 2) }}</td>
                            <td>
                                <span class="badge {{ $order->getStatusBadgeClass() }}">{{ $order->status }}</span>
                                @if($order->status === 'Routed' && $order->routedUrl())
                                    <a href="{{ $order->routedUrl() }}" class="block text-xs text-brand-600 underline-offset-2 hover:underline">view {{ $order->routed_type }}</a>
                                @endif
                            </td>
                            <td class="text-right">
                                @if($order->isNew())
                                    <div class="flex items-center justify-end gap-1">
                                        <form action="{{ route('online-orders.route', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-primary btn-sm" title="Create {{ $order->order_type === 'custom' ? 'project' : 'sale' }}">
                                                {{ $order->order_type === 'custom' ? 'To project' : 'To sale' }}
                                            </button>
                                        </form>
                                        <form action="{{ route('online-orders.ignore', $order) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-ghost btn-sm">Ignore</button>
                                        </form>
                                    </div>
                                @else
                                    <form action="{{ route('online-orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Remove this order?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="rounded-lg p-2 text-zinc-400 transition hover:bg-red-50 hover:text-red-600" title="Remove">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $orders->links() }}</div>
        @endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No online orders</p>
            <p class="mt-1 text-sm text-zinc-500">Connect a channel and hit Sync, or use “Simulate order” to try the flow.</p>
        </div>
    @endif
</div>

@endsection
