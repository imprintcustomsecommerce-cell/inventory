@extends('layouts.app')

@section('title', 'Materials')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Materials</h1>
        <p class="mt-1 text-sm text-zinc-500">
            Raw materials &amp; supplies.
            @if($lowCount > 0)<span class="font-medium text-amber-700">{{ $lowCount }} low/out of stock</span>@endif
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('materials.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        @if(auth()->user()->canCreateItems())
            <a href="{{ route('materials.create') }}" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Add Material
            </a>
        @endif
    </div>
</div>

@if($lowCount > 0)
    <a href="{{ route('materials.index', ['status' => 'low']) }}" class="mb-6 flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 hover:bg-amber-100">
        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
        <span><strong>{{ $lowCount }}</strong> material{{ $lowCount === 1 ? '' : 's' }} at or below the minimum level. Click to review and restock.</span>
    </a>
@endif

<div class="card overflow-hidden">
    <form method="GET" action="{{ route('materials.index') }}" class="flex flex-col gap-3 border-b border-zinc-200 p-4 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search material, supplier…" class="input pl-9">
        </div>
        <select name="category" onchange="this.form.submit()" class="select sm:w-40">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <select name="status" onchange="this.form.submit()" class="select sm:w-36">
            <option value="">All stock</option>
            <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Low / out</option>
        </select>
        @if($warehouses->isNotEmpty())
            <select name="warehouse" onchange="this.form.submit()" class="select sm:w-40">
                <option value="">All warehouses</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ request('warehouse') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        @endif
        <button type="submit" class="btn btn-dark">Search</button>
    </form>

    @if($materials->count() > 0)
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Material</th>
                        <th>Category</th>
                        @if(auth()->user()->isAdmin())<th>Warehouse</th>@endif
                        <th>Stock</th>
                        <th>Minimum</th>
                        <th>Status</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($materials as $m)
                        <tr>
                            <td>
                                <div class="font-medium text-zinc-900">{{ $m->name }}</div>
                                @if($m->supplier)<div class="text-xs text-zinc-400">{{ $m->supplier }}</div>@endif
                            </td>
                            <td>@if($m->category)<span class="badge badge-zinc">{{ $m->category }}</span>@else <span class="text-zinc-300">—</span> @endif</td>
                            @if(auth()->user()->isAdmin())<td><span class="badge badge-zinc">{{ $m->warehouse?->name ?? '—' }}</span></td>@endif
                            <td><span class="font-semibold text-zinc-900">{{ $m->current_stock }}</span> <span class="text-xs text-zinc-400">{{ $m->unit }}</span></td>
                            <td class="text-zinc-500">{{ $m->minimum_stock }} {{ $m->unit }}</td>
                            <td><span class="badge {{ $m->getStatusBadgeClass() }}">{{ $m->getStatusLabel() }}</span></td>
                            <td>
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('materials.movementForm', $m) }}" title="Stock movement" class="rounded-lg p-2 text-zinc-400 hover:bg-emerald-50 hover:text-emerald-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m-6 3.75l3 3m0 0l3-3m-3 3V1.5"/></svg></a>
                                    <a href="{{ route('materials.movements', $m) }}" title="History" class="rounded-lg p-2 text-zinc-400 hover:bg-blue-50 hover:text-blue-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></a>
                                    <a href="{{ route('materials.edit', $m) }}" title="Edit" class="rounded-lg p-2 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-900"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z"/></svg></a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($materials->hasPages())<div class="border-t border-zinc-200 px-5 py-3">{{ $materials->links() }}</div>@endif
    @else
        <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
            <p class="text-sm font-medium text-zinc-900">No materials yet</p>
            <p class="mt-1 text-sm text-zinc-500">{{ auth()->user()->canCreateItems() ? 'Add your raw materials to start tracking stock.' : 'No materials in your warehouse.' }}</p>
            @if(auth()->user()->canCreateItems())<a href="{{ route('materials.create') }}" class="btn btn-primary mt-4">Add Material</a>@endif
        </div>
    @endif
</div>

@endsection
