@extends('layouts.app')

@section('title', 'Product Management')

@section('content')

@php $isAdmin = auth()->user()->isAdmin(); @endphp

<div x-data="productTable()">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Product Management</h1>
            <p class="mt-1 text-sm text-zinc-500">
                {{ $products->total() }} product{{ $products->total() === 1 ? '' : 's' }}
                @if($isAdmin && $missingCount > 0)
                    · <a href="{{ route('products.index', ['no_image' => 1]) }}" class="font-medium text-amber-700 underline">{{ $missingCount }} missing a photo</a>
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('products.export', request()->query()) }}" class="btn btn-ghost">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                Export
            </a>
            @if($isAdmin)
                <a href="{{ route('products.importForm') }}" class="btn btn-ghost">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Import
                </a>
                <a href="{{ route('inventory.trash') }}" class="btn btn-ghost">Trash</a>
            @endif
            @if(auth()->user()->canCreateItems())
                <a href="{{ route('products.create') }}" class="btn btn-primary">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    New Product
                </a>
            @endif
        </div>
    </div>

    <!-- Sticky filter bar -->
    <div class="sticky top-0 z-10 mb-4 rounded-xl border border-zinc-200 bg-white/90 p-3 shadow-sm backdrop-blur">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col gap-2 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, SKU, category, brand…" class="input pl-9">
            </div>
            <select name="category" onchange="this.form.submit()" class="select lg:w-40">
                <option value="">All categories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            @if($warehouses->isNotEmpty())
                <select name="warehouse" onchange="this.form.submit()" class="select lg:w-40">
                    <option value="">All warehouses</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}" {{ request('warehouse') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                    @endforeach
                </select>
            @endif
            <select name="status" onchange="this.form.submit()" class="select lg:w-36">
                <option value="">Any status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
            </select>
            <select name="stock" onchange="this.form.submit()" class="select lg:w-36">
                <option value="">Any stock</option>
                <option value="low" {{ request('stock') == 'low' ? 'selected' : '' }}>Low stock</option>
                <option value="out" {{ request('stock') == 'out' ? 'selected' : '' }}>Out of stock</option>
            </select>
            <div class="flex gap-2">
                <button type="submit" class="btn btn-dark">Filter</button>
                @if(request()->hasAny(['search','category','warehouse','status','stock','no_image']))
                    <a href="{{ route('products.index') }}" class="btn btn-ghost">Clear</a>
                @endif
            </div>
        </form>
    </div>

    @if($isAdmin)
        <!-- Bulk action toolbar -->
        <div x-show="selected.length > 0" x-cloak class="mb-3 flex items-center justify-between rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2.5 text-white">
            <span class="text-sm font-medium"><span x-text="selected.length"></span> selected</span>
            <div class="flex gap-2">
                <button type="button" class="btn btn-sm bg-white text-zinc-900 hover:bg-zinc-100" @click="submitBulk('activate')">Activate</button>
                <button type="button" class="btn btn-sm bg-zinc-700 text-white hover:bg-zinc-600" @click="submitBulk('deactivate')">Deactivate</button>
                <button type="button" class="btn btn-sm bg-red-600 text-white hover:bg-red-700" @click="submitBulk('delete')">Delete</button>
            </div>
        </div>

        <form x-ref="bulkForm" method="POST" action="{{ route('products.bulk') }}" class="hidden">
            @csrf
            <input type="hidden" name="action" x-ref="bulkAction">
            <template x-for="id in selected" :key="id"><input type="hidden" name="ids[]" :value="id"></template>
        </form>
    @endif

    <div class="card overflow-hidden">
        @if($products->count() > 0)
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            @if($isAdmin)<th class="w-10"><input type="checkbox" @change="toggleAll($event)" :checked="allChecked" class="rounded border-zinc-300"></th>@endif
                            <th>Product</th>
                            <th>Category</th>
                            <th>Variations</th>
                            <th>Stock by warehouse</th>
                            <th class="text-right">Price</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr>
                                @if($isAdmin)
                                    <td><input type="checkbox" value="{{ $product->id }}" x-model.number="selected" class="rounded border-zinc-300"></td>
                                @endif
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-white">
                                            @if($product->imageUrl())
                                                <img src="{{ $product->imageUrl() }}" alt="" class="max-h-full max-w-full object-contain">
                                            @else
                                                <svg class="h-5 w-5 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('products.show', $product) }}" class="font-medium text-zinc-900 hover:text-brand-600">{{ $product->name }}</a>
                                            <div class="text-xs text-zinc-400">{{ $product->sku ?? 'No SKU' }}@if($product->brand) · {{ $product->brand }}@endif</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-zinc-500">{{ $product->category ?? '—' }}</td>
                                <td>
                                    @if($product->variants->isNotEmpty())
                                        <span class="text-zinc-700">{{ $product->variants->count() }}</span>
                                        <span class="text-xs text-zinc-400">{{ collect($product->variants)->map(fn($v) => $v->variantLabel())->filter(fn($l)=>$l!=='—')->take(3)->implode(', ') }}</span>
                                    @else
                                        <span class="text-zinc-300">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex flex-wrap gap-1">
                                        @forelse($product->stockByWarehouse() as $whName => $qty)
                                            <span class="badge {{ $whName === 'Store' ? 'badge-amber' : 'badge-green' }}">{{ $whName }} {{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</span>
                                        @empty
                                            <span class="text-zinc-300">—</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="text-right font-medium text-zinc-900">₱{{ number_format($product->retail_price, 2) }}</td>
                                <td>
                                    <span class="badge {{ $product->isActive() ? 'badge-green' : 'badge-zinc' }}">{{ $product->isActive() ? 'Active' : 'Inactive' }}</span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ route('products.show', $product) }}" class="btn btn-ghost btn-sm">Open</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-zinc-200 px-5 py-3">{{ $products->links() }}</div>
        @else
            <div class="flex flex-col items-center justify-center px-6 py-16 text-center">
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100">
                    <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0a48.667 48.667 0 00-16.5 0"/></svg>
                </div>
                <p class="mt-4 text-sm font-medium text-zinc-900">No products found</p>
                <p class="mt-1 text-sm text-zinc-500">{{ auth()->user()->canCreateItems() ? 'Create one or import your products file.' : 'Stock arrives here by transfer from the stockroom.' }}</p>
                @if(auth()->user()->canCreateItems())
                    <a href="{{ route('products.create') }}" class="btn btn-primary mt-4">New Product</a>
                @endif
            </div>
        @endif
    </div>
</div>

<script>
    function productTable() {
        return {
            selected: [],
            pageIds: @json($products->pluck('id')),
            get allChecked() {
                return this.pageIds.length > 0 && this.pageIds.every(id => this.selected.includes(id));
            },
            toggleAll(e) {
                if (e.target.checked) {
                    this.selected = [...new Set([...this.selected, ...this.pageIds])];
                } else {
                    this.selected = this.selected.filter(id => !this.pageIds.includes(id));
                }
            },
            submitBulk(action) {
                if (action === 'delete' && !confirm('Move the selected products to trash?')) return;
                this.$refs.bulkAction.value = action;
                this.$refs.bulkForm.submit();
            },
        };
    }
</script>

@endsection
