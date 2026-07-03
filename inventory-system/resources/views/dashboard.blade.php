@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

<div class="mb-8 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p class="mt-1 text-sm text-zinc-500">Here's what's happening across your shop today.</p>
    </div>
    <div class="inline-flex items-center gap-2 rounded-lg border border-zinc-200 bg-white px-3.5 py-2 text-sm font-medium text-zinc-600">
        <svg class="h-4 w-4 text-brand-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5"/></svg>
        {{ now()->format('D, M d, Y') }}
    </div>
</div>

<!-- KPI row -->
@php
    $canSell = auth()->user()->canSell();
    $kpis = [];
    if ($canSell) {
        $kpis[] = ['label' => 'Sales today', 'value' => '₱' . number_format($sales['today'], 2), 'sub' => '₱' . number_format($sales['month'], 2) . ' this month', 'ring' => 'bg-emerald-50 text-emerald-600', 'icon' => 'M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z'];
    }
    $kpis[] = ['label' => 'Stock value', 'value' => '₱' . number_format($inventory['stock_value'], 2), 'sub' => $inventory['total_items'] . ' items', 'ring' => 'bg-zinc-100 text-zinc-600', 'icon' => 'M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0z'];
    $kpis[] = ['label' => 'Low stock', 'value' => $inventory['low_stock'], 'sub' => 'need restock', 'ring' => 'bg-amber-50 text-amber-600', 'icon' => 'M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z'];
    $kpis[] = ['label' => 'Active projects', 'value' => $projects['active'], 'sub' => $projects['in_production'] . ' in production', 'ring' => 'bg-zinc-100 text-zinc-600', 'icon' => 'M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z'];
    $kpis[] = ['label' => 'Overdue', 'value' => $projects['overdue'], 'sub' => 'past due date', 'ring' => 'bg-red-50 text-red-600', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'];
    $kpis[] = ['label' => 'Open quotes', 'value' => $crm['open_quotes'], 'sub' => '₱' . number_format($crm['pipeline'], 0) . ' in pipeline', 'ring' => 'bg-zinc-100 text-zinc-600', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'];
    $kpis[] = ['label' => 'Receivables', 'value' => '₱' . number_format($crm['receivables'], 0), 'sub' => $crm['overdue_invoices'] . ' overdue', 'ring' => 'bg-amber-50 text-amber-600', 'icon' => 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z'];
    $gridCols = 'sm:grid-cols-3 lg:grid-cols-4';
@endphp
<div class="mb-8 grid grid-cols-2 gap-4 {{ $gridCols }}">
    @foreach($kpis as $i => $k)
        @php
            // First two cards are "hero" cards (yellow, then dark) for a POS-dashboard feel.
            $tone = $i === 0 ? 'yellow' : ($i === 1 ? 'dark' : 'plain');
            $cardCls = match ($tone) {
                'yellow' => 'bg-brand-400 border-brand-400',
                'dark' => 'bg-zinc-900 border-zinc-900',
                default => 'bg-white border-zinc-200',
            };
            $labelCls = match ($tone) { 'dark' => 'text-zinc-400', 'yellow' => 'text-zinc-800', default => 'text-zinc-500' };
            $valueCls = $tone === 'dark' ? 'text-white' : 'text-zinc-900';
            $subCls = match ($tone) { 'dark' => 'text-zinc-500', 'yellow' => 'text-zinc-700', default => 'text-zinc-400' };
            $chipCls = match ($tone) { 'dark' => 'bg-white/10 text-brand-400', 'yellow' => 'bg-zinc-900/10 text-zinc-900', default => $k['ring'] };
        @endphp
        <div class="rounded-xl border p-5 shadow-sm transition hover:shadow-md {{ $cardCls }}">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium {{ $labelCls }}">{{ $k['label'] }}</span>
                <span class="flex h-8 w-8 items-center justify-center rounded-lg {{ $chipCls }}">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $k['icon'] }}"/></svg>
                </span>
            </div>
            <p class="mt-3 text-2xl font-bold {{ $valueCls }}">{{ $k['value'] }}</p>
            <p class="mt-0.5 text-xs {{ $subCls }}">{{ $k['sub'] }}</p>
        </div>
    @endforeach
</div>

@if(!empty($analytics))
    @php
        $a = $analytics;
        $tr = collect($a['trend'])->values();
        $max = max(1, $a['trend_max']);
        $n = $tr->count();
        $W = 600; $H = 180; $pad = 8;
        $step = $n > 1 ? $W / ($n - 1) : $W;
        $coords = [];
        foreach ($tr as $idx => $t) {
            $x = round($idx * $step, 1);
            $y = round($H - $pad - ($t['revenue'] / $max) * ($H - 2 * $pad), 1);
            $coords[] = ['x' => $x, 'y' => $y, 'label' => $t['label']];
        }
        $line = collect($coords)->map(fn ($c) => "{$c['x']},{$c['y']}")->implode(' ');
        $area = "0,{$H} {$line} " . round(($n - 1) * $step, 1) . ",{$H}";
        $lastIdx = $n - 1;
        $rating = $operations['avg_rating'] ?? 0;
        $satPct = $rating > 0 ? round($rating / 5 * 100) : 0;
        $gArc = round(pi() * 70, 1);
        $gOff = round($gArc * (1 - $satPct / 100), 1);
    @endphp
    <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Analytics tracking: revenue line chart -->
        <div class="card p-6 lg:col-span-2">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-zinc-500">Analytics tracking</h2>
                    <p class="mt-1 text-3xl font-bold text-zinc-900">₱{{ number_format($a['revenue_month'], 2) }}</p>
                    <p class="text-xs text-zinc-400">Revenue this month · last 6 months</p>
                </div>
                <span class="badge badge-green">Net ₱{{ number_format($a['net_profit'], 0) }}</span>
            </div>
            <div class="mt-4">
                <svg viewBox="0 0 {{ $W }} {{ $H }}" class="w-full" role="img" aria-label="Revenue trend">
                    <defs>
                        <linearGradient id="revfillTop" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#facc15" stop-opacity="0.35"/>
                            <stop offset="100%" stop-color="#facc15" stop-opacity="0"/>
                        </linearGradient>
                    </defs>
                    <polygon points="{{ $area }}" fill="url(#revfillTop)"/>
                    <polyline points="{{ $line }}" fill="none" stroke="#eab308" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"/>
                    @foreach($coords as $ci => $c)
                        <circle cx="{{ $c['x'] }}" cy="{{ $c['y'] }}" r="{{ $ci === $lastIdx ? 5 : 3.5 }}" fill="{{ $ci === $lastIdx ? '#eab308' : '#ffffff' }}" stroke="#eab308" stroke-width="2" vector-effect="non-scaling-stroke"/>
                    @endforeach
                </svg>
                <div class="mt-1 flex justify-between">
                    @foreach($coords as $c)
                        <span class="text-xs text-zinc-400">{{ $c['label'] }}</span>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Customer satisfaction gauge -->
        <div class="card flex flex-col p-6">
            <h2 class="text-sm font-semibold text-zinc-900">Customer satisfaction</h2>
            <div class="flex flex-1 flex-col items-center justify-center">
                <svg viewBox="0 0 180 108" class="mt-2 w-full max-w-[240px]">
                    <path d="M20 92 A70 70 0 0 1 160 92" fill="none" stroke="#e4e4e7" stroke-width="16" stroke-linecap="round"/>
                    <path d="M20 92 A70 70 0 0 1 160 92" fill="none" stroke="#facc15" stroke-width="16" stroke-linecap="round" stroke-dasharray="{{ $gArc }}" stroke-dashoffset="{{ $gOff }}"/>
                </svg>
                <p class="-mt-8 text-3xl font-bold text-zinc-900">{{ $satPct }}%</p>
                <p class="mt-1 text-xs text-zinc-400">Based on {{ $operations['feedback_count'] }} review{{ $operations['feedback_count'] == 1 ? '' : 's' }}</p>
            </div>
        </div>
    </div>
@endif

@if(!empty($alerts))
    <div class="card mb-6 overflow-hidden">
        <div class="flex items-center gap-2 border-b border-zinc-200 px-5 py-3">
            <svg class="h-5 w-5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
            <h2 class="text-sm font-semibold text-zinc-900">Needs attention</h2>
        </div>
        <ul class="divide-y divide-zinc-100">
            @foreach($alerts as $alert)
                <li>
                    <a href="{{ $alert['route'] }}" class="flex items-center gap-3 px-5 py-3 transition hover:bg-zinc-50">
                        <span class="flex h-2.5 w-2.5 shrink-0 rounded-full {{ $alert['tone'] === 'red' ? 'bg-red-500' : 'bg-amber-400' }}"></span>
                        <span class="min-w-0 flex-1">
                            <span class="text-sm font-medium text-zinc-900">{{ $alert['label'] }}</span>
                            <span class="block text-xs text-zinc-500">{{ $alert['detail'] }}</span>
                        </span>
                        <svg class="h-4 w-4 shrink-0 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/></svg>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
@endif

<!-- Operations -->
<div class="mb-8">
    <h2 class="mb-3 text-sm font-semibold text-zinc-900">Operations</h2>
    <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <a href="{{ route('online-orders.index') }}" class="card p-5 transition hover:border-zinc-300">
            <p class="text-sm font-medium text-zinc-500">New online orders</p>
            <p class="mt-2 text-2xl font-bold {{ $operations['new_orders'] > 0 ? 'text-amber-600' : 'text-zinc-900' }}">{{ $operations['new_orders'] }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">awaiting routing</p>
        </a>
        <a href="{{ route('deliveries.index') }}" class="card p-5 transition hover:border-zinc-300">
            <p class="text-sm font-medium text-zinc-500">Deliveries in transit</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900">{{ $operations['in_transit'] }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">scheduled or out</p>
        </a>
        <a href="{{ route('quality.index') }}" class="card p-5 transition hover:border-zinc-300">
            <p class="text-sm font-medium text-zinc-500">Open QC issues</p>
            <p class="mt-2 text-2xl font-bold {{ $operations['open_issues'] > 0 ? 'text-red-600' : 'text-zinc-900' }}">{{ $operations['open_issues'] }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">defects / reprints</p>
        </a>
        <a href="{{ route('feedback.index') }}" class="card p-5 transition hover:border-zinc-300">
            <p class="text-sm font-medium text-zinc-500">Avg. feedback</p>
            <p class="mt-2 text-2xl font-bold text-zinc-900">
                {{ $operations['feedback_count'] > 0 ? number_format($operations['avg_rating'], 1) : '—' }}
                @if($operations['feedback_count'] > 0)<span class="text-base text-amber-500">★</span>@endif
            </p>
            <p class="mt-0.5 text-xs text-zinc-400">{{ $operations['feedback_count'] }} {{ Str::plural('review', $operations['feedback_count']) }}</p>
        </a>
    </div>
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Low stock -->
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3">
            <h2 class="text-sm font-semibold text-zinc-900">Needs restocking</h2>
            <a href="{{ route('inventory.lowStock') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900">View all</a>
        </div>
        @if($lowStockItems->count() > 0)
            <table class="data-table">
                <tbody>
                    @foreach($lowStockItems as $item)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $item->displayName() }}</td>
                            <td class="text-zinc-500">{{ $item->current_stock }} / {{ $item->minimum_stock }} {{ $item->unit }}</td>
                            <td class="text-right">
                                <span class="badge {{ $item->isOutOfStock() ? 'badge-red' : 'badge-amber' }}">{{ $item->getStatusLabel() }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="px-5 py-10 text-center text-sm text-zinc-500">Everything is above its minimum level. 🎉</p>
        @endif
    </div>

    <!-- Upcoming projects -->
    <div class="card overflow-hidden">
        <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3">
            <h2 class="text-sm font-semibold text-zinc-900">Upcoming deadlines</h2>
            <a href="{{ route('projects.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900">View all</a>
        </div>
        @if($upcomingProjects->count() > 0)
            <table class="data-table">
                <tbody>
                    @foreach($upcomingProjects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('projects.show', $project) }}" class="font-medium text-zinc-900 hover:text-brand-600">{{ $project->project_name }}</a>
                                <div class="text-xs text-zinc-400">{{ $project->customer_name ?? 'No customer' }}</div>
                            </td>
                            <td><span class="badge {{ $project->getStatusBadgeClass() }}">{{ $project->status }}</span></td>
                            <td class="text-right {{ $project->isOverdue() ? 'font-medium text-red-600' : 'text-zinc-500' }}">{{ $project->due_date->format('M d') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="px-5 py-10 text-center text-sm text-zinc-500">No scheduled projects.</p>
        @endif
    </div>
</div>

<!-- Recent sales -->
@if($canSell)
<div class="card mt-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3">
        <h2 class="text-sm font-semibold text-zinc-900">Recent sales</h2>
        <a href="{{ route('sales.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900">View all</a>
    </div>
    @if($recentSales->count() > 0)
        <table class="data-table">
            <tbody>
                @foreach($recentSales as $sale)
                    <tr>
                        <td class="whitespace-nowrap text-zinc-400">{{ $sale->created_at->diffForHumans() }}</td>
                        <td class="font-medium text-zinc-900">{{ $sale->item_label }}</td>
                        <td><span class="badge badge-zinc">{{ $sale->warehouse?->name ?? '—' }}</span></td>
                        <td class="text-zinc-500">{{ $sale->quantity }} ×</td>
                        <td class="font-semibold text-emerald-600">₱{{ number_format($sale->total, 2) }}</td>
                        <td class="text-right text-zinc-500">{{ $sale->user?->name ?? '—' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="px-5 py-10 text-center text-sm text-zinc-500">No sales yet.</p>
    @endif
</div>
@endif

<!-- Recent quotes -->
<div class="card mt-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3">
        <h2 class="text-sm font-semibold text-zinc-900">Recent quotes</h2>
        <a href="{{ route('quotes.index') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900">View all</a>
    </div>
    @if($recentQuotes->count() > 0)
        <table class="data-table">
            <tbody>
                @foreach($recentQuotes as $quote)
                    <tr class="cursor-pointer hover:bg-zinc-50" onclick="window.location='{{ route('quotes.show', $quote) }}'">
                        <td class="whitespace-nowrap text-zinc-400">{{ $quote->created_at->diffForHumans() }}</td>
                        <td class="font-medium text-zinc-900">{{ $quote->quote_number }}</td>
                        <td class="text-zinc-500">{{ $quote->title }}</td>
                        <td class="text-zinc-500">{{ $quote->customer?->name ?? '—' }}</td>
                        <td><span class="badge {{ $quote->getStatusBadgeClass() }}">{{ $quote->status }}</span></td>
                        <td class="text-right font-semibold text-zinc-900">₱{{ number_format($quote->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="px-5 py-10 text-center text-sm text-zinc-500">No quotes yet.</p>
    @endif
</div>

<!-- Recent activity -->
<div class="card mt-6 overflow-hidden">
    <div class="flex items-center justify-between border-b border-zinc-200 px-5 py-3">
        <h2 class="text-sm font-semibold text-zinc-900">Recent stock activity</h2>
        <a href="{{ route('inventory.allMovements') }}" class="text-xs font-medium text-zinc-500 hover:text-zinc-900">View all</a>
    </div>
    @if($recentMovements->count() > 0)
        <table class="data-table">
            <tbody>
                @foreach($recentMovements as $m)
                    <tr>
                        <td class="whitespace-nowrap text-zinc-400">{{ $m->created_at->diffForHumans() }}</td>
                        <td>
                            <span class="badge {{ $m->type === 'stock_in' ? 'badge-green' : ($m->type === 'stock_out' ? 'badge-red' : 'badge-amber') }}">{{ $m->getTypeLabel() }}</span>
                        </td>
                        <td class="font-medium text-zinc-900">{{ $m->item?->displayName() ?? '—' }}</td>
                        <td class="text-zinc-700">{{ $m->quantity }} {{ $m->item?->unit }}</td>
                        <td class="text-right text-zinc-500">{{ $m->user?->name ?? 'System' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="px-5 py-10 text-center text-sm text-zinc-500">No stock activity yet.</p>
    @endif
</div>

@if(!empty($analytics))
    @php $a = $analytics; @endphp

    <div class="mt-10 mb-4 flex items-center gap-3">
        <h2 class="text-lg font-bold tracking-tight text-zinc-900">Analytics</h2>
        <span class="text-sm text-zinc-400">{{ now()->format('F Y') }}</span>
    </div>

    <!-- Money KPIs -->
    <div class="mb-6 grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
        @php
            $money = [
                ['label' => 'Revenue', 'value' => '₱' . number_format($a['revenue_month'], 0), 'accent' => 'text-zinc-900'],
                ['label' => 'Gross profit', 'value' => '₱' . number_format($a['profit_month'], 0), 'accent' => 'text-emerald-600'],
                ['label' => 'Expenses', 'value' => '₱' . number_format($a['expenses_month'], 0), 'accent' => 'text-zinc-900'],
                ['label' => 'Net profit', 'value' => '₱' . number_format($a['net_profit'], 0), 'accent' => $a['net_profit'] >= 0 ? 'text-emerald-600' : 'text-red-600'],
                ['label' => 'Receivables', 'value' => '₱' . number_format($a['receivables'], 0), 'accent' => $a['receivables_overdue'] > 0 ? 'text-red-600' : 'text-zinc-900'],
                ['label' => 'Purchasing', 'value' => '₱' . number_format($a['purchasing_spend_month'], 0), 'accent' => 'text-zinc-900'],
            ];
        @endphp
        @foreach($money as $m)
            <div class="card p-4">
                <p class="text-xs font-medium text-zinc-500">{{ $m['label'] }}</p>
                <p class="mt-1 text-xl font-bold {{ $m['accent'] }}">{{ $m['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Top products -->
        <div class="card overflow-hidden">
            <div class="border-b border-zinc-200 px-6 py-4"><h3 class="text-sm font-semibold text-zinc-900">Top products</h3></div>
            @if($a['top_products']->count() > 0)
                <table class="data-table">
                    <tbody>
                        @foreach($a['top_products'] as $p)
                            <tr>
                                <td class="font-medium text-zinc-900">{{ $p->item_label ?? '—' }}</td>
                                <td class="text-right text-zinc-500">{{ rtrim(rtrim(number_format($p->qty, 2), '0'), '.') }} sold</td>
                                <td class="text-right font-medium text-zinc-900">₱{{ number_format($p->revenue, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="px-6 py-8 text-center text-sm text-zinc-500">No sales yet.</p>
            @endif
        </div>

        <!-- Collections -->
        <div class="card p-6">
            <h3 class="text-sm font-semibold text-zinc-900">Invoices &amp; collections</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">Collected this month</dt><dd class="font-medium text-emerald-600">₱{{ number_format($a['collected_month'], 2) }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Outstanding</dt><dd class="font-medium text-zinc-900">₱{{ number_format($a['receivables'], 2) }}</dd></div>
                <div class="flex justify-between border-t border-zinc-100 pt-3"><dt class="text-zinc-500">Overdue</dt><dd class="font-semibold {{ $a['receivables_overdue'] > 0 ? 'text-red-600' : 'text-zinc-900' }}">₱{{ number_format($a['receivables_overdue'], 2) }}</dd></div>
                <div class="flex justify-between border-t border-zinc-100 pt-3"><dt class="text-zinc-500">Quote pipeline</dt><dd class="font-medium text-zinc-900">₱{{ number_format($a['quotes_pipeline'], 2) }} <span class="text-zinc-400">({{ $a['quotes_conversion'] }}% won)</span></dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Project margin</dt><dd class="font-semibold {{ $a['projects_margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₱{{ number_format($a['projects_margin'], 2) }}</dd></div>
            </dl>
        </div>

        <!-- Spend by supplier + expenses -->
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold text-zinc-900">Spend</h3>
                <span class="text-xs text-zinc-400">this month</span>
            </div>
            <p class="mt-2 text-xs font-medium uppercase tracking-wide text-zinc-400">By supplier</p>
            @forelse($a['purchasing_by_supplier'] as $row)
                <div class="mt-1 flex items-center justify-between text-sm">
                    <span class="text-zinc-600">{{ $row->supplier?->name ?? 'Unassigned' }}</span>
                    <span class="font-medium text-zinc-900">₱{{ number_format($row->spend, 2) }}</span>
                </div>
            @empty
                <p class="mt-1 text-sm text-zinc-400">No purchase orders.</p>
            @endforelse

            <p class="mt-4 text-xs font-medium uppercase tracking-wide text-zinc-400">Expenses by category</p>
            @forelse($a['expenses_by_category'] as $cat => $amt)
                <div class="mt-1 flex items-center justify-between text-sm">
                    <span class="text-zinc-600">{{ $cat }}</span>
                    <span class="font-medium text-zinc-900">₱{{ number_format($amt, 2) }}</span>
                </div>
            @empty
                <p class="mt-1 text-sm text-zinc-400">No expenses logged.</p>
            @endforelse
        </div>
    </div>
@endif

@endsection
