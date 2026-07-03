@extends('layouts.app')

@section('title', 'Edit Product')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('products.show', $product) }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to product
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Edit product</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $product->name }} — manage sizes on the product page.</p>
    </div>

    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data" class="card divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        <div class="space-y-5 p-6">
            @if($warehouses->isNotEmpty())
                <div>
                    <label class="label">Warehouse</label>
                    <select name="warehouse_id" required class="select">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id', $product->warehouse_id) == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <div>
                <label class="label">Product name</label>
                <input type="text" name="name" value="{{ old('name', $product->name) }}" required class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Category</label>
                    <input type="text" name="category" value="{{ old('category', $product->category) }}" class="input">
                </div>
                <div>
                    <label class="label">Brand</label>
                    <input type="text" name="brand" value="{{ old('brand', $product->brand) }}" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="label">Retail price (₱)</label>
                    <input type="number" name="retail_price" value="{{ old('retail_price', $product->retail_price) }}" min="0" step="0.01" class="input">
                </div>
                <div>
                    <label class="label">Cost / imported (₱)</label>
                    <input type="number" name="cost_price" value="{{ old('cost_price', $product->cost_price) }}" min="0" step="0.01" class="input">
                </div>
                <div>
                    <label class="label">SKU</label>
                    <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="input">
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Material</label>
                    <input type="text" name="material" value="{{ old('material', $product->material) }}" class="input">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="select">
                        <option value="active" {{ old('status', $product->status ?? 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="label">Photo</label>
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50">
                        @if($product->imageUrl())
                            <img src="{{ $product->imageUrl() }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-6 w-6 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5z"/></svg>
                        @endif
                    </div>
                    <input type="file" name="image" accept="image/*" class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800">
                </div>
            </div>

            <div>
                <label class="label">Description</label>
                <textarea name="description" rows="3" class="textarea">{{ old('description', $product->description) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('products.show', $product) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>
</div>

@endsection
