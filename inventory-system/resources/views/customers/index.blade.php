@extends('layouts.app')

@section('title', 'Customers')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Customers</h1>
        <p class="mt-1 text-sm text-zinc-500">Your client directory for quotes and orders.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('customers.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        <a href="{{ route('customers.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Customer
        </a>
    </div>
</div>

<div class="mb-8 grid grid-cols-3 gap-4">
    @php
        $cards = [
            ['label' => 'Total', 'value' => $stats['total']],
            ['label' => 'With quotes', 'value' => $stats['with_quotes']],
            ['label' => 'With projects', 'value' => $stats['with_projects']],
        ];
    @endphp
    @foreach($cards as $c)
        <div class="card p-5">
            <p class="text-sm font-medium text-zinc-500">{{ $c['label'] }}</p>
            <p class="mt-2 text-3xl font-bold text-zinc-900">{{ $c['value'] }}</p>
        </div>
    @endforeach
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, company, email, phone…" class="input pl-9">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Search</button>
            @if(request('search'))
                <a href="{{ route('customers.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($customers->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Company</th>
                        <th>Contact</th>
                        <th>Quotes</th>
                        <th>Projects</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($customers as $customer)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $customer->name }}</td>
                            <td class="text-zinc-500">{{ $customer->company ?? '—' }}</td>
                            <td class="text-zinc-500">
                                {{ $customer->email ?? $customer->phone ?? '—' }}
                            </td>
                            <td class="text-zinc-700">{{ $customer->quotes_count }}</td>
                            <td class="text-zinc-700">{{ $customer->projects_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $customers->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No customers found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') ? 'Try a different search.' : 'Add your first customer to get started.' }}</p>
            <a href="{{ route('customers.create') }}" class="btn btn-primary mt-4">New Customer</a>
        </div>
    @endif
</div>

@endsection
