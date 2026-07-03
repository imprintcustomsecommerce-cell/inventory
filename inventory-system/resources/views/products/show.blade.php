@extends('layouts.app')

@section('title', $product->name)

@section('content')

<a href="{{ route('products.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
    Back to products
</a>

<div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    <!-- Product summary -->
    <div class="lg:col-span-1">
        <div class="card overflow-hidden">
            <div class="flex aspect-square items-center justify-center bg-white p-3">
                @if($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="max-h-full max-w-full object-contain">
                @else
                    <svg class="h-16 w-16 text-zinc-200" fill="none" viewBox="0 0 24 24" stroke-width="1.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                @endif
            </div>
            <div class="space-y-3 p-5">
                <div class="flex flex-wrap items-center gap-1.5">
                    @foreach($product->stockByWarehouse() as $whName => $qty)
                        <span class="badge {{ $whName === 'Store' ? 'badge-amber' : 'badge-green' }}">{{ $whName }} {{ rtrim(rtrim(number_format($qty, 2), '0'), '.') }}</span>
                    @endforeach
                    @if($product->category)<span class="badge badge-zinc">{{ $product->category }}</span>@endif
                    @if($product->brand)<span class="text-xs text-zinc-400">{{ $product->brand }}</span>@endif
                </div>
                <h1 class="text-xl font-bold text-zinc-900">{{ $product->name }}</h1>

                <dl class="space-y-2 border-t border-zinc-100 pt-3 text-sm">
                    <div class="flex justify-between"><dt class="text-zinc-500">Retail price</dt><dd class="font-semibold text-zinc-900">₱{{ number_format($product->retail_price, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Cost (imported)</dt><dd class="text-zinc-700">₱{{ number_format($product->cost_price, 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Margin / pc</dt><dd class="font-semibold {{ $product->margin() >= 0 ? 'text-emerald-600' : 'text-red-600' }}">₱{{ number_format($product->margin(), 2) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-zinc-500">Warehouse</dt><dd class="text-zinc-700">{{ $product->warehouse?->name ?? '—' }}</dd></div>
                    @if($product->material)<div class="flex justify-between"><dt class="text-zinc-500">Material</dt><dd class="text-zinc-700">{{ $product->material }}</dd></div>@endif
                    <div class="flex justify-between"><dt class="text-zinc-500">Total stock</dt><dd class="font-semibold text-zinc-900">{{ rtrim(rtrim(number_format($product->totalStock(), 2), '0'), '.') }} pcs</dd></div>
                </dl>

                @if($product->description)
                    <div class="border-t border-zinc-100 pt-3">
                        <p class="text-sm whitespace-pre-line text-zinc-600">{{ $product->description }}</p>
                    </div>
                @endif

                <div class="flex gap-2 border-t border-zinc-100 pt-3">
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-ghost btn-sm">Edit</a>
                    @if(auth()->user()->isAdmin())
                        <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Move “{{ $product->name }}” and its sizes to trash?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Sizes -->
    <div class="lg:col-span-2">
        <div class="card overflow-hidden">
            <div class="border-b border-zinc-200 px-5 py-3">
                <h2 class="text-sm font-semibold text-zinc-900">Sizes &amp; stock</h2>
            </div>

            @if($product->variants->count() > 0)
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Variation</th>
                                <th>SKU</th>
                                <th>Warehouse</th>
                                <th>In stock</th>
                                <th>Minimum</th>
                                <th>Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->variants as $v)
                                <tr>
                                    <td class="font-semibold text-zinc-900">{{ $v->variantLabel() }}</td>
                                    <td class="text-zinc-500">{{ $v->sku ?? '—' }}</td>
                                    <td><span class="badge {{ $v->warehouse?->name === 'Store' ? 'badge-amber' : 'badge-green' }}">{{ $v->warehouse?->name ?? '—' }}</span></td>
                                    <td class="text-zinc-900">{{ $v->current_stock }} {{ $v->unit }}</td>
                                    <td class="text-zinc-500">{{ $v->minimum_stock }}</td>
                                    <td>
                                        <span class="badge {{ $v->isOutOfStock() ? 'badge-red' : ($v->isLowStock() ? 'badge-amber' : 'badge-green') }}">{{ $v->getStatusLabel() }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center justify-end gap-1">
                                            <a href="{{ route('inventory.stockInForm', $v->id) }}" title="Stock in" class="rounded-lg p-2 text-zinc-400 hover:bg-emerald-50 hover:text-emerald-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg></a>
                                            <a href="{{ route('inventory.stockOutForm', $v->id) }}" title="Stock out" class="rounded-lg p-2 text-zinc-400 hover:bg-red-50 hover:text-red-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15"/></svg></a>
                                            <a href="{{ route('inventory.adjustForm', $v->id) }}" title="Adjust" class="rounded-lg p-2 text-zinc-400 hover:bg-amber-50 hover:text-amber-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.43.992a6.932 6.932 0 010 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.542-.56.94-1.11.94h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 010-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.281z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg></a>
                                            <a href="{{ route('inventory.transferForm', $v->id) }}" title="Transfer" class="rounded-lg p-2 text-zinc-400 hover:bg-blue-50 hover:text-blue-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5"/></svg></a>
                                            @if($v->warehouse?->sellsStock())
                                                <a href="{{ route('sales.create', $v->id) }}" title="Sell" class="rounded-lg p-2 text-zinc-400 hover:bg-emerald-50 hover:text-emerald-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg></a>
                                            @endif
                                            <a href="{{ route('inventory.movements', $v->id) }}" title="History" class="rounded-lg p-2 text-zinc-400 hover:bg-blue-50 hover:text-blue-600"><svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="px-5 py-8 text-center text-sm text-zinc-500">No sizes yet. Add one below.</p>
            @endif

            <!-- Add variation -->
            @if(auth()->user()->canCreateItems())
            <form action="{{ route('products.addSize', $product) }}" method="POST" class="border-t border-zinc-200 bg-zinc-50 p-4">
                @csrf
                <p class="label mb-2">Add a variation</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6 lg:items-end">
                    <div>
                        <label class="label">Size</label>
                        <select name="size" required class="select">
                            <option value="">Select</option>
                            @foreach($allSizes as $s)
                                <option value="{{ $s }}">{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label">Color</label>
                        <input type="text" name="color" placeholder="e.g. Navy" class="input">
                    </div>
                    <div>
                        <label class="label">Variation SKU</label>
                        <input type="text" name="sku" placeholder="optional" class="input">
                    </div>
                    <div>
                        <label class="label">Start qty</label>
                        <input type="number" name="current_stock" min="0" step="1" value="0" class="input">
                    </div>
                    <div>
                        <label class="label">Min</label>
                        <input type="number" name="minimum_stock" min="0" step="1" value="0" class="input">
                    </div>
                    <button type="submit" class="btn btn-primary">Add</button>
                </div>
            </form>
            @endif
        </div>
    </div>
</div>

@endsection
