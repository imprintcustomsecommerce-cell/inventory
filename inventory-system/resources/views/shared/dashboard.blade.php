@extends('shared.layouts.app')

@section('title', 'Dashboard')

@section('content')

@php
    $canSell = auth()->user()->canSell();

    $newOrders = $operations['new_orders'] ?? 0;
    $inTransit = $operations['in_transit'] ?? 0;
    $openIssues = $operations['open_issues'] ?? 0;
@endphp

<!-- Page Header -->
<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
            Hello, {{ auth()->user()->name }}!
        </h1>
        <p class="mt-1 text-sm text-zinc-500">
            Welcome back, {{ explode(' ', auth()->user()->name)[0] }}. Here’s today’s inventory overview.
        </p>
    </div>

    <div class="flex items-center gap-2 rounded-xl border border-zinc-200 bg-white px-4 py-2 text-sm text-zinc-600 shadow-sm">
        <svg class="h-4 w-4 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/>
        </svg>
        {{ now()->format('D, M d, Y') }}
    </div>
</div>

<!-- Summary Cards -->
<div class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-3">
    <a href="{{ route('online-orders.index') }}" class="card p-5 transition hover:border-brand-300 hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500">New Online Orders</p>
                <p class="mt-2 text-3xl font-semibold {{ $newOrders > 0 ? 'text-yellow-600' : 'text-zinc-950' }}">
                    {{ $newOrders }}
                </p>
            </div>
            <div class="rounded-xl bg-yellow-50 px-2.5 py-1 text-xs font-semibold text-yellow-700">
                Orders
            </div>
        </div>
        <p class="mt-4 text-xs text-zinc-400">Awaiting routing</p>
    </a>

    <a href="{{ route('deliveries.index') }}" class="card p-5 transition hover:border-brand-300 hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500">Deliveries in Transit</p>
                <p class="mt-2 text-3xl font-semibold text-zinc-950">
                    {{ $inTransit }}
                </p>
            </div>
            <div class="rounded-xl bg-zinc-100 px-2.5 py-1 text-xs font-semibold text-zinc-600">
                Delivery
            </div>
        </div>
        <p class="mt-4 text-xs text-zinc-400">Scheduled or out</p>
    </a>

    <a href="{{ route('quality.index') }}" class="card p-5 transition hover:border-brand-300 hover:shadow-md">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-zinc-500">Open QC Issues</p>
                <p class="mt-2 text-3xl font-semibold {{ $openIssues > 0 ? 'text-red-600' : 'text-zinc-950' }}">
                    {{ $openIssues }}
                </p>
            </div>
            <div class="rounded-xl {{ $openIssues > 0 ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700' }} px-2.5 py-1 text-xs font-semibold">
                QC
            </div>
        </div>
        <p class="mt-4 text-xs text-zinc-400">Defects / reprints</p>
    </a>
</div>

<!-- Alerts -->
@if(!empty($alerts))
    <div class="mb-8 overflow-hidden card border-amber-200">
        <div class="border-b border-amber-100 bg-amber-50 px-5 py-4">
            <h2 class="text-sm font-semibold text-zinc-950">Needs Attention</h2>
            <p class="mt-0.5 text-xs text-zinc-500">Items that require immediate review.</p>
        </div>

        <div class="divide-y divide-zinc-100">
            @foreach($alerts as $alert)
                <a href="{{ $alert['route'] }}" class="flex items-center gap-4 px-5 py-4 transition hover:bg-zinc-50">
                    <span class="h-2.5 w-2.5 rounded-full {{ $alert['tone'] === 'red' ? 'bg-red-500' : 'bg-amber-400' }}"></span>

                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-zinc-950">
                            {{ $alert['label'] }}
                        </p>
                        <p class="truncate text-xs text-zinc-500">
                            {{ $alert['detail'] }}
                        </p>
                    </div>

                    <svg class="h-4 w-4 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
                    </svg>
                </a>
            @endforeach
        </div>
    </div>
@endif

<!-- Main Grid -->
<div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

    <!-- Low Stock -->
    <div class="overflow-hidden card">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-zinc-950">Needs Restocking</h2>
                <p class="mt-0.5 text-xs text-zinc-500">Items below minimum stock level.</p>
            </div>

            <a href="{{ route('inventory.lowStock') }}" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">
                View all
            </a>
        </div>

        @if($lowStockItems->count() > 0)
            <div class="divide-y divide-zinc-100">
                @foreach($lowStockItems as $item)
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-zinc-950">
                                {{ $item->displayName() }}
                            </p>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                {{ $item->current_stock }} / {{ $item->minimum_stock }} {{ $item->unit }}
                            </p>
                        </div>

                        <span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $item->isOutOfStock() ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $item->getStatusLabel() }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-zinc-500">Everything is above minimum level.</p>
            </div>
        @endif
    </div>

    <!-- Upcoming Projects -->
    <div class="overflow-hidden card">
        <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
            <div>
                <h2 class="text-sm font-semibold text-zinc-950">Upcoming Deadlines</h2>
                <p class="mt-0.5 text-xs text-zinc-500">Projects with approaching due dates.</p>
            </div>

            <a href="{{ route('projects.index') }}" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">
                View all
            </a>
        </div>

        @if($upcomingProjects->count() > 0)
            <div class="divide-y divide-zinc-100">
                @foreach($upcomingProjects as $project)
                    <div class="flex items-center gap-4 px-5 py-4">
                        <div class="min-w-0 flex-1">
                            <a href="{{ route('projects.show', $project) }}" class="truncate text-sm font-medium text-zinc-950 hover:text-yellow-600">
                                {{ $project->project_name }}
                            </a>
                            <p class="mt-0.5 text-xs text-zinc-500">
                                {{ $project->customer_name ?? 'No customer' }}
                            </p>
                        </div>

                        <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600">
                            {{ $project->status }}
                        </span>

                        <span class="whitespace-nowrap text-sm {{ $project->isOverdue() ? 'font-medium text-red-600' : 'text-zinc-500' }}">
                            {{ $project->due_date->format('M d') }}
                        </span>
                    </div>
                @endforeach
            </div>
        @else
            <div class="px-5 py-12 text-center">
                <p class="text-sm text-zinc-500">No scheduled projects.</p>
            </div>
        @endif
    </div>
</div>

<!-- Recent Sales -->
@if($canSell)
<div class="mt-6 overflow-hidden card">
    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
        <div>
            <h2 class="text-sm font-semibold text-zinc-950">Recent Sales</h2>
            <p class="mt-0.5 text-xs text-zinc-500">Latest sales transactions.</p>
        </div>

        <a href="{{ route('sales.index') }}" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">
            View all
        </a>
    </div>

    @if($recentSales->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-zinc-100">
                    @foreach($recentSales as $sale)
                        <tr class="hover:bg-zinc-50">
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-400">
                                {{ $sale->created_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-4 font-medium text-zinc-950">
                                {{ $sale->item_label }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600">
                                    {{ $sale->warehouse?->name ?? '—' }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-zinc-500">
                                {{ $sale->quantity }} ×
                            </td>
                            <td class="px-5 py-4 font-semibold text-emerald-600">
                                ₱{{ number_format($sale->total, 2) }}
                            </td>
                            <td class="px-5 py-4 text-right text-zinc-500">
                                {{ $sale->user?->name ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-5 py-12 text-center">
            <p class="text-sm text-zinc-500">No sales yet.</p>
        </div>
    @endif
</div>
@endif

<!-- Recent Quotes -->
<div class="mt-6 overflow-hidden card">
    <div class="flex items-center justify-between border-b border-zinc-100 px-5 py-4">
        <div>
            <h2 class="text-sm font-semibold text-zinc-950">Recent Quotes</h2>
            <p class="mt-0.5 text-xs text-zinc-500">Latest quote activity.</p>
        </div>

        <a href="{{ route('quotes.index') }}" class="text-xs font-medium text-yellow-600 hover:text-yellow-700">
            View all
        </a>
    </div>

    @if($recentQuotes->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <tbody class="divide-y divide-zinc-100">
                    @foreach($recentQuotes as $quote)
                        <tr class="cursor-pointer hover:bg-zinc-50" onclick="window.location='{{ route('quotes.show', $quote) }}'">
                            <td class="whitespace-nowrap px-5 py-4 text-zinc-400">
                                {{ $quote->created_at->diffForHumans() }}
                            </td>
                            <td class="px-5 py-4 font-medium text-zinc-950">
                                {{ $quote->quote_number }}
                            </td>
                            <td class="px-5 py-4 text-zinc-500">
                                {{ $quote->title }}
                            </td>
                            <td class="px-5 py-4 text-zinc-500">
                                {{ $quote->customer?->name ?? '—' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="rounded-full bg-zinc-100 px-2.5 py-1 text-xs font-medium text-zinc-600">
                                    {{ $quote->status }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right font-semibold text-zinc-950">
                                ₱{{ number_format($quote->total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="px-5 py-12 text-center">
            <p class="text-sm text-zinc-500">No quotes yet.</p>
        </div>
    @endif
</div>

@endsection