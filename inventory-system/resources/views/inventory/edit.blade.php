@extends('layouts.app')

@section('title', 'Edit Item')

@section('content')

<div class="mx-auto max-w-2xl">
    <a href="{{ route('inventory.index') }}" class="mb-6 inline-flex items-center gap-1.5 text-sm font-medium text-zinc-500 hover:text-zinc-900">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5"/></svg>
        Back to inventory
    </a>

    <div class="mb-6">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900">Edit item</h1>
        <p class="mt-1 text-sm text-zinc-500">{{ $item->name }}</p>
    </div>

    <form action="{{ route('inventory.update', $item->id) }}" method="POST" enctype="multipart/form-data" class="card divide-y divide-zinc-200">
        @csrf
        @method('PUT')
        <div class="space-y-5 p-6">
            @if($warehouses->isNotEmpty())
                <div>
                    <label class="label">Warehouse</label>
                    <select name="warehouse_id" required class="select">
                        @foreach($warehouses as $w)
                            <option value="{{ $w->id }}" {{ old('warehouse_id', $item->warehouse_id) == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="label">Item name</label>
                <input type="text" name="name" value="{{ old('name', $item->name) }}" required class="input @error('name') input-error @enderror">
                @error('name') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <div>
                    <label class="label">Category</label>
                    <select name="category" class="select">
                        <option value="">Select category</option>
                        @foreach(\App\Models\InventoryItem::CATEGORIES as $opt)
                            <option value="{{ $opt }}" {{ old('category', $item->category) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Size</label>
                    <select name="size" class="select">
                        <option value="">—</option>
                        @foreach(\App\Models\InventoryItem::SIZES as $opt)
                            <option value="{{ $opt }}" {{ old('size', $item->size) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="label">Unit</label>
                    <select name="unit" required class="select @error('unit') input-error @enderror">
                        @foreach(['pcs','sets','packs','boxes'] as $opt)
                            <option value="{{ $opt }}" {{ old('unit', $item->unit) == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                <div>
                    <label class="label">Current stock</label>
                    <input type="number" value="{{ $item->current_stock }}" disabled class="input cursor-not-allowed bg-zinc-100 text-zinc-500">
                    <p class="mt-1.5 text-xs text-zinc-400">Change via Stock In / Stock Out.</p>
                </div>
                <div>
                    <label class="label">Minimum stock alert</label>
                    <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $item->minimum_stock) }}" min="0" step="0.01" required class="input @error('minimum_stock') input-error @enderror">
                    @error('minimum_stock') <p class="form-error">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="label">Unit cost <span class="font-normal text-zinc-400">(₱ per {{ $item->unit }}, optional)</span></label>
                <input type="number" name="unit_cost" value="{{ old('unit_cost', $item->unit_cost) }}" min="0" step="0.01" class="input @error('unit_cost') input-error @enderror">
                <p class="mt-1.5 text-xs text-zinc-400">Used to compute material cost on projects.</p>
                @error('unit_cost') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Photo <span class="font-normal text-zinc-400">(optional)</span></label>
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center overflow-hidden rounded-lg border border-zinc-200 bg-zinc-50">
                        @if($item->imageUrl())
                            <img src="{{ $item->imageUrl() }}" alt="{{ $item->name }}" class="h-full w-full object-cover">
                        @else
                            <svg class="h-6 w-6 text-zinc-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                        @endif
                    </div>
                    <input type="file" name="image" accept="image/*"
                           class="block w-full text-sm text-zinc-600 file:mr-4 file:rounded-lg file:border-0 file:bg-zinc-900 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-zinc-800 @error('image') text-red-600 @enderror">
                </div>
                <p class="mt-1.5 text-xs text-zinc-400">Upload a new photo to replace the current one.</p>
                @error('image') <p class="form-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label">Remarks <span class="font-normal text-zinc-400">(optional)</span></label>
                <textarea name="remarks" rows="3" class="textarea">{{ old('remarks', $item->remarks) }}</textarea>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 bg-zinc-50 px-6 py-4">
            <a href="{{ route('inventory.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
    </form>

    <!-- Danger zone -->
    @if(auth()->user()->isAdmin())
    <div class="mt-6 rounded-xl border border-red-200 bg-red-50 p-6">
        <h3 class="text-sm font-semibold text-red-900">Delete this item</h3>
        <p class="mt-1 text-sm text-red-700">Permanently removes the item and its entire movement history. This cannot be undone.</p>
        <form action="{{ route('inventory.destroy', $item->id) }}" method="POST" onsubmit="return confirm('Delete “{{ $item->name }}” and all its history? This cannot be undone.');" class="mt-4">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">Delete item</button>
        </form>
    </div>
    @endif
</div>

@endsection
