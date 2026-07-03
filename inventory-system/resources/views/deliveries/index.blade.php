@extends('layouts.app')

@section('title', 'Deliveries')

@section('content')

<div class="mb-8">
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Deliveries</h1>
    <p class="mt-1 text-sm text-zinc-500">Dispatch board for finished jobs.</p>
</div>

<!-- Stats -->
<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $cards = [
            ['label' => 'Scheduled', 'value' => $stats['scheduled'], 'accent' => 'text-zinc-900'],
            ['label' => 'Out for Delivery', 'value' => $stats['in_transit'], 'accent' => 'text-amber-600'],
            ['label' => 'Delivered', 'value' => $stats['delivered'], 'accent' => 'text-emerald-600'],
            ['label' => 'Failed / Returned', 'value' => $stats['failed'], 'accent' => 'text-red-600'],
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
    <form method="GET" action="{{ route('deliveries.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search recipient, tracking #, project…" class="input pl-9">
        </div>
        <select name="status" class="select sm:w-52" onchange="this.form.submit()">
            <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open (scheduled + transit)</option>
            <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All</option>
            @foreach(\App\Models\ProjectDelivery::STATUSES as $s)
                <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-dark">Filter</button>
    </form>

    @if($deliveries->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Project</th>
                        <th>Recipient</th>
                        <th>Method</th>
                        <th>Scheduled</th>
                        <th>Tracking</th>
                        <th class="text-right">Open</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($deliveries as $delivery)
                        <tr>
                            <td><span class="badge {{ $delivery->getStatusBadgeClass() }}">{{ $delivery->status }}</span></td>
                            <td class="font-medium text-zinc-900">{{ $delivery->project?->project_name ?? '—' }}</td>
                            <td class="text-zinc-700">
                                {{ $delivery->recipient_name ?? '—' }}
                                @if($delivery->recipient_contact)<div class="text-xs text-zinc-400">{{ $delivery->recipient_contact }}</div>@endif
                            </td>
                            <td class="text-zinc-500">{{ $delivery->method ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $delivery->scheduled_date?->format('M d, Y') ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $delivery->tracking_number ?? '—' }}</td>
                            <td class="text-right">
                                @if($delivery->project)
                                    <a href="{{ route('projects.show', $delivery->project) }}" class="btn btn-ghost btn-sm">Open</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $deliveries->links() }}</div>
        @endif
    @else
        <div class="px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No deliveries found</p>
            <p class="mt-1 text-sm text-zinc-500">Schedule deliveries from a project's page.</p>
        </div>
    @endif
</div>

@endsection
