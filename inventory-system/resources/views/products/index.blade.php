@extends('layouts.app')

@section('title', 'Products')

@section('content')

<div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Products</h1>
        <p class="mt-1 text-sm text-zinc-500">
            {{ $products->total() }} {{ $showMissing ? 'product(s) without a photo' : 'product' . ($products->total() === 1 ? '' : 's') }} — open one to see its sizes.
            @if(auth()->user()->isAdmin() && $missingCount > 0)
                @if($showMissing)
                    <a href="{{ route('products.index') }}" class="font-medium text-zinc-700 underline">← Back to catalog</a>
                @else
                    <a href="{{ route('products.index', ['no_image' => 1]) }}" class="font-medium text-amber-700 underline">{{ $missingCount }} missing a photo</a>
                @endif
            @endif
        </p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('products.export', request()->query()) }}" class="btn btn-ghost">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Export Excel
        </a>
        @if(auth()->user()->isAdmin())
            <a href="{{ route('products.importForm') }}" class="btn btn-ghost">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                Import
            </a>
        @endif
        @if(auth()->user()->canCreateItems())
            <a href="{{ route('products.create') }}" class="btn btn-primary">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                New Product
            </a>
        @endif
    </div>
</div>

<div class="card mb-6 p-4">
    <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-3 sm:flex-row sm:items-center">
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, category, brand…" class="input pl-9">
        </div>
        <select name="category" onchange="this.form.submit()" class="select sm:w-44">
            <option value="">All categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        @if($warehouses->isNotEmpty())
            <select name="warehouse" onchange="this.form.submit()" class="select sm:w-44">
                <option value="">All warehouses</option>
                @foreach($warehouses as $w)
                    <option value="{{ $w->id }}" {{ request('warehouse') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                @endforeach
            </select>
        @endif
        <div class="flex gap-2">
            <button type="submit" class="btn btn-dark">Search</button>
            @if(request('search') || request('category') || request('warehouse'))
                <a href="{{ route('products.index') }}" class="btn btn-ghost">Clear</a>
            @endif
        </div>
    </form>
</div>

@if($products->count() > 0)
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @foreach($products as $product)
            <a href="{{ route('products.show', $product) }}" class="card group overflow-hidden transition hover:shadow-md">
                <div class="flex aspect-square items-center justify-center overflow-hidden bg-white p-2">
                    @if($product->imageUrl())
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                    @else
                        <svg class="h-12 w-12 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                    @endif
                </div>
                <div class="p-4">
                    <div class="flex flex-wrap items-center gap-1.5">
                        @foreach($product->stockByWarehouse() as $whName => $qty)
                            <span class="badge {{ $whName === 'Store' ? 'badge-amber' : 'badge-green' }}">{{ $whName }} {{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</span>
                        @endforeach
                        @if($product->category)<span class="badge badge-zinc">{{ $product->category }}</span>@endif
                    </div>
                    <h3 class="mt-2 font-semibold text-zinc-900 line-clamp-2">{{ $product->name }}</h3>
                    @if($product->brand)<p class="text-xs text-zinc-400">{{ $product->brand }}</p>@endif
                    <div class="mt-3 flex items-center justify-between">
                        <span class="font-bold text-zinc-900">₱{{ number_format($product->retail_price, 2) }}</span>
                        <span class="text-xs text-zinc-500">{{ rtrim(rtrim(number_format($product->totalStock(), 2), '0'), '.') }} in stock</span>
                    </div>
                </div>
            </a>
        @endforeach
    </div>
    <div class="mt-6">{{ $products->links() }}</div>
@else
    <div class="card flex flex-col items-center justify-center px-6 py-16 text-center">
        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
            <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0a48.667 48.667 0 00-16.5 0"/></svg>
        </div>
        <p class="mt-4 text-sm font-medium text-zinc-900">No products yet</p>
        <p class="mt-1 text-sm text-zinc-500">{{ auth()->user()->canCreateItems() ? 'Create one, or import your products file.' : 'Stock arrives here by transfer from the stockroom.' }}</p>
        @if(auth()->user()->canCreateItems())
            <a href="{{ route('products.create') }}" class="btn btn-primary mt-4">New Product</a>
        @endif
    </div>
@endif

@endsection
