@extends('layouts.app')

@section('title', 'Reports')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Reports &amp; analytics</h1>
    <p class="mt-1 text-sm text-zinc-500">Business overview for {{ now()->format('F Y') }}.</p>
</div>

<!-- KPI row -->
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $kpis = [
            ['label' => 'Revenue (month)', 'value' => '₱' . number_format($sales['revenue_month'], 2), 'sub' => $sales['orders_month'] . ' sales', 'accent' => 'text-zinc-900'],
            ['label' => 'Gross profit (month)', 'value' => '₱' . number_format($sales['profit_month'], 2), 'sub' => 'sales margin', 'accent' => 'text-emerald-600'],
            ['label' => 'Receivables', 'value' => '₱' . number_format($invoices['receivables'], 2), 'sub' => '₱' . number_format($invoices['overdue'], 0) . ' overdue', 'accent' => $invoices['overdue'] > 0 ? 'text-red-600' : 'text-zinc-900'],
            ['label' => 'Purchasing (month)', 'value' => '₱' . number_format($purchasing['spend_month'], 2), 'sub' => '₱' . number_format($purchasing['open_value'], 0) . ' open POs', 'accent' => 'text-zinc-900'],
        ];
    @endphp
    @foreach($kpis as $k)
        <div class="card p-5">
            <p class="text-sm font-medium text-zinc-500">{{ $k['label'] }}</p>
            <p class="mt-2 text-2xl font-bold {{ $k['accent'] }}">{{ $k['value'] }}</p>
            <p class="mt-0.5 text-xs text-zinc-400">{{ $k['sub'] }}</p>
        </div>
    @endforeach
</div>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Sales trend -->
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-zinc-900">Revenue — last 6 months</h2>
        <div class="mt-6 flex items-end gap-3" style="height: 180px;">
            @foreach($trend as $t)
                <div class="flex flex-1 flex-col items-center justify-end gap-2">
                    <span class="text-xs font-medium text-zinc-500">₱{{ number_format($t['revenue'] / 1000, 1) }}k</span>
                    <div class="w-full rounded-t bg-brand-400" style="height: {{ max(2, round($t['revenue'] / $trendMax * 140)) }}px;"></div>
                    <span class="text-xs text-zinc-400">{{ $t['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Top products -->
    <div class="card overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-zinc-900">Top products</h2>
        </div>
        @if($topProducts->count() > 0)
            <table class="data-table">
                <thead>
                    <tr><th>Product</th><th class="text-right">Qty sold</th><th class="text-right">Revenue</th></tr>
                </thead>
                <tbody>
                    @foreach($topProducts as $p)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $p->item_label ?? '—' }}</td>
                            <td class="text-right text-zinc-700">{{ rtrim(rtrim(number_format($p->qty, 2), '0'), '.') }}</td>
                            <td class="text-right font-medium text-zinc-900">₱{{ number_format($p->revenue, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="px-6 py-10 text-center text-sm text-zinc-500">No sales recorded yet.</p>
        @endif
    </div>

    <!-- Quotes pipeline -->
    <div class="card p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-zinc-900">Quotes pipeline</h2>
            <span class="badge badge-zinc">{{ $quotes['conversion'] }}% won</span>
        </div>
        <p class="mt-2 text-sm text-zinc-500">₱{{ number_format($quotes['pipeline'], 2) }} in open pipeline · {{ $quotes['won'] }} of {{ $quotes['total'] }} won</p>
        <dl class="mt-4 space-y-2">
            @foreach($quotes['by_status'] as $status => $count)
                <div class="flex items-center justify-between text-sm">
                    <dt class="text-zinc-500">{{ $status }}</dt>
                    <dd class="font-medium text-zinc-900">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <!-- Invoices / collections -->
    <div class="card p-6">
        <h2 class="text-sm font-semibold text-zinc-900">Invoices &amp; collections</h2>
        <dl class="mt-4 space-y-3 text-sm">
            <div class="flex justify-between"><dt class="text-zinc-500">Collected this month</dt><dd class="font-medium text-emerald-600">₱{{ number_format($invoices['collected_month'], 2) }}</dd></div>
            <div class="flex justify-between"><dt class="text-zinc-500">Outstanding</dt><dd class="font-medium text-zinc-900">₱{{ number_format($invoices['receivables'], 2) }}</dd></div>
            <div class="flex justify-between border-t border-zinc-100 pt-3"><dt class="text-zinc-500">Overdue</dt><dd class="font-semibold {{ $invoices['overdue'] > 0 ? 'text-red-600' : 'text-zinc-900' }}">₱{{ number_format($invoices['overdue'], 2) }}</dd></div>
        </dl>
        <dl class="mt-4 space-y-2 border-t border-zinc-100 pt-4">
            @foreach($invoices['by_status'] as $status => $count)
                <div class="flex items-center justify-between text-sm">
                    <dt class="text-zinc-500">{{ $status }}</dt>
                    <dd class="font-medium text-zinc-900">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <!-- Projects -->
    <div class="card p-6">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-zinc-900">Projects</h2>
            <span class="text-sm font-semibold {{ $projects['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₱{{ number_format($projects['margin'], 2) }} margin</span>
        </div>
        <p class="mt-2 text-sm text-zinc-500">₱{{ number_format($projects['quoted'], 2) }} quoted across all jobs</p>
        <dl class="mt-4 space-y-2">
            @foreach($projects['by_status'] as $status => $count)
                <div class="flex items-center justify-between text-sm">
                    <dt class="text-zinc-500">{{ $status }}</dt>
                    <dd class="font-medium text-zinc-900">{{ $count }}</dd>
                </div>
            @endforeach
        </dl>
    </div>

    <!-- Purchasing -->
    <div class="card overflow-hidden">
        <div class="border-b border-zinc-200 px-6 py-4">
            <h2 class="text-sm font-semibold text-zinc-900">Spend by supplier</h2>
        </div>
        @if($purchasing['by_supplier']->count() > 0)
            <table class="data-table">
                <thead>
                    <tr><th>Supplier</th><th class="text-right">Total spend</th></tr>
                </thead>
                <tbody>
                    @foreach($purchasing['by_supplier'] as $row)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $row->supplier?->name ?? 'Unassigned' }}</td>
                            <td class="text-right font-medium text-zinc-900">₱{{ number_format($row->spend, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="px-6 py-10 text-center text-sm text-zinc-500">No purchase orders yet.</p>
        @endif
    </div>
</div>

@endsection
