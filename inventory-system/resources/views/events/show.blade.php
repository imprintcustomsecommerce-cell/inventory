@extends('layouts.app')

@section('title', $event->name)

@section('content')

<a href="{{ route('events.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to events
</a>

<div class="mb-6 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">{{ $event->name }}</h1>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $event->location ?? 'No location' }}@if($event->event_date) · {{ $event->event_date->format('M d, Y') }}@endif
        </p>
    </div>
    <span class="badge badge-green self-start">Revenue ₱{{ number_format($revenue, 2) }}</span>
</div>

<div class="mb-4 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-900">
    To add stock here, open a product size in Inventory and use <strong>Transfer</strong> → choose “{{ $event->name }}”.
</div>

<div class="card overflow-hidden">
    <div class="border-b border-zinc-200 px-5 py-3"><h2 class="text-sm font-semibold text-zinc-900">Stock at this event</h2></div>
    @if($items->count() > 0)
        <table class="data-table">
            <thead><tr><th>Item</th><th>In stock</th><th class="text-right">Action</th></tr></thead>
            <tbody>
                @foreach($items as $it)
                    <tr>
                        <td class="font-medium text-zinc-900">{{ $it->name }}{{ $it->size ? " ({$it->size})" : '' }}</td>
                        <td class="text-zinc-700">{{ $it->current_stock }} {{ $it->unit }}</td>
                        <td class="text-right">
                            <a href="{{ route('sales.create', $it->id) }}" class="btn btn-primary btn-sm">Sell</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="px-5 py-10 text-center text-sm text-zinc-500">No stock here yet. Transfer items from Inventory to this event.</p>
    @endif
</div>

@endsection
