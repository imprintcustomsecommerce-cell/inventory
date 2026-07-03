@extends('layouts.app')

@section('title', 'Quotes')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Quotes</h1>
        <p class="mt-1 text-sm text-zinc-500">Price estimates for customer orders.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('quotes.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        <a href="{{ route('quotes.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Quote
        </a>
    </div>
</div>

<div class="mb-8 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @php
        $cards = [
            ['label' => 'Total', 'value' => $stats['total'], 'accent' => 'text-zinc-900'],
            ['label' => 'Sent', 'value' => $stats['sent'], 'accent' => 'text-amber-600'],
            ['label' => 'Approved', 'value' => $stats['approved'], 'accent' => 'text-emerald-600'],
            ['label' => 'Open value', 'value' => '₱' . number_format($stats['value'], 0), 'accent' => 'text-zinc-900'],
        ];
    @endphp
    @foreach($cards as $i => $c)
        <div class="rounded-xl border p-5 shadow-sm transition hover:shadow-md {{ $i === 0 ? 'bg-brand-400 border-brand-400' : 'bg-white border-zinc-200' }}">
            <p class="text-sm font-medium {{ $i === 0 ? 'text-zinc-800' : 'text-zinc-500' }}">{{ $c['label'] }}</p>
            <p class="mt-2 text-3xl font-bold {{ $i === 0 ? 'text-zinc-900' : $c['accent'] }}">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('quotes.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search number, title, customer…" class="input pl-9">
        </div>
        <select name="status" class="select sm:w-52">
            <option value="">All statuses</option>
            @foreach(\App\Models\Quote::STATUSES as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Filter</button>
            @if(request('search') || request('status'))
                <a href="{{ route('quotes.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($quotes->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Quote #</th>
                        <th>Title</th>
                        <th>Customer</th>
                        <th>Status</th>
                        <th>Valid until</th>
                        <th class="text-right">Total</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($quotes as $quote)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $quote->quote_number }}</td>
                            <td class="text-zinc-500">{{ $quote->title }}</td>
                            <td class="text-zinc-500">{{ $quote->customer?->name ?? '—' }}</td>
                            <td><span class="badge {{ $quote->getStatusBadgeClass() }}">{{ $quote->status }}</span></td>
                            <td>
                                @if($quote->valid_until)
                                    <span class="{{ $quote->isExpired() ? 'font-medium text-red-600' : 'text-zinc-500' }}">{{ $quote->valid_until->format('M d, Y') }}</span>
                                @else
                                    <span class="text-zinc-300">—</span>
                                @endif
                            </td>
                            <td class="text-right text-zinc-700">₱{{ number_format($quote->total, 2) }}</td>
                            <td class="text-right">
                                <a href="{{ route('quotes.show', $quote) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($quotes->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $quotes->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No quotes found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') || request('status') ? 'Try adjusting your filters.' : 'Create your first quote to get started.' }}</p>
            <a href="{{ route('quotes.create') }}" class="btn btn-primary mt-4">New Quote</a>
        </div>
    @endif
</div>

@endsection
