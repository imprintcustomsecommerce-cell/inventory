@extends('layouts.app')

@section('title', 'New Product')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('products.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to products
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">New product</h1>
        <p class="mt-1 text-sm text-zinc-500">Add a product, then pick the sizes it comes in.</p>
    </div>

    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="card divide-y divide-zinc-200">
        @csrf
        <div class="space-y-5 p-6">
            @if($warehouses->isNotEmpty())
                <div>
                    <label class="label">Warehouse</label>
                    <select name="warehouse_id" required class="select">
                        <option value="">Select warehouse</option>
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @elseif(auth()->user()->warehouse)
                <div class="flex items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm">
                    <span class="text-zinc-600">Adding to</span>
                    <span class="badge badge-zinc">{{ auth()->user()->warehouse->name }}</span>
                </div>
            @endif

            <div>
                <label class="label">Product name</label>
                <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Makina Oversized Shirt" class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Category</label>
                    <input type="text" name="category" value="{{ old('category') }}" placeholder="e.g. Cotton Shirt" class="input">
                </div>
                <div>
                    <label class="label">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand') }}" placeholder="e.g. Imprint Customs" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="label">Retail price (₱)</label>
                    <input type="number" name="retail_price" value="{{ old('retail_price', 0) }}" min="0" step="0.01" class="input">
                </div>
                <div>
                    <label class="label">Cost / imported (₱)</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', 0) }}" min="0" step="0.01" class="input">
                </div>
                <div>
                    <label class="label">SKU <span class="font-normal text-zinc-400">(optional)</span></label>
                    <input type="text" name="sku" value="{{ old('sku') }}" class="input">
                </div>
            </div>

            <div>
                <label class="label">Material <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="text" name="material" value="{{ old('material') }}" placeholder="e.g. 100% Cotton - Oversized Fit" class="input">
            </div>

            <div>
                <label class="label">Sizes</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(\App\Models\InventoryItem::SIZES as $s)
                        <label class="inline-flex cursor-pointer items-center gap-2 rounded-lg border border-zinc-200 px-3 py-1.5 text-sm hover:bg-zinc-50">
                            <input type="checkbox" name="sizes[]" value="{{ $s }}" class="rounded border-zinc-300 text-zinc-900 accent-brand-400">
                            {{ $s }}
                        </label>
                    @endforeach
                </div>
                <p class="mt-1.5 text-xs text-zinc-400">Each checked size becomes a stock line (starting at 0). You can add stock afterward.</p>
            </div>

            <div>
                <label class="label">Photo <span class="font-normal text-zinc-400">(optional)</span></label>
                <input type="file" name="image" accept="image/*" class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
            </div>

            <div>
                <label class="label">Description <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="description" rows="3" class="textarea">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('products.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Create product</button>
        </div>
    </form>
</div>

@endsection
