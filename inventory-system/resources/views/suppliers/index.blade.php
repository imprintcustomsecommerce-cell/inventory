@extends('layouts.app')

@section('title', 'Suppliers')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Suppliers</h1>
        <p class="mt-1 text-sm text-zinc-500">Vendors you order materials and supplies from.</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('suppliers.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            New Supplier
        </a>
    </div>
</div>

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, contact, email, phone…" class="input pl-9">
        </div>
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Search</button>
            @if(request('search'))
                <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>

    @if($suppliers->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Lead time</th>
                        <th>POs</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($suppliers as $supplier)
                        <tr>
                            <td class="font-medium text-zinc-900">{{ $supplier->name }}</td>
                            <td class="text-zinc-500">{{ $supplier->contact_person ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $supplier->phone ?? '—' }}</td>
                            <td class="text-zinc-500">{{ $supplier->lead_time ?? '—' }}</td>
                            <td class="text-zinc-700">{{ $supplier->purchase_orders_count }}</td>
                            <td class="text-right">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="border-t border-zinc-200 px-5 py-3">{{ $suppliers->withQueryString()->links() }}</div>
        @endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>
            </div>
            <p class="mt-4 text-sm font-medium text-zinc-900">No suppliers found</p>
            <p class="mt-1 text-sm text-zinc-500">{{ request('search') ? 'Try a different search.' : 'Add your first supplier to start ordering.' }}</p>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary mt-4">New Supplier</a>
        </div>
    @endif
</div>

@endsection
