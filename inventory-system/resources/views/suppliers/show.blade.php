@extends('layouts.app')

@section('title', $supplier->name)

@section('content')

<a href="{{ route('suppliers.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to suppliers
</a>

<div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $supplier->name }}</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $supplier->contact_person ?? 'No contact person' }}</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-primary">New purchase order</a>
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-ghost">Edit</a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <div class="space-y-6 lg:col-span-1">
        <div class="card p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Contact</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Email</dt>
                    <dd class="text-zinc-900">{{ $supplier->email ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Phone</dt>
                    <dd class="text-zinc-900">{{ $supplier->phone ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-zinc-500">Lead time</dt>
                    <dd class="text-zinc-900">{{ $supplier->lead_time ?? '—' }}</dd>
                </div>
                <div class="border-t border-zinc-100 pt-3">
                    <dt class="text-zinc-500">Address</dt>
                    <dd class="mt-1 text-zinc-900">{{ $supplier->address ?? '—' }}</dd>
                </div>
            </dl>
            @if($supplier->notes)
                <div class="mt-4 border-t border-zinc-100 pt-4">
                    <p class="text-sm text-zinc-500">Notes</p>
                    <p class="mt-1 text-sm text-zinc-700">{{ $supplier->notes }}</p>
                </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4">
                <h2 class="text-sm font-semibold text-zinc-900">Purchase orders</h2>
                <a href="{{ route('purchases.create', ['supplier_id' => $supplier->id]) }}" class="btn btn-ghost btn-sm">New order</a>
            </div>
            @if($supplier->purchaseOrders->count() > 0)
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>PO #</th>
                            <th>Status</th>
                            <th>Ordered</th>
                            <th class="text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($supplier->purchaseOrders as $order)
                            <tr class="cursor-pointer hover:bg-zinc-50" onclick="window.location='{{ route('purchases.show', $order) }}'">
                                <td class="font-medium text-zinc-900">{{ $order->po_number }}</td>
                                <td><span class="badge {{ $order->getStatusBadgeClass() }}">{{ $order->status }}</span></td>
                                <td class="text-zinc-500">{{ $order->order_date?->format('M d, Y') }}</td>
                                <td class="text-right text-zinc-700">₱{{ number_format($order->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="px-6 py-10 text-center text-sm text-zinc-500">No purchase orders yet.</p>
            @endif
        </div>
    </div>
</div>

@endsection
